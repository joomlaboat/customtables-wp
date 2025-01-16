<?php

use CustomTables\common;
use CustomTables\CTMiscHelper;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

$max_file_size = CTMiscHelper::file_upload_max_size();

?>

<div class="wrap">
    <h1 class="wp-heading-inline">Import Tables</h1>

<?php

$message = get_transient('plugin_error_message');
if ($message) {
	echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($message) . '</p></div>';
	// Once displayed, clear the transient
	delete_transient('plugin_error_message');
}

$success_message = get_transient('plugin_success_message');
if (!empty($success_message)) {
	echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($success_message) . '</p></div>';
	// Optionally, you can delete the transient after displaying it
	delete_transient('plugin_success_message');
}

?>

    <p class="install-help">This function allows for the importation of table structures from .txt files encoded in JSON
        format.</p>
    <form method="post" action="" id="esFileUploaderForm_Tables" enctype="multipart/form-data">
		<?php wp_nonce_field('import-table'); ?>

        <ul style="list-style: none;">
            <li><input type="checkbox" name="importfields" value="1" checked="checked"/> Import Table Fields</li>
            <li><input type="checkbox" name="importlayouts" value="1" checked="checked"/> Import Layouts</li>
            <!--<li><input type="checkbox" name="importmenu" value="1" checked="checked" /> Import Menu</li>-->
        </ul>
        <p>
			<?php
			echo esc_html__('Maximum allowed file size', 'customtables');
			echo ': ';
			echo esc_html(CTMiscHelper::formatSizeUnits($max_file_size));
			?>
        </p>

        <input type="file" name="filetosubmit" accept=".txt"
               onchange="document.getElementById('upload-file').disabled=false"/>
        <input type="submit" id="upload-file" name="upload_file" class="button" value="Upload File"
               disabled=""/>
        <input type="hidden" name="action" value="import"/>
    </form>
    <p><a href="https://ct4.us/contact-us/" target="_blank"><?php echo esc_html__('Contact us for any support inquiries.', 'customtables'); ?></a></p>
</div>

