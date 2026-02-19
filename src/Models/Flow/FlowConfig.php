<?php

namespace Raion\Gateways\Models\Flow;

use Raion\Gateways\Config\GatewayConfig;
use Raion\Gateways\Config\ConfigKeys;

class FlowConfig
{
    public function getApiKey(): string
    {
        return GatewayConfig::get(ConfigKeys::FLOW_API_KEY);
    }

    public function getSecretKey(): string
    {
        return GatewayConfig::get(ConfigKeys::FLOW_SECRET_KEY);
    }

    public function getApiUrl(): string
    {
        return GatewayConfig::get(ConfigKeys::FLOW_API_URL);
    }
}
