<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\GoogleBusinessService;
use Illuminate\Console\Command;

class TestGoogleImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:google-import {user_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Testa a importação de negócios do Google My Business';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->argument('user_id') ?? 1;
        $user = User::find($userId);

        if (!$user) {
            $this->error("Usuário com ID {$userId} não encontrado.");
            return 1;
        }

        if (!$user->google_token) {
            $this->error("Usuário não possui token do Google configurado.");
            return 1;
        }

        $this->info("Iniciando teste de importação para o usuário: {$user->name} (ID: {$user->id})");

        try {
            $service = app(GoogleBusinessService::class);
            
            $this->info("Token do Google encontrado: " . ($user->google_token ? 'Sim' : 'Não'));
            
            $result = $service->importBusinesses($user);
            
            $this->info('Importação concluída com sucesso!');
            
            $businesses = $user->businesses()->get();
            
            if ($businesses->isEmpty()) {
                $this->warn('Nenhum negócio encontrado após a importação.');
            } else {
                $this->info("Total de negócios importados: " . $businesses->count());
                
                $this->table(
                    ['ID', 'Nome', 'Endereço', 'Telefone', 'Status'],
                    $businesses->map(function($business) {
                        return [
                            $business->id,
                            $business->name,
                            $business->address ?? 'N/A',
                            $business->phone ?? 'N/A',
                            $business->status ?? 'Ativo'
                        ];
                    })
                );
            }

            return 0;
        } catch (\Exception $e) {
            $this->error("Erro durante a importação: " . $e->getMessage());
            $this->error("Stack trace:");
            $this->error($e->getTraceAsString());
            return 1;
        }
    }
}