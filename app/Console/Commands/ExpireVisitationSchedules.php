<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Enums\VisitationScheduleStatusEnum;
use App\Models\VisitationSchedule;
use Carbon\Carbon;

class ExpireVisitationSchedules extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'visitation:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();

        $expiredCount = 0;

        $schedules = VisitationSchedule::query()
    ->whereDate('visitDate', $now->toDateString())
    ->where('published', 1)
    ->where('status', VisitationScheduleStatusEnum::NOT_YET->value)
    ->get();

        foreach ($schedules as $schedule) {
            $visitTime = Carbon::parse(
                $schedule->visitDate . ' ' . ($schedule->visitEndTime ?: $schedule->visitTime),
                'Asia/Ho_Chi_Minh'
            );

            if ($now->greaterThan($visitTime)) {
                $schedule->update([
                    'status' => VisitationScheduleStatusEnum::EXPIRED->value,
                ]);

                $expiredCount++;
            }
        }

        $this->info("Đã cập nhật {$expiredCount} lịch sang EXPIRED.");

        return self::SUCCESS;
    }
}
