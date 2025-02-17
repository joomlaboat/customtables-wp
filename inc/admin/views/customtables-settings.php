<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

?>

<div class="wrap ct_doc">
	<?php if (isset($errors) && is_wp_error($errors)) : ?>
		<div class="error">
			<ul>
				<?php
				foreach ($errors->get_error_messages() as $err) {
					echo "<li>" . esc_html($err) . "</li>";
				}
				?>
			</ul>
		</div>
	<?php
	endif;

	if (!empty($messages)) {
		foreach ($messages as $msg) {
			echo '<div id="message" class="updated notice is-dismissible"><p>' . esc_html($msg) . '</p></div>';
		}
	}
	?>

	<?php if (isset($add_user_errors) && is_wp_error($add_user_errors)) : ?>
		<div class="error">
			<?php
			foreach ($add_user_errors->get_error_messages() as $message) {
				echo "<p>" . esc_html($message) . "</p>";
			}
			?>
		</div>
	<?php endif; ?>
	<div id="ajax-response"></div>


	<?php if (current_user_can('install_plugins')): ?>

		<form method="post" name="settings" id="settings" class="validate" novalidate="novalidate">
			<input name="action" type="hidden" value="save-settings"/>
			<?php wp_nonce_field('settings'); ?>


			<h2><?php esc_html_e('Custom Tables - Settings', 'customtables'); ?></h2>

			<h2 class="nav-tab-wrapper wp-clearfix">
				<button type="button" data-toggle="tab" data-tabs=".gtabs.settings" data-tab=".tab-ui"
						class="nav-tab nav-tab-active">
					User Interface
				</button>
				<button type="button" data-toggle="tab" data-tabs=".gtabs.settings" data-tab=".tab-api" class="nav-tab">
					API
				</button>
				<button type="button" data-toggle="tab" data-tabs=".gtabs.settings" data-tab=".tab-advanced"
						class="nav-tab">
					Advanced
				</button>
			</h2>

			<div class="gtabs settings">
				<div class="gtab active tab-ui">
					<?php include_once('customtables-settings-ui.php'); ?>
				</div>

				<div class="gtab tab-api">
					<?php include('customtables-settings-api.php'); ?>
				</div>

				<div class="gtab tab-advanced">
					<?php include('customtables-settings-advanced.php'); ?>
				</div>
			</div>

			<!-- Submit Button -->
			<?php
			$buttonText = esc_html__('Save Settings', 'customtables');
			submit_button($buttonText, 'primary', 'savesettings');
			?>

			<p><a href="https://ct4.us/contact-us/"
				  target="_blank"><?php echo esc_html__('Facing issues? Contact our support team.', 'customtables'); ?></a>
			</p>
		</form>

	<?php endif; ?>

</div>