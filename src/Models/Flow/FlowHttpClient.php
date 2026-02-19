<?php

namespace Raion\Gateways\Models\Flow;

class FlowHttpClient
{
    public function request(string $method, string $url, array $params): array
    {
        $ch = curl_init();
          if ($method === FlowVerbs::GET) {
            $url .= FlowConstants::QUERYSIGN . http_build_query($params);
        } else {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/x-www-form-urlencoded',
                'Accept: application/json',
            ));
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        }

        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,  true);
        curl_setopt($ch, CURLOPT_ENCODING,  '');
        curl_setopt($ch, CURLOPT_MAXREDIRS,  10);
        curl_setopt($ch, CURLOPT_TIMEOUT,  0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION,  true);
        $output = curl_exec($ch);
        if ($output === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new FlowException("cURL error: " . $error);
        }

        $info = curl_getinfo($ch);
        curl_close($ch);

        return ["output" => $output, "info" => $info];
    }

    public function post( string $url, array $params)
    {

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => http_build_query($params),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/x-www-form-urlencoded',
                'Accept: application/json',
            ),
        ));

        $output = curl_exec($curl);
        $info = curl_getinfo($curl);
        curl_close($curl);

        return ["output" => $output, "info" => $info];
    }
}