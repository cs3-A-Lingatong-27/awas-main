<?php

namespace App\Console\Commands;

use App\Models\Assessment;
use Illuminate\Console\Command;

class CloseAssessmentConfirmations extends Command
{
    protected $signature = 'app:close-assessment-confirmations';
    protected $description = 'Auto-mark pending assessments as conducted at end of day';

    public function handle(): int
    {
        $now = now();

        $updated = Assessment::query()
            ->where('confirmation_status', 'pending')
            ->update([
                'confirmation_status' => 'conducted',
                'conducted_at' => $now,
            ]);

        $this->info("Assessments auto-closed: {$updated}");
        return self::SUCCESS;
    }
}
