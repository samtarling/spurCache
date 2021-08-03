<?php
/**
 * IP Object
 * 
 * PHP version 7.3
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
use DateTime;
use Exception;

/**
 * IP Object
 *
 * @category Class
 * @package  SpurCache
 * @author   sam <sam@theresnotime.co.uk>
 * @license  GNU GPLv3
 * @link     #
 */
class IP
{
    public $id;
    public $cache_timestamp;
    public $IP;
    public $user_count;
    public $maxmind_city;
    public $maxmind_cc;
    public $maxmind_subdivision;
    public $services;
    public $org;
    public $getipintel_score;
    public $raw_feed_result;
    public $do_not_purge;
    public $hidden;

    /**
     * Construct
     *
     * @param string $ip_address IP address
     * 
     * @return object
     */
    function __construct($ip_address)
    {
        try {
            $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../../config/');
            $dotenv->load();

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
                // Should only ever be one
                $cache_row = $result->fetch_assoc();
                $this->id = $cache_row['cache_id'];
                $this->cache_timestamp = DateTime::createFromFormat('Y-m-d H:i:s', $cache_row['cache_timestamp']);
                $this->IP = $cache_row['ip_address'];
                $this->user_count = intval($cache_row['user_count']);
                $this->maxmind_city = $cache_row['maxmind_city'];
                $this->maxmind_cc = $cache_row['maxmind_cc'];
                $this->maxmind_subdivision = $cache_row['maxmind_subdivision'];
                $this->services = unserialize($cache_row['services']);
                $this->org = $cache_row['org'];
                $this->getipintel_score = floatval($cache_row['getipintel_score']);
                $this->raw_feed_result = $cache_row['raw_feed_result'];
                $this->do_not_purge = boolval($cache_row['do_not_purge']);
                $this->hidden = boolval($cache_row['hidden']);

                return $this;
            } else {
                throw new Exception(
                    "Did not return one result for $ip_address (got " . $result->num_rows . ")"
                );
            }
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
}