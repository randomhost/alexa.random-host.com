<?php

namespace randomhost\Alexa\Responder;

use randomhost\Alexa\Configuration;
use randomhost\Alexa\Response\Response;
use RuntimeException;

/**
 * Abstract base class for Responder implementation.
 *
 * @author    Ch'Ih-Yu <chi-yu@web.de>
 * @copyright 2020 random-host.tv
 * @license   https://opensource.org/licenses/BSD-3-Clause  BSD License (3 Clause)
 *
 * @see       https://random-host.tv
 */
abstract class AbstractResponder implements ResponderInterface
{
    /**
     * Plays a "confirmation" sound.
     */
    public const SOUND_CONFIRM = 'confirm';

    /**
     * Plays an "error" sound.
     */
    public const SOUND_ERROR = 'error';

    /**
     * Plays a "ready" sound.
     */
    public const SOUND_READY = 'ready';

    /**
     * Plays a "stop" sound.
     */
    public const SOUND_STOP = 'stop';

    /**
     * Valid sound names.
     *
     * @var string[]
     */
    protected $validSounds
        = [
            self::SOUND_CONFIRM,
            self::SOUND_ERROR,
            self::SOUND_READY,
            self::SOUND_STOP,
        ];

    /**
     * Configuration instance.
     *
     * @var Configuration
     */
    protected $config;

    /**
     * Response instance.
     *
     * @var Response
     */
    protected $response;

    /**
     * Base URL for audio files.
     *
     * @var string
     */
    protected $audioBaseUrl = '';

    /**
     * Base URL for image files.
     *
     * @var string
     */
    protected $imageBaseUrl = '';

    /**
     * Sets the Response instance.
     *
     * @param Response $response Response instance.
     *
     * @return $this
     */
    public function setResponse(Response $response): ResponderInterface
    {
        $this->response = $response;

        return $this;
    }

    /**
     * Sets the Configuration instance.
     *
     * @param Configuration $config Configuration instance.
     *
     * @return $this
     */
    public function setConfiguration(Configuration $config): ResponderInterface
    {
        $this->config = $config;

        $this
            ->determineAudioBaseUrl()
            ->determineImageBaseUrl()
        ;

        return $this;
    }

    /**
     * Returns a random response from the given array of responses.
     *
     * @param array $responses Array of responses.
     *
     * @return mixed
     */
    protected function randomizeResponseText(array $responses)
    {
        return $responses[array_rand($responses)];
    }

    /**
     * Prefixes the given response with a pre-defined sound file.
     *
     * @param string $sound    One of the self::SOUND_* constants.
     * @param string $response Response string.
     *
     * @return string String with SSML markup.
     */
    protected function withSound(string $sound, string $response): string
    {
        if (!in_array($sound, $this->validSounds)) {
            throw new RuntimeException('Invalid sound name');
        }

        return sprintf(
            '<speak>%1$s%2$s</speak>',
            $this->buildSoundTag($sound),
            $response
        );
    }

    /**
     * Returns the SSML markup for playing the given sound file.
     *
     * @param string $sound Name of the sound file (without file extension).
     *
     * @return string String with SSML markup.
     */
    protected function buildSoundTag(string $sound): string
    {
        return sprintf(
            '<audio src="%1$s%2$s.mp3" />',
            $this->audioBaseUrl,
            $sound
        );
    }

    /**
     * Returns the full URL for the given image file.
     *
     * @param string $image Image file name.
     */
    protected function buildImageUrl(string $image): string
    {
        return $this->imageBaseUrl.$image;
    }

    /**
     * Determines the base URL for audio files.
     *
     * @return $this
     */
    private function determineAudioBaseUrl(): self
    {
        $baseUrl = $this->config->get('audio', 'baseUrl');
        if (is_null($baseUrl) || empty($baseUrl)) {
            throw new RuntimeException('Could not read audio base URL');
        }

        $this->audioBaseUrl = $baseUrl;

        return $this;
    }

    /**
     * Determines the base URL for image files.
     *
     * @return $this
     */
    private function determineImageBaseUrl(): self
    {
        $baseUrl = $this->config->get('image', 'baseUrl');
        if (is_null($baseUrl) || empty($baseUrl)) {
            throw new RuntimeException('Could not read image base URL');
        }

        $this->imageBaseUrl = $baseUrl;

        return $this;
    }
}
