<?php

namespace randomhost\Alexa\Responder\Intent\Builtin;

use randomhost\Alexa\Responder\AbstractResponder;
use randomhost\Alexa\Responder\ResponderInterface;

/**
 * Stop Intent.
 *
 * @author    Ch'Ih-Yu <chi-yu@web.de>
 * @copyright 2017 random-host.com
 * @license   http://www.debian.org/misc/bsd.license  BSD License (3 Clause)
 * @link      http://composer.random-host.com
 */
class Stop extends AbstractResponder implements ResponderInterface
{
    /**
     * Runs the Responder.
     *
     * @return $this
     */
    public function run()
    {
        $responses = $this->config->get('response', 'stop');
        if (is_null($responses) || empty($responses)) {
            $responses = array('Programm beendet.');
        }

        $this->response
            ->respondSSML(
                sprintf(
                    $this->withSound(self::SOUND_STOP, '%s'),
                    $this->randomizeResponseText($responses)
                )
            )
            ->endSession(true);

        return $this;
    }
}
