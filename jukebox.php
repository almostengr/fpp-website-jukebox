<?php

require_once "/opt/fpp/www/common.php";


function getHeaders(): array
{
    return array(
        "Content-Type" => "application/json",
    );
}

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

define("FPP_STATUS_API_URL", "http://127.0.0.1/api/fppd/status");
define("GET", "GET");
define("WEBSITE_JUKEBOX", "fpp-website-jukebox");

$lastFppStatusCheckTime = 0;

$fppStatus = array();

while (true) {
    $currentTime = time();
    $pollTime = 5;
    
    if (($currentTime - $lastFppStatusCheckTime) >= $pollTime)
    {
        // get the lastest status information from FPP
        try {
            $fppStatus = callAPI(GET, FPP_STATUS_API_URL);
            $lastFppStatusCheckTime = $currentTime;
        }
        catch( Exception $exception)
        {
            error_log($exception->getMessage());
            continue;
        }
    }
    
    if ($fppStatus->seconds_remaining <= $pollTime)
    {
        try {
            // get next song in queue from website        
            $websiteApi = ReadSettingFromFile($websiteEndpoint, WEBSITE_JUKEBOX);
            
            $headers = getHeaders();
            array_push($headers, array('X-Auth-Token' => ReadSettingFromFile(API_KEY, WEBSITE_JUKEBOX));
            
            $websiteResponse = callAPI(GET, $websiteApi, array(), $headers ));

//             $websiteRepsonse->data->sequenceName
            // /api/playlist/:PlaylistName/start
            $sequenceUri = "http://127.0.0.1/api/playlist/" . $websiteRepsonse->data->sequenceName . "/start";
            $sequenceResponse = callAPI(GET, $sequenceUri);
        }
        catch (Exception $exception)
        {
            error_log($exception->getMessage());
            continue;
        }

        // if is song, then add song to playlist 
        // if no song, then pick random sequence and play it

    }
}