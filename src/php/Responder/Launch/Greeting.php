<?php

namespace randomhost\Alexa\Responder\Launch;

use randomhost\Alexa\Responder\AbstractResponder;
use randomhost\Alexa\Responder\ResponderInterface;

/**
 * Commands responder.
 *
 * @author    Ch'Ih-Yu <chi-yu@web.de>
 * @copyright 2020 random-host.tv
 * @license   https://opensource.org/licenses/BSD-3-Clause  BSD License (3 Clause)
 *
 * @see       https://random-host.tv
 */
class Greeting extends AbstractResponder implements ResponderInterface
{
    /**
     * Runs the Responder.
     *
     * @return $this
     */
    public function run(): ResponderInterface
    {
        $greetings = $this->config->get('response', 'greeting');
        if (is_null($greetings) || empty($greetings)) {
            $this->response
                ->respond('Hallo.')
                ->endSession(false)
            ;

            return $this;
        }

        $this->response
            ->respondSSML(
                sprintf(
                    $this->withSound(self::SOUND_READY, '%s'),
                    $this->randomizeResponseText($greetings)
                )
            )
            ->endSession(false)
        ;

        return $this;
    }
}
