<?php
/**
 * Cache Controller Object
 * 
 * PHP version 8
 *
 * @category  Class
 * @package   SpurCache
 * @author    sam <sam@theresnotime.co.uk>
 * @copyright 2021 Sam
 * @license   GNU GPLv3
 * @version   GIT:1.0.0
 * @link      #
 */
declare(strict_types=1);
namespace spurCache;

require_once __DIR__ . '/../../vendor/autoload.php';

use mysqli;
use Exception;

/**
 * Cache Controller Object
 *
 * @category Class
 * @package  SpurCache
 * @author   sam <sam@theresnotime.co.uk>
 * @license  GNU GPLv3
 * @link     #
 */
class CacheController
{
    /**
     * Constructor
     */
    function __construct()
    {
        $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../../config/');
        $dotenv->load();
    }

    /**
     * Check a given IP exists in the db, and return an IP object if true
     *
     * @param string $ip_address IP address to lookup
     * 
     * @return object
     */
    public function getCachedRecord($ip_address)
    {
        try {
            // Create connection
            $conn = new mysqli(
                $_ENV['DB_HOSTNAME'],
                $_ENV['DB_USERNAME'],
                $_ENV['DB_PASSWORD'],
                $_ENV['DB_DATABASE']
            );

            // Check connection
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }
            
            $cache_id = sha1($ip_address);
            $stmt = $conn->prepare('SELECT * FROM feed_cache WHERE cache_id = ?');
            $stmt->bind_param('s', $cache_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $ip_row = $result->fetch_assoc();
                $IP = new IP($ip_row['ip_address']);
                return $IP;
            } else {
                return false;
            }
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
    
    /**
     * Write a Spur record to the cache db
     * 
     * @param string $ip                  IP address
     * @param int    $user_count          User count
     * @param string $maxmind_city        City
     * @param string $maxmind_cc          CC
     * @param string $maxmind_subdivision SD
     * @param array  $services            Services
     * @param string $org                 Org
     * @param string $raw_feed_result     Raw result
     * @param int    $record_num          Current record num
     * @param bool   $do_not_purge        Do not purge this result
     * @param bool   $hidden              Hide this result
     * 
     * @todo Better param comments
     * @todo Implement IPInfo scoring
     * 
     * @return mixed
     */
    public function writeCacheRecord(
        $ip,
        $user_count,
        $maxmind_city,
        $maxmind_cc,
        $maxmind_subdivision,
        $services,
        $org,
        $raw_feed_result,
        $record_num,
        $do_not_purge = false,
        $hidden = false
    ) {
        try {
            // Create connection
            $conn = new mysqli(
                $_ENV['DB_HOSTNAME'],
                $_ENV['DB_USERNAME'],
                $_ENV['DB_PASSWORD'],
                $_ENV['DB_DATABASE']
            );

            // Check connection
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            $stmt = $conn->prepare(
                'INSERT INTO feed_cache (
                    cache_id,
                    cache_timestamp,
                    ip_address,
                    user_count,
                    maxmind_city,
                    maxmind_cc,
                    maxmind_subdivision,
                    services,
                    org,
                    getipintel_score,
                    raw_feed_result,
                    do_not_purge,
                    hidden
                ) VALUES (
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?
                ) ON DUPLICATE KEY UPDATE cache_timestamp = ?, services = ?, getipintel_score = ?'
            );

            $cache_timestamp = date('Y-m-d H:i:s');
            $services = serialize($services);
            $cache_id = sha1($ip);
            $raw_feed_result = addslashes($raw_feed_result); // Paranoid

            //$getIpIntel = (new IPIntel($ip))->result;

            $stmt->bind_param(
                'sssisssssisiissi',
                $cache_id,
                $cache_timestamp,
                $ip,
                $user_count,
                $maxmind_city,
                $maxmind_cc,
                $maxmind_subdivision,
                $services,
                $org,
                $getIpIntel,
                $raw_feed_result,
                $do_not_purge,
                $hidden,
                $cache_timestamp,
                $services,
                $getIpIntel
            );

            if ($_ENV['DEBUG'] == "1") {
                echo "[#$record_num]: IP $ip was added to the table (ts=$cache_timestamp, rec=$record_num)" . PHP_EOL;
            }

            $stmt->execute();

        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
}