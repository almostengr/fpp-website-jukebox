<?php

require_once('/home/fpp/media/plugins/fpp-website-jukebox/source/BaseService.php');

interface SettingRepositoryInterface
{
    public function getSetting(string $key): string;
    public function createUpdateSetting(string $key, string $value): void;
}

final class SettingRepository implements SettingRepositoryInterface
{
    const FWJ_PLUGIN_NAME = "fpp_weather_monitor_plugin";

    public function getSetting(string $key): string
    {
        $value = ReadSettingFromFile($key, self::FWJ_PLUGIN_NAME);
        return str_replace("_", " ", $value);
    }

    public function createUpdateSetting(string $key, string $value): void
    {
        $value = str_replace(" ", "_", $value);
        WriteSettingToFile($key, $value, self::FWJ_PLUGIN_NAME);
    }
}