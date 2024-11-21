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

class Save_records
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
     * @since 3.4.5
     */
    function saveFieldSet(?string $listing_id): ?array
    {
        $value = $this->get_record_type_value();

        if ($value === null)
            return null;
        elseif ($value === '')
            return ['value' => null];

        return ['value' => $value];
    }

    /**
     * @throws Exception
     * @since 3.0.0
     */
    protected function get_record_type_value(): ?string
    {
        if (count($this->field->params) > 2) {
            $esr_selector = $this->field->params[2];
            $selectorPair = explode(':', $esr_selector);

            switch ($selectorPair[0]) {
                case 'single';
                    $value = common::inputPostInt($this->field->comesfieldname, null, 'create-edit-record');

                    if (isset($value))
                        return $value;

                    break;

                case 'radio':
                case 'checkbox':
                case 'multi':

                    //returns NULL if field parameter not found - nothing to save
                    //returns empty array if nothing selected - save empty value
                    $valueArray = common::inputPost($this->field->comesfieldname, null, 'array');

                    if ($valueArray) {
                        return self::getCleanRecordValue($valueArray);
                    } else {
                        $value_off = common::inputPostInt($this->field->comesfieldname . '_off', null, 'create-edit-record');
                        if ($value_off) {
                            return '';
                        } else {
                            return null;
                        }
                    }

                case 'multibox';
                    $valueArray = common::inputPost($this->field->comesfieldname, null, 'array');

                    if (isset($valueArray)) {
                        return self::getCleanRecordValue($valueArray);
                    }
                    break;
            }
        }
        return null;
    }

    protected static function getCleanRecordValue($array): string
    {
        $values = array();
        foreach ($array as $a) {
            if ((int)$a != 0)
                $values[] = (int)$a;
        }
        return ',' . implode(',', $values) . ',';
    }

}