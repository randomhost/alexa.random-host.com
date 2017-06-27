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
use randomhost\Alexa\Responder\Intent\Minecraft\PlayerCount as MinecraftPlayerCount;
use randomhost\Alexa\Responder\Intent\Minecraft\PlayerList as MinecraftPlayerList;
use randomhost\Alexa\Responder\Intent\Minecraft\Version as MinecraftVersion;
use randomhost\Alexa\Responder\Intent\System\Load;
use randomhost\Alexa\Responder\Intent\System\Updates;
use randomhost\Alexa\Responder\Intent\System\Uptime;
use randomhost\Alexa\Responder\Launch\Greeting;
use randomhost\Alexa\Responder\ResponderInterface;
use randomhost\Alexa\Responder\Unsupported;
use randomhost\Alexa\Response\Response as Response;
use randomhost\Minecraft\Status as MinecraftStatus;

/**
 * Controller for Alexa skills.
 *
 * @author    Ch'Ih-Yu <chi-yu@web.de>
 * @copyright 2017 random-host.com
 * @license   http://www.debian.org/misc/bsd.license  BSD License (3 Clause)
 * @link      http://composer.random-host.com
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
     * Controller constructor.
     *
     * @param string $config Optional: Configuration file name without file extension.
     */
    public function __construct($config = 'config')
    {
        $this->response = new Response();
        $this->configuration = new Configuration($config);
    }

    /**
     * Runs the controller.
     */
    public function run()
    {
        try {
            $rawRequest = $this->fetchRawRequest();

            $request = $this->buildRequest($rawRequest);

            switch (true) {
                case ($request instanceof Intent):
                    $responder = $this->buildResponderForIntentRequest($request);
                    $this->sendResponse($responder);
                    break;
                case ($request instanceof Launch):
                    $responder = new Greeting();
                    $this->sendResponse($responder);
                    break;
                case ($request instanceof SessionEnded):
                    // log sessions which ended due to processing errors
                    if ($request->getReason() === 'ERROR') {
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
        } catch (Exception $e) {
            trigger_error($e->getMessage());
        }
    }

    /**
     * Returns the raw request body.
     *
     * @return string
     *
     * @throws InvalidArgumentException Thrown in case the request body is missing.
     */
    protected function fetchRawRequest()
    {
        $rawRequest = file_get_contents('php://input');
        if (false === $rawRequest) {
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
    protected function buildRequest($rawData)
    {
        $factory = new RequestFactory();
        $request = $factory->getInstanceForData(
            $rawData,
            $this->configuration->getAppId()
        );

        return $request;
    }

    /**
     * Returns a Responder instance for the given IntentRequest instance.
     *
     * @param Intent $request IntentRequest instance.
     *
     * @return ResponderInterface
     */
    protected function buildResponderForIntentRequest(Intent $request)
    {
        $intentName = $request->getIntentName();

        switch ($intentName) {
            case 'AMAZON.HelpIntent':
                return new Help();
            case 'AMAZON.CancelIntent':
                return new Cancel();
            case 'AMAZON.StopIntent':
                return new Stop();
            case (strpos($intentName, 'Minecraft') === 0):
                $mcStatus = new MinecraftStatus('localhost');
                $mcData = $mcStatus->query(true);

                return $this->buildResponderForMinecraftIntent($intentName, $mcData);
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
    protected function buildResponderForMinecraftIntent($intentName, $data)
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
     * Runs the given responder and renders the response.
     *
     * @param ResponderInterface $responder ResponderInterface implementation.
     */
    protected function sendResponse(ResponderInterface $responder)
    {
        $responder
            ->setConfiguration($this->configuration)
            ->setResponse($this->response)
            ->run();

        $this->renderResponse($this->response->render());
    }

    /**
     * Renders the given data as JSON string.
     *
     * @param mixed $data Response data.
     */
    protected function renderResponse($data)
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
