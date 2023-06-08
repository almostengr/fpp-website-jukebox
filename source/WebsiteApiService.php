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

    public function getNextSongInQueue()
    {
        $websiteApi = $this->settingService->getSetting(self::WEBSITE_ENDPOINT);

        $headers = $this->getHeaders();
        array_push($headers, array(self::X_AUTH_TOKEN => $this->settingService->getSetting(API_KEY)));

        return $this->callAPI(self::GET, $websiteApi, array(), $this->getHeaders(), "", false);
    }

    public function deleteQueue()
    {
        $websiteApi = $this->settingService->getSetting(self::WEBSITE_ENDPOINT);

        $headers = $this->getHeaders();
        array_push($headers, array(self::X_AUTH_TOKEN => $this->settingService->getSetting(API_KEY)));

        return $this->callAPI(self::DELETE, $websiteApi, array(), $this->getHeaders(), "", false);
    }
}