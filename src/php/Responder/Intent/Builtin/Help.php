<?php

namespace randomhost\Alexa\Responder\Intent\Builtin;

use randomhost\Alexa\Responder\AbstractResponder;
use randomhost\Alexa\Responder\ResponderInterface;

/**
 * Help Intent.
 *
 * @author    Ch'Ih-Yu <chi-yu@web.de>
 * @copyright 2017 random-host.com
 * @license   http://www.debian.org/misc/bsd.license  BSD License (3 Clause)
 * @link      http://composer.random-host.com
 */
class Help extends AbstractResponder implements ResponderInterface
{
    /**
     * Help output.
     *
     * @var array
     */
    protected $helpOutput
        = array(
            "In der Rubrik Minecraft:",
            "Anzahl der Spieler,",
            "Spieler Liste,",
            "Version des Servers.",
            "In der Rubrik Sonstiges:",
            "Random Facts.",
        );

    /**
     * Runs the Responder.
     *
     * @return $this
     */
    public function run()
    {
        $responses = $this->config->get('response', 'help');
        if (is_null($responses) || empty($responses)) {
            $responses = array('Folgende Funktionen sind verfügbar:');
        }

        $this->response
            ->respond(
                $this->randomizeResponseText($responses)
                ."\r\n".
                implode("\r\n", $this->helpOutput)
            )
            ->endSession(false);

        return $this;
    }
}
