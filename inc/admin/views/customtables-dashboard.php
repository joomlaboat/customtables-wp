<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use CustomTables\common;

?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e('Custom Tables - Dashboard', 'customtables'); ?></h1>

    <hr style="margin-bottom: 30px; "/>

    <a href="admin.php?page=customtables-tables" style="margin-right:30px;" class="button">
        <img alt="<?php echo esc_html__('Tables', 'customtables'); ?>"
             src="<?php echo esc_html(home_url()); ?>/wp-content/plugins/customtables/libraries/customtables/media/images/controlpanel/icons/listoftables.png"/>
        <p style="text-align: center;"><?php echo esc_html__('Tables', 'customtables'); ?></p>
    </a>

    <a href="admin.php?page=customtables-layouts" style="margin-right:30px;" class="button">
        <img alt="<?php echo esc_html__('Layouts', 'customtables'); ?>"
             src="<?php echo esc_html(home_url()) ?>/wp-content/plugins/customtables/libraries/customtables/media/images/controlpanel/icons/listoflayouts.png">
        <p style="text-align: center;"><?php echo esc_html__('Layouts', 'customtables'); ?></p>
    </a>

    <a href="admin.php?page=customtables-import-tables" style="margin-right:30px;" class="button">
        <img alt="<?php echo esc_html__('Import Tables', 'customtables'); ?>"
             src="<?php echo esc_html(home_url()); ?>/wp-content/plugins/customtables/libraries/customtables/media/images/controlpanel/icons/importtables.png">
        <p style="text-align: center;"><?php echo esc_html__('Import Tables', 'customtables'); ?></p>
    </a>

    <a href="admin.php?page=customtables-documentation" class="button">
        <img alt="<?php echo esc_html__('Documentation', 'customtables'); ?>"
             src="<?php echo esc_html(home_url()); ?>/wp-content/plugins/customtables/libraries/customtables/media/images/controlpanel/icons/documentation.png">
        <p style="text-align: center;"><?php echo esc_html__('Documentation', 'customtables'); ?></p>
    </a>

    <p><a href="https://ct4.us/contact-us/" target="_blank"><?php echo esc_html__('Need support? We’re just a message away.', 'customtables'); ?></a></p>
</div>
