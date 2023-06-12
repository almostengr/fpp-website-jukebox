<?php

require_once('/home/fpp/media/plugins/fpp-website-jukebox/source/BaseApiService.php');

interface FppApiServiceInterface
{
    public function getSequenceList(): array;
    public function getShowStatus(): stdClass;
    public function verifySequenceExists(string $sequenceName): bool;
    public function insertPlaylistAfterCurrent(string $playlistName);
}

final class FppApiService extends BaseApiService implements FppApiServiceInterface
{
    const FPP_API = "http://127.0.0.1/api/";

    public function getShowStatus(): stdClass
    {
        $route = self::FPP_API . "fppd/status";
        return $this->callAPI(BaseApiService::GET, $route);
    }

    public function verifySequenceExists(string $sequenceName): bool
    {
        $sequenceArray = $this->getSequenceList();
        return array_search($sequenceName, $sequenceArray);
    }

    public function getSequenceList(): array
    {
        $route = self::FPP_API . "/sequence";
        return $this->callAPI(self::GET, $route);
    }

    public function insertPlaylistAfterCurrent(string $playlistName)
    {
        $route = self::FPP_API . "/command/Insert Playlist After Current/" . $playlistName;
        return $this->callAPI(BaseApiService::GET, $route);
    }
}