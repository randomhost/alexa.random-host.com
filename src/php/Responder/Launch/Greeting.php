<?php

namespace randomhost\Alexa\Responder\Launch;

use randomhost\Alexa\Responder\AbstractResponder;
use randomhost\Alexa\Responder\ResponderInterface;

/**
 * Commands responder.
 *
 * @author    Ch'Ih-Yu <chi-yu@web.de>
 * @copyright 2017 random-host.com
 * @license   http://www.debian.org/misc/bsd.license  BSD License (3 Clause)
 * @link      http://composer.random-host.com
 */
class Greeting extends AbstractResponder implements ResponderInterface
{
    /**
     * Runs the Responder.
     *
     * @return $this
     */
    public function run()
    {
        $greetings = $this->config->get('response', 'greeting');
        if (is_null($greetings) || empty($greetings)) {
            $this->response
                ->respond('Hallo.')
                ->endSession(false);

            return $this;
        }

        $this->response
            ->respond($this->randomizeResponseText($greetings))
            ->endSession(false);

        return $this;
    }
}
