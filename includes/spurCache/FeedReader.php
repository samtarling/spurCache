<?php
/**
 * Feed Reading Object
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
 * Feed Reading Object
 *
 * @category Class
 * @package  SpurCache
 * @author   sam <sam@theresnotime.co.uk>
 * @license  GNU GPLv3
 * @link     #
 */
class FeedReader
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
     * Get feed data from Spur
     *
     * @return mixed
     */
    public function getFeedData()
    {
        $ch = curl_init();
        curl_setopt(
            $ch,
            CURLOPT_RETURNTRANSFER,
            true
        );
        curl_setopt(
            $ch,
            CURLOPT_URL,
            $_ENV['SPUR_FEED_URL']
        );
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            array(
                'Token: ' . $_ENV['SPUR_FEED_TOKEN']
            )
        );
        $result = curl_exec($ch);
        curl_close($ch);

        //TODO check this is expected format
        $insertId = $this->saveFeedData($result);

        if ($insertId !== false && is_int($insertId)) {
            return $insertId;
        } else {
            return false;
        }
    }

    /**
     * Save the raw feed data to a table for processing
     *
     * @param string $feed_data Raw feed data
     * 
     * @return void
     */
    public function saveFeedData($feed_data)
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
                'INSERT INTO raw_feed (
                    feed_timestamp,
                    feed_data,
                    total_records
                ) VALUES (
                    ?,
                    ?,
                    ?
                )'
            );

            $date = date('Y-m-d H:i:s');
            $splitData = explode("\n", $feed_data);
            $total_records = count($splitData);
            $stmt->bind_param('ssi', $date, $feed_data, $total_records);
            if ($stmt->execute()) {
                $last_id = $conn->insert_id;
                return intval($last_id);
            } else {
                return false;
            }
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Read Spur feed file
     * 
     * @param int $feed_id `raw_feed` feed_id to process
     *
     * @return string
     */
    public function processFeedData($feed_id)
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

            $stmt = $conn->prepare('SELECT * FROM raw_feed WHERE feed_id = ?');
            $stmt->bind_param('i', $feed_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $this->changeFeedStatus($feed_id, 2); // Processing

                $feed_row = $result->fetch_assoc();
                $feed_data = explode("\n", $feed_row['feed_data']);
                $total_records = intval($feed_row['total_records']);
                $current_record = 0;

                if ($_ENV['DEBUG'] == "1") {
                    echo "Got $total_records to process..." . PHP_EOL;
                }

                foreach ($feed_data as $datapoint) {
                    $dataJson = json_decode($datapoint, true);

                    // Required
                    if (key_exists('ip', $dataJson)) {
                        $ip = $dataJson['ip'];
                    } else {
                        // If not present, assume a bad record and skip
                        if ($_ENV['DEBUG'] == "1") {
                            echo "$current_record did not have an IP address" . PHP_EOL;
                        }
                        continue;
                    }

                    // Optional
                    if (key_exists('user_count', $dataJson)) {
                        $user_count = intval($dataJson['user_count']);
                    } else {
                        $user_count = null;
                    }
                    
                    // Optional
                    if (key_exists('org', $dataJson)) {
                        $org = $dataJson['org'];
                    } else {
                        $org = null;
                    }

                    // Optional
                    if (key_exists('maxmind_cc', $dataJson)) {
                        $maxmind_cc = $dataJson['maxmind_cc'];
                    } else {
                        $maxmind_cc = null;
                    }

                    // Optional
                    if (key_exists('maxmind_city', $dataJson)) {
                        $maxmind_city = $dataJson['maxmind_city'];
                    } else {
                        $maxmind_city = null;
                    }

                    // Optional
                    if (key_exists('maxmind_subdivision', $dataJson)) {
                        $maxmind_subdivision = $dataJson['maxmind_subdivision'];
                    } else {
                        $maxmind_subdivision = null;
                    }

                    // Required
                    if (key_exists('services', $dataJson)) {
                        $services = $dataJson['services'];
                    } else {
                        // If not present, no need to log, skip
                        continue;
                    }

                    (new CacheController())->writeCacheRecord(
                        $ip,
                        $user_count,
                        $maxmind_city,
                        $maxmind_cc,
                        $maxmind_subdivision,
                        $services,
                        $org,
                        $datapoint,
                        $current_record
                    );

                    $current_record++;
                }
                if ($_ENV['DEBUG'] == "1") {
                    echo "Processed $current_record/$total_records" . PHP_EOL;
                }
                $this->changeFeedStatus($feed_id, 3); // Done
                
            } else {
                throw new Exception(
                    "Did not return one result for $feed_id (got " . $result->num_rows . ")"
                );
            }
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Update a given feed_id's status
     *
     * @param integer $feed_id     Feed ID
     * @param integer $feed_status New feed status
     * 
     * @return boolean
     */
    public function changeFeedStatus($feed_id, $feed_status)
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

            $stmt = $conn->prepare('UPDATE raw_feed SET status = ?, status_timestamp = ? WHERE feed_id = ?');
            $date = date('Y-m-d H:i:s');
            $stmt->bind_param('isi', $feed_status, $date, $feed_id);
            return $stmt->execute();
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
}