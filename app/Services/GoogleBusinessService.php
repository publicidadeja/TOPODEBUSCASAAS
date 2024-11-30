<?php

namespace App\Services;

use App\Models\Business;
use Google\Service\MyBusinessAccountManagement;
use Google\Service\MyBusinessBusinessInformation;

class GoogleBusinessService
{
    protected $googleAuth;

    public function __construct(GoogleAuthService $googleAuth)
    {
        $this->googleAuth = $googleAuth;
    }

    public function importBusinesses($user)
    {
        try {
            $client = $this->googleAuth->getGoogleClient();
            
            // Inicializa os serviços do GMB
            $accountManagement = new MyBusinessAccountManagement($client);
            $businessInfo = new MyBusinessBusinessInformation($client);

            // Lista todas as contas
            $accounts = $accountManagement->accounts->listAccounts();
            
            $importedBusinesses = [];

            foreach ($accounts as $account) {
                // Lista todas as localizações para cada conta
                $locations = $businessInfo->accounts_locations->listAccountsLocations(
                    $account->name
                );

                foreach ($locations->locations as $location) {
                    // Cria ou atualiza o negócio no banco de dados
                    $business = Business::updateOrCreate(
                        ['google_business_id' => $location->name],
                        [
                            'user_id' => $user->id,
                            'name' => $location->locationName,
                            'address' => $location->address->addressLines[0] ?? '',
                            'phone' => $location->phoneNumbers->primary ?? '',
                            'website' => $location->websiteUri ?? '',
                            'segment' => $location->primaryCategory->displayName ?? '',
                            'google_account_id' => $account->name,
                            'google_location_id' => $location->name,
                        ]
                    );

                    $importedBusinesses[] = $business;
                }
            }

            return $importedBusinesses;

        } catch (\Exception $e) {
            \Log::error('Erro ao importar negócios do GMB: ' . $e->getMessage());
            throw $e;
        }
    }
}