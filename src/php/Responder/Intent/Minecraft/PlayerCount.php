<?php

namespace randomhost\Alexa\Responder\Intent\Minecraft;

use randomhost\Alexa\Responder\ResponderInterface;

/**
 * Handles the MinecraftPlayerCount intent.
 *
 * @author    Ch'Ih-Yu <chi-yu@web.de>
 * @copyright 2017 random-host.com
 * @license   http://www.debian.org/misc/bsd.license  BSD License (3 Clause)
 * @link      http://composer.random-host.com
 */
class PlayerCount extends AbstractMinecraft implements ResponderInterface
{
    /**
     * Runs the Responder.
     *
     * @return $this
     */
    public function run()
    {
        if (!is_array($this->data) || !array_key_exists('player_count', $this->data)) {
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

        $responses = $this->config->get('response', 'minecraftPlayerCount');
        if (is_null($responses) || empty($responses)) {
            $responses = array(
                "noPlayers1" => array("Es sind keine Spieler auf dem Server."),
                "noPlayers2" => array(""),
                "onePlayer1" => array("Es ist genau ein Spieler dort."),
                "onePlayer2" => array(""),
                "twoPlayers1" => array("Es sind genau zwei Spieler dort."),
                "twoPlayers2" => array(""),
                "nPlayers1" => array("Es sind %u Spieler auf dem Server."),
                "nPlayers2" => array(""),
            );
        }

        $playerCount = intval($this->data['player_count']);

        $this->response->withCard(
            'Minecraft Spieler',
            sprintf(
                'Es %s sich %u Spieler auf dem Server.',
                (1 === $playerCount) ? 'befindet' : 'befinden',
                $playerCount
            ),
            $this->buildImageUrl('minecraft-large.jpg')
        );

        switch (true) {
            case ($playerCount <= 0):
                $this->response
                    ->respondSSML(
                        $this->withSound(
                            self::SOUND_CONFIRM,
                            $this->randomizeResponseText($responses['noPlayers1'])
                            ."\r\n".
                            $this->randomizeResponseText($responses['noPlayers2'])
                        )
                    )
                    ->endSession(false);

                return $this;
            case ($playerCount === 1):
                $this->response
                    ->respondSSML(
                        $this->withSound(
                            self::SOUND_CONFIRM,
                            $this->randomizeResponseText($responses['onePlayer1'])
                            .".\r\n".
                            $this->randomizeResponseText($responses['onePlayer2'])
                        )
                    )
                    ->endSession(false);

                return $this;
            case ($playerCount === 2):
                $this->response
                    ->respondSSML(
                        $this->withSound(
                            self::SOUND_CONFIRM,
                            $this->randomizeResponseText($responses['twoPlayers1'])
                            ."\r\n".
                            $this->randomizeResponseText($responses['twoPlayers2'])
                        )
                    )
                    ->endSession(false);

                return $this;
            default:
                $this->response
                    ->respondSSML(
                        $this->withSound(
                            self::SOUND_CONFIRM,
                            sprintf(
                                $this->randomizeResponseText($responses['nPlayers1']),
                                $playerCount
                            )
                            ."\r\n".
                            $this->randomizeResponseText($responses['nPlayers2'])
                        )
                    )
                    ->endSession(false);

                return $this;
        }
    }
}
