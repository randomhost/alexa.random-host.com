<?php

namespace randomhost\Alexa\Responder\Intent\Fun;

use randomhost\Alexa\Responder\AbstractResponder;
use randomhost\Alexa\Responder\ResponderInterface;

/**
 * Surprise Intent.
 *
 * @author    Ch'Ih-Yu <chi-yu@web.de>
 * @copyright 2020 random-host.tv
 * @license   https://opensource.org/licenses/BSD-3-Clause  BSD License (3 Clause)
 *
 * @see       https://random-host.tv
 */
class Surprise extends AbstractResponder implements ResponderInterface
{
    /**
     * Runs the Responder.
     *
     * @return $this
     */
    public function run(): ResponderInterface
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
                ->endSession(true)
            ;

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
            ->endSession(true)
        ;

        return $this;
    }
}
