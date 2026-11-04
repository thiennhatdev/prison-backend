<?php

namespace Tests\Feature;

use App\Models\ContentView;
use App\Repositories\PostRepository;
use App\Repositories\PrisonRuleRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Schema;
use Mockery\MockInterface;
use Tests\TestCase;

class ContentViewTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        (require database_path('migrations/2026_11_04_000000_create_content_views_table.php'))->up();
        $this->withoutMiddleware();
    }

    public function test_successful_detail_requests_count_each_view_for_the_correct_content(): void
    {
        $this->mock(PostRepository::class, function (MockInterface $mock) {
            $mock->shouldReceive('postDetail')->with('7')->twice()->andReturn(['id' => 7]);
        });
        $this->mock(PrisonRuleRepository::class, function (MockInterface $mock) {
            $mock->shouldReceive('detail')->with('8')->once()->andReturn(['id' => 8]);
        });

        $this->getJson('/api/post/7')->assertOk()->assertJsonPath('data.id', 7);
        $this->getJson('/api/post/7')->assertOk();
        $this->getJson('/api/prison-rule/8')->assertOk();

        $this->assertSame(2, ContentView::where('content_type', ContentView::POST)->count());
        $this->assertDatabaseHas('content_views', ['content_type' => ContentView::PRISON_RULE, 'content_id' => 8]);
        $this->assertDatabaseCount('content_views', 3);
    }

    public function test_missing_content_does_not_count_as_a_view(): void
    {
        $this->mock(PostRepository::class, function (MockInterface $mock) {
            $mock->shouldReceive('postDetail')->andThrow(new ModelNotFoundException());
        });
        $this->mock(PrisonRuleRepository::class, function (MockInterface $mock) {
            $mock->shouldReceive('detail')->andThrow(new ModelNotFoundException());
        });

        $this->getJson('/api/post/99')->assertNotFound();
        $this->getJson('/api/prison-rule/99')->assertNotFound();
        $this->assertDatabaseCount('content_views', 0);
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('content_views');
        parent::tearDown();
    }
}
