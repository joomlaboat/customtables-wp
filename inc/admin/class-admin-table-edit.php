<?php

namespace CustomTablesWP\Inc\Admin;

use CustomTables\common;
use CustomTables\CT;
use CustomTables\ListOfTables;
use Exception;
use WP_Error;

class Admin_Table_Edit
{
	/**
	 * The text domain of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_text_domain The text domain of this plugin.
	 */
	public $plugin_text_domain;
	public CT $ct;
	public $helperListOfTables;
	public ?int $tableId;
	public WP_Error $errors;

	/**
	 *
	 *
	 * @param string $plugin_text_domain Text domain of the plugin.
	 *
	 * @since 1.0.0
	 */
	public function __construct($plugin_text_domain)
	{
		require_once(CUSTOMTABLES_LIBRARIES_PATH . DIRECTORY_SEPARATOR . 'customtables' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'admin-listoftables.php');
		$this->ct = new CT;
		$this->helperListOfTables = new \CustomTables\ListOfTables($this->ct);
		$this->plugin_text_domain = $plugin_text_domain;

		$this->tableId = common::inputGetInt('table');

		if ($this->tableId === 0)
			$this->tableId = null;

		if ($this->tableId !== null)
			$this->ct->getTable($this->tableId);
	}

	/**
	 * @throws Exception
	 * @1.1.1
	 */
	function handle_table_actions()
	{
		$action = common::inputPostCmd('action', '', 'create-edit-table');

		if ('createtable' === $action || 'savetable' === $action) {

			$errors = $this->helperListOfTables->save($this->tableId);

			if ($errors !== null and count($errors) > 0) {

				$this->errors = new WP_Error();

				foreach ($errors as $error)
					$this->errors->add('error_code', $error);

				return;
			}

			$url = 'admin.php?page=customtables-tables';

			ob_start(); // Start output buffering
			ob_end_clean(); // Discard the output buffer
			wp_redirect(admin_url($url));
			exit;
		}
	}
}