<?php


declare(strict_types=1);


/*
 * Copyright (c) 2018-2019 Yann 'Ze' Richard <ze@nbox.org>
 *
 * SPDX-License-Identifier: LGPL-3.0-or-later
 * License-Filename: LICENSE
 */

namespace UniversiteRennes2\PwnedPasswords;

use RuntimeException;

/**
 * PwnedPasswords - a PHP lib for Troy Hunt's Pwned Passwords API
 */
class PwnedPasswords
{
    public const PP_USER_AGENT       = 'Zeuh\php-pwned-passwords';
    public const PP_API_TIMEOUT      = 15;
    public const PP_API_CONN_TIMEOUT = 15;
    public const PP_RANGE_LENGTH     = 5;

    /**
     * The default URI of the Pwned Passwords "range" API end-point
     *
     * @var string
     */
    public $apiUrl = 'https://api.pwnedpasswords.com/range/';

    /**
     * Curl options array used when querying API.
     *
     * @var string[]
     */
    public $curlOptions = array();

    /**
     * Constructor, verifying cURL and TLS 1.2 compatibility,
     * throw RuntimeException on error.
     */
    public function __construct()
    {
        if (! extension_loaded('curl')) {
            throw new RuntimeException('Missing cURL extension');
        }
        if (! defined('CURL_SSLVERSION_TLSv1_2')) {
            throw new RuntimeException('There is no TLS 1.2 support in cURL : since PHP 5.5.19 and 5.6.3.');
        }
    }

    /**
     * Set new API end-point URL
     *
     * @param string $apiUrl API URL endpoint
     */
    public function setApiUrl(string $apiUrl) : void
    {
        if (substr($apiUrl, -1, 1) !== '/') {
            $apiUrl .= '/';
        }
        $this->apiUrl = $apiUrl;
    }

    /**
     * Set cURL options to be used.
     *
     * @param string[] $curlOptions cURL options
     */
    public function setCurlOptions(array $curlOptions) : void
    {
        $this->curlOptions = $curlOptions;
    }

    /**
     * Check API endpoint to find how many times the given password have been found in data set.
     *
     * @param string $password Password to check against Pwned Passwords API.
     *
     * @return int Number of times it appears in the data set, 0 when not appears.
     */
    public function howManyPwned(string $password) : int
    {
        return $this->getFromApi($password);
    }

    /**
     * Check API endpoint to find if given password have been found.
     *
     * @param string $password Password to check against Pwned Passwords API.
     *
     * @return bool TRUE if password have been pwned, FALSE otherwise.
     *
     * @throws RuntimeException on Pwned Passwords's API error.
     */
    public function isPwned(string $password) : bool
    {
        $nbPwned = $this->getFromApi($password);
        return (bool) $nbPwned;
    }

    /**
     * Call the API endpoint
     *
     * @param string $hashPrefix k-anonimity prefix to get
     *
     * @return string[] Return array with http return code and body
     */
    public function callApi(string $hashPrefix) : array
    {
        $ch = curl_init($this->apiUrl . $hashPrefix);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, self::PP_USER_AGENT);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::PP_API_CONN_TIMEOUT);
        curl_setopt($ch, CURLOPT_TIMEOUT, self::PP_API_TIMEOUT);
        if (! empty($this->curlOptions)) {
            curl_setopt_array($ch, $this->curlOptions);
        }
        $results = curl_exec($ch);
        // Connection not finished successfully, throw exception rather than just returning false
        if ($results === false) {
            throw new RuntimeException(
                'Connection to API endpoint failed : ' . curl_error($ch) . ' (' . curl_errno($ch) . ')'
            );
        }
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return array($httpCode, $results);
    }

    public function getFromApi(string $password) : int
    {
        $hash       = strtoupper(sha1($password));
        $hashPrefix = substr($hash, 0, self::PP_RANGE_LENGTH);
        $hashSuffix = substr($hash, self::PP_RANGE_LENGTH);

        $res      = $this->callApi($hashPrefix);
        $httpCode = (int) $res[0];
        $body     = $res[1];

        switch ($httpCode) {
            case 200:
                $lines = explode("\r\n", $body);
                foreach ($lines as $line) {
                    if (mb_strpos($line, ':') === false) {
                        // bad format for the line ?!
                        error_log(
                            sprintf(static::class . '::' . __METHOD__ . " : Bad line in Api reply for the '%s' k-anonimity prefix: %s", $hashPrefix, $line)
                        );
                        continue;
                    }
                    [$resSuffix, $resCount] = explode(':', trim($line));
                    if (strcmp($resSuffix, $hashSuffix) === 0) {
                        return (int) $resCount;
                    }
                }
                return 0;
            case 503:
                throw new RuntimeException(
                    'API Service Unavailable'
                );
            case 429:
                throw new RuntimeException(
                    'Rate limit of API end-point exceeded !'
                );
            default:
                // There is no 404 or other responses on PwnedPasswords API :
                // https://haveibeenpwned.com/API/v2#PwnedPasswords
                throw new RuntimeException(
                    sprintf('Unknown return code from API end-point %u for %s prefix', $httpCode, $hashPrefix)
                );
        }
    }
}
