<?php

namespace randomhost\Alexa\Responder\Intent\System;

use randomhost\Alexa\Responder\AbstractResponder;
use randomhost\Alexa\Responder\ResponderInterface;
use RuntimeException;

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
     * Load within the last minute.
     */
    const LOAD_LAST_1 = 1;

    /**
     * Load within the last 5 minutes.
     */
    const LOAD_LAST_5 = 5;

    /**
     * Load within the last 15 minutes.
     */
    const LOAD_LAST_15 = 15;

    /**
     * Runs the Responder.
     *
     * @return $this
     */
    public function run()
    {
        try {
            $load = $this->fetchSystemLoad();

            $this->response
                ->respondSSML(
                    $this->withSound(
                        self::SOUND_CONFIRM,
                        sprintf(
                            'Die durchschnittliche Auslastung beträgt: '.
                            '<say-as interpret-as="spell-out">%s</say-as> in der letzten Minute, '.
                            '<say-as interpret-as="spell-out">%s</say-as> in den letzten 5 Minuten und '.
                            '<say-as interpret-as="spell-out">%s</say-as> in den letzten 15 Minuten.',
                            str_replace('.', ',', $load[self::LOAD_LAST_1]),
                            str_replace('.', ',', $load[self::LOAD_LAST_5]),
                            str_replace('.', ',', $load[self::LOAD_LAST_15])
                        )
                    )
                )
                ->withCard(
                    'System Auslastung',
                    sprintf(
                        "Die durchschnittliche Auslastung beträgt:\r\n".
                        "%s in der letzten Minute,\r\n".
                        "%s in den letzten 5 Minuten und\r\n".
                        "%s in den letzten 15 Minuten.",
                        str_replace('.', ',', $load[self::LOAD_LAST_1]),
                        str_replace('.', ',', $load[self::LOAD_LAST_5]),
                        str_replace('.', ',', $load[self::LOAD_LAST_15])
                    )
                )
                ->endSession(true);
        } catch (RuntimeException $e) {
            $this->response
                ->respondSSML(
                    $this->withSound(
                        self::SOUND_ERROR,
                        'Die Auslastung konnte leider nicht ermittelt werden.'
                    )
                )
                ->endSession(true);
        }

        return $this;
    }

    /**
     * Returns the system uptime.
     *
     * @return float[]
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
            throw new RuntimeException(
                'Could not fetch system load'
            );
        }

        return array(
            self::LOAD_LAST_1 => (float)$matches[1],
            self::LOAD_LAST_5 => (float)$matches[2],
            self::LOAD_LAST_15 => (float)$matches[3],
        );
    }
}
