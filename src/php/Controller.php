<?php

namespace randomhost\Alexa;

use Exception;
use InvalidArgumentException;
use randomhost\Alexa\Request\Factory as RequestFactory;
use randomhost\Alexa\Request\Request as Request;
use randomhost\Alexa\Request\Type\Intent;
use randomhost\Alexa\Request\Type\Launch;
use randomhost\Alexa\Request\Type\SessionEnded;
use randomhost\Alexa\Responder\Intent\Builtin\Cancel;
use randomhost\Alexa\Responder\Intent\Builtin\Help;
use randomhost\Alexa\Responder\Intent\Builtin\Stop;
use randomhost\Alexa\Responder\Intent\Fun\RandomFact;
use randomhost\Alexa\Responder\Intent\Fun\Surprise;
use randomhost\Alexa\Responder\Intent\Minecraft\AbstractMinecraft;
use randomhost\Alexa\Responder\Intent\Minecraft\PlayerCount as MinecraftPlayerCount;
use randomhost\Alexa\Responder\Intent\Minecraft\PlayerList as MinecraftPlayerList;
use randomhost\Alexa\Responder\Intent\Minecraft\Version as MinecraftVersion;
use randomhost\Alexa\Responder\Intent\System\Load;
use randomhost\Alexa\Responder\Intent\System\Updates;
use randomhost\Alexa\Responder\Intent\System\Uptime;
use randomhost\Alexa\Responder\Intent\TeamSpeak3\AbstractTeamSpeak3;
use randomhost\Alexa\Responder\Intent\TeamSpeak3\UserCount as TeamSpeak3UserCount;
use randomhost\Alexa\Responder\Intent\TeamSpeak3\UserList as TeamSpeak3UserList;
use randomhost\Alexa\Responder\Launch\Greeting;
use randomhost\Alexa\Responder\ResponderInterface;
use randomhost\Alexa\Responder\Unsupported;
use randomhost\Alexa\Response\Response as Response;
use randomhost\Minecraft\Status as MinecraftStatus;
use TeamSpeak3;
use TeamSpeak3_Node_Server;

/**
 * Controller for Alexa skills.
 *
 * @author    Ch'Ih-Yu <chi-yu@web.de>
 * @copyright 2020 random-host.tv
 * @license   https://opensource.org/licenses/BSD-3-Clause  BSD License (3 Clause)
 *
 * @see       https://random-host.tv
 */
class Controller
{
    /**
     * Response instance.
     *
     * @var Response
     */
    private $response;

    /**
     * Configuration instance.
     *
     * @var Configuration
     */
    private $configuration;

    /**
     * Controller constructor.
     *
     * @param string $config Optional: Configuration file name without file extension.
     */
    public function __construct(string $config = 'config')
    {
        $this->response = new Response();
        $this->configuration = new Configuration($config);
    }

    /**
     * Runs the controller.
     */
    public function run(): void
    {
        try {
            $rawRequest = $this->fetchRawRequest();

            $request = $this->buildRequest($rawRequest);

            switch (true) {
                case $request instanceof Intent:
                    $responder = $this->buildResponderForIntentRequest($request);
                    $this->sendResponse($responder);

                    break;
                case $request instanceof Launch:
                    $responder = new Greeting();
                    $this->sendResponse($responder);

                    break;
                case $request instanceof SessionEnded:
                    // log sessions which ended due to processing errors
                    if ('ERROR' === $request->getReason()) {
                        trigger_error(
                            'Session ended with error',
                            E_USER_WARNING
                        );
                    }

                    $this->renderResponse(null);

                    break;
                default:
                    trigger_error(
                        'Unsupported request type '.var_export(get_class($request), true),
                        E_USER_WARNING
                    );

                    $this->renderResponse(null);
            }
        } catch (InvalidArgumentException $e) {
            header('HTTP/1.1 400 Bad Request');
            header('Status: 400 Bad Request');
            trigger_error($e->getMessage(), E_USER_NOTICE);
            exit;
        } catch (Exception $e) {
            header('HTTP/1.1 503 Service Temporarily Unavailable');
            header('Status: 503 Service Temporarily Unavailable');
            header('Retry-After: 60');
            trigger_error($e->getMessage(), E_USER_WARNING);
            exit;
        }
    }

