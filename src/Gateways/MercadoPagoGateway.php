<?php

namespace Raion\Gateways\Gateways;

use Raion\Gateways\Config\ConfigKeys;
use Raion\Gateways\Config\GatewayConfig;
use Raion\Gateways\Interfaces\GatewayInterface;
use Raion\Gateways\Models\GatewayResponse;
use Raion\Gateways\Exceptions\TransactionException;

use MercadoPago\Client\Common\RequestOptions;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Exceptions\MPApiException;
use MercadoPago\MercadoPagoConfig;
use PSpell\Config;
use Raion\Gateways\Models\Gateways;

class MercadoPagoGateway implements GatewayInterface
{
    public function __construct()
    {
        
        MercadoPagoConfig::setAccessToken(GatewayConfig::get(
            ConfigKeys::MERCADOPAGO_ACCESS_TOKEN
        ));
        
        MercadoPagoConfig::setRuntimeEnviroment(GatewayConfig::get(
            ConfigKeys::MERCADOPAGO_RUNTIME_ENVIRONMENT,
            MercadoPagoConfig::LOCAL
        ));
    }

    /**
     * @throws TransactionException
     * @throws MPApiException
     */
    public function createTransaction(string $id, int $amount, string $currency, string $description, string $email): GatewayResponse
    {
        try {

            $client = new PreferenceClient();
            $baseUrl = GatewayConfig::get(ConfigKeys::BASE_URL);

            // Get callback URLs from config or use defaults
            $successUrl = GatewayConfig::get(
                "MERCADOPAGO_SUCCESS_URL",
                $baseUrl . "/pago/confirmar/mercadopago/{id}"
            );
            $failureUrl = GatewayConfig::get(
                "MERCADOPAGO_FAILURE_URL",
                $baseUrl . "/pago/confirmar/mercadopago/{id}"
            );
            $pendingUrl = GatewayConfig::get(
                "MERCADOPAGO_PENDING_URL",
                $baseUrl . "/pago/confirmar/mercadopago/{id}"
            );

            // Replace {id} placeholder with actual order ID
            $successUrl = str_replace('{id}', $id, $successUrl);
            $failureUrl = str_replace('{id}', $id, $failureUrl);
            $pendingUrl = str_replace('{id}', $id, $pendingUrl);

            $config = [
                "items" => array(
                    array(
                        "title" => $description,
                        "quantity" => 1,
                        "unit_price" => $amount
                    )
                ),
                "back_urls" => [
                    "success" => $successUrl,
                    "failure" => $failureUrl,
                    "pending" => $pendingUrl,
                ],
                "auto_return" => "approved",
                "external_reference" => $id,
            ];

            echo "1 Getting response Mercado Pago";
            $accessToken = GatewayConfig::get(ConfigKeys::MERCADOPAGO_ACCESS_TOKEN);
            echo "2 Getting response Mercado Pago";
            $options = new RequestOptions();
            echo "3 Getting response Mercado Pago";
            $options->setAccessToken($accessToken);
            
            echo "4 Getting response Mercado Pago";
            $preference = $client->create($config, $options);
            echo "5 Getting response Mercado Pago";
            //echo $preference->getResponse();

            return new GatewayResponse($preference->id, $baseUrl . "/mercadopago/" . $id);
        } catch (MPApiException $exception) {
            throw TransactionException::creationFailed('MercadoPago', $exception->getMessage(), $exception);
        }
    }

    public function getTransactionInProcess(string $token)
    {
        $client = new PreferenceClient();
        return $client->get($token);
        // TODO: Implement getTransactionInProcess() method.
    }

    public function getTransaction(string $token, string $id)
    {
        $client = new PreferenceClient();
        return $client->get($token);
        // TODO: Implement getTransaction() method.
    }

    public function name(): string
    {
        return Gateways::MercadoPago->value;
    }

    public function getRedirectUrl(string $url, string $token): string
    {
        return $url;
    }

    public function getConfirmationUrl(): string
    {
        // TODO: Implement getConfirmationUrl() method.
        return "Not implemented";
    }

    public function getResultUrl(string $id): string
    {
        return GatewayConfig::get(ConfigKeys::BASE_URL) . "/pago/resultado/mercadopago/" . $id;
        // TODO: Implement getResultUrl() method.
    }

    protected function authenticate()
    {
        // Getting the access token from .env file (create your own function)
        $mpAccessToken = getenv('mercado_pago_access_token');
        // Set the token the SDK's config
        MercadoPagoConfig::setAccessToken(GatewayConfig::get(ConfigKeys::MERCADOPAGO_ACCESS_TOKEN));
        // (Optional) Set the runtime enviroment to LOCAL if you want to test on localhost
        // Default value is set to SERVER
        MercadoPagoConfig::setRuntimeEnviroment(GatewayConfig::get(ConfigKeys::MERCADOPAGO_RUNTIME_ENVIRONMENT));
    }
}
