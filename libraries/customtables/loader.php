<?php
/**
 * CustomTables Joomla! 3.x/4.x/5.x Component and WordPress 6.x Plugin
 * @package Custom Tables
 * @author Ivan Komlev <support@joomlaboat.com>
 * @link https://joomlaboat.com
 * @copyright (C) 2018-2024. Ivan Komlev
 * @license GNU/GPL Version 2 or later - https://www.gnu.org/licenses/gpl-2.0.html
 **/

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) exit;

use Joomla\CMS\Uri\Uri;

function CustomTablesLoader($include_utilities = false, $include_html = false, $PLUGIN_NAME_DIR = null, $componentName = 'com_customtables', bool $loadTwig = true): void
{
    if (defined('CUSTOMTABLES_MEDIA_WEBPATH'))
        return;

    if (defined('_JEXEC')) {

        if ($componentName == 'com_extensiontranslator')
            $libraryPath = JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . $componentName . DIRECTORY_SEPARATOR . 'libraries';
        else
            $libraryPath = JPATH_SITE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . $componentName . DIRECTORY_SEPARATOR . 'libraries';

        if (!defined('CUSTOMTABLES_ABSPATH'))
            define('CUSTOMTABLES_ABSPATH', JPATH_SITE . DIRECTORY_SEPARATOR);

        if (!defined('CUSTOMTABLES_IMAGES_PATH'))
            define('CUSTOMTABLES_IMAGES_PATH', JPATH_SITE . DIRECTORY_SEPARATOR . 'images');

        if (!defined('CUSTOMTABLES_PRO_PATH'))
            define('CUSTOMTABLES_PRO_PATH', JPATH_SITE . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'content' . DIRECTORY_SEPARATOR . 'customtables' . DIRECTORY_SEPARATOR);

    } elseif (defined('WPINC')) {
        $libraryPath = $PLUGIN_NAME_DIR . 'libraries';

        if (!defined('CUSTOMTABLES_ABSPATH'))
            define('CUSTOMTABLES_ABSPATH', ABSPATH);

        if (!defined('CUSTOMTABLES_IMAGES_PATH'))
            define('CUSTOMTABLES_IMAGES_PATH', ABSPATH . 'wp-content' . DIRECTORY_SEPARATOR . 'uploads');

        if (!defined('CUSTOMTABLES_PRO_PATH'))
            define('CUSTOMTABLES_PRO_PATH', WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'customtablespro' . DIRECTORY_SEPARATOR);
    }

    if (!defined('CUSTOMTABLES_LIBRARIES_PATH'))
        define('CUSTOMTABLES_LIBRARIES_PATH', $libraryPath);

    if (defined('_JEXEC')) {
        define('CUSTOMTABLES_MEDIA_WEBPATH', URI::root(false) . 'components/com_customtables/libraries/customtables/media/');
        define('CUSTOMTABLES_LIBRARIES_WEBPATH', URI::root(false) . 'components/com_customtables/libraries/');

        define('CUSTOMTABLES_PLUGIN_WEBPATH', URI::root(false) . 'plugins/content/customtables/');

        $url = URI::root(false);
        if (strlen($url) > 0 and $url[strlen($url) - 1] == '/')
            $url = substr($url, 0, strlen($url) - 1);

        define('CUSTOMTABLES_MEDIA_HOME_URL', $url);
    } elseif (defined('WPINC')) {
        define('CUSTOMTABLES_MEDIA_WEBPATH', home_url() . '/wp-content/plugins/customtables/libraries/customtables/media/');
        define('CUSTOMTABLES_MEDIA_HOME_URL', home_url());
    }

    //or Factory::getApplication()->getName() == 'administrator'
    if (!defined('_JEXEC') or ($loadTwig === null or $loadTwig) and !class_exists('Twig')) {

        if ($componentName == 'com_customtables' or $componentName == 'com_extensiontranslator') {
            $twig_file = CUSTOMTABLES_LIBRARIES_PATH . DIRECTORY_SEPARATOR . 'twig' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
            require_once($twig_file);
        }
    }

    $path = dirname(__FILE__) . DIRECTORY_SEPARATOR;
    $pathIntegrity = $path . 'integrity' . DIRECTORY_SEPARATOR;

    require_once($pathIntegrity . 'integrity.php');
    require_once($pathIntegrity . 'fields.php');
    require_once($pathIntegrity . 'coretables.php');
    require_once($pathIntegrity . 'tables.php');

    $path_helpers = $path . 'helpers' . DIRECTORY_SEPARATOR;

    require_once($path_helpers . 'imagemethods.php');
    require_once($path_helpers . 'email.php');
    require_once($path_helpers . 'user.php');
    require_once($path_helpers . 'misc.php');
    require_once($path_helpers . 'fileutils.php');


    if (defined('_JEXEC')) {
        require_once(CUSTOMTABLES_LIBRARIES_PATH . DIRECTORY_SEPARATOR . 'ct-common-joomla.php');
        require_once(CUSTOMTABLES_LIBRARIES_PATH . DIRECTORY_SEPARATOR . 'ct-database-joomla.php');
        if (file_exists(CUSTOMTABLES_LIBRARIES_PATH . DIRECTORY_SEPARATOR . 'pagination.php'))
            require_once(CUSTOMTABLES_LIBRARIES_PATH . DIRECTORY_SEPARATOR . 'pagination.php');
    } elseif (defined('WPINC')) {
        require_once(CUSTOMTABLES_LIBRARIES_PATH . DIRECTORY_SEPARATOR . 'ct-common-wp.php');
        require_once(CUSTOMTABLES_LIBRARIES_PATH . DIRECTORY_SEPARATOR . 'ct-database-wp.php');
    }

    require_once($path_helpers . 'tables.php');
    require_once($path_helpers . 'compareimages.php');
    require_once($path_helpers . 'findsimilarimage.php');

    if ($include_utilities) {
        $path_utilities = $path . 'utilities' . DIRECTORY_SEPARATOR;
        require_once($path_utilities . 'importtables.php');
        require_once($path_utilities . 'exporttables.php');
    }

    $pathDataTypes = $path . 'ct' . DIRECTORY_SEPARATOR;
    require_once($pathDataTypes . 'ct.php');
    require_once($pathDataTypes . 'environment.php');
    require_once($pathDataTypes . 'params.php');

    $pathDataTypes = $path . 'datatypes' . DIRECTORY_SEPARATOR;
    require_once($pathDataTypes . 'datatypes.php');
    require_once($pathDataTypes . 'filemethods.php');

    $pathDataTypes = $path . 'layouts' . DIRECTORY_SEPARATOR;
    require_once($pathDataTypes . 'layouts.php');

    require_once($pathDataTypes . 'twig.php');
    require_once($pathDataTypes . 'general_tags.php');
    require_once($pathDataTypes . 'record_tags.php');
    require_once($pathDataTypes . 'html_tags.php');


    $pathDataTypes = $path . 'logs' . DIRECTORY_SEPARATOR;
    require_once($pathDataTypes . 'logs.php');

    $pathDataTypes = $path . 'ordering' . DIRECTORY_SEPARATOR;
    require_once($pathDataTypes . 'ordering.php');

    if ($include_html) {
        $pathDataTypes = $path . 'ordering' . DIRECTORY_SEPARATOR;
        require_once($pathDataTypes . 'html.php');
    }

    $pathDataTypes = $path . 'records' . DIRECTORY_SEPARATOR;
    require_once($pathDataTypes . 'savefieldqueryset.php');
    require_once($pathDataTypes . 'record.php');

    //$path_datatypes = $path . 'customphp' . DIRECTORY_SEPARATOR;
    //require_once($path_datatypes.'customphp.php');

    $pathDataTypes = $path . 'table' . DIRECTORY_SEPARATOR;
    require_once($pathDataTypes . 'table.php');

    $pathDataTypes = $path . 'html' . DIRECTORY_SEPARATOR;
    require_once($pathDataTypes . 'toolbar.php');
    require_once($pathDataTypes . 'forms.php');
    require_once($pathDataTypes . 'inputbox.php');
    require_once($pathDataTypes . 'value.php');

    $pathDataTypes = $path . 'tables' . DIRECTORY_SEPARATOR;
    require_once($pathDataTypes . 'tables.php');

    $pathDataTypes = $path . 'fields' . DIRECTORY_SEPARATOR;
    require_once($pathDataTypes . 'fields.php');

    $pathDataTypes = $path . 'languages' . DIRECTORY_SEPARATOR;
    require_once($pathDataTypes . 'languages.php');

    $pathDataTypes = $path . 'filter' . DIRECTORY_SEPARATOR;
    require_once($pathDataTypes . 'filtering.php');

    //$path_datatypes = $path . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR;
    //require_once($path_datatypes.'Logs.php');

    $pathViews = CUSTOMTABLES_LIBRARIES_PATH . DIRECTORY_SEPARATOR . 'customtables' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR;

    require_once($pathViews . 'edit.php');
    require_once($pathViews . 'catalog.php');
    require_once($pathViews . 'details.php');
}