    /**
     * Returns the raw request body.
     *
     * @throws InvalidArgumentException Thrown in case the request body is missing.
     *
     * @return string
     */
    protected function fetchRawRequest(): string
    {
        $rawRequest = file_get_contents('php://input');
        if (empty($rawRequest)) {
            throw new InvalidArgumentException(
                'Invalid call. No request body.'
            );
        }

        return $rawRequest;
    }

    /**
     * Returns a Request object for the given raw request data.
     *
     * @param string $rawData Raw request data.
     *
     * @return Request
     */
    protected function buildRequest(string $rawData): Request
    {
        $factory = new RequestFactory();

        return $factory->getInstanceForData(
            $rawData,
            $this->configuration->getAppId()
        );
    }

    /**
     * Returns a Responder instance for the given IntentRequest instance.
     *
     * @param Intent $request IntentRequest instance.
     *
     * @return ResponderInterface
     */
    protected function buildResponderForIntentRequest(Intent $request): ResponderInterface
    {
        $intentName = $request->getIntentName();

        switch ($intentName) {
            case 'AMAZON.HelpIntent':
                return new Help();
            case 'AMAZON.CancelIntent':
                return new Cancel();
            case 'AMAZON.StopIntent':
                return new Stop();
            case 0 === strpos($intentName, 'Minecraft'):
                $minecraftHost = $this->configuration->get('minecraft', 'host');
                if (is_null($minecraftHost)) {
                    return new Unsupported();
                }
                $mcStatus = new MinecraftStatus($minecraftHost);
                $mcData = $mcStatus->query(true);

                return $this->buildResponderForMinecraftIntent($intentName, $mcData);
            case 0 === strpos($intentName, 'TeamSpeak'):
                $ts3Uri = $this->configuration->get('teamspeak', 'uri');
                if (is_null($ts3Uri)) {
                    return new Unsupported();
                }
                $ts3 = TeamSpeak3::factory($ts3Uri);

                return $this->buildResponderForTeamSpeak3Intent($intentName, $ts3);
            case 'RandomFactIntent':
                return new RandomFact();
            case 'SurpriseIntent':
                return new Surprise();
            case 'LoadIntent':
                return new Load();
            case 'UpdatesIntent':
                return new Updates();
            case 'UptimeIntent':
                return new Uptime();
            default:
                trigger_error(
                    sprintf(
                        'Unsupported intent name %s ',
                        var_export($intentName, true)
                    ),
                    E_USER_WARNING
                );

                return new Unsupported();
        }
    }

    /**
     * Returns a Minecraft Intent.
     *
     * @param string $intentName Intent name.
     * @param array  $data       Minecraft data.
     *
     * @return MinecraftPlayerCount|MinecraftPlayerList|MinecraftVersion
     */
    protected function buildResponderForMinecraftIntent(string $intentName, array $data): AbstractMinecraft
    {
        switch ($intentName) {
            case 'MinecraftPlayerCountIntent':
                $responder = new MinecraftPlayerCount($data);

                break;
            case 'MinecraftPlayerListIntent':
                $responder = new MinecraftPlayerList($data);

                break;
            case 'MinecraftVersionIntent':
            default:
                $responder = new MinecraftVersion($data);

                break;
        }

        return $responder;
    }

    /**
     * Returns a TeamSpeak 3 Intent.
     *
     * @param string                 $intentName Intent name.
     * @param TeamSpeak3_Node_Server $ts3        TeamSpeak3_Node_Host instance.
     *
     * @return TeamSpeak3UserCount|TeamSpeak3UserList
     */
    protected function buildResponderForTeamSpeak3Intent(
        string $intentName,
        TeamSpeak3_Node_Server $ts3
    ): AbstractTeamSpeak3 {
        switch ($intentName) {
            case 'TeamSpeakUserListIntent':
                $responder = new TeamSpeak3UserList($ts3);

                break;
            case 'TeamSpeakUserCountIntent':
            default:
                $responder = new TeamSpeak3UserCount($ts3);

                break;
        }

        return $responder;
    }

    /**
     * Runs the given responder and renders the response.
     *
     * @param ResponderInterface $responder ResponderInterface implementation.
     */
    protected function sendResponse(ResponderInterface $responder): void
    {
        $responder
            ->setConfiguration($this->configuration)
            ->setResponse($this->response)
            ->run()
        ;

        $this->renderResponse($this->response->render());
    }

    /**
     * Renders the given data as JSON string.
     *
     * @param mixed $data Response data.
     */
    protected function renderResponse($data): void
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
