<?php

require_once('/home/fpp/media/plugins/fpp-website-jukebox/source/SettingRepository.php');

interface SettingServiceInterface
{
    public function getSetting(string $key);
    public function createUpdateSetting(string $key, string $value);
}

final class SettingService extends BaseService implements SettingServiceInterface
{
    private $repository;

    public function __construct(SettingRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getSetting(string $key): string
    {
        return $this->repository->getSetting($key);
    }

    public function createUpdateSetting(string $key, string $value): bool|string
    {
        switch ($key) {
            case POLL_TIME:
                if ($value <= 0) {
                    return "Poll time must be greater than zero.";
                }
                break;

            case WEBSITE_ENDPOINT:
                if (filter_var($value, FILTER_VALIDATE_URL) === false) {
                    return "Please enter a valid website endpoint URL";
                }
                break;

            default:
        }

        $this->repository->createUpdateSetting($key, $value);
        return true;
    }
}