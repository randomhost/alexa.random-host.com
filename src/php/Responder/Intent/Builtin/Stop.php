<?php

namespace randomhost\Alexa\Responder\Intent\Builtin;

use randomhost\Alexa\Responder\AbstractResponder;
use randomhost\Alexa\Responder\ResponderInterface;

/**
 * Stop Intent.
 *
 * @author    Ch'Ih-Yu <chi-yu@web.de>
 * @copyright 2020 random-host.tv
 * @license   https://opensource.org/licenses/BSD-3-Clause  BSD License (3 Clause)
 *
 * @see       https://random-host.tv
 */
class Stop extends AbstractResponder implements ResponderInterface
{
    /**
     * Runs the Responder.
     *
     * @return $this
     */
    public function run(): ResponderInterface
    {
        $responses = $this->config->get('response', 'stop');
        if (is_null($responses) || empty($responses)) {
            $responses = ['Programm beendet.'];
        }

        $this->response
            ->respondSSML(
                sprintf(
                    $this->withSound(self::SOUND_STOP, '%s'),
                    $this->randomizeResponseText($responses)
                )
            )
            ->endSession(true)
        ;

        return $this;
    }
}
