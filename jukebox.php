<?php

require_once "/home/fpp/media/plugins/fpp-website-jukebox/common.php";

function getSequences(): array
{
    return array_diff(scandir(ReadSettingFromFile("playlistDirectory")), array('..', '.'));
}

function insertPlaylistAfterCurrent(string $playlistName)
{
    $route = LOCALHOST_API . "/command/Insert Playlist After Current/" . $playlistName;
    return callAPI(GET, $route);
}

function getNextSongInQueue()
{
    $websiteApi = ReadSettingFromFile(WEBSITE_ENDPOINT, JUKEBOX_PLUGIN_NAME);

    $headers = getHeaders();
    array_push($headers, array('X-Auth-Token' => ReadSettingFromFile(API_KEY, JUKEBOX_PLUGIN_NAME)));

    return callAPI(GET, $websiteApi, array(), $headers);
}

$lastStatusCheckTime = 0;

while (true) {
    try {
        $currentTime = time();
        $pollTime = 5;
        $getNextSongInQueue = false;

        if (($currentTime - $lastStatusCheckTime) > $pollTime) {
            $jukeboxEnabled = ReadSettingFromFile(JUKEBOX_ENABLED, JUKEBOX_PLUGIN_NAME);
            $lastStatusCheckTime = $currentTime;

            if ($jukeboxEnabled == false) {
                continue;
            }

            $fppStatus = callAPI(GET, LOCALHOST_API . "/fppd/status");

            if ($fppStatus->seconds_remaining <= $pollTime) {
                $getNextSongInQueue = true;
            }
        }

        if ($getNextSongInQueue) {
            $websiteResponse = getNextSongInQueue();
            $nextSequence = $websiteResponse->data->sequenceName;
            $insertCommandResponse = insertPlaylistAfterCurrent($nextSequence);
        }
    } catch (Exception $exception) {
        error_log($exception->getMessage());
        continue;
    }
}