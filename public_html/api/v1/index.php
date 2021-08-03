<?php
/**
 * API endpoint
 * 
 * PHP version 8
 *
 * @category  API
 * @package   SpurCache
 * @author    sam <sam@theresnotime.co.uk>
 * @copyright 2021 Sam
 * @license   GNU GPLv3
 * @version   GIT:1.0.0
 * @link      #
 */
declare(strict_types=1);
require_once __DIR__ . '/../../../vendor/autoload.php';
$apiController = new spurCache\APIController();

if (isset($_GET['key'])) {
    $api_key = $_GET['key'];
    if ($apiController->validateKey($api_key)) {
        if (isset($_GET['action'])) {
            $action = $_GET['action'];
            $apiController->parseAction($api_key, $action);
        } else {
            $apiController->noSuchAction();
        }
    } else {
        // Invalid key
        $apiController->invalidKey();
    }
} else {
    // No key
    $apiController->invalidKey();
}