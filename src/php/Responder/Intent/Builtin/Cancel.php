<?php

namespace randomhost\Alexa\Responder\Intent\Builtin;

use randomhost\Alexa\Responder\AbstractResponder;
use randomhost\Alexa\Responder\ResponderInterface;

/**
 * Cancel Intent.
 *
 * @author    Ch'Ih-Yu <chi-yu@web.de>
 * @copyright 2017 random-host.com
 * @license   http://www.debian.org/misc/bsd.license  BSD License (3 Clause)
 * @link      http://composer.random-host.com
 */
class Cancel extends AbstractResponder implements ResponderInterface
{
    /**
     * Runs the Responder.
     *
     * @return $this
     */
    public function run()
    {
        $responses = $this->config->get('response', 'cancel');
        if (is_null($responses) || empty($responses)) {
            $responses = array('Wie du wünscht.');
        }

        $this->response
            ->respondSSML(
                sprintf(
                    $this->withSound(self::SOUND_STOP, '%s'),
                    $this->randomizeResponseText($responses)
                )
            )
            ->endSession(false);

        return $this;
    }
}
