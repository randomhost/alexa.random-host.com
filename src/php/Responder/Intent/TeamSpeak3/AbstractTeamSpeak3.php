<?php

namespace randomhost\Alexa\Responder\Intent\TeamSpeak3;

use randomhost\Alexa\Responder\AbstractResponder;
use randomhost\Alexa\Responder\ResponderInterface;
use TeamSpeak3_Node_Server;

/**
 * Abstract base class for TeamSpeak 3 Intents.
 *
 * @author    Ch'Ih-Yu <chi-yu@web.de>
 * @copyright 2017 random-host.com
 * @license   http://www.debian.org/misc/bsd.license  BSD License (3 Clause)
 * @link      http://composer.random-host.com
 */
abstract class AbstractTeamSpeak3 extends AbstractResponder implements ResponderInterface
{
    /**
     * AbstractTeamSpeak3 3 Server instance.
     *
     * @var TeamSpeak3_Node_Server
     */
    protected $ts3;

    /**
     * AbstractTeamSpeak3 constructor.
     *
     * @param TeamSpeak3_Node_Server $ts3 TeamSpeak3_Node_Server instance.
     */
    public function __construct(TeamSpeak3_Node_Server $ts3)
    {
        $this->ts3 = $ts3;
    }
}
