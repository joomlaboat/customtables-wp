<?php
/**
 * Plugin Name:       CustomTables
 * Plugin URI:        https://ct4.us/
 * GitHub:            https://github.com/joomlaboat/customtables-wp
 * Author:            Ivan Komlev
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use CustomTables\Documentation;

include_once(CUSTOMTABLES_LIBRARIES_PATH . DIRECTORY_SEPARATOR . 'customtables' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'admin-documentation.php');
$documentation = new Documentation(true,true);

?>
<div class="wrap ct_doc">
    <h2><?php esc_html_e( 'Custom Tables - Documentation', 'customtables'); ?></h2>
    <h2 class="nav-tab-wrapper wp-clearfix">
        <button data-toggle="tab" data-tabs=".gtabs.demo" data-tab=".tab-1" class="nav-tab nav-tab-active" >Field Types</button>
        <button data-toggle="tab" data-tabs=".gtabs.demo" data-tab=".tab-2" class="nav-tab" >Layouts</button>
        <button data-toggle="tab" data-tabs=".gtabs.demo" data-tab=".tab-3" class="nav-tab" >More</button>
    </h2>

    <div class="gtabs demo" >
        <div class="gtab active tab-1">
            <h3><?php echo CustomTables\common::translate('COM_CUSTOMTABLES_TABLEFIELDTYPES_DESC'); ?></h3><br/>
            <?php echo $documentation->getFieldTypes(); ?>
        </div>

        <div class="gtab tab-2">
            <h3><?php echo CustomTables\common::translate('COM_CUSTOMTABLES_LAYOUTTAGS'); ?></h3><br/>
            <?php echo $documentation->getLayoutTags(); ?>
        </div>

        <div class="gtab tab-3"><h1>More about CustomTables</h1>

            <a href="https://joomlaboat.com/custom-tables" target="_blank"
               style="color:#51A351;">More</a>

        </div>
    </div>
</div>
