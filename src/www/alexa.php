<?php
/**
 * Provides Alexa skills.
 *
 * @author    Ch'Ih-Yu <chi-yu@web.de>
 * @copyright 2017 random-host.com
 * @license   http://www.debian.org/misc/bsd.license  BSD License (3 Clause)
 * @link      http://composer.random-host.com
 */

use randomhost\Alexa\Controller;

require_once realpath(__DIR__ . '/../../vendor') . '/autoload.php';

$controller = new Controller();
$controller->run();
