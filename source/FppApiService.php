<?php

require_once('/home/fpp/media/plugins/fpp-website-jukebox/source/BaseApiService.php');


interface FppApiServiceInterface
{
    public function getSequences();
    public function getShowStatus();
    public function insertPlaylistAfterCurrent(string $playlistName);
}


final class FppApiService extends BaseApiService implements FppApiServiceInterface
{
    const FPP_API = "http://127.0.0.1/api/";

    public function getShowStatus()
    {
        $route = self::FPP_API . "fppd/status";
        return $this->callAPI(BaseApiService::GET, $route);
    }

    public function getSequences(): array
    {
        return array_diff(scandir(ReadSettingFromFile("playlistDirectory")), array('..', '.'));
    }
    
    public function insertPlaylistAfterCurrent(string $playlistName)
    {
        $route = self::FPP_API . "/command/Insert Playlist After Current/" . $playlistName;
        return $this->callAPI(BaseApiService::GET, $route);
    }
}
