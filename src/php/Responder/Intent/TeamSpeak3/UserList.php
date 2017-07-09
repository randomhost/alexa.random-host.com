<?php

namespace randomhost\Alexa\Responder\Intent\TeamSpeak3;

use TeamSpeak3_Node_Client;

/**
 * Handles the TeamSpeakUserList intent.
 *
 * @author    Ch'Ih-Yu <chi-yu@web.de>
 * @copyright 2017 random-host.com
 * @license   http://www.debian.org/misc/bsd.license  BSD License (3 Clause)
 * @link      http://composer.random-host.com
 */
class UserList extends AbstractTeamSpeak3
{
    /**
     * Runs the Responder.
     *
     * @return $this
     */
    public function run()
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
                ->endSession(true);

            return $this;
        }

        $responses = $this->config->get('response', 'teamspeakUserList');
        if (is_null($responses) || empty($responses)) {
            $responses = array(
                "noUsers" => array("Es sind keine Benutzer auf dem Server."),
                "oneUser" => array("%s ist der einzige Benutzer auf dem Server."),
                "nUsers" => array("Die folgenden Benutzer sind auf dem Server:"),
            );
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
                            sprintf(
                                $this->randomizeResponseText($responses['oneUser']),
                                reset($clientsSsml)
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
                            $this->randomizeResponseText($responses['nUsers'])
                            .".\r\n".
                            implode(",\r\n", $clientsSsml)
                        )
                    )
                    ->endSession(false);

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
    private function filterClientList(array $clientsRaw)
    {
        $clients = array();
        foreach ($clientsRaw as $client) {
            if ($client["client_type"]) {
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
    private function filterClientListForSsml(array $clientsRaw)
    {
        $clients = array();
        foreach ($clientsRaw as $client) {
            $clients[] = htmlspecialchars($client, ENT_XML1);
        }

        return $clients;
    }
}
