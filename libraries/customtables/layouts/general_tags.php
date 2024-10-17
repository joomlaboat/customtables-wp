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

use Exception;
use Joomla\CMS\Router\Route;

class Twig_Fields_Tags
{
    var CT $ct;
    var bool $isTwig;

    function __construct(CT &$ct, bool $isTwig = true)
    {
        $this->ct = &$ct;
        $this->isTwig = $isTwig;
    }

    function json(): string
    {
        return common::ctJsonEncode(Fields::shortFieldObjects($this->ct->Table->fields));
    }

    function list($param = 'fieldname'): array
    {
        $available_params = ['fieldname', 'title', 'defaultvalue', 'description', 'isrequired', 'isdisabled', 'type', 'typeparams', 'valuerule', 'valuerulecaption'];

        if (!in_array($param, $available_params)) {
            $this->ct->errors[] = '{{ fields.array("' . $param . '") }} - Unknown parameter.';
            return [];
        }

        $fields = Fields::shortFieldObjects($this->ct->Table->fields);
        $list = [];
        foreach ($fields as $field)
            $list[] = $field[$param];

        return $list;
    }

    function count(): int
    {
        return count($this->ct->Table->fields);
    }
}

class Twig_Url_Tags
{
    var CT $ct;
    var bool $isTwig;

    function __construct(CT &$ct, $isTwig = true)
    {
        $this->ct = &$ct;
        $this->isTwig = $isTwig;
    }

    function link(): string
    {
        return $this->ct->Env->current_url;
    }

    /**
     * @throws Exception
     * @since 3.2.9
     */
    function base64(): ?string
    {
        if (defined('_JEXEC')) {
            return $this->ct->Env->encoded_current_url;
        } else {
            common::enqueueMessage('Warning: The {{ url.base64() }} tag is not supported in the current version of the Custom Tables for WordPress plugin.');
            return null;
        }
    }

    /**
     * @throws Exception
     * @since 3.2.9
     */
    function root(): ?string
    {
        if (!$this->ct->Env->advancedTagProcessor) {
            common::enqueueMessage('Warning: The {{ url.root }} ' . esc_html__("Available in PRO Version", "customtables"));
            return null;
        }

        $include_host = false;

        $functionParams = func_get_args();
        if (isset($functionParams[0])) {

            if (is_bool($functionParams[0]))
                $include_host = $functionParams[0];
            elseif ($functionParams[0] == 'includehost')
                $include_host = true;
        }

        $add_trailing_slash = true;
        if (isset($functionParams[1])) {
            if (is_bool($functionParams[1]))
                $add_trailing_slash = $functionParams[1];
            elseif ($functionParams[0] == 'notrailingslash')
                $add_trailing_slash = false;
        }

        if ($include_host)
            $WebsiteRoot = common::UriRoot(false, false);
        else
            $WebsiteRoot = CUSTOMTABLES_MEDIA_HOME_URL;

        if ($add_trailing_slash) {
            if ($WebsiteRoot == '' or $WebsiteRoot[strlen($WebsiteRoot) - 1] != '/') //Root must have a slash character / in the end
                $WebsiteRoot .= '/';
        } else {
            $l = strlen($WebsiteRoot);
            if ($WebsiteRoot != '' and $WebsiteRoot[$l - 1] == '/')
                $WebsiteRoot = substr($WebsiteRoot, 0, $l - 1);//delete trailing slash
        }

        return $WebsiteRoot;
    }

    /**
     * @throws Exception
     * @since 3.2.9
     */
    function getuint($param, $default = 0): ?int
    {
        return common::inputGetUInt($param, $default);
    }

    /**
     * @throws Exception
     * @since 3.2.9
     */
    function getfloat($param, $default = 0): float
    {
        return common::inputGetFloat($param, $default);
    }

    /**
     * @throws Exception
     * @since 3.2.9
     */
    function getword($param, $default = ''): string
    {
        return common::inputGetWord($param, $default);
    }

    /**
     * @throws Exception
     * @since 3.2.9
     */
    function getalnum($param, $default = ''): string
    {
        return common::inputGetCmd($param, $default);
    }

    /**
     * @throws Exception
     * @since 3.2.9
     */
    function getcmd($param, $default = ''): string
    {
        return common::inputGetCmd($param, $default);
    }

    /**
     * @throws Exception
     * @since 3.2.9
     */
    function getstringandencode($param, $default = ''): ?string
    {
        if ($this->ct->Env->advancedTagProcessor and class_exists('CustomTables\ctProHelpers')) {
            return ctProHelpers::getstringandencode($param, $default);
        } else {
            common::enqueueMessage('Warning: The {{ url.getstringandencode() }} ' . esc_html__("Available in PRO Version", "customtables"));
            return null;
        }
    }

