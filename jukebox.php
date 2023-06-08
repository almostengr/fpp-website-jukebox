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
$jukeboxEnabled = false;

while (true) {
    $currentTime = time();

    try {
        if (($currentTime - $lastStatusCheckTime) < $pollTime) {
            continue;
        }

        $jukeboxEnabled = $settingService->getSetting(self::JUKEBOX_ENABLED);
        $lastStatusCheckTime = $currentTime;

        if ($jukeboxEnabled == false) {
            continue;
        }

        $fppStatus = $fppApi->getShowStatus();
        if ($fppStatus->seconds_remaining > $pollTime) {
            continue;
        }

        $response = $websiteApi->getNextSongInQueue();
        $nextSequence = $response->data->sequenceName;
        $insertCmdResponse = $fppApi->insertPlaylistAfterCurrent($nextSequence);
    } catch (Exception $exception) {
        error_log($exception->getMessage());
    }
}