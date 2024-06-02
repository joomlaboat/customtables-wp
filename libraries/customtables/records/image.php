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
if ( ! defined( 'ABSPATH' ) ) exit;

use CustomTablesImageMethods;
use Exception;

class Save_image
{
    var CT $ct;
    public Field $field;
    var ?array $row_new;

    function __construct(CT &$ct, Field $field)
    {
        $this->ct = &$ct;
        $this->field = $field;
    }

    /**
     * @throws Exception
     * @since 3.3.3
     */
    function saveFieldSet(?string $listing_id): ?array
    {
        $newValue = null;

        //A checkbox value 1 delete existing image 0 - not
        $to_delete = common::inputPostCmd($this->field->comesfieldname . '_delete', null, 'create-edit-record');

        //Get new image
        $fileId = null;

        if (defined('_JEXEC')) {
            $fileId = common::inputPostString($this->field->comesfieldname, null, 'create-edit-record');
        } elseif (defined('WPINC')) {
            //Get new image
            if (isset($_FILES[$this->field->comesfieldname]))
                $fileId = $_FILES[$this->field->comesfieldname]['tmp_name'];
        }
        //Set the variable to "false" to do not delete existing image
        $deleteExistingImage = false;

        if ($fileId !== null and $fileId != '') {
            //Upload new image
            $value = $this->get_image_type_value($listing_id);

            //Set new image value
            $newValue = ['value' => $value];
            $deleteExistingImage = true;
        } elseif ($to_delete == 'true') {
            $newValue = ['value' => null];//This way it will be clear if the value changed or not. If $this->newValue = null means that value not changed.
            $deleteExistingImage = true;
        }

        if ($deleteExistingImage) {
            //Get existing image
            $whereClause = new MySQLWhereClause();
            $whereClause->addCondition($this->ct->Table->realidfieldname, $listing_id);

            $ExistingImageRows = database::loadAssocList($this->field->ct->Table->realtablename, [$this->field->realfieldname],
                $whereClause, null, null, 1);

            if (count($ExistingImageRows) == 0) {
                $ExistingImage = null;
            } else {
                $ExistingImage = $ExistingImageRows[0][$this->field->realfieldname];
            }

            if ($ExistingImage !== null and ($ExistingImage != '' or (is_numeric($ExistingImage) and $ExistingImage > 0))) {
                $imageMethods = new CustomTablesImageMethods;
                $ImageFolder = CustomTablesImageMethods::getImageFolder($this->field->params);
                $fileNameType = $this->field->params[3] ?? '';
                $imageMethods->DeleteExistingSingleImage(
                    $ExistingImage,
                    CUSTOMTABLES_ABSPATH . $ImageFolder,
                    $this->field->params[0] ?? '',
                    $this->field->ct->Table->realtablename,
                    $this->field->realfieldname,
                    $this->field->ct->Table->realidfieldname,
                    $fileNameType);
            }
        }

        return $newValue;
    }


    /**
     * @throws Exception
     * @since 3.2.2
     */
    function get_image_type_value(?string $listing_id): ?string
    {
        $imageMethods = new CustomTablesImageMethods;
        $ImageFolder = CustomTablesImageMethods::getImageFolder($this->field->params);
        if ($ImageFolder == '') {
            $absoluteImageFolder = CUSTOMTABLES_ABSPATH;
        } elseif ($ImageFolder[0] == DIRECTORY_SEPARATOR) {
            $absoluteImageFolder = CUSTOMTABLES_ABSPATH . substr($ImageFolder, 1);
        } else {
            $absoluteImageFolder = CUSTOMTABLES_ABSPATH . $ImageFolder;
        }

        $pathToImageFile = null;
        $fileName = null;

        if (defined('_JEXEC')) {
            $pathToImageFile = common::inputPostString($this->field->comesfieldname, null, 'create-edit-record');
            $fileName = common::inputPostString('com' . $this->field->realfieldname . '_filename', '', 'create-edit-record');
        } elseif (defined('WPINC')) {
            //Get new image
            if (isset($_FILES[$this->field->comesfieldname])) {
                $pathToImageFile = $_FILES[$this->field->comesfieldname]['tmp_name'];
                $fileName = $_FILES[$this->field->comesfieldname]['name'];
            }
        } else
            return null;

        if ($listing_id == null or $listing_id == '' or (is_numeric($listing_id) and intval($listing_id) < 0)) {
            $value = $imageMethods->UploadSingleImage('', $pathToImageFile, $fileName, $this->field->realfieldname, $absoluteImageFolder, $this->field->params, $this->field->ct->Table->realtablename, $this->ct->Table->realidfieldname);
        } else {
            $whereClause = new MySQLWhereClause();
            $whereClause->addCondition($this->ct->Table->realidfieldname, $listing_id);

            $ExistingImageRows = database::loadObjectList($this->field->ct->Table->realtablename, [$this->field->realfieldname], $whereClause, null, null, 1);
            if (count($ExistingImageRows) == 0)
                $ExistingImage = null;
            else
                $ExistingImage = $ExistingImageRows[$this->field->realfieldname] ?? null;

            $value = $imageMethods->UploadSingleImage($ExistingImage, $pathToImageFile, $fileName, $this->field->realfieldname,
                $absoluteImageFolder, $this->field->params, $this->field->ct->Table->realtablename, $this->field->ct->Table->realidfieldname);
        }

        if ($value == "-1" or $value == "2") {
            // -1 if file extension not supported
            // 2 if file already exists
            common::enqueueMessage('Could not upload image file.');
            $value = null;
        }
        return $value;
    }
}