<?php

namespace App\Services;

use Google_Client;
use Google\Service\MyBusinessAccountManagement;

class GoogleAuthService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Google_Client();
        $this->client->setClientId(config('services.google.client_id'));
        $this->client->setClientSecret(config('services.google.client_secret'));
        $this->client->setRedirectUri(config('services.google.redirect'));
        $this->client->setScopes(config('services.google.gmb_scope'));
    }

    public function getAuthUrl()
    {
        return $this->client->createAuthUrl();
    }

    public function handleCallback($code)
    {
        $token = $this->client->fetchAccessTokenWithAuthCode($code);
        return $token;
    }

    public function setAccessToken($token)
    {
        $this->client->setAccessToken($token);
    }

    public function getGoogleClient()
    {
        return $this->client;
    }

    protected function getCachedBusinessData($businessId)
{
    return Cache::remember('business.'.$businessId, 3600, function() use ($businessId) {
        return $this->fetchBusinessDataFromGoogle($businessId);
    });
}
}