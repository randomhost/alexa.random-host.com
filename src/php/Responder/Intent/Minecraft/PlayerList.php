<?php

namespace randomhost\Alexa\Responder\Intent\Minecraft;

use randomhost\Alexa\Responder\ResponderInterface;

/**
 * Handles the MinecraftPlayerList intent.
 *
 * @author    Ch'Ih-Yu <chi-yu@web.de>
 * @copyright 2020 random-host.tv
 * @license   https://opensource.org/licenses/BSD-3-Clause  BSD License (3 Clause)
 *
 * @see       https://random-host.tv
 */
class PlayerList extends AbstractMinecraft implements ResponderInterface
{
    /**
     * Runs the Responder.
     *
     * @return $this
     */
    public function run(): ResponderInterface
    {
        if (!is_array($this->data) || !array_key_exists('players', $this->data)) {
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

        $responses = $this->config->get('response', 'minecraftPlayerList');
        if (is_null($responses) || empty($responses)) {
            $responses = [
                'noPlayers1' => ['Es sind keine Spieler auf dem Server.'],
                'noPlayers2' => [''],
                'onePlayer1' => ['%s ist der einzige Spieler dort.'],
                'onePlayer2' => [''],
                'nPlayers' => ['Die folgenden Spieler sind online:'],
            ];
        }

        $players = $this->data['players'];

        $playerCount = count($players);

        $this->response->withCard(
            'Minecraft Spieler Liste',
            sprintf(
                "Es %s sich %u Spieler auf dem Server.\r\n%s",
                (1 === $playerCount) ? 'befindet' : 'befinden',
                $playerCount,
                implode(",\r\n", $players)
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
                            sprintf(
                                $this->randomizeResponseText($responses['onePlayer1']),
                                reset($players)
                            )
                            .".\r\n".
                            sprintf(
                                $this->randomizeResponseText($responses['onePlayer2']),
                                reset($players)
                            )
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
                            $this->randomizeResponseText($responses['nPlayers'])
                            .".\r\n".
                            implode(",\r\n", $players)
                        )
                    )
                    ->endSession(true)
                ;

                return $this;
        }
    }
}
