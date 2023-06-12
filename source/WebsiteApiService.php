<?php

interface WebsiteApiServiceInterface
{
    public function getNextSongInQueue();
    public function deleteQueue();
}

final class WebsiteApiService extends BaseApiService implements WebsiteApiServiceInterface
{
    private SettingServiceInterface $settingService;

    public function __construct(SettingServiceInterface $settingService)
    {
        $this->settingService = $settingService;
    }

    protected function updateCurrentSong(string $songName)
    {
        $songName = str_replace(".mp3", "", $songName);
        $songName = str_replace(".mp4", "", $songName);
        $websiteApi = $this->settingService->getSetting(WEBSITE_ENDPOINT);

        $headers = $this->getHeaders();
        array_push($headers, array(self::X_AUTH_TOKEN => $this->settingService->getSetting(API_KEY)));

        $data = array("action" => "playing", "songname" => $songName);
        return $this->callAPI(self::PUT, $websiteApi, $data, $this->getHeaders(), "", false);
    }

    public function getNextSongInQueue()
    {
        $websiteApi = $this->settingService->getSetting(WEBSITE_ENDPOINT);

        $headers = $this->getHeaders();
        array_push($headers, array(self::X_AUTH_TOKEN => $this->settingService->getSetting(API_KEY)));

        $data = array("action" => "nextsong");
        return $this->callAPI(self::PUT, $websiteApi, $data, $this->getHeaders(), "", false);
    }

    public function deleteQueue()
    {
        $websiteApi = $this->settingService->getSetting(WEBSITE_ENDPOINT);

        $headers = $this->getHeaders();
        array_push($headers, array(self::X_AUTH_TOKEN => $this->settingService->getSetting(API_KEY)));

        $data = array("action" => "clearqueue");
        return $this->callAPI(self::PUT, $websiteApi, $data, $this->getHeaders(), "", false);
    }
}