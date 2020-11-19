<?php

namespace randomhost\Alexa;

use RuntimeException;

/**
 * Configuration.
 *
 * @author    Ch'Ih-Yu <chi-yu@web.de>
 * @copyright 2020 random-host.tv
 * @license   https://opensource.org/licenses/BSD-3-Clause  BSD License (3 Clause)
 *
 * @see      https://random-host.tv
 */
class Configuration
{
    /**
     * Config data array.
     *
     * @var array
     */
    private $json;

    /**
     * App ID.
     *
     * @var string
     */
    private $appId = '';

    /**
     * Configuration constructor.
     *
     * @param string $config Optional: Configuration file name without file extension.
     */
    public function __construct(string $config = 'config')
    {
        $this
            ->loadConfig($config)
            ->loadMandatoryParameters()
        ;
    }

    /**
     * Returns the app ID.
     *
     * @return string
     */
    public function getAppId(): string
    {
        return $this->appId;
    }

    /**
     * Returns the value for the given setting from the configuration array.
     *
     * If the setting is not configured, null is returned instead.
     *
     * @param string $category Category key.
     * @param string $key      Setting key.
     *
     * @return null|mixed
     */
    public function get(string $category, string $key)
    {
        if (!array_key_exists($category, $this->json)
            || !array_key_exists($key, $this->json[$category])
        ) {
            return null;
        }

        return $this->json[$category][$key];
    }

    /**
     * Loads the JSON config file.
     *
     * @param string $fileName Configuration file name without file extension.
     *
     * @throws RuntimeException Thrown in case the config file could not be loaded.
     *
     * @return $this
     */
    private function loadConfig(string $fileName): self
    {
        if (1 !== preg_match('/^[a-zA-Z_0-9\-]+(\.[a-zA-Z_0-9\-]+)?$/', $fileName)) {
            throw new RuntimeException(
                "Invalid config file name ${$fileName}"
            );
        }

        $filePath = __DIR__.'/../data/'.$fileName.'.json';

        $rawJson = @file_get_contents($filePath);
        if (false === $rawJson) {
            throw new RuntimeException(
                "Failed to load config file at {$filePath}"
            );
        }

        $json = json_decode($rawJson, true);
        if (is_null($json)) {
            throw new RuntimeException(
                "Failed to parse config file at {$filePath}"
            );
        }

        $this->json = $json;

        return $this;
    }

    /**
     * Loads mandatory parameters.
     *
     * @return $this
     */
    private function loadMandatoryParameters(): self
    {
        if (empty($this->json['common']['appId'])) {
            throw new RuntimeException(
                'No app ID configured. Please check your config.'
            );
        }
        $this->appId = $this->json['common']['appId'];

        return $this;
    }
}
