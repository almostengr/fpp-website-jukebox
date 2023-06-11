<?php

require_once('/home/fpp/media/plugins/fpp-website-jukebox/source/BaseService.php');

interface SettingRepositoryInterface
{
    public function getSetting(string $key): string;
    public function createUpdateSetting(string $key, string $value): void;
}

final class SettingRepository implements SettingRepositoryInterface
{
    private string $pluginName = "fpp-website-jukebox";
    
    public function getSetting(string $key): string
    {
        $value = ReadSettingFromFile($key, $this->pluginName);
        return str_replace("_", " ", $value);
    }

    public function createUpdateSetting(string $key, string $value): void
    {
        $value = str_replace(" ", "_", $value);
        WriteSettingToFile($key, $value, $this->pluginName);
    }
}