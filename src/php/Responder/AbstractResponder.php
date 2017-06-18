<?php

namespace randomhost\Alexa\Responder;

use randomhost\Alexa\Configuration;
use randomhost\Alexa\Response\Response;

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
}
