#!/usr/bin/php

<?php

require_once "/home/fpp/media/plugins/fpp-website-jukebox/source/SettingService.php";

$settingRepository = new $settingRepository();
$settingService = new SettingService($settingRepository);
$settingService->createUpdateSetting(self::JUKEBOX_ENABLED, false);