<?php
/**
 * CustomTables Joomla! 3.x/4.x Native Component
 * @package Custom Tables
 * @author Ivan Komlev <support@joomlaboat.com>
 * @link https://joomlaboat.com
 * @copyright (C) 2018-2023 Ivan Komlev
 * @license GNU/GPL Version 2 or later - https://www.gnu.org/licenses/gpl-2.0.html
 **/

// no direct access
if (!defined('_JEXEC') and !defined('WPINC')) {
    die('Restricted access');
}

use CustomTables\CT;
use CustomTables\TwigProcessor;

trait render_csv
{
    public static function get_CatalogTable_singleline_CSV(CT &$ct, $layoutType, $layout)
    {
        if (ob_get_contents())
            ob_clean();

        //Prepare line layout
        $layout = str_replace("\n", '', $layout);
        $layout = str_replace("\r", '', $layout);

        $twig = new TwigProcessor($ct, $layout);

        $records = [];

        foreach ($ct->Records as $row)
            $records[] = trim(strip_tags(tagProcessor_Item::RenderResultLine($ct, $layoutType, $twig, $row)));//TO DO

        $result = implode('
', $records);

        return $result;
    }

    protected static function get_CatalogTable_CSV(CT &$ct, $layoutType, $fields)
    {
        $catalogresult = '';

        $fields = str_replace("\n", '', $fields);
        $fields = str_replace("\r", '', $fields);

        $fieldarray = JoomlaBasicMisc::csv_explode(',', $fields, '"', true);

        //prepare header and record layouts
        $result = '';

        $recordline = '';

        $header_fields = array();
        $line_fields = array();
        foreach ($fieldarray as $field) {
            $fieldpair = JoomlaBasicMisc::csv_explode(':', $field, '"', false);
            $header_fields[] = trim(strip_tags(html_entity_decode($fieldpair[0])));//header
            if (isset($fieldpair[1]))
                $vlu = str_replace('"', '', $fieldpair[1]);
            else
                $vlu = "";

            $line_fields[] = $vlu;//content
        }

        $recordline .= '"' . implode('","', $line_fields) . '"';
        $result .= '"' . implode('","', $header_fields) . '"';//."\r\n";

        //Parse Header
        $LayoutProc = new LayoutProcessor($ct);
        $LayoutProc->layout = $result;
        $result = $LayoutProc->fillLayout();
        $result = str_replace('&&&&quote&&&&', '"', $result);

        //Initiate the file output

        $result = strip_tags($result);
        $result .= strip_tags(self::renderCSVoutput($ct, $layoutType));

        if ($ct->Table->recordcount > $ct->LimitStart + $ct->Limit) {
            if ($ct->Limit > 0) {
                for ($limitstart = $ct->LimitStart + $ct->Limit; $limitstart < $ct->Table->recordcount; $limitstart += $ct->Limit) {
                    $ct->LimitStart = $limitstart;

                    $ct->getRecords();//get records

                    if (count($ct->Records) == 0)
                        break;//no records left - escape

                    $result .= self::renderCSVoutput($ct, $layoutType, $recordline);//output next chunk
                }
            }
        }

        return strip_tags($result);
    }

    protected static function renderCSVoutput(CT &$ct, int $layoutType, string $itemLayout)
    {
        $twig = new TwigProcessor($ct, $itemLayout);

        $number = 1 + $ct->LimitStart; //table row number, it can be used in the layout as {number}
        $tablecontent = '';

        foreach ($ct->Records as $row) {
            $row['_number'] = $number;
            $row['_islast'] = $number == count($ct->Records);

            $tablecontent .= '
' . strip_tags(tagProcessor_Item::RenderResultLine($ct, $layoutType, $twig, $row));//TODO

            $number++;
        }
        return $tablecontent;
    }
}
