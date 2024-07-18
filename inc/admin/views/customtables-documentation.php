<?php
/**
 * Plugin Name:       CustomTables
 * Plugin URI:        https://ct4.us/
 * GitHub:            https://github.com/joomlaboat/customtables-wp
 * Author:            Ivan Komlev
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use CustomTables\Documentation;

include_once(CUSTOMTABLES_LIBRARIES_PATH . DIRECTORY_SEPARATOR . 'customtables' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'admin-documentation.php');
$documentation = new Documentation(true, true);

?>
<div class="wrap ct_doc">
    <h2><?php esc_html_e('Custom Tables - Documentation', 'customtables'); ?></h2>

    <p><a href="https://ct4.us/contact-us/"
          target="_blank"><?php echo esc_html__('Have questions? Get in touch with our support team.', 'customtables'); ?></a>
    </p>

    <h2 class="nav-tab-wrapper wp-clearfix">
        <button data-toggle="tab" data-tabs=".gtabs.demo" data-tab=".tab-1" class="nav-tab nav-tab-active">Field Types
        </button>
        <button data-toggle="tab" data-tabs=".gtabs.demo" data-tab=".tab-2" class="nav-tab">Layouts</button>
        <button data-toggle="tab" data-tabs=".gtabs.demo" data-tab=".tab-3" class="nav-tab">More</button>
    </h2>

    <div class="gtabs demo">
        <div class="gtab active tab-1">
            <?php

            $allowed_html = array(
                'p' => array(),
                'i' => array(),
                'br' => array()
            );

            echo wp_kses(__('<p>A database field is a single piece of information from a record. A database record is a set of fields.</p>
<p>The properties of a field describe the characteristics and behavior of data added to that field.
A field\'s data type is the most important property because it determines what kind of data the field can store. </p>
<p>Field Type Parameters - Tells additional details to the database about how to store a value and configures the general behavior of the input boxes.</p>
<p>The field can be used in five different ways : </p>
<p>
1. {{ fieldname }} or {{ fieldname(<i>params</i>) }} - Returns processed value of the field using the field type parameters. If it\'s a float field type then the returned value will be 123.00 for example instead of 123.<br/>
2. {{ fieldname.value }} or {{ fieldname(<i>params</i>) }} - Returns pure, unprocessed value of the field.<br/>
3. {{ fieldname.title }} - Returns the field title in current language.<br/>
4. {{ fieldname.label }} or {{ fieldname.label(<i>clickable</i> = true|false) }} - Renders the label HTML tags with the field title in current language. It can be clickable to sort the records by that field.<br/>
5. {{ fieldname.edit }} or {{ fieldname(<i>params</i>) }} - Returns an edit record input box and the params configures the appearance and functionality.<br/></p>
<br/>

', 'customtables'), $allowed_html); ?>


            <?php echo wp_kses(__('<p>Below is the list of parameters every field type accepts and how to use it : </p><br/>'), $allowed_html); ?>
            <br/>




            <?php
            $documentation_safe = str_replace('ct_readmoreClosed', '', $documentation->getFieldTypes());
            echo wp_kses_post($documentation_safe);
            ?>
        </div>

        <div class="gtab tab-2">
            <h3><?php // echo esc_html__('Layout Tags', 'customtables'); ?></h3><br/>
            <?php
            $documentation_safe = str_replace('ct_readmoreClosed', '', $documentation->getLayoutTags());
            echo wp_kses_post($documentation_safe);
            ?>
        </div>

        <div class="gtab tab-3"><h1>More about CustomTables</h1>

            <a href="https://ct4.us/contact-us/" target="_blank"
               style="color:#51A351;">We're here to help. Contact us for support.</a>

        </div>
    </div>
</div>
