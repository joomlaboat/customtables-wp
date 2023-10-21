<?php
/**
 * CustomTables Joomla! 3.x/4.x Native Component
 * @package Custom Tables
 * @author Ivan Komlev <support@joomlaboat.com>
 * @link https://joomlaboat.com
 * @copyright (C) 2018-2023. Ivan Komlev
 * @license GNU/GPL Version 2 or later - https://www.gnu.org/licenses/gpl-2.0.html
 **/

// No direct access to this file
if (!defined('_JEXEC') and !defined('WPINC')) {
    die('Restricted access');
}

use CustomTables\database;
use CustomTables\Fields;

function importCSVfile($filename, $ct_tableid)
{
    if (file_exists($filename))
        return importCSVdata($filename, $ct_tableid);
    else
        return JoomlaBasicMisc::JTextExtended('COM_CUSTOMTABLES_FILE_NOT_FOUND');
}

function getLines($filename): ?array
{
    $delimiter = detectDelimiter($filename);

    if (($handle = fopen($filename, "r")) !== FALSE) {
        $lines = [];
        $enclosure = "\"";

        while (($data = fgetcsv($handle, 0, $delimiter, $enclosure)) !== FALSE)
            $lines[] = $data;

        fclose($handle);
        return $lines;
    }
    return null;
}

//https://stackoverflow.com/questions/26717462/php-best-approach-to-detect-csv-delimiter/59581170  
function detectDelimiter($csvFile)
{
    //first line is a list of field name, so this approach is ok here
    $delimiters = [";" => 0, "," => 0, "\t" => 0, "|" => 0];

    $handle = fopen($csvFile, "r");
    $firstLine = fgets($handle);
    fclose($handle);
    foreach ($delimiters as $delimiter => &$count) {
        $count = count(str_getcsv($firstLine, $delimiter));
    }

    return array_search(max($delimiters), $delimiters);
}

function processFieldParams(&$fieldList, array &$fields): array
{
    foreach ($fieldList as $f_index) {
        if ($f_index >= 0) {
            $fieldType = $fields[$f_index]->type;
            if ($fieldType == 'sqljoin') {
                $type_params = JoomlaBasicMisc::csv_explode(',', $fields[$f_index]->typeparams, '"', false);

                $tableName = $type_params[0];
                $fieldName = $type_params[1];
                $tableRow = ESTables::getTableRowByName($tableName);

                if (!is_object($tableRow)) {
                    echo json_encode(['error' => 'sqljoin field(' . $fields[$f_index]->fieldtitle . ') table not found']);
                    die;
                }

                $SQJJoinField = Fields::getFieldRowByName($fieldName, $tableRow->id);

                $fields[$f_index]->sqljoin = (object)[
                    'table' => $tableRow->realtablename,
                    'field' => $SQJJoinField->realfieldname,
                    'realidfieldname' => $tableRow->realidfieldname,
                    'published_field_found' => $tableRow->published_field_found];
            }
        }
    }
    return $fields;
}

function importCSVdata(string $filename, $ct_tableid): string
{
    $arrayOfLines = getLines($filename);
    if ($arrayOfLines === null)
        return JoomlaBasicMisc::JTextExtended('COM_CUSTOMTABLES_CSV_FILE_EMPTY');

    $tablerow = ESTables::getTableRowByID($ct_tableid);
    $fields = Fields::getFields($ct_tableid, true);

    $first_line_fieldnames = false;

    $line = $arrayOfLines[0];

    $fieldList = prepareFieldList($line, $fields, $first_line_fieldnames);
    $fields = processFieldParams($fieldList, $fields);

    foreach ($fieldList as $f) {
        if ($f == -2)
            return JoomlaBasicMisc::JTextExtended('COM_CUSTOMTABLES_FIELD_NAMES_DO_NOT_MATCH');
    }

    $offset = 0;
    if ($first_line_fieldnames)
        $offset = 1;

    for ($i = $offset; $i < count($arrayOfLines); $i++) {
        if (count($arrayOfLines[$i]) > 0) {
            $sets = prepareSQLQuery($fieldList, $fields, $arrayOfLines[$i]);
            $listing_id = findRecord($tablerow->realtablename, $tablerow->realidfieldname, $tablerow->published_field_found, $sets);

            if (is_null($listing_id)) {
                $query = 'INSERT ' . $tablerow->realtablename . ' SET ' . implode(', ', $sets);
                database::setQuery($query);
            }
        }
    }
    return '';
}

