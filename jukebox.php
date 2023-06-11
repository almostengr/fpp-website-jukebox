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
$lastSongName = "";

while (true) {
    $currentTime = time();

    try {
        if (($currentTime - $lastStatusCheckTime) < $pollTime) {
            continue;
        }

        $lastStatusCheckTime = $currentTime;
        $fppStatus = $fppApi->getShowStatus();

        if ($fppStatus->current_song != $lastSongName) {
            $websiteApi->updateCurrentSong($fppStatus->current_song);
        }

        $checkQueueTime = $settingService->getSetting(POLL_TIME);
        if ($fppStatus->seconds_remaining > $checkQueueTime) {
            continue;
        }

        $response = $websiteApi->getNextSongInQueue();
        $nextSequence = $response->data->sequenceName;
        $insertCmdResponse = $fppApi->insertPlaylistAfterCurrent($nextSequence);
    } catch (Exception $exception) {
        error_log($exception->getMessage());
    }
}