    /**
     * @throws Exception
     * @since 3.2.9
     */
    function getstring($param, $default = ''): string
    {
        return common::inputGetString($param, $default);
    }

    /**
     * @throws Exception
     * @since 3.2.9
     */
    function getstringanddecode($param, $default = ''): ?string
    {
        if ($this->ct->Env->advancedTagProcessor and class_exists('CustomTables\ctProHelpers')) {
            return ctProHelpers::getstringanddecode($param, $default);
        } else {
            common::enqueueMessage('Warning: The {{ url.getstringanddecode() }} ' . esc_html__("Available in PRO Version", "customtables"));
            return null;
        }
    }

    /**
     * @throws Exception
     * @since 3.2.9
     */
    function itemid(): ?int
    {
        if (defined('_JEXEC'))
            return $this->ct->Params->ItemId;
        else {
            common::enqueueMessage('Warning: The {{ url.itemid }} tag is not supported in the current version of the Custom Tables for WordPress plugin.');
            return null;
        }
    }

    /**
     * @throws Exception
     * @since 3.2.9
     */
    function getint($param, $default = 0): ?int
    {
        return common::inputGetInt($param, $default);
    }

    /**
     * @throws Exception
     * @since 3.2.9
     */
    function set($option, $param = ''): void
    {
        if (defined('_JEXEC'))
            common::inputSet($option, $param);
        else
            common::enqueueMessage('Warning: The {{ url.set() }} tag is not supported in the current version of the Custom Tables for WordPress plugin.');
    }

    /**
     * @throws Exception
     * @since 3.2.9
     */
    function server($param): ?string
    {
        if (!$this->ct->Env->advancedTagProcessor) {
            common::enqueueMessage('Warning: The {{ url.server }} ' . esc_html__("Available in PRO Version", "customtables"));
            return null;
        }
        return common::getServerParam($param);
    }

    /**
     * @throws Exception
     * @since 3.2.9
     */
    function format($format, $link_type = 'anchor', $image = '', $imagesize = '', $layoutname = '', $csv_column_separator = ','): ?string
    {
        if (defined('_JEXEC')) {
            if ($this->ct->Env->print == 1 or ($this->ct->Env->frmt != 'html' and $this->ct->Env->frmt != ''))
                return '';

            $link = CTMiscHelper::deleteURLQueryOption($this->ct->Env->current_url, 'frmt');
            $link = CTMiscHelper::deleteURLQueryOption($link, 'layout');
            //}

            $link = Route::_($link);

            //check if format supported
            $allowed_formats = ['csv', 'json', 'xml', 'xlsx', 'pdf', 'image'];
            if ($format == '' or !in_array($format, $allowed_formats))
                $format = 'csv';

            $link .= (!str_contains($link, '?') ? '?' : '&') . 'frmt=' . $format . '&clean=1';

            if ($layoutname != '')
                $link .= '&layout=' . $layoutname;

            if ($format == 'csv' and $csv_column_separator != ',')
                $link .= '&sep=' . $csv_column_separator;

            if ($link_type == 'anchor' or $link_type == '') {
                $allowed_sizes = ['16', '32', '48'];
                if ($imagesize == '' or !in_array($imagesize, $allowed_sizes))
                    $imagesize = 32;

                if ($format == 'image')
                    $format_image = 'jpg';
                else
                    $format_image = $format;

                $alt = 'Download ' . strtoupper($format) . ' file';

                if ($image == '') {
                    if ($this->ct->Env->toolbarIcons != '' and $format == 'csv') {
                        $img = '<i class="ba-btn-transition ' . $this->ct->Env->toolbarIcons . ' fa-file-csv" data-icon="' . $this->ct->Env->toolbarIcons . ' fa-file-csv" title="' . $alt . '"></i>';
                    } else {
                        $image = '/components/com_customtables/libraries/customtables/media/images/fileformats/' . $imagesize . 'px/' . $format_image . '.png';
                        $img = '<img src="' . $image . '" alt="' . $alt . '" title="' . $alt . '" style="width:' . $imagesize . 'px;height:' . $imagesize . 'px;">';
                    }
                } else
                    $img = '<img src="' . $image . '" alt="' . $alt . '" title="' . $alt . '" style="width:' . $imagesize . 'px;height:' . $imagesize . 'px;">';

                return '<a href="' . $link . '" class="toolbarIcons" id="ctToolBarExport2CSV" target="_blank">' . $img . '</a>';

            } elseif ($link_type == '_value' or $link_type == 'linkonly') {
                //link only
                return $link;
            }
            return '';
        } else {
            common::enqueueMessage('Warning: The {{ url.format() }} tag is not supported in the current version of the Custom Tables for WordPress plugin.');
            return null;
        }
    }
}