function findRecord($realtablename, $realidfieldname, bool $published_field_found, array $wheres)
{
    if ($published_field_found)
        $wheres[] = 'published=1';

    $query = 'SELECT ' . $realidfieldname . ' FROM ' . $realtablename . ' WHERE ' . implode(' AND ', $wheres) . ' LIMIT 1';
    $rows = database::loadAssocList($query);

    if (count($rows) == 0)
        return null;

    return $rows[0][$realidfieldname];
}

function findSQLRecordJoin($realtablename, $join_realfieldname, $realidfieldname, bool $published_field_found, $vlus_str)
{
    $vlus = explode(',', $vlus_str);
    $wheres_or = array();
    foreach ($vlus as $vlu)
        $wheres_or[] = database::quoteName($join_realfieldname) . '=' . database::quote($vlu);

    $wheres[] = '(' . implode(' OR ', $wheres_or) . ')';

    if ($published_field_found)
        $wheres[] = 'published=1';

    $query = 'SELECT ' . $realidfieldname . ' FROM ' . $realtablename . ' WHERE ' . implode(' AND ', $wheres);
    $rows = database::loadAssocList($query);

    if (count($rows) == 0)
        return null;

    $listing_ids = array();
    foreach ($rows as $row)
        $listing_ids[] = $row[$realidfieldname];

    return $listing_ids;
}

function findSQLJoin($realtablename, $join_realfieldname, $realidfieldname, bool $published_field_found, $vlu)
{
    $wheres = [database::quoteName($join_realfieldname) . '=' . database::quote($vlu)];

    return findRecord($realtablename, $realidfieldname, $published_field_found, $wheres);
}

function addSQLJoinSets($realtablename, $sets)
{
    $query = 'INSERT ' . $realtablename . ' SET ' . implode(',', $sets);
    database::setQuery($query);
}

