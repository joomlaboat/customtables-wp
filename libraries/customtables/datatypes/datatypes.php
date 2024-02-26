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

// no direct access
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class DataTypes
{
	public static function fieldTypeTranslation()
	{
		$typeArray = array(
			'string' => "Text String (Single Line)",
			'multilangstring' => "Multilingual Text String",
			'text' => "Text",
			'multilangtext' => "Multilingual Text Area",
			'int' => "Integer Number",
			'float' => "Float (Decimal)",
			'customtables' => "Hierarchical List",
			'records' => "Table Join List",
			'checkbox' => "Checkbox",
			'radio' => "Radio Buttons",
			'email' => "Email",
			'url' => "URL / Link",
			'date' => "Date",
			'time' => "Time",
			'image' => "Image File",
			'imagegallery' => "Image Gallery",
			'signature' => "Signature",
			'ordering' => "Ordering",
			'filebox' => "File Box",
			'file' => "File",
			'filelink' => "File Link",
			'creationtime' => "Creation Time",
			'changetime' => "Change Time",
			'lastviewtime' => "Last View Time",
			'viewcount' => "View Count",
			'userid' => "Record Author User",
			'user' => "User",
			'server' => "Server Info",
			'alias' => "Alias (For SEO Links)",
			'color' => "Color",
			'id' => "Autoincrement ID",
			'phponadd' => "PHP OnAdd Script",
			'phponchange' => "PHP OnChange Script",
			'phponview' => "PHP OnView Script",
			'sqljoin' => "Table Join",
			'googlemapcoordinates' => "GPS Coordinates",
			'dummy' => "Translation",
			'article' => "Article Link",
			//'multilangarticle' => "Multilingual Article",
			'virtual' => "Virtual",
			'md5' => "MD5 Hash",
			'log' => "Change Log",
			'usergroup' => "User Group",
			'usergroups' => "User Groups",
			'blob' => "Blob (Save file into the database)",
			'language' => "Language"
		);

		return $typeArray;
	}

	public static function isrequiredTranslation(): array
	{
		return array(
			1 => "Required",
			0 => "Not Required",
			//2 => "Generated Virtual",
			//3 => "Generated Stored"
		);
	}
}
