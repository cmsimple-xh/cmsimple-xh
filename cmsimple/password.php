<?php

/**
 * @file password.php
 *
 * A Compatibility library with PHP 5.5's simplified password hashing API.
 *
 * @author Anthony Ferrara <ircmaxell@php.net>
 * @copyright 2012 The Authors
 * @copyright MIT License <http://www.opensource.org/licenses/mit-license.html>
 */

if (!function_exists('random_bytes')) {
    
    /**
     * @param int $length
     * @return string
     */
    function random_bytes($length)
    {
        $buffer = '';
        $buffer_valid = false;
        if (function_exists('mcrypt_create_iv') && defined('MCRYPT_DEV_URANDOM') && !defined('PHALANGER')) {
            $buffer = mcrypt_create_iv($length, MCRYPT_DEV_URANDOM);
            if ($buffer) {
                $buffer_valid = true;
            }
        }
        if (!$buffer_valid && function_exists('openssl_random_pseudo_bytes')) {
            $strong = false;
            $buffer = openssl_random_pseudo_bytes($length, $strong);
            if ($buffer && $strong) {
                $buffer_valid = true;
            }
        }
        if (!$buffer_valid && @is_readable('/dev/urandom')) {
            $file = fopen('/dev/urandom', 'r');
            $read = 0;
            $local_buffer = '';
            while ($read < $length) {
                $local_buffer .= fread($file, $length - $read);
                $read = strlen($local_buffer);
            }
            fclose($file);
            if ($read >= $length) {
                $buffer_valid = true;
            }
            $buffer = str_pad($buffer, $length, "\0") ^ str_pad($local_buffer, $length, "\0");
        }
        if (!$buffer_valid || strlen($buffer) < $length) {
            $buffer_length = strlen($buffer);
            for ($i = 0; $i < $length; $i++) {
                if ($i < $buffer_length) {
                    $buffer[$i] = $buffer[$i] ^ chr(mt_rand(0, 255));
                } else {
                    $buffer .= chr(mt_rand(0, 255));
                }
            }
        }
        return $buffer;
    }
}
