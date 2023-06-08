<?php

require_once('/home/fpp/media/plugins/fpp-website-jukebox/source/SettingRepository.php');

interface SettingServiceInterface
{
    public function getSetting(string $key);
    public function createUpdateSetting(string $key, string $value);
}

final class SettingService extends BaseService implements SettingServiceInterface
{
    private  $repository;

    public function __construct(SettingRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getSetting(string $key)
    {
        return $this->repository->getSetting($key);
    }

    public function createUpdateSetting(string $key, string $value)
    {
        return $this->repository->createUpdateSetting($key, $value);
    }
}
