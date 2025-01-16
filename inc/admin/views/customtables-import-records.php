<?php

use CustomTables\CTMiscHelper;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

$max_file_size = CTMiscHelper::file_upload_max_size();

?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e('Import Records', 'customtables'); ?></h1>

    <?php
    $message = get_transient('plugin_error_message');
    if (!empty($message)) {
        echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($message) . '</p></div>';
        delete_transient('plugin_error_message'); // Once displayed, clear the transient
    }

    $success_message = get_transient('plugin_success_message');
    if (!empty($success_message)) {
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($success_message) . '</p></div>';
        // Optionally, you can delete the transient after displaying it
        delete_transient('plugin_success_message');
    }
    ?>

    <p class="install-help"><?php esc_html_e('This function allows for the importation of records from CSV file.', 'customtables'); ?></p>
    <form method="post" action="" id="esFileUploaderForm_Tables" enctype="multipart/form-data">
        <?php
        wp_nonce_field('import-table'); // Add a nonce field
        ?>

        <p>
            <?php
            esc_html_e('Maximum allowed file size', 'customtables');
            echo ': ';
            echo esc_html(CTMiscHelper::formatSizeUnits($max_file_size));
            ?>
        </p>

        <input type="file" name="filetosubmit" accept=".csv"
               onchange="document.getElementById('upload-file').disabled=false"/>
        <input type="submit" id="upload-file" name="upload_file" class="button"
               value="<?php esc_html_e('Upload File', 'customtables'); ?>"
               disabled=""/>
        <input type="hidden" name="action" value="import-csv"/>

        <p><a href="https://ct4.us/contact-us/" target="_blank"><?php echo esc_html__('Need support? We’re just a message away.', 'customtables'); ?></a></p>
</div>
