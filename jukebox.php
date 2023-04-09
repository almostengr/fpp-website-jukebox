<?php

require_once "/opt/fpp/www/common.php";
require_once "/home/fpp/media/plugins/fpp-website-jukebox/common.php";

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
        throw new Exception("Connection failure");
    }
    $responseCode = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
    curl_close($curl);

    if ($responseCode >= 200 && $responseCode <= 299) {
        return json_decode($response, $returnArray);
    }

    throw new Exception(json_encode(array("code" => $responseCode, "body" => $response)));
}

function getSequences(): array
{
    return array_diff(scandir(ReadSettingFromFile("playlistDirectory")), array('..', '.'));
}

function insertPlaylistAfterCurrent(string $playlistName)
{
    $route = LOCALHOST_API . "/command/Insert Playlist After Current/" . $playlistName;
    return callAPI(GET, rawurlencode($route));
}

function getNextSongInQueue()
{
    $websiteApi = ReadSettingFromFile(WEBSITE_ENDPOINT, WEBSITE_JUKEBOX);

    $headers = getHeaders();
    array_push($headers, array('X-Auth-Token' => ReadSettingFromFile(API_KEY, WEBSITE_JUKEBOX)));

    return callAPI(GET, $websiteApi, array(), $headers);
}

$lastFppStatusCheckTime = 0;
$fppStatus = array();
$songQueued = false;

while (true) {
    $currentTime = time();
    $pollTime = 5;

    try {
        if (($currentTime - $lastFppStatusCheckTime) >= $pollTime) {
            $fppStatus = callAPI(GET, LOCALHOST_API . "/fppd/status");
            $lastFppStatusCheckTime = $currentTime;
            $songQueued = false;
        }

        if ($fppStatus->seconds_remaining <= $pollTime && $songQueued == false) {
            $websiteResponse = getNextSongInQueue();

            $nextSequence = $websiteResponse->data->sequenceName;
            if ($nextSequence == null) {
                $sequences = getSequences();
                $nextSequence = $sequences[array_rand($sequences)];
            }

            $insertCommandResponse = insertPlaylistAfterCurrent($nextSequence);
            $songQueued = true;
        }
    } catch (Exception $exception) {
        error_log($exception->getMessage());
        continue;
    }
}