<?php
/**
 * API Key Object
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
 * API Key Object
 *
 * @category Class
 * @package  SpurCache
 * @author   sam <sam@theresnotime.co.uk>
 * @license  GNU GPLv3
 * @link     #
 */
class APIKey
{
    public $key;
    public $valid_from;
    public $valid_until;
    public $assigned_to;
    public $is_valid;

    /**
     * Construct
     *
     * @param string $api_key API Key
     * 
     * @return object
     */
    function __construct($api_key)
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

            $stmt = $conn->prepare('SELECT * FROM api_users WHERE api_key = ?');
            $stmt->bind_param('s', $api_key);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                // Should only ever be one
                $api_row = $result->fetch_assoc();
                $this->key = $api_row['api_key'];
                $this->valid_from = DateTime::createFromFormat('Y-m-d H:i:s', $api_row['valid_from']);
                $this->valid_until = DateTime::createFromFormat('Y-m-d H:i:s', $api_row['valid_until']);
                $this->assigned_to = $api_row['assigned_to'];

                $now = new DateTime();

                if ($this->valid_from <= $now && $now <= $this->valid_until) {
                    $this->is_valid = true;
                } else {
                    $this->is_valid = false;
                }

                return $this;
            } else {
                throw new Exception(
                    "Did not return one result for $api_key (got " . $result->num_rows . ")"
                );
            }
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
}