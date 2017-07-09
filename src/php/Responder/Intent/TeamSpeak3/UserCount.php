<?php

namespace randomhost\Alexa\Responder\Intent\TeamSpeak3;

/**
 * Handles the TeamSpeakUserCount intent.
 *
 * @author    Ch'Ih-Yu <chi-yu@web.de>
 * @copyright 2017 random-host.com
 * @license   http://www.debian.org/misc/bsd.license  BSD License (3 Clause)
 * @link      http://composer.random-host.com
 */
class UserCount extends AbstractTeamSpeak3
{
    /**
     * Runs the Responder.
     *
     * @return $this
     */
    public function run()
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
                ->endSession(true);

            return $this;
        }

        $responses = $this->config->get('response', 'teamspeakUserCount');
        if (is_null($responses) || empty($responses)) {
            $responses = array(
                "noUsers" => array("Es sind keine Benutzer auf dem Server."),
                "oneUser" => array("Es ist genau ein Benutzer auf dem Server."),
                "nUsers" => array("Es sind %u Benutzer auf dem Server."),
            );
        }

        $clientCount = 0;
        foreach ($clients as $client) {
            if ($client["client_type"]) {
                continue;
            }
            $clientCount++;
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
            case ($clientCount <= 0):
                $this->response
                    ->respondSSML(
                        $this->withSound(
                            self::SOUND_CONFIRM,
                            $this->randomizeResponseText($responses['noUsers'])
                        )
                    )
                    ->endSession(false);

                return $this;
            case ($clientCount === 1):
                $this->response
                    ->respondSSML(
                        $this->withSound(
                            self::SOUND_CONFIRM,
                            $this->randomizeResponseText($responses['oneUser'])
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
                                $this->randomizeResponseText($responses['nUsers']),
                                $clientCount
                            )
                        )
                    )
                    ->endSession(false);

                return $this;
        }
    }
}
