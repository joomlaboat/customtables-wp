<?php
/**
 * CustomTables Joomla! 3.x/4.x/5.x Component and WordPress 6.x Plugin
 * @package Custom Tables
 * @author Ivan Komlev <support@joomlaboat.com>
 * @link https://joomlaboat.com
 * @copyright (C) 2018-2025. Ivan Komlev
 * @license GNU/GPL Version 2 or later - https://www.gnu.org/licenses/gpl-2.0.html
 **/

namespace CustomTables;

// no direct access
if (!defined('ABSPATH')) exit;

use CustomTablesImageMethods;
use Exception;

class Value_image extends BaseValue
{
	function __construct(CT &$ct, Field $field, $rowValue, array $option_list = [])
	{
		parent::__construct($ct, $field, $rowValue, $option_list);
	}


	public static function getImageSRC(?array $row, string $realFieldName, array $ImageFolderArray, bool $addPath = true): ?array
	{
		$isShortcut = false;
		if (isset($row[$realFieldName]) and $row[$realFieldName] !== false and $row[$realFieldName] !== '' and $row[$realFieldName] !== '0') {
			$img = $row[$realFieldName];

			if (is_numeric($img)) {

				$img = intval($img);
				$isShortcut = $img < 0;
				$imageFile_ = $ImageFolderArray['path'] . DIRECTORY_SEPARATOR . '_esthumb_' . $img;

				if ($addPath)
					$imageSrc_ = $ImageFolderArray['web'] . '/_esthumb_' . $img;
				else
					$imageSrc_ = '_esthumb_' . $img;
			} else {
				$imageFile_ = $ImageFolderArray['path'] . DIRECTORY_SEPARATOR . $img;
				if ($addPath)
					$imageSrc_ = $ImageFolderArray['web'] . '/' . $img;
				else
					$imageSrc_ = $img;
			}
		} else {
			$imageFile_ = '';
			$imageSrc_ = '';
		}

		if (file_exists($imageFile_ . '.jpg')) {
			$imageFile = $imageFile_ . '.jpg';
			$imageSrc = $imageSrc_ . '.jpg';
		} elseif (file_exists($imageFile_ . '.png')) {
			$imageFile = $imageFile_ . '.png';
			$imageSrc = $imageSrc_ . '.png';
		} elseif (file_exists($imageFile_ . '.webp')) {
			$imageFile = $imageFile_ . '.webp';
			$imageSrc = $imageSrc_ . '.webp';
		} else {
			$imageFile = '';
			$imageSrc = '';
		}

		if ($imageSrc == '')
			return null;

		return ['src' => $imageSrc, 'shortcut' => $isShortcut, 'path' => $imageFile];
	}

	/*
	protected static function renderUploaderLimitations(): string
	{
		$max_file_size = CTMiscHelper::file_upload_max_size();

		return '
				<div style="margin:10px; border:lightgrey 1px solid;border-radius:10px;padding:10px;display:inline-block;vertical-align:top;">
				' . esc_html__("Min. Image Size", "customtables") . ': 10px x 10px<br/>
				' . esc_html__("Max. Image Size", "customtables") . ': 1000px x 1000px<br/>
				' . esc_html__("Permitted Max. File Size", "customtables") . ': ' . CTMiscHelper::formatSizeUnits($max_file_size) . '<br/>
				' . esc_html__("File Format", "customtables") . ': JPEG, GIF, PNG, WEBP
				</div>';
	}
	*/

	/**
	 * @throws Exception
	 * @since 3.3.1
	 */
	function render(): ?string
	{
		$image = self::getImageSRCLayoutView($this->option_list, $this->rowValue, $this->field->params);
		if ($image === null)
			return null;

		return $image['tag'];
	}

	/**
	 * @throws Exception
	 * @since 3.4.5
	 */
	static public function getImageSRCLayoutView(array $option_list, ?string $rowValue, array $params): ?array
	{
		if ($rowValue !== null and $rowValue !== '' and is_numeric($rowValue) and intval($rowValue) < 0)
			$rowValue = -intval($rowValue);

		$siteName = common::getSiteName();

		$option = $option_list[0] ?? '';
		$ImageFolderArray = CustomTablesImageMethods::getImageFolder($params);

		if ($option == '' or $option == '_esthumb' or $option == '_thumb') {
			$prefix = '_esthumb_';

			$imageFileExtension = 'jpg';

			$imageFile = $ImageFolderArray['path'] . DIRECTORY_SEPARATOR . $prefix . $rowValue . '.' . $imageFileExtension;
			if (file_exists($imageFile)) {
				$imageSrc = $ImageFolderArray['web'] . '/' . $prefix . $rowValue . '.' . $imageFileExtension;
				$imageTag = '<img src="' . $imageSrc . '" style="width:150px;height:150px;" alt="' . $siteName . '" title="' . $siteName . '" />';
				return ['src' => $imageSrc, 'tag' => $imageTag];
			}
			return null;
		} elseif ($option == '_original') {

			$fileNameType = $params[3] ?? '';
			if ($fileNameType == '') {
				$prefix = '_original_';
				$imageName = $ImageFolderArray['path'] . DIRECTORY_SEPARATOR . $prefix . $rowValue;
			} else {
				$prefix = '';
				$imageName = $ImageFolderArray['path'] . DIRECTORY_SEPARATOR . $rowValue;
			}

			$imgMethods = new CustomTablesImageMethods;
			$imageFileExtension = $imgMethods->getImageExtension($imageName);

			if ($imageFileExtension != '') {
				$imageSrc = $ImageFolderArray['web'] . '/' . $prefix . $rowValue . '.' . $imageFileExtension;
				$imageTag = '<img src="' . $imageSrc . '" alt="' . $siteName . '" title="' . $siteName . '" />';
				return ['src' => $imageSrc, 'tag' => $imageTag];
			}
			return null;
		}


		$imgMethods = new CustomTablesImageMethods;

		//--- TODO: WARNING - ERROR -- REAL EXT NEEDED - IT COMES FROM OPTIONS

		$imageSizes = $imgMethods->getCustomImageOptions($params[0]);

		foreach ($imageSizes as $img) {
			if ($img[0] == $option) {

				$prefix = $option;
				$imageName = $ImageFolderArray['path'] . DIRECTORY_SEPARATOR . $prefix . '_' . $rowValue;
				$imageFileExtension = $imgMethods->getImageExtension($imageName);
				$imageFile = $imageName . '.' . $imageFileExtension;
				$imageSrc = $ImageFolderArray['web'] . '/' . $prefix . '_' . $rowValue . '.' . $imageFileExtension;

				if (file_exists($imageFile)) {
					$styles = [];
					if ($img[1] > 0)
						$styles[] = 'width:' . $img[1] . 'px;';

					if ($img[2] > 0)
						$styles[] = 'height:' . $img[2] . 'px;';

					$imageTag = '<img src="' . $imageSrc . '" alt="' . $siteName . '" title="' . $siteName . '"'
						. (count($styles) > 0 ? ' style="' . implode(";", $styles) . '"' : '') . ' />';

					return ['src' => $imageSrc, 'tag' => $imageTag];
				}
			}
		}
		return null;
	}

	//Drupal has this implemented fairly elegantly:
	//https://stackoverflow.com/questions/1.6.1.1/php-get-actual-maximum-upload-size

	// Returns a file size limit in bytes based on the PHP upload_max_filesize
	// and post_max_size
}