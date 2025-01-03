<?php
/**
 * Plugin Name:       CustomTables
 * Plugin URI:        https://ct4.us/
 * GitHub:            https://github.com/joomlaboat/customtables-wp
 * Author:            Ivan Komlev
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

namespace CustomTablesWP\Inc\Admin;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use CustomTables\common;
use CustomTables\CT;
use CustomTables\ListOfTables;
use Exception;
use WP_Error;

class Admin_Table_Edit
{
	public CT $ct;
	public ListOfTables $helperListOfTables;
	public ?int $tableId;
	public WP_Error $errors;

	/**
	 * @throws Exception
	 * @since 1.0.0
	 */
	public function __construct()
	{
		require_once(CUSTOMTABLES_LIBRARIES_PATH . DIRECTORY_SEPARATOR . 'customtables' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'admin-listoftables.php');
		$this->ct = new CT;
		$this->helperListOfTables = new ListOfTables($this->ct);
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
	function handle_table_actions(): void
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