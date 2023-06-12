<?php

require_once "/home/fpp/media/plugins/fpp-website-jukebox/source/FppApiService.php";
require_once "/home/fpp/media/plugins/fpp-website-jukebox/source/SettingService.php";
require_once "/home/fpp/media/plugins/fpp-website-jukebox/source/WebsiteApiService.php";

$settingRepository = new SettingRepository();
$settingService = new SettingService($settingRepository);
$fppApi = new FppApiService();
$websiteApi = new WebsiteApiService($settingService);

$lastStatusCheckTime = 0;
$pollTime = 5;
$currentSong = "";

while (true) {
    $currentTime = time();

    if (($currentTime - $lastStatusCheckTime) < $pollTime) {
        continue;
    }
    
    try {
        $fppStatus = $fppApi->getShowStatus();
        $lastStatusCheckTime = $currentTime;

        if ($fppStatus->current_song != $currentSong) {
            if ($currentSong === "")
            {
                $websiteApi->deleteQueue();
            }

            $websiteApi->updateCurrentSong($fppStatus->current_song);
            $currentSong = $fppStatus->current_song;
        }

        if ($fppStatus->status_name != "playing") {
            continue;
        }

        $checkQueueTime = $settingService->getSetting(POLL_TIME);
        if ($fppStatus->seconds_remaining > $checkQueueTime) {
            continue;
        }

        $response = $websiteApi->getNextSongInQueue();
        $nextSequence = $response->data->sequenceName;
        $sequenceFound = $fppApi->verifySequenceExists($nextSequence);

        if ($sequenceFound === false) {
            $lastStatusCheckTime = 0;
            continue;
        }

        $fppApi->insertPlaylistAfterCurrent($nextSequence);
    } catch (Exception $exception) {
        error_log($exception->getMessage());
    }
}