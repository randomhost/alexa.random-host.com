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
 * @copyright 2020 random-host.tv
 * @license   https://opensource.org/licenses/BSD-3-Clause  BSD License (3 Clause)
 *
 * @see       https://random-host.tv
 */
class Uptime extends AbstractResponder implements ResponderInterface
{
    /**
     * System uptime days.
     */
    private const UPTIME_DAY = 'day';

    /**
     * System uptime hours.
     */
    private const UPTIME_HOUR = 'hour';

    /**
     * System uptime minutes.
     */
    private const UPTIME_MINUTE = 'minute';

    /**
     * System uptime seconds.
     */
    private const UPTIME_SECOND = 'second';

    /**
     * Runs the Responder.
     *
     * @return $this
     */
    public function run(): ResponderInterface
    {
        try {
            $responses = $this->config->get('response', 'uptime');
            if (is_null($responses) || empty($responses)) {
                $responses = ['Die Uptime beträgt %s.'];
            }

            $uptime = $this->fetchSystemUptime();
            $uptimeStr = sprintf(
                '%s, %s, %s und %s',
                $this->getPhraseUptimeDay($uptime[self::UPTIME_DAY]),
                $this->getPhraseUptimeHour($uptime[self::UPTIME_HOUR]),
                $this->getPhraseUptimeMinute($uptime[self::UPTIME_MINUTE]),
                $this->getPhraseUptimeSecond($uptime[self::UPTIME_SECOND])
            );

            $response = $this->randomizeResponseText($responses);

            $this->response
                ->respondSSML($this->withSound(self::SOUND_CONFIRM, sprintf($response, $uptimeStr)))
                ->withCard('System Uptime', sprintf("Die Uptime beträgt:\r\n%s", $uptimeStr))
                ->endSession(true)
            ;
        } catch (RuntimeException $e) {
            $this->response
                ->respondSSML(
                    $this->withSound(
                        self::SOUND_ERROR,
                        'Die Uptime konnte leider nicht ermittelt werden.'
                    )
                )
                ->endSession(true)
            ;
        }

        return $this;
    }

    /**
     * Returns the system uptime.
     *
     * @return int[]
     */
    private function fetchSystemUptime(): array
    {
        $uptime = shell_exec('cat /proc/uptime');
        if (false === $uptime) {
            throw new RuntimeException('Failed to fetch system uptime.');
        }

        $uptimeParts = explode(' ', $uptime);
        $seconds = reset($uptimeParts);
        $seconds = floatval($seconds);
        $seconds = floor($seconds);

        if (0 === $seconds) {
            throw new RuntimeException('Failed to fetch system uptime.');
        }

        $dateFrom = new DateTime('@0');
        $dateTo = new DateTime("@{$seconds}");

        $dateDiff = $dateFrom->diff($dateTo);

        return [
            self::UPTIME_DAY => (int) $dateDiff->format('%a'),
            self::UPTIME_HOUR => (int) $dateDiff->format('%h'),
            self::UPTIME_MINUTE => (int) $dateDiff->format('%i'),
            self::UPTIME_SECOND => (int) $dateDiff->format('%s'),
        ];
    }

    /**
     * Returns the phrase for uptime days.
     *
     * @param int $day Uptime days.
     *
     * @return string
     */
    private function getPhraseUptimeDay(int $day): string
    {
        return $day.((1 === $day) ? ' Tag' : ' Tage');
    }

    /**
     * Returns the phrase for uptime hours.
     *
     * @param int $hour Uptime hours.
     *
     * @return string
     */
    private function getPhraseUptimeHour(int $hour): string
    {
        return $hour.((1 === $hour) ? ' Stunde' : ' Stunden');
    }

    /**
     * Returns the phrase for uptime minutes.
     *
     * @param int $minute Uptime minutes.
     *
     * @return string
     */
    private function getPhraseUptimeMinute(int $minute): string
    {
        return $minute.((1 === $minute) ? ' Minute' : ' Minuten');
    }

    /**
     * Returns the phrase for uptime seconds.
     *
     * @param int $seconds Uptime seconds.
     *
     * @return string
     */
    private function getPhraseUptimeSecond(int $seconds): string
    {
        return $seconds.((1 === $seconds) ? ' Sekunde' : ' Sekunden');
    }
}
