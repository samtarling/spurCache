<?php
/**
 * Check for expired records
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
    $cacheController->checkExpire($IP->IP);
    echo "Checked " . $IP->IP . PHP_EOL;
}