<?php

namespace App\Helpers;

class IpHelper
{
    /**
     * Returns real visitor IP
     *
     * @return string|void
     */
    public static function getRealIpAddress()
    {
        // CloudFlare proxy
        if (isset($_SERVER['HTTP_CF_CONNECTING_IP']) and $_SERVER['HTTP_CF_CONNECTING_IP']) {
            return self::validateIp($_SERVER['HTTP_CF_CONNECTING_IP']);
        }

        // proxy
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) and $_SERVER['HTTP_X_FORWARDED_FOR']) {
            $ip = self::extractIP($_SERVER['HTTP_X_FORWARDED_FOR']);
            if ($ip) {
                return self::validateIp($ip);
            }
        }

        // proxy
        if (isset($_SERVER['HTTP_X_FORWARDED']) and $_SERVER['HTTP_X_FORWARDED']) {
            $ip = self::extractIP($_SERVER['HTTP_X_FORWARDED']);
            if ($ip) {
                return self::validateIp($ip);
            }
        }

        // HTTP prox / load balancer
        if (isset($_SERVER['HTTP_FORWARDED_FOR']) and $_SERVER['HTTP_FORWARDED_FOR']) {
            $ip = self::extractIP($_SERVER['HTTP_FORWARDED_FOR']);
            if ($ip) {
                return self::validateIp($ip);
            }
        }

        if (isset($_SERVER['HTTP_FORWARDED']) and $_SERVER['HTTP_FORWARDED']) {
            $ip = self::extractIP($_SERVER['HTTP_FORWARDED']);
            if ($ip) {
                return self::validateIp($ip);
            }
        }

        if (isset($_SERVER['HTTP_CLIENT_IP']) and $_SERVER['HTTP_CLIENT_IP']) {
            $ip = self::extractIP($_SERVER['HTTP_CLIENT_IP']);
            if ($ip) {
                return self::validateIp($ip);
            }
        }

        // proxy && HTTP_(X_) FORWARDED (_FOR) not defined && HTTP_VIA defined
        // other exotic variables may be defined
        if (isset($_SERVER['HTTP_VIA']) and $_SERVER['HTTP_VIA']) {
            $ip = self::extractIP($_SERVER['HTTP_VIA']);
            if ($ip) {
                return self::validateIp($ip);
            } else {
                $ip = $_SERVER['HTTP_VIA'];
                if (isset($_SERVER['HTTP_X_COMING_FROM'])) {
                    $ip .= '_' . $_SERVER['HTTP_X_COMING_FROM'];
                }if (isset($_SERVER['HTTP_COMING_FROM'])) {
                    $ip .= '_' . $_SERVER['HTTP_COMING_FROM'];
                }
                return self::validateIp($ip);
            }
        }

        // proxy && only exotic variables defined
        // the exotic variables are not enough, we add the REMOTE_ADDR of the proxy
        if ((isset($_SERVER['HTTP_X_COMING_FROM']) and $_SERVER['HTTP_X_COMING_FROM']) or (isset($_SERVER['HTTP_COMING_FROM']) and $_SERVER['HTTP_COMING_FROM'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
            if (isset($_SERVER['HTTP_X_COMING_FROM'])) {
                $ip .= '_' . $_SERVER['HTTP_X_COMING_FROM'];
            }
            if (isset($_SERVER['HTTP_COMING_FROM'])) {
                $ip .= '_' . $_SERVER['HTTP_COMING_FROM'];
            }
            return self::validateIp($ip);
        }

        if (isset($_SERVER['REMOTE_ADDR']) and $_SERVER['REMOTE_ADDR']) {
            return self::validateIp($_SERVER['REMOTE_ADDR']);
        }
    }

    /**
     * @param string $ipAddress
     * @return string
     */
    private static function validateIp(string $ipAddress): string
    {
        $ip = filter_var($ipAddress, FILTER_VALIDATE_IP);
        if (!$ip && preg_match("/([0-9]{1,3}\.){3,3}[0-9]{1,3}/", $ipAddress, $array)) {
            $ip = filter_var($array[0], FILTER_VALIDATE_IP);
        }

        return $ip;
    }

    /**
     * @param $ip
     * @return bool|mixed|string
     */
    public static function extractIP($ip)
    {
        // extract IPs
        $ips = explode(',', $ip);

        // trim, so we can compare against trusted proxies properly
        $ips = array_map('trim', $ips);

        // Any left?
        if (empty($ips)) {
            return false;
        }

        return array_shift($ips);
    }
}