<?php

namespace randomhost\Alexa\Responder\Intent\System;

use DateTime;
use randomhost\Alexa\Responder\AbstractResponder;
use randomhost\Alexa\Responder\ResponderInterface;
use RuntimeException;

/**
 * Uptime Intent.
 *
 * @author    Ch'Ih-Yu <chi-yu@web.de>
 * @copyright 2017 random-host.com
 * @license   http://www.debian.org/misc/bsd.license  BSD License (3 Clause)
 * @link      http://composer.random-host.com
 */
class Uptime extends AbstractResponder implements ResponderInterface
{
    /**
     * Runs the Responder.
     *
     * @return $this
     */
    public function run()
    {
        $responses = $this->config->get('response', 'uptime');
        if (is_null($responses) || empty($responses)) {
            $responses = array('Die Uptime beträgt %s.');
        }

        $uptime = $this->fetchSystemUptime();

        $this->response
            ->respondSSML(
                $this->withSound(
                    self::SOUND_CONFIRM,
                    sprintf($this->randomizeResponseText($responses), $uptime)
                )

            )
            ->endSession(false);

        return $this;
    }

    /**
     * Returns the system uptime.
     *
     * @return float
     */
    private function fetchSystemUptime()
    {
        $uptime = shell_exec('cat /proc/uptime');
        if (false === $uptime) {
            throw new RuntimeException('Failed to fetch system uptime.');
        }

        $seconds = reset(explode(' ', $uptime));
        $seconds = floatval($seconds);
        $seconds = floor($seconds);

        if (0 === $seconds) {
            throw new RuntimeException('Failed to fetch system uptime.');
        }

        $dateFrom = new DateTime('@0');
        $dateTo = new DateTime("@$seconds");

        $dateDiff = $dateFrom->diff($dateTo);

        $days = (int)$dateDiff->format('%a');
        $hours = (int)$dateDiff->format('%h');
        $minutes = (int)$dateDiff->format('%i');
        $seconds = (int)$dateDiff->format('%s');

        $strDays = ($days === 1) ? 'Tag' : 'Tage';
        $strHours = ($hours === 1) ? 'Stunde' : 'Stunden';
        $strMinutes = ($minutes === 1) ? 'Minute' : 'Minuten';
        $strSeconds = ($seconds === 1) ? 'Sekunde' : 'Sekunden';

        return $dateDiff->format(
            "%a ${strDays}, ".
            "%h ${strHours}, ".
            "%i ${strMinutes} ".
            "und %s ${strSeconds}"
        );
    }
}
