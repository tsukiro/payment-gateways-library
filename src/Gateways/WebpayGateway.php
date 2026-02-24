<?php
namespace Raion\Gateways\Gateways;

use Psr\Log\LoggerInterface;
use Raion\Gateways\Config\ConfigKeys;
use Raion\Gateways\Logging\NullLogger;
use Raion\Gateways\Models\GatewayResponse;
use Raion\Gateways\Config\GatewayConfig;
use Exception;
use Raion\Gateways\Interfaces\GatewayInterface;
use Raion\Gateways\Models\Gateways;
use GuzzleHttp\Exception\GuzzleException;
use Raion\Gateways\Exceptions\TransactionException;
use Raion\Gateways\Validation\TransactionValidator;
use Transbank\Webpay\Options;
use Transbank\Webpay\WebpayPlus\Exceptions\TransactionCommitException;
use Transbank\Webpay\WebpayPlus\Exceptions\TransactionCreateException;
use Transbank\Webpay\WebpayPlus\Exceptions\TransactionStatusException;
use Transbank\Webpay\WebpayPlus\Transaction;

class WebpayGateway implements GatewayInterface
{
    private Transaction $transaction;
    private string $baseUrl;
    private LoggerInterface $logger;
    private TransactionValidator $validator;

    /**
     * Constructor que inicializa la configuración de Transbank
     */
    public function __construct(?LoggerInterface $logger = null, ?TransactionValidator $validator = null)
    {
        $apiKey = GatewayConfig::get("TRANSBANK_API_KEY", Options::INTEGRATION_API_KEY);
        $commerceCode = GatewayConfig::get("TRANSBANK_COMMERCE_CODE", '597055555532');
        $environment = GatewayConfig::get("TRANSBANK_ENVIRONMENT", Options::ENVIRONMENT_INTEGRATION);

        $options = new Options($apiKey, $commerceCode, $environment);
        $this->transaction = new Transaction($options);
        $this->baseUrl = GatewayConfig::get(ConfigKeys::BASE_URL);
        $this->logger = $logger ?? new NullLogger();
        $this->validator = $validator ?? new TransactionValidator();
    }

    /**
     * Set a logger instance
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
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
        $this->logger->info('Creating Webpay transaction', [
            'gateway' => 'webpay',
            'order_id' => $id,
            'amount' => $amount
        ]);

        // Validar parámetros
        try {
            $this->validator->validateTransaction('webpay', $id, $amount, $currency, $description, $email);
        } catch (\Exception $e) {
            $this->logger->error('Validation failed for Webpay transaction', [
                'gateway' => 'webpay',
                'order_id' => $id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }

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

            $this->logger->info('Webpay transaction created successfully', [
                'gateway' => 'webpay',
                'order_id' => $id,
                'token' => $resp->getToken()
            ]);

            return new GatewayResponse($resp->getToken(), $resp->getUrl());
        } catch (TransactionCreateException | GuzzleException $e) {
            $this->logger->error('Failed to create Webpay transaction', [
                'gateway' => 'webpay',
                'order_id' => $id,
                'error' => $e->getMessage()
            ]);
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

    /**
     * Confirm a transaction (unified method for TransactionManager)
     *
     * @param string $token Transaction token
     * @return array Confirmation data
     * @throws TransactionException
     */
    public function confirmTransaction(string $token, ?array $data = []): array
    {
        // Webpay uses commit to confirm, which is already called in getTransactionInProcess
        return $this->getTransactionInProcess($token);
    }
}