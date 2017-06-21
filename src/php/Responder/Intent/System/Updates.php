<?php

namespace randomhost\Alexa\Responder\Intent\System;

use randomhost\Alexa\Responder\AbstractResponder;
use randomhost\Alexa\Responder\ResponderInterface;

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
     * Runs the Responder.
     *
     * @return $this
     */
    public function run()
    {
        $updates = $this->fetchPackageUpdates();

        $this->response
            ->respondSSML($updates)
            ->endSession(false);

        return $this;
    }

    /**
     * Returns available updates.
     *
     * @return float
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
            return $this->withSound(
                self::SOUND_ERROR,
                'Die verfügbaren Updates konnten leider nicht ermittelt werden.'
            );
        }

        $pkgUpgraded = (int)$matches[1];
        $pkgNew = (int)$matches[2];
        $pkgRemove = (int)$matches[3];
        $pkgNotUpgraded = (int)$matches[4];

        if ($pkgUpgraded === 0 && $pkgNew === 0 && $pkgRemove === 0 && $pkgNotUpgraded === 0) {
            return $this->withSound(
                self::SOUND_CONFIRM,
                'Es stehen keine Updates zur Verfügung.'
            );
        }

        $strUpgraded = ($pkgUpgraded === 1) ? 'ein Update' : "${pkgUpgraded} Updates";
        $strNew = ($pkgNew === 1) ? 'eine Neuinstallation' : "${pkgNew} Neuinstallationen";
        $strRemove = ($pkgRemove === 1) ? 'eine Entfernung' : "${pkgRemove} Entfernungen";
        $strNotUpgraded = ($pkgNotUpgraded === 1) ? 'wird 1 Paket'
            : "werden ${pkgNotUpgraded} Pakete";

        return $this->withSound(
            self::SOUND_CONFIRM,
            sprintf(
                'Es stehen %s, %s und %s aus. Dabei %s explizit nicht aktualisiert.',
                $strUpgraded,
                $strNew,
                $strRemove,
                $strNotUpgraded
            )
        );
    }
}
