<?php

namespace Tests\Feature;

use App\Http\Middleware\LogAccess;
use App\Models\AccessLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AccessLogTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        (require database_path('migrations/2026_11_04_000001_create_access_logs_table.php'))->up();
    }

    public function test_records_final_responses_including_errors_and_excludes_sensitive_input(): void
    {
        Route::get('/log-test', fn () => response('OK'));
        Route::get('/log-error', fn () => abort(500));

        $this->withHeader('User-Agent', 'Test Browser')->get('/log-test?token=secret')->assertOk();
        $this->getJson('/missing-log-test')->assertNotFound();
        $this->getJson('/log-error')->assertStatus(500);

        $this->assertDatabaseHas('access_logs', ['path' => '/log-test', 'status' => 200, 'user_agent' => 'Test Browser']);
        $this->assertDatabaseHas('access_logs', ['path' => '/missing-log-test', 'status' => 404]);
        $this->assertDatabaseHas('access_logs', ['path' => '/log-error', 'status' => 500]);
        $this->assertStringNotContainsString('secret', AccessLog::first()->toJson());
    }

    public function test_verification_tokens_are_redacted_and_untrusted_forwarded_ip_is_ignored(): void
    {
        $request = Request::create('/api/visitation-schedule/verify/private-token', 'GET', [], [], [], [
            'REMOTE_ADDR' => '192.0.2.1',
            'HTTP_X_FORWARDED_FOR' => '198.51.100.1',
        ]);
        $middleware = new LogAccess();
        $response = $middleware->handle($request, fn () => response('OK'));
        $middleware->terminate($request, $response);

        $this->assertDatabaseHas('access_logs', [
            'path' => '/api/visitation-schedule/verify/[redacted]',
            'ip' => '192.0.2.1',
        ]);
    }

    public function test_log_page_requires_admin_authentication(): void
    {
        $this->get('/admin/access-logs')->assertRedirect();
    }

    public function test_prune_removes_only_logs_older_than_seven_days(): void
    {
        $this->travelTo(now()->startOfSecond());
        foreach ([now()->subDays(8), now()->subDays(7), now()] as $date) {
            AccessLog::create([
                'accessed_at' => $date, 'status' => 200,
                'method' => 'GET', 'path' => '/example', 'duration_ms' => 10,
            ]);
        }

        $this->artisan('access-logs:prune')->assertSuccessful();
        $this->assertDatabaseCount('access_logs', 2);
        $this->assertSame(0, AccessLog::where('accessed_at', '<', now()->subDays(7))->count());
        $this->travelBack();
    }

    public function test_filters_combine_ip_date_and_errors(): void
    {
        foreach ([
            ['192.0.2.1', '2026-11-04 12:00:00', 500],
            ['192.0.2.1', '2026-11-04 12:00:00', 200],
            ['192.0.2.2', '2026-11-04 12:00:00', 404],
            ['192.0.2.1', '2026-11-03 12:00:00', 404],
        ] as [$ip, $date, $status]) {
            AccessLog::create([
                'ip' => $ip, 'accessed_at' => $date, 'status' => $status,
                'method' => 'GET', 'path' => '/example', 'duration_ms' => 10,
            ]);
        }

        $controller = new \App\Http\Controllers\Admin\AccessLogController();
        $view = $controller->index(Request::create('/admin/access-logs', 'GET', [
            'ip' => '192.0.2.1', 'from_date' => '2026-11-04',
            'to_date' => '2026-11-04', 'errors_only' => '1',
        ]));
        $this->assertSame(1, $view->getData()['logs']->total());

        $view = $controller->index(Request::create('/admin/access-logs', 'GET', ['to_date' => '2026-11-03']));
        $this->assertSame(1, $view->getData()['logs']->total());
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('access_logs');
        parent::tearDown();
    }
}
