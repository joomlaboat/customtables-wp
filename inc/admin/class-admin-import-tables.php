<?php

namespace CustomTablesWP\Inc\Admin;

use CustomTables\common;
use CustomTables\CT;
use CustomTables\ImportTables;
use Exception;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Admin_Import_Tables
{
	public function invalid_nonce_redirect()
	{
		$page = common::inputGetCmd('page');
		wp_die(__('Invalid Nonce', 'customtables'),
			__('Error', 'customtables'),
			array(
				'response' => 403,
				'back_link' => esc_url(add_query_arg(array('page' => wp_unslash($page)), admin_url('users.php'))),
			)
		);
	}

	/**
	 * @throws Exception
	 */
	function handle_import_actions()
	{
		// check for individual row actions
		$the_table_action = esc_html(wp_strip_all_tags($this->current_action()));
		if($the_table_action == 'import') {

			if (!isset($_REQUEST['_wpnonce']) or !wp_verify_nonce(sanitize_text_field($_REQUEST['_wpnonce']), 'import-table')) {
				$this->invalid_nonce_redirect();
				return;
			}

			if (isset($_POST['upload_file'])) {
				$uploaded_file = $_FILES['filetosubmit'];

				//To allow txt file
				define('ALLOW_UNFILTERED_UPLOADS', true);

				// Define allowed MIME types
				$allowed_mime_types = array(
					'text/plain' // Allow only .txt files
				);

				// Set up the upload overrides
				$upload_overrides = array(
					'test_form' => false,
					'mimes'     => $allowed_mime_types
				);

				// Sanitize the uploaded file using wp_handle_upload()
				$move_file = wp_handle_upload($uploaded_file, $upload_overrides);

				if ($move_file && !isset($move_file['error'])) {
					$importFields = common::inputPostInt('importfields', 0,'import-table');
					$importLayouts = common::inputPostInt('importlayouts', 0,'import-table');
					$importMenu = false;//common::inputPostInt('importmenu', 0,'import-table');

					$msg = '';

					$ok = ImportTables::processFile($move_file['file'],'', $msg, '', $importFields, $importLayouts, $importMenu);

					if($ok)
						set_transient('customtables_success_message', 'Custom Tables backup file has been processes successfully.', 60);
					else
						set_transient('customtables_error_message', 'Error processing file: ' . esc_html($msg), 60);
				} else {
					// Store message for 60 seconds
					if(isset($move_file['error']))
						set_transient('customtables_error_message', 'Error uploading file: ' . esc_html($move_file['error']), 60);
					else
						set_transient('customtables_error_message', 'Unknown error while uploading file', 60);
				}
			}
		}
	}

	public function current_action(): string
	{
		if (isset($_POST['action']))
			return esc_html(wp_strip_all_tags($_POST['action']));

		return '';
	}
}