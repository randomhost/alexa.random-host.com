<?php

namespace randomhost\Alexa\Responder\Intent\System;

use randomhost\Alexa\Responder\AbstractResponder;
use randomhost\Alexa\Responder\ResponderInterface;
use RuntimeException;

/**
 * Updates Intent.
 *
 * @author    Ch'Ih-Yu <chi-yu@web.de>
 * @copyright 2017 random-host.com
 * @license   http://www.debian.org/misc/bsd.license  BSD License (3 Clause)
 * @link      http://composer.random-host.com
 */
class Updates extends AbstractResponder implements ResponderInterface
{
    /**
     * Pending package upgrades.
     */
    const PACKAGE_UPGRADE = 'upgrade';

    /**
     * Pending new packages.
     */
    const PACKAGE_NEW = 'new';

    /**
     * Pending package removals.
     */
    const PACKAGE_REMOVE = 'remove';

    /**
     * Packages kept at their current version.
     */
    const PACKAGE_KEEP = 'keep';

    /**
     * Runs the Responder.
     *
     * @return $this
     */
    public function run()
    {
        try {
            $updates = $this->fetchPackageUpdates();

            if ($updates[self::PACKAGE_UPGRADE] === 0
                && $updates[self::PACKAGE_NEW] === 0
                && $updates[self::PACKAGE_REMOVE] === 0
                && $updates[self::PACKAGE_KEEP] === 0
            ) {
                $noUpdates = 'Es stehen keine Updates zur Verfügung.';

                $this->response
                    ->respondSSML($this->withSound(self::SOUND_CONFIRM, $noUpdates))
                    ->withCard('System Updates', $noUpdates)
                    ->endSession(false);

                return $this;
            }

            $this->response
                ->respondSSML(
                    $this->withSound(
                        self::SOUND_CONFIRM,
                        $this->getPhrasePackages($updates)
                    )
                )
                ->withCard(
                    'System Updates',
                    sprintf(
                        "Aktualisierte Pakete: %u\r\n".
                        "Neue Pakete: %u\r\n".
                        "Entfernte Pakete: %u\r\n".
                        "Beibehaltene Pakete: %u",
                        $updates[self::PACKAGE_UPGRADE],
                        $updates[self::PACKAGE_NEW],
                        $updates[self::PACKAGE_REMOVE],
                        $updates[self::PACKAGE_KEEP]
                    )
                )
                ->endSession(false);
        } catch (RuntimeException $e) {
            $this->response
                ->respondSSML(
                    $this->withSound(
                        self::SOUND_ERROR,
                        'Die verfügbaren Updates konnten leider nicht ermittelt werden.'
                    )
                )
                ->endSession(true);
        }

        return $this;
    }

    /**
     * Returns available updates.
     *
     * @return int[]
     */
    private function fetchPackageUpdates()
    {
        $rawResult = shell_exec('apt-get --just-print upgrade');

        $updates = preg_match(
            '#([0-9]+) upgraded, ([0-9]+) newly installed, ([0-9]+) to remove and ([0-9]+) not upgraded#',
            $rawResult,
            $matches
        );

        if ($updates !== 1) {
            throw new RuntimeException(
                'Could not fetch package updates'
            );
        }

        return array(
            self::PACKAGE_UPGRADE => (int)$matches[1],
            self::PACKAGE_NEW => (int)$matches[2],
            self::PACKAGE_REMOVE => (int)$matches[3],
            self::PACKAGE_KEEP => (int)$matches[4],
        );
    }

    /**
     * Returns the phrase for pending package changes.
     *
     * @param int[] $updates Number of package updates, ordered by type.
     *
     * @return string
     */
    private function getPhrasePackages($updates)
    {
        $parts = array(
            $this->getPhrasePackageUpgrade($updates[self::PACKAGE_UPGRADE]),
            $this->getPhrasePackageNew($updates[self::PACKAGE_NEW]),
            $this->getPhrasePackageRemove($updates[self::PACKAGE_REMOVE]),
            $this->getPhrasePackageKeep($updates[self::PACKAGE_KEEP]),
        );

        $phrase = implode(' ', $parts);

        if (trim($phrase) === '') {
            return 'Es stehen keine Updates zur Verfügung.';
        }

        return $phrase;
    }

    /**
     * Returns the phrase for pending package upgrades.
     *
     * @param int $packages Number of packages.
     *
     * @return string
     */
    private function getPhrasePackageUpgrade($packages)
    {
        if ($packages === 0) {
            return '';
        }

        if ($packages === 1) {
            return 'Es steht ein Update aus.';
        }

        return "Es stehen ${packages} Updates aus.";
    }

    /**
     * Returns the phrase for pending new packages.
     *
     * @param int $packages Number of packages.
     *
     * @return string
     */
    private function getPhrasePackageNew($packages)
    {
        if ($packages === 0) {
            return '';
        }

        if ($packages === 1) {
            return 'Ein Paket kommt neu hinzu.';
        }

        return "${packages} Pakete kommen neu hinzu.";
    }

    /**
     * Returns the phrase for pending package removals.
     *
     * @param int $packages Number of packages.
     *
     * @return string
     */
    private function getPhrasePackageRemove($packages)
    {
        if ($packages === 0) {
            return '';
        }

        if ($packages === 1) {
            return 'Ein Paket wird entfernt.';
        }

        return "${packages} Pakete werden entfernt.";
    }

    /**
     * Returns the phrase for package which will be kept at their current version.
     *
     * @param int $packages Number of packages.
     *
     * @return string
     */
    private function getPhrasePackageKeep($packages)
    {
        if ($packages === 0) {
            return '';
        }

        if ($packages === 1) {
            return 'Ein Paket wird nicht aktualisiert.';
        }

        return "${packages} Pakete werden nicht aktualisiert.";
    }
}
