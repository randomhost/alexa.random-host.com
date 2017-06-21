<?php

namespace randomhost\Alexa\Responder\Intent\Minecraft;

use randomhost\Alexa\Responder\ResponderInterface;

/**
 * Handles the MinecraftVersion intent.
 *
 * @author    Ch'Ih-Yu <chi-yu@web.de>
 * @copyright 2017 random-host.com
 * @license   http://www.debian.org/misc/bsd.license  BSD License (3 Clause)
 * @link      http://composer.random-host.com
 */
class Version extends AbstractMinecraft implements ResponderInterface
{
    /**
     * Runs the Responder.
     *
     * @return $this
     */
    public function run()
    {
        if (!is_array($this->data) || !array_key_exists('version', $this->data)) {
            $this->response
                ->respondSSML(
                    $this->withSound(
                        self::SOUND_ERROR,
                        'Tut mir leid. Der Minecraft Server hat leider nicht geantwortet.'
                    )
                )
                ->endSession(true);

            return $this;
        }

        $version = $this->data['version'];

        $responses = $this->config->get('response', 'minecraftVersion');
        if (is_null($responses) || empty($responses)) {
            $responses = array('Es läuft Version %s.');
        }

        $this->response
            ->respondSSML(
                $this->withSound(
                    self::SOUND_CONFIRM,
                    sprintf($this->randomizeResponseText($responses), $version)
                )
            )
            ->endSession(false);

        return $this;
    }
}
