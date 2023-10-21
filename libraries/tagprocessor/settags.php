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
use Joomla\CMS\Factory;

/* All tags already implemented using Twig */

class tagProcessor_Set
{
    public static function process(CT &$ct, &$pageLayout)
    {
        tagProcessor_Set::setHeadTag($ct, $pageLayout);
        tagProcessor_Set::setMetaDescription($ct, $pageLayout);
        tagProcessor_Set::setMetaKeywords($ct, $pageLayout);
        tagProcessor_Set::setPageTitle($ct, $pageLayout);
    }

    protected static function setHeadTag(CT &$ct, string &$htmlresult)
    {
        $options = array();
        $fList = JoomlaBasicMisc::getListToReplace('headtag', $options, $htmlresult, '{}');

        $i = 0;
        foreach ($fList as $fItem) {
            $opts = JoomlaBasicMisc::csv_explode(',', $options[$i], '"', false);

            if ($ct->Env->isModal) {
                $htmlresult = str_replace($fItem, $opts[0], $htmlresult);
            } else {
                $document = Factory::getDocument();
                $document->addCustomTag($opts[0]);
                $htmlresult = str_replace($fItem, '', $htmlresult);
            }
            $i++;
        }
    }

    protected static function setMetaDescription(CT &$ct, string &$htmlresult)
    {
        $options = array();
        $fList = JoomlaBasicMisc::getListToReplace('metadescription', $options, $htmlresult, '{}');

        $i = 0;
        foreach ($fList as $fItem) {
            $opts = JoomlaBasicMisc::csv_explode(',', $options[$i], '"', false);
            if ($ct->Env->isModal) {
            } else {
                $doc = Factory::getDocument();
                $doc->setMetaData('description', $opts[0]);
            }

            $htmlresult = str_replace($fItem, '', $htmlresult);

            $i++;
        }

    }

    protected static function setMetaKeywords(CT &$ct, string &$htmlresult)
    {
        $options = array();
        $fList = JoomlaBasicMisc::getListToReplace('metakeywords', $options, $htmlresult, '{}');

        $i = 0;
        foreach ($fList as $fItem) {
            $opts = JoomlaBasicMisc::csv_explode(',', $options[$i], '"', false);

            if ($ct->Env->isModal) {

            } else {
                $doc = Factory::getDocument();
                $doc->setMetaData('keywords', $opts[0]);
            }

            $htmlresult = str_replace($fItem, '', $htmlresult);

            $i++;
        }

    }

    protected static function setPageTitle(CT &$ct, string &$htmlresult)
    {
        $options = array();
        $fList = JoomlaBasicMisc::getListToReplace('pagetitle', $options, $htmlresult, '{}');
        $document = Factory::getDocument();
        $i = 0;
        foreach ($fList as $fItem) {
            $opts = JoomlaBasicMisc::csv_explode(',', $options[$i], '"', false);

            if (!$ct->Env->isModal)
                $document->setTitle(JoomlaBasicMisc::JTextExtended($opts[0]));

            $htmlresult = str_replace($fItem, '', $htmlresult);
            $i++;
        }

        if (count($fList) == 0 and $ct->Params->pageTitle !== null)
            $document->setTitle(JoomlaBasicMisc::JTextExtended($ct->Params->pageTitle));
    }
}
