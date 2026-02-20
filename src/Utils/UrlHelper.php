<?php

namespace Raion\Gateways\Utils;

class UrlHelper
{
    public static function buildUrl(string $baseUrl, string $path, array $queryParams = []): string
    {
        $url = rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
        if (!empty($queryParams)) {
            $url .= '?' . http_build_query($queryParams);
        }
        return $url;
    }
}