<?php

interface ApiServiceInterface
{
    public function callAPI(string $method, string $url, array $data = array(), array $headers = array(), string $userAgent = EMPTY_STRING, bool $returnArray = false);
}

final class ApiService extends ApiServiceInterface
{
    protected function getHeaders(): array
    {
        return array(
            "Content-Type" => "application/json",
        );
    }

    public function callAPI(string $method, string $url, array $data = array(), array $headers = array(), string $userAgent = EMPTY_STRING, bool $returnArray = false)
    {
        $curl = curl_init();
        switch ($method) {
            case "POST":
                curl_setopt($curl, CURLOPT_POST, 1);
                if ($data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;

            case "PUT":
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
                if ($data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;

            default:
                if ($data)
                    $url = sprintf("%s?%s", $url, http_build_query($data));
        }

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

        if (!empty($userAgent)) {
            curl_setopt($curl, CURLOPT_USERAGENT, $userAgent);
        }

        $response = curl_exec($curl);
        if (!$response) {
            die("Connection Failure");
        }
        $responseCode = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        curl_close($curl);

        if ($responseCode >= 200 && $responseCode <= 299) {
            return json_decode($response, $returnArray);
        }

        throw new Exception(json_encode(array("code" => $responseCode, "body" => $response)));
    }
}