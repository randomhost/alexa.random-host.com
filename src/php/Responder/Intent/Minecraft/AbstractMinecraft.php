<?php

namespace randomhost\Alexa\Responder\Intent\Minecraft;

use randomhost\Alexa\Responder\AbstractResponder;
use randomhost\Alexa\Responder\ResponderInterface;

/**
 * Abstract base class for Minecraft Intents.
 *
 * @author    Ch'Ih-Yu <chi-yu@web.de>
 * @copyright 2017 random-host.com
 * @license   http://www.debian.org/misc/bsd.license  BSD License (3 Clause)
 * @link      http://composer.random-host.com
 */
abstract class AbstractMinecraft extends AbstractResponder implements ResponderInterface
{
    /**
     * Data from the minecraft Server.
     *
     * @var array
     */
    protected $data;

    /**
     * MinecraftPlayerCount constructor.
     *
     * @param array $data Data from the Minecraft server.
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }
}
