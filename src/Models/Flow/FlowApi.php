<?php

namespace Raion\Gateways\Models\Flow;

use Raion\Gateways\Config\ConfigKeys;
use Raion\Gateways\Config\GatewayConfig;
use Exception;

class FlowApi {
	
	protected $apiKey;
	protected $secretKey;
	
	public function __construct() {
		$this->apiKey = GatewayConfig::get(ConfigKeys::FLOW_API_KEY);
		$this->secretKey = GatewayConfig::get(ConfigKeys::FLOW_SECRET_KEY);
	}
	
	public function setKeys($apiKey, $secretKey) {
		$this->apiKey = $apiKey;
		$this->secretKey = $secretKey;
	}

    public function createTransaction($params, $method = FlowVerbs::POST) {
        $service = FlowPaths::PaymentCreate;
        return $this->send($service, $params, $method);
    }

    public function createTransactionEmail($params, $method = FlowVerbs::POST) {
        $service = FlowPaths::PaymentCreateEmail;
        return $this->send($service, $params, $method);
    }

    public function getTransactionStatus($params, $method = FlowVerbs::GET) {
        $service = FlowPaths::PaymentGetStatus;
        return $this->send($service, $params, $method);
    }

    private function send($service, $params, $method = FlowVerbs::GET) {

		if ($method == FlowVerbs::GET) {
			$response = $this->httpGet($this->getUrl($service), $this->generateParams($params));
		} 

        if ($method == FlowVerbs::POST) {
			$response = $this->httpPost($this->getUrl($service), $this->generateParams($params));
		}
		
		return $this->parseResponse($response);
	}

	private function getUrl($service){
        return GatewayConfig::get(FlowConstants::APIURL) . FlowConstants::SEPARATOR . $service;
    }

    private function generateParams($params) {
        $params = array(FlowConstants::APIKEY => $this->apiKey) + $params;
        $params[FlowConstants::SIGNATURE] = $this->sign($params);
        return $params;
    }

    private function parseResponse($response) {
        if (isset($response[FlowConstants::INFO])) {
            $code = $response[FlowConstants::INFO][FlowConstants::HTTPCODE];
            if (!in_array($code, array("200", "400", "401"))) {
                throw new Exception("Unexpected error occurred. HTTP_CODE: " . $code, $code);
            }
        }
        $body = json_decode($response["output"], true);
        return $body;
    }
	
	private function sign($params) {
		$keys = array_keys($params);
		sort($keys);
		$toSign = "";
		foreach ($keys as $key) {
			$toSign .= $key . $params[$key];
		}
		if(!function_exists("hash_hmac")) {
			throw new Exception("function hash_hmac not exist", 1);
		}
		return hash_hmac(FlowConstants::ENCRYPTION, $toSign , $this->secretKey);
	}
	
	private function httpGet($url, $params) {
		$url = $url . FlowConstants::QUERYSIGN . http_build_query($params);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		$output = curl_exec($ch);
		if($output === false) {
			$error = curl_error($ch);
			throw new Exception($error, 1);
		}
		$info = curl_getinfo($ch);
		curl_close($ch);
		return array("output" => $output, "info" => $info);
	}
	
	private function httpPost($url, $params ) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		$output = curl_exec($ch);
		if($output === false) {
			$error = curl_error($ch);
			throw new Exception($error, 1);
		}
		$info = curl_getinfo($ch);
		curl_close($ch);
		return array("output" =>$output, "info" => $info);
	}
}