<?php

namespace randomhost\Alexa\Responder\Intent\System;

use DateTime;
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
     * Runs the Responder.
     *
     * @return $this
     */
    public function run()
    {
        $updates = $this->fetchPackageUpdates();

        $this->response
            ->respond($updates)
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
            $response = 'Es stehen keine Updates zur Verfügung.';
        } else {
            $pkgUpgraded = (int) $matches[1];
            $pkgNew = (int) $matches[2];
            $pkgRemove = (int) $matches[3];
            $pkgNotUpgraded = (int) $matches[4];


            $strUpgraded = ($pkgUpgraded === 1) ? 'ein Update' : "${pkgUpgraded} Updates";
            $strNew = ($pkgNew === 1) ? 'eine Neuinstallation' : "${pkgNew} Neuinstallationen";
            $strRemove = ($pkgRemove === 1) ? 'eine Entfernung' : "${pkgRemove} Entfernungen";
            $strNotUpgraded = ($pkgNotUpgraded === 1) ? 'wird 1 Paket' : "werden ${pkgNotUpgraded} Pakete";

            $response = sprintf(
                'Es stehen %s, %s und %s aus. Dabei %s explizit nicht aktualisiert.',
                $strUpgraded,
                $strNew,
                $strRemove,
                $strNotUpgraded
            );
        }

        return $response;
    }
}
