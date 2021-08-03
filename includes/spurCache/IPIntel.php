<?php
/**
 * IPIntel Object
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

use Exception;

/**
 * IPIntel Object
 *
 * @category Class
 * @package  SpurCache
 * @author   sam <sam@theresnotime.co.uk>
 * @license  GNU GPLv3
 * @link     #
 */
class IPIntel
{
    public $status;
    public $result;
    public $queryIP;

    /**
     * Query an IP address against https://getipintel.net
     *
     * @param string $ip_address IP address to query
     * 
     * @return mixed
     */
    function __construct($ip_address)
    {
        $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../../config/');
        $dotenv->load();

        try {
            if (filter_var($ip_address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) || filter_var($ip_address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                // Valid IP
                $apiCall = $_ENV['IP_INTEL_ENDPOINT'] . "?ip=$ip_address&contact=" . $_ENV['IP_INTEL_EMAIL'] . "&flags=f&format=json";
                $data = json_decode(file_get_contents($apiCall), true);

                if ($data['status'] === 'success') {
                    $this->status = $data['status'];
                    $this->result = floatval($data['result']);
                    $this->queryIP = $data['queryIP'];
                    return $this;
                } else {
                    return false;
                }
            } else {
                // Not a valid IP
                throw new Exception("$ip_address is not a valid IP address");
            }
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
}