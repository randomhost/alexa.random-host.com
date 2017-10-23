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
     * System uptime days.
     */
    const UPTIME_DAY = 'day';

    /**
     * System uptime hours.
     */
    const UPTIME_HOUR = 'hour';

    /**
     * System uptime minutes.
     */
    const UPTIME_MINUTE = 'minute';

    /**
     * System uptime seconds.
     */
    const UPTIME_SECOND = 'second';


    /**
     * Runs the Responder.
     *
     * @return $this
     */
    public function run()
    {
        try {
            $responses = $this->config->get('response', 'uptime');
            if (is_null($responses) || empty($responses)) {
                $responses = array('Die Uptime beträgt %s.');
            }

            $uptime = $this->fetchSystemUptime();
            $uptimeStr = sprintf(
                "%s, %s, %s und %s",
                $this->getPhraseUptimeDay($uptime[self::UPTIME_DAY]),
                $this->getPhraseUptimeHour($uptime[self::UPTIME_HOUR]),
                $this->getPhraseUptimeMinute($uptime[self::UPTIME_MINUTE]),
                $this->getPhraseUptimeSecond($uptime[self::UPTIME_SECOND])
            );

            $response = $this->randomizeResponseText($responses);

            $this->response
                ->respondSSML($this->withSound(self::SOUND_CONFIRM, sprintf($response, $uptimeStr)))
                ->withCard('System Uptime', sprintf("Die Uptime beträgt:\r\n%s", $uptimeStr))
                ->endSession(true);
        } catch (RuntimeException $e) {
            $this->response
                ->respondSSML(
                    $this->withSound(
                        self::SOUND_ERROR,
                        'Die Uptime konnte leider nicht ermittelt werden.'
                    )
                )
                ->endSession(true);
        }

        return $this;
    }

    /**
     * Returns the system uptime.
     *
     * @return int[]
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

        return array(
            self::UPTIME_DAY => (int)$dateDiff->format('%a'),
            self::UPTIME_HOUR => (int)$dateDiff->format('%h'),
            self::UPTIME_MINUTE => (int)$dateDiff->format('%i'),
            self::UPTIME_SECOND => (int)$dateDiff->format('%s'),
        );
    }

    /**
     * Returns the phrase for uptime days.
     *
     * @param int $day Uptime days.
     *
     * @return string
     */
    private function getPhraseUptimeDay($day)
    {
        return $day.(($day === 1) ? ' Tag' : ' Tage');
    }

    /**
     * Returns the phrase for uptime hours.
     *
     * @param int $hour Uptime hours.
     *
     * @return string
     */
    private function getPhraseUptimeHour($hour)
    {
        return $hour.(($hour === 1) ? ' Stunde' : ' Stunden');
    }

    /**
     * Returns the phrase for uptime minutes.
     *
     * @param int $minute Uptime minutes.
     *
     * @return string
     */
    private function getPhraseUptimeMinute($minute)
    {
        return $minute.(($minute === 1) ? ' Minute' : ' Minuten');
    }

    /**
     * Returns the phrase for uptime seconds.
     *
     * @param int $seconds Uptime seconds.
     *
     * @return string
     */
    private function getPhraseUptimeSecond($seconds)
    {
        return $seconds.(($seconds === 1) ? ' Sekunde' : ' Sekunden');
    }
}
