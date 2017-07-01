<?php

namespace randomhost\Alexa\Responder\Intent\Minecraft;

use randomhost\Alexa\Responder\ResponderInterface;

/**
 * Handles the MinecraftPlayerList intent.
 *
 * @author    Ch'Ih-Yu <chi-yu@web.de>
 * @copyright 2017 random-host.com
 * @license   http://www.debian.org/misc/bsd.license  BSD License (3 Clause)
 * @link      http://composer.random-host.com
 */
class PlayerList extends AbstractMinecraft implements ResponderInterface
{
    /**
     * Runs the Responder.
     *
     * @return $this
     */
    public function run()
    {
        if (!is_array($this->data) || !array_key_exists('players', $this->data)) {
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

        $responses = $this->config->get('response', 'minecraftPlayerList');
        if (is_null($responses) || empty($responses)) {
            $responses = array(
                "noPlayers1" => array("Es sind keine Spieler auf dem Server."),
                "noPlayers2" => array(""),
                "onePlayer1" => array("%s ist der einzige Spieler dort."),
                "onePlayer2" => array(""),
                "nPlayers" => array("Die folgenden Spieler sind online:"),
            );
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
                    ->endSession(false);

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
                    ->endSession(false);

                return $this;
        }
    }
}
