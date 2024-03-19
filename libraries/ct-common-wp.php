<?php
/**
 * CustomTables Joomla! 3.x/4.x/5.x Component and WordPress 6.x Plugin
 * @package Custom Tables
 * @author Ivan Komlev <support@joomlaboat.com>
 * @link https://joomlaboat.com
 * @copyright (C) 2018-2024. Ivan Komlev
 * @license GNU/GPL Version 2 or later - https://www.gnu.org/licenses/gpl-2.0.html
 **/

namespace CustomTables;

use DateTime;
use DateTimeZone;
use Exception;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class common
{
	public static function enqueueMessage(string $text, string $type = 'error'): void
	{
		if ($type == 'notice')
			set_transient('plugin_success_message', $text, 60);
		elseif ($type == 'error')
			set_transient('plugin_error_message', $text, 60);
	}

	public static function inputPostString($parameter, ?string $default, string $action): ?string
	{
		if (isset($_POST['_wpnonce'])) {
			if (function_exists('\wp_verify_nonce') and !wp_verify_nonce(sanitize_text_field($_POST['_wpnonce']), $action))
				return $default;
		}

		if (!isset($_POST[$parameter]))
			return $default;

		$source = wp_strip_all_tags($_POST[$parameter]);
		return sanitize_text_field($source);
	}

	public static function inputPostFloat($parameter, ?float $default, string $action): ?float
	{
		if (isset($_POST['_wpnonce'])) {
			if (function_exists('\wp_verify_nonce') and !wp_verify_nonce(sanitize_text_field($_POST['_wpnonce']), $action))
				return $default;
		}

		if (!isset($_POST[$parameter]))
			return $default;

		// Only use the first floating point value
		return (float)$_POST[$parameter];
	}

	public static function inputGetFloat($parameter, ?float $default = null): ?float
	{
		if (isset($_GET['_wpnonce'])) {
			if (function_exists('\wp_verify_nonce') and !wp_verify_nonce(sanitize_text_field($_GET['_wpnonce']), 'get')) {
				//return $default;
			}
		}

		if (!isset($_GET[$parameter]))
			return $default;

		// Only use the first floating point value
		return (float)$_GET[$parameter];
	}

	public static function inputPostInt($parameter, ?int $default, string $action): ?int
	{
		if (isset($_POST['_wpnonce'])) {
			if (function_exists('\wp_verify_nonce') and !wp_verify_nonce(sanitize_text_field($_POST['_wpnonce']), $action))
				return $default;
		}

		if (!isset($_POST[$parameter]))
			return $default;

		return (int)$_POST[$parameter];
	}

	public static function inputPostUInt($parameter, ?int $default, string $action): ?int
	{
		if (isset($_POST['_wpnonce'])) {
			if (function_exists('\wp_verify_nonce') and !wp_verify_nonce(sanitize_text_field($_POST['_wpnonce']), $action))
				return $default;
		}

		if (!isset($_POST[$parameter]))
			return $default;

		return (int)$_POST[$parameter];
	}

	public static function inputGetUInt($parameter, ?int $default = null): ?int
	{
		if (isset($_GET['_wpnonce'])) {
			if (function_exists('\wp_verify_nonce') and !wp_verify_nonce(sanitize_text_field($_GET['_wpnonce']), 'get')) {
				//return $default;
			}
		}

		if (!isset($_GET[$parameter]))
			return $default;

		return (int)$_GET[$parameter];
	}

	public static function inputPostCmd(string $parameter, ?string $default, string $action): ?string
	{
		if (isset($_POST['_wpnonce'])) {
			if (function_exists('\wp_verify_nonce')) {
				if (!wp_verify_nonce(sanitize_text_field($_POST['_wpnonce']), $action))
					return null;
			}
		}

		// Allow a-z, 0-9, underscore, dot, dash. Also remove leading dots from result.
		if (!isset($_POST[$parameter]))
			return $default;

		$result = (string)preg_replace('/[^A-Z\d_\.-]/i', '', sanitize_key($_POST[$parameter]));
		return ltrim($result, '.');
	}

	public static function inputGetCmd(string $parameter, ?string $default = null): ?string
	{
		if (isset($_GET['_wpnonce'])) {
			if (function_exists('\wp_verify_nonce') and !wp_verify_nonce(sanitize_text_field($_GET['_wpnonce']), 'get')) {
				//return $default;
			}
		}

		if (!isset($_GET[$parameter]))
			return $default;

		$result = (string)preg_replace('/[^A-Z\d_\.-]/i', '', sanitize_key($_GET[$parameter]));
		return ltrim($result, '.');
	}

	public static function inputPostRaw(string $parameter, ?string $default, string $action): ?string
	{
		if (isset($_POST['_wpnonce'])) {
			if (function_exists('\wp_verify_nonce') and !wp_verify_nonce(sanitize_text_field($_POST['_wpnonce']), $action))
				return $default;
		}

		if (!isset($_POST[$parameter]))
			return $default;

		return stripslashes($_POST[$parameter]);
	}

	public static function inputGetRow(string $parameter, ?string $default = null)
	{
		if (isset($_GET['_wpnonce'])) {
			if (function_exists('\wp_verify_nonce') and !wp_verify_nonce(sanitize_text_field($_GET['_wpnonce']), 'get')) {
				//return $default;
			}
		}

		if (!isset($_GET[$parameter]))
			return $default;

		return stripslashes($_GET[$parameter]);
	}

	public static function inputPostBase64(string $parameter, ?string $default, string $action): ?string
	{
		if (isset($_POST['_wpnonce'])) {
			if (function_exists('\wp_verify_nonce') and !wp_verify_nonce(sanitize_text_field($_POST['_wpnonce']), $action))
				return $default;
		}

		// Allow a-z, 0-9, underscore, dot, dash. Also remove leading dots from result.
		if (!isset($_POST[$parameter]))
			return $default;

		// Allow a-z, 0-9, slash, plus, equals.
		return (string)preg_replace('/[^A-Z\d\/+=]/i', '', sanitize_text_field($_POST[$parameter]));
	}

	public static function inputGetBase64(string $parameter, ?string $default = null): ?string
	{
		if (isset($_GET['_wpnonce'])) {
			if (function_exists('\wp_verify_nonce') and !wp_verify_nonce(sanitize_text_field($_GET['_wpnonce']), 'get')) {
				//return $default;
			}
		}

		if (!isset($_GET[$parameter]))
			return $default;

		// Allow a-z, 0-9, slash, plus, equals.
		return (string)preg_replace('/[^A-Z\d\/+=]/i', '', sanitize_text_field($_GET[$parameter]));
	}

	public static function inputGetWord(string $parameter, ?string $default = null): ?string
	{
		if (isset($_GET['_wpnonce'])) {
			if (function_exists('\wp_verify_nonce') and !wp_verify_nonce(sanitize_text_field($_GET['_wpnonce']), 'get')) {
				//return $default;
			}
		}

		if (!isset($_GET[$parameter]))
			return $default;

		// Only allow characters a-z, and underscores
		return (string)preg_replace('/[^A-Z_]/i', '', sanitize_text_field($_GET[$parameter]));
	}

	public static function inputPostAlnum(string $parameter, ?string $default, string $action): ?string
	{
		if (isset($_POST['_wpnonce'])) {
			if (function_exists('\wp_verify_nonce') and !wp_verify_nonce(sanitize_text_field($_POST['_wpnonce']), $action))
				return $default;
		}

		// Allow a-z, 0-9, underscore, dot, dash. Also remove leading dots from result.
		if (!isset($_POST[$parameter]))
			return $default;

		// Allow a-z and 0-9 only
		return (string)preg_replace('/[^A-Z\d]/i', '', sanitize_text_field($_POST[$parameter]));
	}

	public static function inputGetAlnum(string $parameter, ?string $default = null): ?string
	{
		if (isset($_GET['_wpnonce'])) {
			if (function_exists('\wp_verify_nonce') and !wp_verify_nonce(sanitize_text_field($_GET['_wpnonce']), 'get')) {
				//return $default;
			}
		}

		if (!isset($_GET[$parameter]))
			return $default;

		// Allow a-z and 0-9 only
		return (string)preg_replace('/[^A-Z\d]/i', '', sanitize_text_field($_GET[$parameter]));
	}

	public static function inputPost($parameter, $default = null, $filter = null)
	{
		echo 'common::inputPost not supported in WordPress';
		return null;
	}

	public static function inputSet(string $parameter, string $value): void
	{
		echo 'common::inputSet not supported in WordPress';
	}

	public static function inputFiles(string $fileId)
	{
		echo 'common::inputFiles not supported in WordPress';
		return null;
	}

	public static function inputCookieSet(string $parameter, $value, $time, $path, $domain): void
	{
		die('common::inputCookieSet not supported in WordPress');
	}

	public static function inputCookieGet($parameter)
	{
		die('common::inputCookieGet not supported in WordPress');
	}

	public static function inputServer($parameter, $default = null, $filter = null)
	{
		die('common::inputServer not supported in WordPress');
	}

	public static function ExplodeSmartParams(string $param): array
	{
		$items = array();

		$a = CTMiscHelper::csv_explode(' and ', $param, '"', true);
		foreach ($a as $b) {
			$c = CTMiscHelper::csv_explode(' or ', $b, '"', true);

			if (count($c) == 1)
				$items[] = array('and', $b);
			else {
				foreach ($c as $d)
					$items[] = array('or', $d);
			}
		}
		return $items;
	}

	public static function folderList(string $directory): ?array
	{
		$folders = [];
		$directoryLength = strlen($directory);

		if ($directory > 0 and $directory[$directoryLength - 1] !== DIRECTORY_SEPARATOR)
			$directoryLength += 1;

		if (is_dir($directory)) {
			$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);

			foreach ($iterator as $item) {
				if ($item->isDir())
					$folders[] = substr($item->getPathname(), $directoryLength);
			}
		} else {
			// Handle the case when $directory is not a valid directory
			// You can throw an exception, return an error message, etc.
			return null;
		}
		return $folders;
	}

	public static function escape($var)
	{
		if ($var === null)
			$var = '';

		if (strlen($var) > 50) {
			// use the helper htmlEscape method instead and shorten the string
			return self::htmlEscape($var, 'UTF-8', true);
		}
		// use the helper htmlEscape method instead.
		return self::htmlEscape($var);
	}

	public static function htmlEscape($var, $charset = 'UTF-8', $shorten = false, $length = 40)
	{
		if (self::checkString($var)) {
			// Encode special characters to HTML entities
			$encoded = htmlentities($var, ENT_COMPAT, $charset);

			// Decode HTML entities to their corresponding characters
			$decoded = html_entity_decode($encoded, ENT_COMPAT, $charset);

			// Remove any potential scripting or dangerous content
			$string = wp_strip_all_tags($decoded);

			if ($shorten) {
				return self::shorten($string, $length);
			}
			return $string;
		} else {
			return '';
		}
	}

	public static function checkString($string): bool
	{
		if (isset($string) && is_string($string) && strlen($string) > 0) {
			return true;
		}
		return false;
	}

	public static function shorten($string, $length = 40, $addTip = true)
	{
		if (self::checkString($string)) {
			$initial = strlen($string);
			$words = preg_split('/([\s\n\r]+)/', $string, -1, PREG_SPLIT_DELIM_CAPTURE);
			$words_count = count((array)$words);

			$word_length = 0;
			$last_word = 0;
			for (; $last_word < $words_count; ++$last_word) {
				$word_length += strlen($words[$last_word]);
				if ($word_length > $length) {
					break;
				}
			}

			$newString = implode(array_slice($words, 0, $last_word));
			$final = strlen($newString);
			if ($initial != $final && $addTip) {
				$title = self::shorten($string, 400, false);
				return '<span class="hasTip" title="' . $title . '" style="cursor:help;">' . trim($newString) . '...</span>';
			} elseif ($initial != $final && !$addTip) {
				return trim($newString) . '...';
			}
		}
		return $string;
	}

	public static function ctJsonEncode($argument): string
	{
		return wp_json_encode($argument);
	}

	public static function ctStripTags($argument): string
	{
		return wp_strip_all_tags($argument);
	}

	public static function getReturnToURL(bool $decode = true): ?string
	{
		$returnto_id = common::inputGetInt('returnto');

		if (empty($returnto_id))
			return null;

		if ($decode) {
			// Construct the session variable key from the received returnto ID
			$returnto_key = 'returnto_' . $returnto_id;

			// Start the session (if not started already)
			if (!headers_sent() and !session_id()) {
				session_start();
			}

			// Retrieve the value associated with the returnto key from the $_SESSION
			return sanitize_text_field($_SESSION[$returnto_key] ?? '');
		} else
			return $returnto_id;
	}

	public static function inputGetInt(string $parameter, ?int $default = null): ?int
	{
		if (isset($_GET['_wpnonce'])) {
			if (function_exists('\wp_verify_nonce') and !\wp_verify_nonce(sanitize_text_field($_GET['_wpnonce']), 'get')) {
				//return $default;
			}
		}

		if (!isset($_GET[$parameter]))
			return $default;

		$value = sanitize_key($_GET[$parameter] ?? null);

		if ($value === null)
			return $default;

		// Allow a-z, 0-9, underscore, dot, dash. Also remove leading dots from result.
		preg_match('/-?\d+/', (string)$value, $matches);
		return @ (int)$matches[0];
	}

	public static function makeReturnToURL(string $currentURL = null): ?string
	{
		if ($currentURL === null) {
			// Get the current URL
			//$current_url = esc_url_raw(home_url(add_query_arg(array(), $wp->request)));

			$currentURL = common::curPageURL();
		}

		// Generate a unique identifier for the session variable
		$returnto_id = uniqid();

		$returnto_key = 'returnto_' . $returnto_id;

		// Start the session (if not started already)
		if (!headers_sent() and !session_id()) {
			session_start();
		}

		// Set the session variable using the generated ID as the key
		$_SESSION[$returnto_key] = $currentURL;

		return $returnto_id;
	}

	public static function curPageURL(): string
	{
		$WebsiteRoot = str_replace(site_url(), '', home_url());
		$RequestURL = esc_url($_SERVER["REQUEST_URI"]);

		if ($WebsiteRoot !== '' && str_ends_with($WebsiteRoot, '/')) {
			if ($RequestURL !== '' && $RequestURL[0] === '/') {
				$WebsiteRoot = rtrim($WebsiteRoot, '/');
			}
		}

		return $WebsiteRoot . $RequestURL;
	}

	public static function getServerParam(string $param): string
	{
		return sanitize_text_field($_SERVER[$param]);
	}

	public static function inputGet(string $parameter, $default, string $filter)
	{
		echo 'common::inputGet not supported in WordPress';
		return null;
	}

	public static function ctParseUrl($argument)
	{
		return wp_parse_url($argument);
	}

	public static function generateRandomString(int $length = 32): string
	{
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $length; $i++)
			$randomString .= $characters[wp_rand(0, $charactersLength - 1)];

		return $randomString;
	}

	public static function saveString2File(string $filePath, string $content): ?string
	{
		global $wp_filesystem;

		if (!function_exists('WP_Filesystem')) {
			require_once ABSPATH . '/wp-admin/includes/file.php';
		}

		WP_Filesystem();

		if (!$wp_filesystem) {
			return 'Unable to initialize WP_Filesystem.';
		}

		try {
			$result = $wp_filesystem->put_contents($filePath, $content);

			if (!$result) {
				throw new Exception('Failed to write content to file.');
			}

		} catch (Exception $e) {
			return $e->getMessage();
		}

		return null;
	}

	public static function getStringFromFile(string $filePath): ?string
	{
		global $wp_filesystem;

		if (!function_exists('WP_Filesystem')) {
			require_once ABSPATH . '/wp-admin/includes/file.php';
		}

		WP_Filesystem();

		if (!$wp_filesystem) {
			return 'Unable to initialize WP_Filesystem.';
		}

		try {
			$content = $wp_filesystem->get_contents($filePath);

			if ($content === false) {
				throw new Exception('Failed to read content from file.');
			}

		} catch (Exception $e) {
			return $e->getMessage();
		}

		return $content;
	}

	/**
	 * @throws Exception
	 */
	public static function default_timezone_set(): void
	{
		$timezone_string = get_option('timezone_string');

		if ($timezone_string) {
			$timezone_object = new DateTimeZone($timezone_string);
			$current_time = new DateTime(null, $timezone_object);
			$offset = $timezone_object->getOffset($current_time);
		}
	}

	public static function getWhereParameter($field): string
	{
		$list = self::getWhereParameters();

		if ($list === null)
			return '';

		foreach ($list as $l) {
			$p = explode('=', $l);
			$fld_name = str_replace('_t_', '', $p[0]);
			$fld_name = str_replace('_r_', '', $fld_name); //range

			if ($fld_name == $field and isset($p[1]))
				return $p[1];
		}
		return '';
	}

	protected static function getWhereParameters(): ?array
	{
		$value = common::inputGetString('where');
		if ($value !== null) {
			$b = urldecode($value);
			$b = str_replace(' or ', ' and ', $b);
			$b = str_replace(' OR ', ' and ', $b);
			$b = str_replace(' AND ', ' and ', $b);
			return explode(' and ', $b);
		}
		return null;
	}

	public static function inputGetString($parameter, $default = null)
	{
		if (isset($_GET['_wpnonce'])) {
			if (function_exists('\wp_verify_nonce') and !wp_verify_nonce(sanitize_text_field($_GET['_wpnonce']), 'get')) {
				//return $default;
			}
		}

		if (!isset($_GET[$parameter]))
			return $default;

		return sanitize_text_field($_GET[$parameter]);
	}

	public static function sanitize_post_field_array($input, string $type = 'int'): array
	{
		$sanitized_array = [];
		foreach ($input as $item) {
			// Ensure the item is an integer and meets any other criteria you have
			if ($type == 'int')
				$sanitized_item = intval($item);
			elseif ($type == 'string')
				$sanitized_item = sanitize_text_field($item);
			else
				$sanitized_item = null;

			$sanitized_array[] = $sanitized_item;
		}
		return $sanitized_array;
	}

	public static function filterText(?string $text): string
	{
		if ($text === null)
			return '';

		$allowed_html = array(
			'a' => array(
				'href' => array(),
				'title' => array()
			),
			'b' => array(),
			'strong' => array(),
			'em' => array(),
			'i' => array(),
			'u' => array(),
			'br' => array(),
			'p' => array(),
			'ul' => array(),
			'ol' => array(),
			'li' => array(),
			'blockquote' => array(),
			'pre' => array(),
			'code' => array(),
			'div' => array(
				'style' => array()
			),
			'hr' => array(
				'style' => array()
			),
		);

		return wp_kses($text, $allowed_html);
	}

	public static function redirect(string $link, ?string $msg = null): void
	{
		echo '<script>window.location.replace("' . esc_url($link) . '");</script>';
		exit;
	}

	public static function loadJSAndCSS(Params $params, Environment $env): void
	{
	}

	public static function formatDate(?string $date = null, ?string $format = 'Y-m-d H:i:s', ?string $emptyValue = 'Never'): ?string
	{
		if ($format === null)
			$format = 'Y-m-d H:i:s';

		if ($date === null or $date == '0000-00-00 00:00:00')
			return $emptyValue;

		$timestamp = strtotime($date);

		if ($format === 'timestamp')
			return (string)$timestamp;

		return date_i18n($format, $timestamp);
	}
}
