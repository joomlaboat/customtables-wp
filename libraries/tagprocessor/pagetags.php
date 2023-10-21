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

/* All tags are implemented using Twig

Implemented:

{print} - {{ html.print() }}
{recordlist} - {{ record.list }}
{format:csv} - {{ html.format('csv') }}
{searchbutton} - {{ html.searchbutton }}
{search:email} - {{ html.search('email') }}
{recordcount} - {{ record.count_sentence }}
{count} - {{ record.count }}
{navigation} - {{ html.navigation(|type|,|css_class|) }}
{add} - {{ html.add(|alias_or_itemid|) }}
{add:,importcsv} - {{ html.importcsv }}
{pagination} - {{ html.pagination }}
{pagination:limit} - {{ html.limit(|step|) }}
{pagination:order} - {{ html.orderby }}
{batchtoolbar:modes} - {html.batch('delete','publish')}
{checkbox} - {html.batch('checkbox')
{html.batch('delete','checkbox')} - new tag

New:

{{ html.message("text",|type|) }} - types: Message(Green), Notice(Blue), Warning(Yellow), Error(Red)

*/

use \CustomTables\Twig_Html_Tags;
use \CustomTables\Twig_Url_Tags;
use \CustomTables\Twig_Record_Tags;

class tagProcessor_Page
{
    public static function process(CT &$ct, string &$pageLayout): void
    {
        $ct_html = new Twig_Html_Tags($ct, false);
        $ct_url = new Twig_Url_Tags($ct, false);
        $ct_record = new Twig_Record_Tags($ct);

        tagProcessor_Page::FormatLink($ct_url, $pageLayout);//{format:xls}  the link to the same page but in xls format
        tagProcessor_Page::PathValue($ct_html, $pageLayout); //Converted to Twig. Original replaced.
        tagProcessor_Page::AddNew($ct_html, $pageLayout); //Converted to Twig. Original replaced.
        tagProcessor_Page::Pagination($ct_html, $pageLayout); //Converted to Twig. Original replaced.
        tagProcessor_Page::PageToolBar($ct_html, $pageLayout); //Converted to Twig. Original replaced.
        tagProcessor_Page::PageToolBarCheckBox($ct_html, $pageLayout); //Converted to Twig. Original replaced.
        tagProcessor_Page::SearchButton($ct_html, $pageLayout); //Converted to Twig. Original replaced.
        tagProcessor_Page::SearchBOX($ct_html, $pageLayout); //Converted to Twig. Original replaced.
        tagProcessor_Page::RecordCountValue($ct, $pageLayout); //Converted to Twig. Original replaced.
        tagProcessor_Page::RecordCount($ct, $ct_html, $pageLayout); //Converted to Twig. Original replaced.
        tagProcessor_Page::PrintButton($ct_html, $pageLayout); //Converted to Twig. Original replaced.
        tagProcessor_Page::processRecordlist($pageLayout, $ct_record); //Twig version added - original replaced
    }

    public static function FormatLink(Twig_Url_Tags &$ct_url, string &$pageLayout): void
    {
        $options = array();
        $fList = JoomlaBasicMisc::getListToReplace('format', $options, $pageLayout, '{}');

        $i = 0;

        foreach ($fList as $fItem) {
            $option_list = explode(',', $options[$i]);
            $format = $option_list[0];

            //$format, $link_type = 'anchor', $image = '', $imagesize = '', $menu_item_alias = '', $csv_column_separator = ','

            $link_type = isset($option_list[1]) ? $option_list[1] : '';
            $image = isset($option_list[2]) ? $option_list[2] : '';
            $imagesize = isset($option_list[3]) ? $option_list[3] : '';
            $menu_item_alias = isset($option_list[4]) ? $option_list[4] : '';

            $vlu = $ct_url->format($format, $link_type, $image, $imagesize, $menu_item_alias, ',');

            $pageLayout = str_replace($fItem, $vlu, $pageLayout);
            $i++;
        }
    }

    public static function PathValue(Twig_Html_Tags &$ct_html, string &$pageLayout): void
    {
        $options = array();
        $fList = JoomlaBasicMisc::getListToReplace('navigation', $options, $pageLayout, '{}');

        $i = 0;

        foreach ($fList as $fItem) {
            $pair = explode(',', $options[$i]);

            $ul_css_class = $pair[0];
            $list_type = $pair[1] ?? 'list';

            $vlu = $ct_html->navigation($list_type, $ul_css_class);

            $pageLayout = str_replace($fItem, $vlu, $pageLayout);
            $i++;
        }
    }

    protected static function AddNew(Twig_Html_Tags &$ct_html, string &$pageLayout): void
    {
        $options = array();
        $fList = JoomlaBasicMisc::getListToReplace('add', $options, $pageLayout, '{}');

        $i = 0;

        foreach ($fList as $fItem) {
            $opt = explode(',', $options[$i]);

            if (isset($opt[1]) and $opt[1] == 'importcsv') {
                $vlu = $ct_html->importcsv();
            } else {
                $Alias_or_ItemId = $opt[0];
                $vlu = $ct_html->add($Alias_or_ItemId);
            }

            $pageLayout = str_replace($fItem, $vlu, $pageLayout);
            $i++;
        }
    }

