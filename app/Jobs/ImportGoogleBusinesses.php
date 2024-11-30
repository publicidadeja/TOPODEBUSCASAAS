<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\GoogleBusinessService;

class ImportGoogleBusinesses implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;

    /**
     * Create a new job instance.
     */
    public function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     */
    public function handle(GoogleBusinessService $service)
{
    \Log::info('Iniciando importação de negócios para o usuário: ' . $this->user->id);
    try {
        $result = $service->importBusinesses($this->user);
        \Log::info('Importação concluída', ['result' => $result]);
    } catch (\Exception $e) {
        \Log::error('Erro na importação', ['error' => $e->getMessage()]);
        throw $e;
    }
} }