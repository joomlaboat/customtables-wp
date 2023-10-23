<?php

/**
 * The admin area of the plugin to load the User List Table
 */

?>


<div class="wrap ct_doc">
    <h2><?php _e('Custom Tables - Database Schema', $this->plugin_text_domain); ?></h2>

    <h2 class="nav-tab-wrapper wp-clearfix">
        <button data-toggle="tab" data-tabs=".gtabs.demo" data-tab=".tab-1" class="nav-tab nav-tab-active">Diagram</button>
        <button data-toggle="tab" data-tabs=".gtabs.demo" data-tab=".tab-2" class="nav-tab">Checks</button>
    </h2>

    <div class="gtabs demo">
        <div class="gtab active tab-1">
            <?php include_once('customtables-schema-diagram.php'); ?>
        </div>

        <div class="gtab tab-2">
            <?php include('customtables-schema-checks.php'); ?>
        </div>
    </div>

</div>

