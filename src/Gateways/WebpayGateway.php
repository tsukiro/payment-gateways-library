<?php
namespace Raion\Gateways\Gateways;

use Raion\Gateways\Config\ConfigKeys;
use Raion\Gateways\Models\GatewayResponse;
use Raion\Gateways\Config\GatewayConfig;
use Exception;
use Raion\Gateways\Interfaces\GatewayInterface;
use Raion\Gateways\Models\Gateways;
use GuzzleHttp\Exception\GuzzleException;
use Raion\Gateways\Exceptions\TransactionException;
use Transbank\Webpay\Options;
use Transbank\Webpay\WebpayPlus\Exceptions\TransactionCommitException;
use Transbank\Webpay\WebpayPlus\Exceptions\TransactionCreateException;
use Transbank\Webpay\WebpayPlus\Exceptions\TransactionStatusException;
use Transbank\Webpay\WebpayPlus\Transaction;

class WebpayGateway implements GatewayInterface
{
    private Transaction $transaction;
    private string $baseUrl;

    /**
     * Constructor que inicializa la configuración de Transbank
     */
    public function __construct()
    {
        $apiKey = GatewayConfig::get("TRANSBANK_API_KEY", Options::INTEGRATION_API_KEY);
        $commerceCode = GatewayConfig::get("TRANSBANK_COMMERCE_CODE", '597055555532');
        $environment = GatewayConfig::get("TRANSBANK_ENVIRONMENT", Options::ENVIRONMENT_INTEGRATION);

        $options = new Options($apiKey, $commerceCode, $environment);
        $this->transaction = new Transaction($options);
        $this->baseUrl = GatewayConfig::get(ConfigKeys::BASE_URL);
    }

    /**
     * Creates a new transaction in Webpay
     *
     * @param string $id Order identifier
     * @param int $amount Transaction amount
     * @param string $currency Currency (not used in Webpay)
     * @param string $description Transaction description
     * @param string $email Customer email
     * @return GatewayResponse
     * @throws TransactionException
     */
    public function createTransaction(string $id, int $amount, string $currency, string $description, string $email): GatewayResponse
    {
        try {
            // We use $description as sessionId to have a more meaningful value
            $sessionId = substr(preg_replace('/[^a-zA-Z0-9]/', '', $description), 0, 20);

            // Get callback URL from config or use default
            $confirmationUrl = GatewayConfig::get(
                "WEBPAY_CONFIRMATION_URL", 
                $this->baseUrl . "/pago/confirmar/webpay/{id}"
            );
            
            // Replace {id} placeholder with actual order ID
            $confirmationUrl = str_replace('{id}', $id, $confirmationUrl);

            $resp = $this->transaction->create(
                $id,
                $sessionId,
                $amount,
                $confirmationUrl
            );

            return new GatewayResponse($resp->getToken(), $resp->getUrl());
        } catch (TransactionCreateException | GuzzleException $e) {
            throw TransactionException::creationFailed('Webpay', $e->getMessage(), $e);
        }
    }

    /**
     * Gets the status of a transaction in process
     *
     * @param string $token Transaction token
     * @param string $id Order ID
     * @return array Transaction data
     * @throws TransactionException
     */
    public function getTransactionInProcess(string $token): array
    {
        try {
            $response = $this->transaction->commit($token);

            // Convert to array to maintain consistency with other implementations
            return [
                'status' => $response->getStatus(),
                'amount' => $response->getAmount(),
                'buyOrder' => $response->getBuyOrder(),
                'authorizationCode' => $response->getAuthorizationCode(),
                'paymentTypeCode' => $response->getPaymentTypeCode(),
                'responseCode' => $response->getResponseCode(),
                'transactionDate' => $response->getTransactionDate(),
                'vci' => $response->getVci()
            ];
        } catch (TransactionCommitException | GuzzleException $e) {
            throw TransactionException::processingFailed('Webpay', $e->getMessage(), $e);
        }
    }

    /**
     * Gets the gateway name
     */
    public function name(): string
    {
        return Gateways::Webpay->value;
    }

    /**
     * Generates the redirect URL with token
     */
    public function getRedirectUrl(string $url, string $token): string
    {
        return $url . '?token_ws=' . $token;
    }

    /**
     * Gets the confirmation URL
     */
    public function getConfirmationUrl(): string
    {
        return $this->baseUrl . "/pago/confirmar/webpay";
    }

    /**
     * Gets the result URL for an order
     */
    public function getResultUrl(string $id): string
    {
        return $this->baseUrl . "/pago/resultado/webpay/" . $id;
    }

    /**
     * Gets the complete details of a transaction
     *
     * @param string $token Transaction token
     * @param string $id Order ID
     * @return array Transaction data
     * @throws TransactionException
     */
    public function getTransaction(string $token, string $id): array
    {
        try {
            $response = $this->transaction->status($token);

            return [
                'status' => $response->getStatus(),
                'amount' => $response->getAmount(),
                'buyOrder' => $response->getBuyOrder(),
                'authorizationCode' => $response->getAuthorizationCode(),
                'paymentTypeCode' => $response->getPaymentTypeCode(),
                'responseCode' => $response->getResponseCode(),
                'transactionDate' => $response->getTransactionDate(),
                'vci' => $response->getVci()
            ];
        } catch (TransactionStatusException | GuzzleException $e) {
            throw TransactionException::statusRetrievalFailed('Webpay', $token, $e);
        }
    }
}