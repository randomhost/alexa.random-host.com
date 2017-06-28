<?php

namespace randomhost\Alexa\Responder\Intent\Fun;

use randomhost\Alexa\Responder\AbstractResponder;
use randomhost\Alexa\Responder\ResponderInterface;

/**
 * Surprise Intent.
 *
 * @author    Ch'Ih-Yu <chi-yu@web.de>
 * @copyright 2017 random-host.com
 * @license   http://www.debian.org/misc/bsd.license  BSD License (3 Clause)
 * @link      http://composer.random-host.com
 */
class Surprise extends AbstractResponder implements ResponderInterface
{
    /**
     * Runs the Responder.
     *
     * @return $this
     */
    public function run()
    {
        $surprises = $this->config->get('response', 'surprise');
        if (is_null($surprises) || empty($surprises)) {
            $this->response
                ->respondSSML(
                    $this->withSound(
                        self::SOUND_ERROR,
                        'Dieser Skill beinhaltet keine Easter Eggs.'
                    )
                )
                ->endSession(true);

            return $this;
        }

        $surprise = $this->randomizeResponseText($surprises);

        if (!empty($surprise['description'])) {
            $this->response->withCard(
                'Überraschung',
                $surprise['description']
            );
        }

        $this->response
            ->respondSSML(
                sprintf(
                    '<speak>%1$s%2$s</speak>',
                    empty($surprise['audio']) ? '' : $this->buildSoundTag($surprise['audio']),
                    empty($surprise['text']) ? '' : $surprise['text']
                )
            )
            ->endSession(true);

        return $this;
    }
}
