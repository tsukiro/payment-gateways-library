<?php

namespace Raion\Gateways\Gateways;

use Psr\Log\LoggerInterface;
use Raion\Gateways\Config\ConfigKeys;
use Raion\Gateways\Config\GatewayConfig;
use Raion\Gateways\Logging\NullLogger;
use Raion\Gateways\Models\GatewayResponse;
use Raion\Gateways\Models\Flow\FlowVerbs;
use Raion\Gateways\Models\Flow\FlowConstants;
use Raion\Gateways\Models\Flow\FlowPaths;
use Raion\Gateways\Exceptions\TransactionException;
use Raion\Gateways\Exceptions\CommunicationException;
use Raion\Gateways\Exceptions\InvalidResponseException;
use Raion\Gateways\Interfaces\GatewayInterface;
use Raion\Gateways\Models\Gateways;
use Raion\Gateways\Models\Flow\FlowConfig;
use Raion\Gateways\Models\Flow\FlowHttpClient;
use Raion\Gateways\Models\Flow\FlowSigner;
use Raion\Gateways\Validation\TransactionValidator;

class FlowGateway implements GatewayInterface
{
    private const DEFAULT_CURRENCY = 'CLP';
    private const DEFAULT_PAYMENT_METHOD = 9; // Documentar qué método es este

    private FlowHttpClient $httpClient;
    private FlowSigner $signer;
    private FlowConfig $config;
    private LoggerInterface $logger;
    private TransactionValidator $validator;

