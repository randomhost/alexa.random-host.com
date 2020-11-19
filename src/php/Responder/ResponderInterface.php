<?php

namespace randomhost\Alexa\Responder;

use randomhost\Alexa\Configuration;
use randomhost\Alexa\Response\Response;

/**
 * Interface for Responder implementations.
 *
 * @author    Ch'Ih-Yu <chi-yu@web.de>
 * @copyright 2020 random-host.tv
 * @license   https://opensource.org/licenses/BSD-3-Clause  BSD License (3 Clause)
 *
 * @see       https://random-host.tv
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
    public function setResponse(Response $response): ResponderInterface;

    /**
     * Sets the Configuration instance.
     *
     * @param Configuration $config Configuration instance.
     *
     * @return $this
     */
    public function setConfiguration(Configuration $config): ResponderInterface;

    /**
     * Runs the Responder.
     *
     * @return $this
     */
    public function run(): ResponderInterface;
}
