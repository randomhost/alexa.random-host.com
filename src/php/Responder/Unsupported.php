<?php

namespace randomhost\Alexa\Responder;

/**
 * Unsupported Responder.
 *
 * @author    Ch'Ih-Yu <chi-yu@web.de>
 * @copyright 2017 random-host.com
 * @license   http://www.debian.org/misc/bsd.license  BSD License (3 Clause)
 * @link      http://composer.random-host.com
 */
class Unsupported extends AbstractResponder implements ResponderInterface
{
    /**
     * Runs the Responder.
     *
     * @return $this
     */
    public function run()
    {
        $responses = $this->config->get('response', 'unsupported');
        if (is_null($responses) || empty($responses)) {
            $responses = array('Entschuldige. Diese Anfrage verstehe ich nicht.');
        }

        $this->response
            ->respond($this->randomizeResponseText($responses))
            ->endSession(true);

        return $this;
    }
}
