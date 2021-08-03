<?php
/**
 * Update the IPIntel scores for all current records
 * 
 * PHP version 8
 *
 * @category  Script
 * @package   SpurCache
 * @author    sam <sam@theresnotime.co.uk>
 * @copyright 2021 Sam
 * @license   GNU GPLv3
 * @version   GIT:1.0.0
 * @link      #
 */
declare(strict_types=1);
require_once __DIR__ . '/../vendor/autoload.php';
$cacheController = new spurCache\CacheController();
$records = $cacheController->getAllRecords(true);
while ($record = $records->fetch_assoc()) {
    $IP = new spurCache\IP($record['ip_address']);
    $IP->updateIPIntel();
    echo "Updated " . $IP->IP . " - got score: " . $IP->getipintel_score . PHP_EOL;
    sleep(5);
}