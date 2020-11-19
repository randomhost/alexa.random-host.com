<?php

namespace randomhost\Alexa\Responder\Intent\Fun;

use randomhost\Alexa\Responder\AbstractResponder;
use randomhost\Alexa\Responder\ResponderInterface;
use RuntimeException;

/**
 * RandomFact Intent.
 *
 * @author    Ch'Ih-Yu <chi-yu@web.de>
 * @copyright 2020 random-host.tv
 * @license   https://opensource.org/licenses/BSD-3-Clause  BSD License (3 Clause)
 *
 * @see       https://random-host.tv
 */
class RandomFact extends AbstractResponder implements ResponderInterface
{
    /**
     * Runs the Responder.
     *
     * @return $this
     */
    public function run(): ResponderInterface
    {
        $randomFacts = $this->config->get('response', 'randomFact');
        if (is_null($randomFacts) || empty($randomFacts)) {
            $this->response
                ->respondSSML(
                    $this->withSound(
                        self::SOUND_ERROR,
                        'Es wurden leider keine Facts configuriert.'
                    )
                )
                ->endSession(true)
            ;

            return $this;
        }

        return $this->setupResponse(
            $this->randomizeResponseText($randomFacts)
        );
    }

    /**
     * Handles the response data and sets up the response.
     *
     * @param array $randomFact Random fact data array.
     *
     * @return $this
     */
    private function setupResponse(array $randomFact): ResponderInterface
    {
        $speech = '';

        if (!empty($randomFact['text'])) {
            // include card image when available
            $image = '';
            if (!empty($randomFact['image'])) {
                $image = $this->buildImageUrl($randomFact['image']);
            }

            // use text response for card
            $this->response->withCard(
                'Random Fact',
                $randomFact['text'],
                $image
            );

            // init spoken response with plain text
            $speech = $randomFact['text'];
        }

        // prefer SSML markup when available
        if (!empty($randomFact['ssml'])) {
            $speech = $randomFact['ssml'];
        }

        if (empty($speech)) {
            throw new RuntimeException(
                'Invalid data format for RandomFact intent'
            );
        }

        $this->response
            ->respondSSML(
                $this->withSound(
                    self::SOUND_CONFIRM,
                    $speech
                )
            )
            ->endSession(true)
        ;

        return $this;
    }
}
