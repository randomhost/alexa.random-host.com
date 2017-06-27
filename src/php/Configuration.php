<?php

namespace randomhost\Alexa;

use RuntimeException;

/**
 * Configuration.
 *
 * @author    Ch'Ih-Yu <chi-yu@web.de>
 * @copyright 2017 random-host.com
 * @license   http://www.debian.org/misc/bsd.license  BSD License (3 Clause)
 * @link      http://composer.random-host.com
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
    public function __construct($config = 'config')
    {
        $this
            ->loadConfig($config)
            ->loadMandatoryParameters();
    }

    /**
     * Returns the app ID.
     *
     * @return string
     */
    public function getAppId()
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
     * @return mixed|null
     */
    public function get($category, $key)
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
     * @return $this
     *
     * @throws RuntimeException Thrown in case the config file could not be loaded.
     */
    private function loadConfig($fileName)
    {
        if (1 !== preg_match('/^[a-zA-Z_0-9\-]+(\.[a-zA-Z_0-9\-]+)?$/', $fileName)) {
            throw new RuntimeException(
                "Invalid config file name ${$fileName}"
            );
        }

        $filePath = __DIR__.'/../data/'.$fileName.'.json';

        $rawJson = file_get_contents($filePath);
        if (false === $rawJson) {
            throw new RuntimeException(
                "Failed to load config file at ${filePath}"
            );
        }

        $json = json_decode($rawJson, true);
        if (is_null($json)) {
            throw new RuntimeException(
                "Failed to parse config file at ${filePath}"
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
    private function loadMandatoryParameters()
    {
        if (empty($this->json['common']['appId'])) {
            throw new RuntimeException(
                "No app ID configured. Please check your config."
            );
        }
        $this->appId = $this->json['common']['appId'];

        return $this;
    }
}
