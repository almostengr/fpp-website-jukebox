#!/usr/bin/php

<?php

require_once "/home/fpp/media/plugins/fpp-website-jukebox/source/WebsiteApiService.php";

$settingRepository = new $settingRepository();
$settingService = new $settingService($settingRepository);
$websiteApi = new WebsiteApiService($settingService);
$websiteApi->deleteQueue();
