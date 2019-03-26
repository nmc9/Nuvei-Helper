<?php

namespace Functions\Settings;

class Checkup{
	public static function check(){

		if (!function_exists('curl_init')) {
			throw new \Exception('Nuvei Payment needs the CURL PHP extension (php_curl).');
		}

		if (!function_exists('xmlrpc_decode')) {
			throw new \Exception('Nuvei Payment needs the XML PHP extension (php_xmlrpc).');
		}

		if (!function_exists('mb_detect_encoding')) {
			throw new \Exception('Nuvei needs the Multibyte String PHP extension.');
		}

	}
}

