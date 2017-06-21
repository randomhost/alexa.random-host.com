<?php

namespace randomhost\Alexa\Responder\Intent\System;

use randomhost\Alexa\Responder\AbstractResponder;
use randomhost\Alexa\Responder\ResponderInterface;

/**
 * Load Intent.
 *
 * @author    Ch'Ih-Yu <chi-yu@web.de>
 * @copyright 2017 random-host.com
 * @license   http://www.debian.org/misc/bsd.license  BSD License (3 Clause)
 * @link      http://composer.random-host.com
 */
class Load extends AbstractResponder implements ResponderInterface
{
    /**
     * Runs the Responder.
     *
     * @return $this
     */
    public function run()
    {
        $load = $this->fetchSystemLoad();

        $this->response
            ->respondSSML($load)
            ->endSession(false);

        return $this;
    }

    /**
     * Returns the system uptime.
     *
     * @return float
     */
    private function fetchSystemLoad()
    {
        $rawResult = shell_exec('uptime');

        $load = preg_match(
            '#load average: ([0-9]+\.[0-9]+), ([0-9]+\.[0-9]+), ([0-9]+\.[0-9]+)#',
            $rawResult,
            $matches
        );

        if ($load !== 1) {
            $response = $this->withSound(
                self::SOUND_ERROR,
                'Die Auslastung konnte leider nicht ermittelt werden.'
            );
        } else {
            $loadLastMinute = (float)$matches[1];
            $loadLastFive = (float)$matches[2];
            $loadLastFifteen = (float)$matches[3];

            $response = $this->withSound(
                self::SOUND_CONFIRM,
                sprintf(
                    'Die durchschnittliche Auslastung beträgt: '.
                    '<say-as interpret-as="spell-out">%s</say-as> in der letzten Minute, '.
                    '<say-as interpret-as="spell-out">%s</say-as> in den letzten 5 Minuten und '.
                    '<say-as interpret-as="spell-out">%s</say-as> in den letzten 15 Minuten.',
                    str_replace('.', ',', $loadLastMinute),
                    str_replace('.', ',', $loadLastFive),
                    str_replace('.', ',', $loadLastFifteen)
                )
            );
        }


        return $response;
    }
}
