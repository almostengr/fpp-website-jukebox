<?php

require_once "/opt/fpp/www/common.php";

define("API_KEY", "apiKey");
define("GET", "GET");
define("JUKEBOX_ENABLED", "jukeboxEnabled");
define("JUKEBOX_PLUGIN_NAME", "fpp-website-jukebox");
define("LOCALHOST_API", "http://127.0.0.1/api");
define("WEB_POLL_TIME", "pollTime");
define("WEBSITE_ENDPOINT", "websiteEndPoint");

function callAPI(string $method, string $url, array $data = array(), array $headers = array(), string $userAgent = EMPTY_STRING, bool $returnArray = false)
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

    curl_setopt($curl, CURLOPT_URL, rawurlencode($url));
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

    if (!empty($userAgent)) {
        curl_setopt($curl, CURLOPT_USERAGENT, $userAgent);
    }

    $response = curl_exec($curl);
    if (!$response) {
        throw new Exception("Connection failure");
    }
    $responseCode = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
    curl_close($curl);

    if ($responseCode >= 200 && $responseCode <= 299) {
        return json_decode($response, $returnArray);
    }

    throw new Exception(json_encode(array("code" => $responseCode, "body" => $response)));
}

function getHeaders(): array
{
    return array(
        "Content-Type" => "application/json",
    );
}