<?php

namespace randomhost\Alexa\Responder;

/**
 * Unsupported Responder.
 *
 * @author    Ch'Ih-Yu <chi-yu@web.de>
 * @copyright 2020 random-host.tv
 * @license   https://opensource.org/licenses/BSD-3-Clause  BSD License (3 Clause)
 *
 * @see      https://random-host.tv
 */
class Unsupported extends AbstractResponder implements ResponderInterface
{
    /**
     * Runs the Responder.
     *
     * @return $this
     */
    public function run(): ResponderInterface
    {
        $responses = $this->config->get('response', 'unsupported');
        if (is_null($responses) || empty($responses)) {
            $responses = ['Entschuldige. Diese Anfrage verstehe ich nicht.'];
        }

        $this->response
            ->respond($this->randomizeResponseText($responses))
            ->endSession(true)
        ;

        return $this;
    }
}
