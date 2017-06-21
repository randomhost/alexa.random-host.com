<?php

namespace randomhost\Alexa\Responder;

use randomhost\Alexa\Configuration;
use randomhost\Alexa\Response\Response;
use RuntimeException;

/**
 * Abstract base class for Responder implementation.
 *
 * @author    Ch'Ih-Yu <chi-yu@web.de>
 * @copyright 2017 random-host.com
 * @license   http://www.debian.org/misc/bsd.license  BSD License (3 Clause)
 * @link      http://composer.random-host.com
 */
abstract class AbstractResponder implements ResponderInterface
{
    /**
     * Plays a "confirmation" sound.
     */
    const SOUND_CONFIRM = "confirm";

    /**
     * Plays an "error" sound.
     */
    const SOUND_ERROR = "error";

    /**
     * Plays a "ready" sound.
     */
    const SOUND_READY = "ready";

    /**
     * Plays a "stop" sound.
     */
    const SOUND_STOP = "stop";

    /**
     * Valid sound names.
     *
     * @var string[]
     */
    protected $validSounds
        = array(
            self::SOUND_CONFIRM,
            self::SOUND_ERROR,
            self::SOUND_READY,
            self::SOUND_STOP,
        );

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
     * Sets the Response instance.
     *
     * @param Response $response Response instance.
     *
     * @return $this
     */
    public function setResponse(Response $response)
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
    public function setConfiguration(Configuration $config)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * Returns a random response from the given array of responses.
     *
     * @param array $responses
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
     * @param string $sound One of the self::SOUND_* constants.
     * @param string $response Response string.
     *
     * @return string String with SSML markup.
     */
    protected function withSound($sound, $response)
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
    protected function buildSoundTag($sound)
    {
        $baseUrl = $this->config->get('audio', 'baseUrl');
        if (is_null($baseUrl) || empty($baseUrl)) {
            throw new RuntimeException('Could not read audio base URL');
        }

        return sprintf('<audio src="%1$s%2$s.mp3" />', $baseUrl, $sound);
    }
}
