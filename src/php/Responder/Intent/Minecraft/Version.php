<?php

namespace randomhost\Alexa\Responder\Intent\Minecraft;

use randomhost\Alexa\Responder\ResponderInterface;

/**
 * Handles the MinecraftVersion intent.
 *
 * @author    Ch'Ih-Yu <chi-yu@web.de>
 * @copyright 2020 random-host.tv
 * @license   https://opensource.org/licenses/BSD-3-Clause  BSD License (3 Clause)
 *
 * @see       https://random-host.tv
 */
class Version extends AbstractMinecraft implements ResponderInterface
{
    /**
     * Runs the Responder.
     *
     * @return $this
     */
    public function run(): ResponderInterface
    {
        if (!is_array($this->data) || !array_key_exists('version', $this->data)) {
            $this->response
                ->respondSSML(
                    $this->withSound(
                        self::SOUND_ERROR,
                        'Tut mir leid. Der Minecraft Server hat leider nicht geantwortet.'
                    )
                )
                ->endSession(true)
            ;

            return $this;
        }

        $version = $this->data['version'];

        $responses = $this->config->get('response', 'minecraftVersion');
        if (is_null($responses) || empty($responses)) {
            $responses = ['Es läuft Version %s.'];
        }

        $this->response
            ->respondSSML(
                $this->withSound(
                    self::SOUND_CONFIRM,
                    sprintf($this->randomizeResponseText($responses), $version)
                )
            )
            ->withCard(
                'Minecraft Server Version',
                sprintf(
                    'Aktuell läuft Version %s.',
                    $version
                ),
                $this->buildImageUrl('minecraft-large.jpg')
            )
            ->endSession(true)
        ;

        return $this;
    }
}
