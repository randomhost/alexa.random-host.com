<?php

namespace randomhost\Alexa\Responder;

use randomhost\Alexa\Configuration;
use randomhost\Alexa\Response\Response;

/**
 * Interface for Responder implementations.
 *
 * @author    Ch'Ih-Yu <chi-yu@web.de>
 * @copyright 2017 random-host.com
 * @license   http://www.debian.org/misc/bsd.license  BSD License (3 Clause)
 * @link      http://composer.random-host.com
 */
interface ResponderInterface
{
    /**
     * Sets the Response instance.
     *
     * @param Response $response Response instance.
     *
     * @return $this
     */
    public function setResponse(Response $response);

    /**
     * Sets the Configuration instance.
     *
     * @param Configuration $config Configuration instance.
     *
     * @return $this
     */
    public function setConfiguration(Configuration $config);

    /**
     * Runs the Responder.
     *
     * @return $this
     */
    public function run();
}
