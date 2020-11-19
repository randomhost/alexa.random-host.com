<?php

namespace randomhost\Alexa\Responder\Intent\TeamSpeak3;

use randomhost\Alexa\Responder\ResponderInterface;

/**
 * Handles the TeamSpeakUserCount intent.
 *
 * @author    Ch'Ih-Yu <chi-yu@web.de>
 * @copyright 2020 random-host.tv
 * @license   https://opensource.org/licenses/BSD-3-Clause  BSD License (3 Clause)
 *
 * @see       https://random-host.tv
 */
class UserCount extends AbstractTeamSpeak3 implements ResponderInterface
{
    /**
     * Runs the Responder.
     *
     * @return $this
     */
    public function run(): ResponderInterface
    {
        $clients = $this->ts3->clientList();
        if (!is_array($clients)) {
            $this->response
                ->respondSSML(
                    $this->withSound(
                        self::SOUND_ERROR,
                        'Tut mir leid. Der TeamSpeak Server hat leider nicht geantwortet.'
                    )
                )
                ->endSession(true)
            ;

            return $this;
        }

        $responses = $this->config->get('response', 'teamspeakUserCount');
        if (is_null($responses) || empty($responses)) {
            $responses = [
                'noUsers' => ['Es sind keine Benutzer auf dem Server.'],
                'oneUser' => ['Es ist genau ein Benutzer auf dem Server.'],
                'nUsers' => ['Es sind %u Benutzer auf dem Server.'],
            ];
        }

        $clientCount = 0;
        foreach ($clients as $client) {
            if ($client['client_type']) {
                continue;
            }
            ++$clientCount;
        }

        $this->response->withCard(
            'TeamSpeak Benutzer',
            sprintf(
                'Es %s sich %u Benutzer auf dem Server.',
                (1 === $clientCount) ? 'befindet' : 'befinden',
                $clientCount
            ),
            $this->buildImageUrl('teamspeak-large.jpg')
        );

        switch (true) {
            case $clientCount <= 0:
                $this->response
                    ->respondSSML(
                        $this->withSound(
                            self::SOUND_CONFIRM,
                            $this->randomizeResponseText($responses['noUsers'])
                        )
                    )
                    ->endSession(true)
                ;

                return $this;
            case 1 === $clientCount:
                $this->response
                    ->respondSSML(
                        $this->withSound(
                            self::SOUND_CONFIRM,
                            $this->randomizeResponseText($responses['oneUser'])
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
                                $this->randomizeResponseText($responses['nUsers']),
                                $clientCount
                            )
                        )
                    )
                    ->endSession(true)
                ;

                return $this;
        }
    }
}
