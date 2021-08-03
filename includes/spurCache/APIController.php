<?php
/**
 * API Controller Object
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
use DateTime;
use Exception;

/**
 * API Controller Object
 *
 * @category Class
 * @package  SpurCache
 * @author   sam <sam@theresnotime.co.uk>
 * @license  GNU GPLv3
 * @link     #
 */
class APIController
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
     * Validate a given API key
     *
     * @param string $api_key API key
     * 
     * @return mixed
     */
    public function validateKey(string $api_key)
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

            $stmt = $conn->prepare('SELECT * FROM api_users WHERE api_key = ?');
            $stmt->bind_param('s', $api_key);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $api_row = $result->fetch_assoc();
                $APIKey = new APIKey($api_row['api_key']);
                return $APIKey->is_valid;
            } else {
                return false;
            }
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Parse the given API action
     *
     * @param string $api_key API key
     * @param string $action  API action
     * 
     * @return void
     */
    public function parseAction(string $api_key, string $action)
    {
        try {
            if ($this->validateKey($api_key)) {
                switch ($action) {
                case "query":
                    $this->apiQueryIP($api_key);
                    break;
                case "time":
                    $this->getServerTime($api_key);
                    break;
                default:
                    $this->noSuchAction();
                    break;
                }
            } else {
                $this->invalidKey();
            }
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Return the server date/time
     * 
     * @param $api_key API key
     *
     * @return void
     */
    public function getServerTime(string $api_key)
    {
        $dateTime = new DateTime();
        $this->logAPICall($api_key, 'action=time');
        $this->returnJSON(true, (array) $dateTime);
    }

    /**
     * Query the db for a given IP, return JSON for API
     *
     * @param string $api_key API key
     * 
     * @return void
     */
    public function apiQueryIP(string $api_key)
    {
        try {
            if ($this->validateKey($api_key)) {
                if (isset($_GET['ip'])) {
                    $ip_address = $_GET['ip'];
                    if (filter_var($ip_address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) || filter_var($ip_address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                        $IP = (new CacheController())->getCachedRecord($ip_address);
                        if ($IP !== false) {
                            if (!$IP->hidden) {
                                $this->logAPICall($api_key, 'action=query&ip=' . $ip_address);
                                $this->returnJSON(true, (array) $IP);
                            } else {
                                $this->returnJSON(true, null, "IP is marked as hidden");
                            }
                        } else {
                            $this->returnJSON(true, null, "IP not found in database");
                        }
                    } else {
                        $this->returnJSON(true, null, "Invalid IP format");
                    }
                } else {
                    $this->returnJSON(true, null, "Missing parameter - &ip=");
                }
            } else {
                $this->invalidKey();
            }
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Format a JSON response
     *
     * @param boolean     $success Was the call successful?
     * @param array|null  $data    Data to return (or null)
     * @param string|null $error   Error message to return (or null)
     * 
     * @return void
     */
    public function returnJSON(bool $success, ?array $data, ?string $error = null)
    {
        header("Content-Type: application/json");
        if ($success) {
            $return = array(
                'status' => 'success',
                'result' => $data
            );
        } else {
            $return = array(
                'status' => 'error',
                'error' => $error
            );
        }

        echo json_encode($return);

    }

    /**
     * Convenience function to return a "no such action" error
     *
     * @return void
     */
    public function noSuchAction()
    {
        $this->returnJSON(false, null, "No such action");
    }

    /**
     * Convenience function to return an "invalid key" error
     *
     * @return void
     */
    public function invalidKey()
    {
        $this->returnJSON(false, null, "Invalid API key");
    }

    /**
     * Log an API call
     *
     * @param string $api_key Used API key
     * @param string $query   Query made
     * 
     * @return void
     */
    public function logAPICall(string $api_key, string $query)
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

            $stmt = $conn->prepare(
                'INSERT INTO api_log (
                    log_timestamp,
                    api_key,
                    query
                ) VALUES (
                    ?,
                    ?,
                    ?
                )'
            );

            $date = date('Y-m-d H:i:s');
            $stmt->bind_param(
                'sss',
                $date,
                $api_key,
                $query
            );
            $stmt->execute();
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
}