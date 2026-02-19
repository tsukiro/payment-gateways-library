<?php 

namespace Raion\Gateways\Models\Flow;

class FlowSigner
{
    private string $apiKey;
    private string $secretKey;

    public function __construct(string $apiKey, string $secretKey)
    {
        $this->apiKey = $apiKey;
        $this->secretKey = $secretKey;
    }

    public function sign(array $params): array
    {
        $params[FlowConstants::SIGNATURE] = $this->generateSignature($params);
        ksort($params);
        return $params;
    }

    private function generateSignature(array $params): string
    {
        $keys = array_keys($params);
        sort($keys);
        $toSign = '';
        foreach ($keys as $key) {
            $toSign .= $key . $params[$key];
        }
           if (!function_exists("hash_hmac")) {
            throw new FlowException("Function hash_hmac does not exist.");
        }

        return hash_hmac(FlowConstants::ENCRYPTION, $toSign, $this->secretKey);
    }
}