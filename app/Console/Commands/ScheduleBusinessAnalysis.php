<?php

namespace App\Console\Commands;

use App\Jobs\AnalyzeBusinessInsights;
use App\Models\Business;
use Illuminate\Console\Command;

class ScheduleBusinessAnalysis extends Command
{
    protected $signature = 'business:analyze';
    protected $description = 'Agenda análises automáticas para todos os negócios';

    public function handle()
    {
        Business::active()->chunk(100, function ($businesses) {
            foreach ($businesses as $business) {
                AnalyzeBusinessInsights::dispatch($business)
                    ->delay(now()->addMinutes(rand(1, 60)));
            }
        });

        $this->info('Análises agendadas com sucesso!');
    }
}