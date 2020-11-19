<?php
/**
 * Provides Alexa skills.
 *
 * @author    Ch'Ih-Yu <chi-yu@web.de>
 * @copyright 2017 random-host.com
 * @license   https://opensource.org/licenses/BSD-3-Clause  BSD License (3 Clause)
 * @link      http://composer.random-host.com
 */

use randomhost\Alexa\Controller;

require_once realpath(__DIR__ . '/../../vendor') . '/autoload.php';

try {
    $controller = new Controller();
    $controller->run();
} catch (Exception $e) {
    header('HTTP/1.1 503 Service Temporarily Unavailable');
    header('Status: 503 Service Temporarily Unavailable');
    header('Retry-After: 60');
}

