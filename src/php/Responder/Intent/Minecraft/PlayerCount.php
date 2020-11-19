<?php

namespace randomhost\Alexa\Responder\Intent\Minecraft;

use randomhost\Alexa\Responder\ResponderInterface;

/**
 * Handles the MinecraftPlayerCount intent.
 *
 * @author    Ch'Ih-Yu <chi-yu@web.de>
 * @copyright 2020 random-host.tv
 * @license   https://opensource.org/licenses/BSD-3-Clause  BSD License (3 Clause)
 *
 * @see       https://random-host.tv
 */
class PlayerCount extends AbstractMinecraft implements ResponderInterface
{
    /**
     * Runs the Responder.
     *
     * @return $this
     */
    public function run(): ResponderInterface
    {
        if (!is_array($this->data) || !array_key_exists('player_count', $this->data)) {
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

        $responses = $this->config->get('response', 'minecraftPlayerCount');
        if (is_null($responses) || empty($responses)) {
            $responses = [
                'noPlayers1' => ['Es sind keine Spieler auf dem Server.'],
                'noPlayers2' => [''],
                'onePlayer1' => ['Es ist genau ein Spieler dort.'],
                'onePlayer2' => [''],
                'twoPlayers1' => ['Es sind genau zwei Spieler dort.'],
                'twoPlayers2' => [''],
                'nPlayers1' => ['Es sind %u Spieler auf dem Server.'],
                'nPlayers2' => [''],
            ];
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
            case $playerCount <= 0:
                $this->response
                    ->respondSSML(
                        $this->withSound(
                            self::SOUND_CONFIRM,
                            $this->randomizeResponseText($responses['noPlayers1'])
                            ."\r\n".
                            $this->randomizeResponseText($responses['noPlayers2'])
                        )
                    )
                    ->endSession(true)
                ;

                return $this;
            case 1 === $playerCount:
                $this->response
                    ->respondSSML(
                        $this->withSound(
                            self::SOUND_CONFIRM,
                            $this->randomizeResponseText($responses['onePlayer1'])
                            .".\r\n".
                            $this->randomizeResponseText($responses['onePlayer2'])
                        )
                    )
                    ->endSession(true)
                ;

                return $this;
            case 2 === $playerCount:
                $this->response
                    ->respondSSML(
                        $this->withSound(
                            self::SOUND_CONFIRM,
                            $this->randomizeResponseText($responses['twoPlayers1'])
                            ."\r\n".
                            $this->randomizeResponseText($responses['twoPlayers2'])
                        )
                    )
                    ->endSession(true)
                ;

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
                    ->endSession(true)
                ;

                return $this;
        }
    }
}
