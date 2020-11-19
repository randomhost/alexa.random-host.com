<?php

namespace randomhost\Alexa\Responder\Intent\TeamSpeak3;

use randomhost\Alexa\Responder\ResponderInterface;
use TeamSpeak3_Node_Client;

/**
 * Handles the TeamSpeakUserList intent.
 *
 * @author    Ch'Ih-Yu <chi-yu@web.de>
 * @copyright 2020 random-host.tv
 * @license   https://opensource.org/licenses/BSD-3-Clause  BSD License (3 Clause)
 *
 * @see       https://random-host.tv
 */
class UserList extends AbstractTeamSpeak3 implements ResponderInterface
{
    /**
     * Runs the Responder.
     *
     * @return $this
     */
    public function run(): ResponderInterface
    {
        $clientsRaw = $this->ts3->clientList();
        if (!is_array($clientsRaw)) {
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

        $responses = $this->config->get('response', 'teamspeakUserList');
        if (is_null($responses) || empty($responses)) {
            $responses = [
                'noUsers' => ['Es sind keine Benutzer auf dem Server.'],
                'oneUser' => ['%s ist der einzige Benutzer auf dem Server.'],
                'nUsers' => ['Die folgenden Benutzer sind auf dem Server:'],
            ];
        }

        $clients = $this->filterClientList($clientsRaw);
        $clientsSsml = $this->filterClientListForSsml($clients);

        $clientCount = count($clients);

        $this->response->withCard(
            'TeamSpeak Benutzer Liste',
            sprintf(
                "Es %s sich %u Benutzer auf dem Server.\r\n%s",
                (1 === $clientCount) ? 'befindet' : 'befinden',
                $clientCount,
                implode(",\r\n", $clients)
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
                            sprintf(
                                $this->randomizeResponseText($responses['oneUser']),
                                reset($clientsSsml)
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
                            $this->randomizeResponseText($responses['nUsers'])
                            .".\r\n".
                            implode(",\r\n", $clientsSsml)
                        )
                    )
                    ->endSession(true)
                ;

                return $this;
        }
    }

    /**
     * Filters the given list of client nodes.
     *
     * @param TeamSpeak3_Node_Client[] $clientsRaw Array of TeamSpeak3_Node_Client instances.
     *
     * @return TeamSpeak3_Node_Client[]
     */
    private function filterClientList(array $clientsRaw): array
    {
        $clients = [];
        foreach ($clientsRaw as $client) {
            if ($client['client_type']) {
                continue;
            }
            $clients[] = $client;
        }

        return $clients;
    }

    /**
     * Filters the given list of client nodes so they can be used in an SSML response.
     *
     * @param TeamSpeak3_Node_Client[] $clientsRaw Array of TeamSpeak3_Node_Client instances.
     *
     * @return TeamSpeak3_Node_Client[]
     */
    private function filterClientListForSsml(array $clientsRaw): array
    {
        $clients = [];
        foreach ($clientsRaw as $client) {
            $clients[] = htmlspecialchars($client, ENT_XML1);
        }

        return $clients;
    }
}