    public function __construct(
        ?FlowHttpClient $httpClient = null,
        ?FlowSigner $signer = null,
        ?FlowConfig $config = null,
        ?LoggerInterface $logger = null,
        ?TransactionValidator $validator = null
    ) {
        $this->httpClient = $httpClient ?? new FlowHttpClient();
        $this->signer = $signer ?? new FlowSigner(GatewayConfig::get(ConfigKeys::FLOW_API_KEY), GatewayConfig::get(ConfigKeys::FLOW_SECRET_KEY));
        $this->config = $config ?? new FlowConfig();
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
     * Creates a new transaction in Flow
     *
     * @param string $id Order identifier
     * @param int $amount Transaction amount
     * @param string $currency Currency (default CLP)
     * @param string $description Transaction description
     * @param string $email Customer email
     * @return GatewayResponse
     * @throws TransactionException
     * @throws InvalidResponseException
     */
    public function createTransaction(string $id, int $amount, string $currency, string $description, string $email): GatewayResponse
    {
        $this->logger->info('Creating Flow transaction', [
            'gateway' => 'flow',
            'order_id' => $id,
            'amount' => $amount,
            'currency' => $currency
        ]);

        // Validar parámetros
        try {
            $this->validator->validateTransaction('flow', $id, $amount, $currency, $description, $email);
        } catch (\Exception $e) {
            $this->logger->error('Validation failed for Flow transaction', [
                'gateway' => 'flow',
                'order_id' => $id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }

        $baseUrl = GatewayConfig::get(ConfigKeys::BASE_URL);
        
        // Get callback URLs from config or use defaults
        $confirmationUrl = GatewayConfig::get(
            "FLOW_CONFIRMATION_URL", 
            $baseUrl . "/pago/confirmar/flow/{id}"
        );
        $returnUrl = GatewayConfig::get(
            "FLOW_RETURN_URL", 
            $baseUrl . "/pago/resultado/flow/{id}"
        );
        
        // Replace {id} placeholder with actual order ID
        $confirmationUrl = str_replace('{id}', $id, $confirmationUrl);
        $returnUrl = str_replace('{id}', $id, $returnUrl);
        
        $params = [
            "commerceOrder" => $id,
            "subject" => $description,
            "currency" => $currency ?: self::DEFAULT_CURRENCY,
            "amount" => $amount,
            "email" => $email,
            "paymentMethod" => self::DEFAULT_PAYMENT_METHOD,
            "urlConfirmation" => $confirmationUrl,
            "urlReturn" => $returnUrl,
        ];

        try {
            $operationResult = $this->send(FlowPaths::PaymentCreate, $params, FlowVerbs::POST);

            if (isset($operationResult["token"]) && isset($operationResult["url"])) {
                $this->logger->info('Flow transaction created successfully', [
                    'gateway' => 'flow',
                    'order_id' => $id,
                    'token' => $operationResult["token"]
                ]);
                return new GatewayResponse($operationResult["token"], $operationResult["url"]);
            } else {
                $this->logger->error('Incomplete Flow API response', [
                    'gateway' => 'flow',
                    'order_id' => $id,
                    'response' => json_encode($operationResult)
                ]);
                throw InvalidResponseException::incompleteResponse(
                    'Flow',
                    'token, url',
                    json_encode($operationResult)
                );
            }
        } catch (InvalidResponseException $e) {
            throw $e;
        } catch (\Exception $exception) {
            $this->logger->error('Failed to create Flow transaction', [
                'gateway' => 'flow',
                'order_id' => $id,
                'error' => $exception->getMessage()
            ]);
            throw TransactionException::creationFailed('Flow', $exception->getMessage(), $exception);
        }
    }

    /**
     * Creates a transaction by email
     *
     * @param array $params Transaction parameters
     * @return array
     */
    public function createTransactionByEmail(array $params): array
    {
        return $this->send(FlowPaths::PaymentCreateEmail, $params, FlowVerbs::POST);
    }

    /**
     * Gets the status of a transaction in process
     *
     * @param string $token Transaction token
     * @param string $id Order ID
     * @return array
     * @throws TransactionException
     */
    public function getTransactionInProcess(string $token): array
    {
        $params = [
            FlowConstants::TOKEN => $token,
        ];
        return $this->send(FlowPaths::PaymentGetStatus, $params, FlowVerbs::GET);
    }

    /**
     * Envía una solicitud a la API de Flow
     *
     * @param string $service Servicio a llamar
     * @param array $params Parámetros de la solicitud
     * @param string $method Método HTTP (GET/POST)
     * @return array
     * @throws CommunicationException
     */
    private function send(string $service, array $params, string $method): array
    {
        try {
            $url = $this->config->getApiUrl() . FlowConstants::SEPARATOR . $service;
            $params[FlowConstants::APIKEY] = $this->config->getApiKey();
            $signedParams = $this->signer->sign($params);

            if ($method === FlowVerbs::POST) {
                $response = $this->httpClient->post($url, $signedParams);
            } else {
                $response = $this->httpClient->request($method, $url, $signedParams);
            }

            return $this->parseResponse($response);
        } catch (\Exception $e) {
            throw CommunicationException::networkError($e->getMessage(), $e);
        }
    }

    /**
     * Procesa la respuesta de la API de Flow
     *
     * @param array $response Respuesta de la API
     * @return array
     * @throws CommunicationException
     * @throws InvalidResponseException
     */
    private function parseResponse(array $response): array
    {
        if (isset($response[FlowConstants::INFO])) {
            $code = $response[FlowConstants::INFO][FlowConstants::HTTPCODE];
            if (!in_array($code, ["200", "400", "401"])) {
                throw CommunicationException::httpError((int)$code);
            }
        }

        $body = json_decode($response["output"], true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw InvalidResponseException::invalidJson('Flow', json_last_error_msg());
        }

        return $body;
    }

    /**
     * Gets the gateway name
     *
     * @return string
     */
    public function name(): string
    {
        return Gateways::Flow->value;
    }

    /**
     * Generates the redirect URL with token
     *
     * @param string $url Base redirect URL
     * @param string $token Transaction token
     * @return string
     */
    public function getRedirectUrl(string $url, string $token): string
    {
        return $url . "?token=" . $token;
    }

    /**
     * Gets the confirmation URL for an order
     *
     * @return string
     */
    public function getConfirmationUrl(): string
    {
        return GatewayConfig::get(ConfigKeys::BASE_URL) . "/pago/confirmar/flow";
    }

    /**
     * Gets the result URL for an order
     *
     * @param string $id Order ID
     * @return string
     */
    public function getResultUrl(string $id): string
    {
        return GatewayConfig::get(ConfigKeys::BASE_URL) . "/pago/resultado/flow/" . $id;
    }

    /**
     * Gets the complete details of a transaction
     *
     * @param string $token Transaction token
     * @param string $id Order ID
     * @return array
     * @throws TransactionException
     */
    public function getTransaction(string $token, string $id): array
    {
        return $this->getTransactionInProcess($token);
    }
}
