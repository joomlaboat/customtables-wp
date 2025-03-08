<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use CustomTables\common;

$errors = common::getTransientMessages('customtables_error_message');
$messages = common::getTransientMessages('customtables_success_message');

?>


<div class="wrap ct_doc">
    <h2><?php esc_html_e('Custom Tables - Database Schema', 'customtables'); ?></h2>

    <h2 class="nav-tab-wrapper wp-clearfix">
        <button data-toggle="tab" data-tabs=".gtabs.demo" data-tab=".tab-1" class="nav-tab nav-tab-active">Diagram</button>
        <button data-toggle="tab" data-tabs=".gtabs.demo" data-tab=".tab-2" class="nav-tab">Checks</button>
    </h2>

	<?php common::showTransient($errors, $messages); ?>

    <div class="gtabs demo">
        <div class="gtab active tab-1">
            <?php include_once('customtables-schema-diagram.php'); ?>
        </div>

        <div class="gtab tab-2">
            <?php include('customtables-schema-checks.php'); ?>
        </div>
    </div>

</div>
<p><a href="https://ct4.us/contact-us/" target="_blank"><?php echo esc_html__('Facing issues? Contact our support team.', 'customtables'); ?></a></p>
