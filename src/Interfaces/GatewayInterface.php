<?php

namespace Raion\Gateways\Interfaces;

use Raion\Gateways\Models\GatewayResponse;

interface GatewayInterface {
    public function createTransaction(string $id, int $amount, string $currency, string $description, string $email): GatewayResponse;
    public function getTransactionInProcess(string $token);
    public function getTransaction(string $token, string $id);
    public function confirmTransaction(string $token, ?array $data = []): array;
    public function name(): string;
    public function getRedirectUrl(string $url, string $token): string;
    public function getConfirmationUrl(): string;
    public function getResultUrl(string $id): string;

}