<?php

namespace App\Console\Commands;

use App\Models\AccessLog;
use Illuminate\Console\Command;

class PruneAccessLogs extends Command
{
    protected $signature = 'access-logs:prune';

    protected $description = 'Delete access logs older than seven days in small batches';

    public function handle(): int
    {
        $cutoff = now()->subDays(7);
        $deleted = 0;

        do {
            $ids = AccessLog::where('accessed_at', '<', $cutoff)
                ->orderBy('id')->limit(1000)->pluck('id');

            $deleted += AccessLog::whereIn('id', $ids)->delete();
        } while ($ids->isNotEmpty());

        $this->info("Đã xóa {$deleted} nhật ký quá 7 ngày.");

        return self::SUCCESS;
    }
}