    protected static function Pagination(Twig_Html_Tags &$ct_html, string &$pageLayout): void
    {
        $options = array();
        $fList = JoomlaBasicMisc::getListToReplace('pagination', $options, $pageLayout, '{}');

        $i = 0;

        foreach ($fList as $fItem) {
            $opt = explode(',', $options[$i]);

            $element_type = $opt[0];

            switch ($element_type) {
                case '':
                case 'paginaton':
                    $vlu = $ct_html->pagination();
                    break;

                case 'limit' :
                    $vlu = $ct_html->limit();
                    break;

                case 'order' :
                    $vlu = $ct_html->orderby();
                    break;

                default:
                    $vlu = 'pagination: type "' . $element_type . '" is unknown.';
            }

            $pageLayout = str_replace($fItem, $vlu, $pageLayout);
            $i++;
        }
    }

    protected static function PageToolBar(Twig_Html_Tags &$ct_html, string &$pageLayout): void
    {
        $options = array();
        $fList = JoomlaBasicMisc::getListToReplace('batchtoolbar', $options, $pageLayout, '{}');

        $i = 0;
        foreach ($fList as $fItem) {
            $modes = explode(',', $options[$i]);
            $vlu = $ct_html->batch($modes);
            $pageLayout = str_replace($fItem, $vlu, $pageLayout);

            $i++;
        }
    }

    static protected function PageToolBarCheckBox(Twig_Html_Tags &$ct_html, string &$pageLayout): void
    {
        $options = array();
        $fList = JoomlaBasicMisc::getListToReplace('checkbox', $options, $pageLayout, '{}');

        foreach ($fList as $fItem) {
            $vlu = $ct_html->batch('checkbox');
            $pageLayout = str_replace($fItem, $vlu, $pageLayout);
        }
    }

    static protected function SearchButton(Twig_Html_Tags &$ct_html, string &$pageLayout): void
    {
        $options = array();
        $fList = JoomlaBasicMisc::getListToReplace('searchbutton', $options, $pageLayout, '{}');

        if (count($fList) > 0) {
            $opair = explode(',', $options[0]);
            $vlu = $ct_html->searchbutton($opair[0]);

            foreach ($fList as $fItem)
                $pageLayout = str_replace($fItem, $vlu, $pageLayout);
        }
    }

    static protected function SearchBOX(Twig_Html_Tags &$ct_html, string &$pageLayout)
    {
        $options = array();
        $fList = JoomlaBasicMisc::getListToReplace('search', $options, $pageLayout, '{}');

        if (count($fList) == 0)
            return false;

        $i = 0;

        foreach ($fList as $fItem) {
            $vlu = '';

            if ($options[$i] != '') {
                $opair = JoomlaBasicMisc::csv_explode(',', $options[$i], '"', false);

                $list_of_fields_string_array = explode(',', $opair[0]);

                $class = $opair[1] ?? '';
                $reload = isset($opair[2]) and $opair[2] == 'reload';
                $improved = ($opair[3] ?? '');// '','improved' or 'virtualselect'
                $vlu = $ct_html->search($list_of_fields_string_array, $class, $reload, $improved);
            }
            $pageLayout = str_replace($fItem, $vlu, $pageLayout);
            $i++;
        }
    }

    static protected function RecordCountValue(CT &$ct, string &$pageLayout): void
    {
        $options = array();
        $fList = JoomlaBasicMisc::getListToReplace('count', $options, $pageLayout, '{}');

        foreach ($fList as $fItem) {

            $vlu = $ct->Table->recordcount;
            $pageLayout = str_replace($fItem, $vlu, $pageLayout);
        }
    }

    static protected function RecordCount(CT &$ct, Twig_Html_Tags &$ct_html, string &$pageLayout): void
    {
        $options = array();
        $fList = JoomlaBasicMisc::getListToReplace('recordcount', $options, $pageLayout, '{}');

        $i = 0;

        foreach ($fList as $fItem) {
            if ($options[$i] == 'numberonly')
                $vlu = $ct->Table->recordcount;
            else
                $vlu = $ct_html->recordcount();

            $pageLayout = str_replace($fItem, $vlu, $pageLayout);
            $i++;
        }
    }

    static protected function PrintButton(Twig_Html_Tags &$ct_html, string &$pageLayout): void
    {
        $options = array();
        $fList = JoomlaBasicMisc::getListToReplace('print', $options, $pageLayout, '{}');

        $i = 0;

        foreach ($fList as $fItem) {
            $class = 'ctEditFormButton btn button';
            if (isset($opair[0]) and $opair[0] != '')
                $class = $opair[0];

            $vlu = $ct_html->print($class);

            $pageLayout = str_replace($fItem, $vlu, $pageLayout);
            $i++;
        }
    }

    protected static function processRecordlist(string &$pageLayout, Twig_Record_Tags &$ct_record): void
    {
        $options = array();
        $fList = JoomlaBasicMisc::getListToReplace('recordlist', $options, $pageLayout, '{}', ':', '"');

        $i = 0;

        foreach ($fList as $fItem) {
            $vlu = $ct_record->list();

            $pageLayout = str_replace($fItem, $vlu, $pageLayout);
            $i++;
        }
    }

}
