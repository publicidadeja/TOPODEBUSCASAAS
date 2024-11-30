<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Business;
use App\Services\GoogleBusinessService;
use Illuminate\Support\Facades\Log;

class UpdateBusinessAnalytics extends Command
{
    protected $signature = 'analytics:update';
    protected $description = 'Atualiza os analytics de todos os negócios';

    protected $googleService;

    public function __construct(GoogleBusinessService $googleService)
    {
        parent::__construct();
        $this->googleService = $googleService;
    }

    public function handle()
    {
        $this->info('Iniciando atualização dos analytics...');
        
        $businesses = Business::all();

        if ($businesses->isEmpty()) {
            $this->warn('Nenhum negócio encontrado para atualizar.');
            return Command::SUCCESS;
        }

        $count = 0;
        $errors = 0;

        foreach ($businesses as $business) {
            $this->info("Processando: {$business->name}");
            
            try {
                if ($this->googleService->updateAnalytics($business)) {
                    $count++;
                    $this->info("✓ Analytics atualizados com sucesso para: {$business->name}");
                } else {
                    $errors++;
                    $this->error("✗ Falha ao atualizar analytics para: {$business->name}");
                }
            } catch (\Exception $e) {
                $errors++;
                Log::error("Erro ao atualizar analytics para {$business->name}: " . $e->getMessage());
                $this->error("✗ Erro ao atualizar {$business->name}: " . $e->getMessage());
            }
        }

        $this->newLine();
        $this->info("Atualização concluída!");
        $this->info("Negócios atualizados com sucesso: {$count}");
        
        if ($errors > 0) {
            $this->warn("Negócios com erro: {$errors}");
        }

        return Command::SUCCESS;
    }
}