function prepareSQLQuery($fieldList, $fields, $line)
{
    $sets = array();
    $i = 0;

    foreach ($fieldList as $f_index) {
        if ($f_index >= 0) {
            $fieldType = $fields[$f_index]->type;

            if ($fieldType == 'sqljoin') {
                if (isset($fields[$f_index]->sqljoin)) {
                    $realtablename = $fields[$f_index]->sqljoin->table;

                    $vlu = findSQLJoin(
                        $realtablename,
                        $fields[$f_index]->sqljoin->field,
                        $fields[$f_index]->sqljoin->realidfieldname,
                        (bool)$fields[$f_index]->sqljoin->published_field_found,
                        $line[$i]);

                    if (is_null($vlu))//Join table record doesn't exists
                    {
                        $sub_sets = [];
                        $sub_sets[] = database::quoteName($fields[$f_index]->sqljoin->field) . '=' . database::quote($line[$i]);
                        addSQLJoinSets($realtablename, $sub_sets);

                        $vlu = findSQLJoin(
                            $realtablename,
                            $fields[$f_index]->sqljoin->field,
                            $fields[$f_index]->sqljoin->realidfieldname,
                            (bool)$fields[$f_index]->sqljoin->published_field_found,
                            $line[$i]);

                    }

                    if ((int)$vlu > 0)
                        $sets[] = database::quoteName($fields[$f_index]->realfieldname) . '=' . (int)$vlu;
                    else
                        $sets[] = database::quoteName($fields[$f_index]->realfieldname) . '=NULL';
                }
            } elseif ($fieldType == 'records') {
                if (isset($fields[$f_index]->sqljoin)) {
                    $realtablename = $fields[$f_index]->sqljoin->table;

                    $vlu = findSQLRecordJoin(
                        $realtablename,
                        $fields[$f_index]->sqljoin->field,
                        $fields[$f_index]->sqljoin->realidfieldname,
                        (bool)$fields[$f_index]->sqljoin->published_field_found,
                        $line[$i]);

                    if (is_null($vlu)) {
                        $sub_sets = [];
                        $sub_sets[] = database::quoteName($fields[$f_index]->sqljoin->field) . '=' . database::quote($line[$i]);
                        addSQLJoinSets($realtablename, $sub_sets);

                        $vlu = findSQLRecordJoin(
                            $realtablename,
                            $fields[$f_index]->sqljoin->field,
                            $fields[$f_index]->sqljoin->realidfieldname,
                            (bool)$fields[$f_index]->sqljoin->published_field_found,
                            $line[$i]);
                    }

                    if (!is_null($vlu) and $vlu != '')
                        $sets[] = database::quoteName($fields[$f_index]->realfieldname) . '=' . database::quote(',' . implode(',', $vlu) . ',');
                    else
                        $sets[] = database::quoteName($fields[$f_index]->realfieldname) . '=NULL';
                }
            } elseif ($fieldType == 'date' or $fieldType == 'creationtime' or $fieldType == 'changetime') {
                if (isset($line[$i]) and $line[$i] != '')
                    $sets[] = database::quoteName($fields[$f_index]->realfieldname) . '=' . database::quote($line[$i]);
                else
                    $sets[] = database::quoteName($fields[$f_index]->realfieldname) . '=NULL';
            } elseif ($fieldType == 'int' or $fieldType == 'user' or $fieldType == 'userid') {
                if (isset($line[$i]) and $line[$i] != '')
                    $sets[] = database::quoteName($fields[$f_index]->realfieldname) . '=' . (int)$line[$i];
                else
                    $sets[] = database::quoteName($fields[$f_index]->realfieldname) . '=NULL';
            } elseif ($fieldType == 'float') {
                if (isset($line[$i]) and $line[$i] != '')
                    $sets[] = database::quoteName($fields[$f_index]->realfieldname) . '=' . (float)$line[$i];
                else
                    $sets[] = database::quoteName($fields[$f_index]->realfieldname) . '=NULL';
            } elseif ($fieldType == 'checkbox') {
                if (isset($line[$i]) and $line[$i] != '') {
                    if ($line[$i] == 'Yes' or $line[$i] == '1')
                        $vlu = 1;
                    else
                        $vlu = 0;

                    $sets[] = database::quoteName($fields[$f_index]->realfieldname) . '=' . $vlu;
                }

            } else {
                if (isset($line[$i])) {
                    $vlu = $line[$i];
                    $sets[] = database::quoteName($fields[$f_index]->realfieldname) . '=' . database::quote($vlu);
                }
            }
        }

        $i++;
    }

    return $sets;
}

/**
 * This was used to detect if CSV file is in UTF 8
 * @param $s
 * @return bool
 */
function ifBomUtf8($s): bool
{
    if (substr($s, 0, 3) == chr(hexdec('EF')) . chr(hexdec('BB')) . chr(hexdec('BF')))
        return true;
    else
        return false;
}

function removeBomUtf8($s): string
{
    if (substr($s, 0, 3) == chr(hexdec('EF')) . chr(hexdec('BB')) . chr(hexdec('BF'))) {
        return substr($s, 3);
    } else {
        if (substr($s, 0, 2) == chr(hexdec('FF')) . chr(hexdec('FE')))
            return substr($s, 2);
        return $s;
    }
}

function prepareFieldList(array $fieldNames, array $fields, bool &$first_line_fieldnames): array
{
    $fieldList = array();

    //Let's check if first line is the field names
    $count = 0;

    foreach ($fieldNames as $fieldName_) {
        $index = 0;

        $fieldName = removeBomUtf8($fieldName_);
        $fieldName = strtolower(preg_replace("/[^a-zA-Z1-9]/", "", $fieldName));

        $found = false;
        foreach ($fields as $field) {
            $clean_field_name = strtolower(preg_replace("/[^a-zA-Z1-9]/", "", $field->fieldtitle));

            if ($fieldName_ == '#' or $fieldName_ == '') {
                $fieldList[] = -1;
                $found = true;
                $count++;
                $first_line_fieldnames = true;
                break;
            } elseif ((string)$clean_field_name == (string)$fieldName or (string)$field->fieldname == (string)$fieldName or (string)$field->fieldtitle == (string)$fieldName) {
                $fieldList[] = $index;
                $found = true;
                $count++;
                $first_line_fieldnames = true;
                break;
            }
            $index++;
        }

        if (!$found) {
            $count++;
            $fieldList[] = -2;
        }
    }

    $first_line_fieldnames = true;
    return $fieldList;
}
