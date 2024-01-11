<?php

namespace CustomTablesWP\Inc\Admin;

use CustomTables\common;
use CustomTables\CT;
use CustomTables\database;
use CustomTables\Layouts;
use CustomTables\ListOfLayouts;
use CustomTables\MySQLWhereClause;
use CustomTablesWP\Inc\Libraries;

/**
 * Class for displaying registered WordPress Users
 * in a WordPress-like Admin Layout with row actions to
 * perform user meta operations
 *
 *
 * @link       http://nuancedesignstudio.in
 * @since      1.0.0
 *
 * @author     Karan NA Gupta
 */
class Admin_Layout_List extends Libraries\WP_List_Table
{
	/**
	 * @since    1.0.0
	 * @access   private
	 */
	public CT $ct;
	public ListOfLayouts $helperListOfLayouts;

	protected int $count_all;
	protected int $count_trashed;
	protected int $count_published;
	protected int $count_unpublished;
	protected ?string $current_status;

	/**
	 * @since 1.0.0
	 */
	public function __construct()
	{
		require_once(CUSTOMTABLES_LIBRARIES_PATH . DIRECTORY_SEPARATOR . 'customtables' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'admin-listoflayouts.php');
		$this->ct = new CT;
		$this->helperListOfLayouts = new ListOfLayouts($this->ct);

		$whereClause = new MySQLWhereClause();
		$whereClause->addCondition('published', -2,'!=');
		$this->count_all = database::loadColumn('#__customtables_layouts',['COUNT(id) AS c'], $whereClause)[0] ?? 0;
		//$this->count_all = database::loadColumn('SELECT COUNT(id) FROM #__customtables_layouts WHERE published!=-2')[0] ?? 0;

		$whereClause = new MySQLWhereClause();
		$whereClause->addCondition('published', -2);
		$this->count_trashed = database::loadColumn('#__customtables_layouts',['COUNT(id) AS c'], $whereClause)[0] ?? 0;
		//$this->count_trashed = database::loadColumn('SELECT COUNT(id) FROM #__customtables_layouts WHERE published=-2')[0] ?? 0;

		$whereClause = new MySQLWhereClause();
		$whereClause->addCondition('published', 1);
		$this->count_published = database::loadColumn('#__customtables_layouts',['COUNT(id) AS c'], $whereClause)[0] ?? 0;
		//$this->count_published = database::loadColumn('SELECT COUNT(id) FROM #__customtables_layouts WHERE published=1')[0] ?? 0;

		$this->count_unpublished = $this->count_all - $this->count_published;

		$this->current_status = common::inputGetCmd('status');

		if ($this->current_status !== null and $this->current_status !== 'all') {
			if ($this->current_status == 'trash' and $this->count_trashed == 0)
				$this->current_status = null;
			if ($this->current_status == 'unpublished' and $this->count_unpublished == 0)
				$this->current_status = null;
			if ($this->current_status == 'published' and $this->count_published == 0)
				$this->current_status = null;
		}

		parent::__construct(array(
			'plural' => 'users',    // Plural value used for labels and the objects being listed.
			'singular' => 'user',        // Singular label for an object being listed, e.g. 'post'.
			'ajax' => false,        // If true, the parent class will call the _js_vars() method in the footer
		));
	}

	/**
	 * Prepares the list of items for displaying.
	 *
	 * Query, filter data, handle sorting, and pagination, and any other data-manipulation required prior to rendering
	 *
	 * @since   1.0.0
	 */
	function prepare_items()
	{
		$data = $this->get_data(); // Fetch your data here

		$columns = $this->get_columns();
		$hidden = array(); // Columns to hide (optional)
		//$sortable = array(); // Columns to make sortable (optional)
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array($columns, $hidden, $sortable);

		// Paginate the data
		$per_page = 10; // Number of items per page
		$current_page = $this->get_pagenum(); // Get the current page
		$total_items = count($data); // Total number of items

		$this->set_pagination_args(array(
			'total_items' => $total_items,
			'per_page' => $per_page,
			'total_pages' => ceil($total_items / $per_page)
		));

		// Slice the data to display the correct items for the current page
		$this->items = array_slice($data, (($current_page - 1) * $per_page), $per_page);
	}

	function get_data(): array
	{
		// Fetch and return your data here
		$search = common::inputGetString('s');
		$orderby = common::inputGetCmd('orderby');
		$order = common::inputGetCmd('order');

		$published = match ($this->current_status) {
			'published' => 1,
			'unpublished' => 0,
			'trash' => -2,
			default => null
		};

		$data = [];

		try {
			$data = $this->helperListOfLayouts->getListQuery($published, $search, null, null, $orderby, $order);
		}catch(\Exception $exception)
		{

		}

		$newData = [];

		$Layouts = new Layouts($this->ct);
		$translations = $Layouts->layoutTypeTranslation();

		foreach ($data as $item) {

			if ($item['published'] == -2)
				$label = '<span>' . $item['layoutname'] . '</span>';
			else
				$label = '<a class="row-title" href="?page=customtables-layouts-edit&action=edit&layout=' . $item['id'] . '">' . $item['layoutname'] . '</a>'
					. (($this->current_status != 'unpublished' and $item['published'] == 0) ? ' — <span class="post-state">Draft</span>' : '');

			$item['layoutname'] = '<strong>' . $label . '</strong>';

			if (isset($translations[$item['layouttype']])) {
				$item['layouttype'] = $translations[$item['layouttype']];
			} else {
				$item['layouttype'] = '<span style="color:red;">NOT SELECTED</span>';
			}

			$newData[] = $item;
		}
		return $newData;
	}

	/**
	 * Get a list of columns. The format is:
	 * 'internal-name' => 'Title'
	 *
	 * @return array
	 * @since 1.0.0
	 *
	 */
	function get_columns()
	{
		return array(
			'cb' => '<input type="checkbox" />',
			'layoutname' => __('Layout Name', 'customtables'),
			'layouttype' => __('Type', 'customtables'),
			'tabletitle' => __('Table', 'customtables'),
			'layout_size' => __('Size', 'customtables'),
			'modifiedby' => __('Modified By', 'customtables'),
			'modified' => __('Modified When', 'customtables'),
			'id' => __('Id', 'customtables')
		);
	}

	/**
	 * Get a list of sortable columns. The format is:
	 * 'internal-name' => 'orderby'
	 * or
	 * 'internal-name' => array( 'orderby', true )
	 *
	 * The second format will make the initial sorting order be descending
	 *
	 * @return array
	 * @since 1.1.0
	 *
	 */
	protected function get_sortable_columns()
	{
		return array(
			'layoutname' => array('layoutname', false),
			'id' => array('id', true)
		);
	}

	function column_layoutname($item): string
	{
		$actions = [];

		$url = 'admin.php?page=customtables-layouts';
		if ($this->current_status != null)
			$url .= '&status=' . $this->current_status;

		if ($this->current_status == 'trash') {
			$actions['restore'] = sprintf('<a href="' . $url . '&action=restore&layout=%s&_wpnonce=%s">' . __('Restore', 'customtables') . '</a>',
				$item['id'], urlencode(wp_create_nonce('restore_nonce')));

			$actions['delete'] = sprintf('<a href="' . $url . '&action=delete&layout=%s&_wpnonce=%s">' . __('Delete Permanently', 'customtables') . '</a>',
				$item['id'], urlencode(wp_create_nonce('delete_nonce')));
		} else {
			$actions['edit'] = sprintf('<a href="?page=customtables-layouts-edit&action=edit&layout=%s">' . __('Edit', 'customtables') . '</a>', $item['id']);
			$actions['trash'] = sprintf('<a href="' . $url . '&action=trash&layout=%s&_wpnonce=%s">' . __('Trash', 'customtables') . '</a>',
				$item['id'], urlencode(wp_create_nonce('trash_nonce')));
		}
		return sprintf('%1$s %2$s', $item['layoutname'], $this->row_actions($actions));
	}

	/**
	 * Text displayed when no layouts found
	 *
	 * @return void
	 * @since   1.0.0
	 *
	 */
	public function no_items(): void
	{
		_e('No layouts found.', 'customtables');
	}

	/**
	 * Render a column when no column specific method exists.
	 *
	 * @param array $item
	 * @param string $column_name
	 *
	 * @return mixed
	 */
	function column_default($item, $column_name)
	{
		return match ($column_name) {
			'layoutname', 'layouttype', 'tabletitle', 'layout_size', 'modifiedby', 'modified', 'id' => $item[$column_name],
			default => print_r($item, true),
		};
	}

	/**
	 * Get value for checkbox column.
	 *
	 * The special 'cb' column
	 *
	 * @param object $item A row's data
	 * @return string Text to be placed inside the column <td>.
	 */
	function column_cb($item): string
	{
		return sprintf(
			'<input type="checkbox" name="layout[]" value="%s" />',
			$item['id']
		);
	}

	public function views()
	{
		$views = $this->get_views();

		if (!empty($views)) {
			echo '<ul class="subsubsub">';
			foreach ($views as $view) {
				echo '<li>' . $view . '</li>';
			}
			echo '</ul>';
		}
	}

	public function get_views(): array
	{
		$link = 'admin.php?page=customtables-layouts';
		$views = [];

		$views['all'] = '<a href="' . admin_url($link) . '" class="' . (($this->current_status === 'all' or $this->current_status === null) ? 'current' : '') . '">'
			. __('All') . ' <span class="count">(' . $this->count_all . ')</span></a>';

		if ($this->count_published > 0)
			$views['published'] = '<a href="' . admin_url($link . '&status=published') . '" class="' . ($this->current_status === 'published' ? 'current' : '') . '">'
				. __('Published') . ' <span class="count">(' . $this->count_published . ')</span></a>';

		if ($this->count_unpublished > 0)
			$views['unpublished'] = '<a href="' . admin_url($link . '&status=unpublished') . '" class="' . ($this->current_status === 'unpublished' ? 'current' : '') . '">'
				. __('Draft') . ' <span class="count">(' . $this->count_unpublished . ')</span></a>';

		if ($this->count_trashed > 0)
			$views['trash'] = '<a href="' . admin_url($link . '&status=trash') . '" class="' . ($this->current_status === 'trash' ? 'current' : '') . '">' . __('Trash')
				. ' <span class="count">(' . $this->count_trashed . ')</span></a>';

		return $views;
	}

	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @return array
	 * @since    1.0.0
	 *
	 */
	public function get_bulk_actions(): array
	{
		/*
		 * on hitting apply in bulk actions the url params are set as
		 * ?action=action&layout=1
		 *
		 * action and action2 are set based on the triggers above or below the layout
		 *
		 */

		$actions = [];

		if ($this->current_status != 'trash')
			$actions['customtables-layouts-edit'] = __('Edit', 'customtables');

		if ($this->current_status == '' or $this->current_status == 'all') {
			$actions['customtables-layouts-publish'] = __('Publish', 'customtables');
			$actions['customtables-layouts-unpublish'] = __('Draft', 'customtables');
		} elseif ($this->current_status == 'unpublished')
			$actions['customtables-layouts-publish'] = __('Publish', 'customtables');
		elseif ($this->current_status == 'published')
			$actions['customtables-layouts-unpublish'] = __('Draft', 'customtables');

		if ($this->current_status != 'trash')
			$actions['customtables-layouts-trash'] = __('Move to Trash', 'customtables');

		if ($this->current_status == 'trash') {
			$actions['customtables-layouts-restore'] = __('Restore', 'customtables');
			$actions['customtables-layouts-delete'] = __('Delete Permanently', 'customtables');
		}
		return $actions;
	}

	/**
	 * Process actions triggered by the user
	 *
	 * @since    1.0.0
	 *
	 */
	function handle_layout_actions()
	{
		/*
		 * Note: Layout bulk_actions can be identified by checking $REQUEST['action'] and $REQUEST['action2']
		 *
		 * action - is set if checkbox from top-most select-all is set, otherwise returns -1
		 * action2 - is set if checkbox the bottom-most select-all checkbox is set, otherwise returns -1
		 */

		// check for individual row actions
		$filter_action = $_REQUEST['filter_action'] ?? null;
		$action = $_REQUEST['action'] ?? null;
		$action2 = $_REQUEST['action2'] ?? null;
		$the_layout_action = $this->current_action($filter_action,$action,$action2);

		if ('restore' === $the_layout_action) {
			$nonce = wp_unslash($_REQUEST['_wpnonce']);
			// verify the nonce.
			if (!wp_verify_nonce($nonce, 'restore_nonce')) {
				$this->invalid_nonce_redirect();
			} else {
				$layoutId = common::inputGetInt('layout');

				$whereClauseUpdate = new MySQLWhereClause();
				$whereClauseUpdate->addCondition('id', $layoutId);

				database::update('#__customtables_layouts', ['published' => 0], $whereClauseUpdate);
				//echo '<div id="message" class="updated notice is-dismissible"><p>1 layout restored from the Trash.</p></div>';
				$this->graceful_redirect();
			}
		}

		if ('trash' === $the_layout_action) {
			$nonce = wp_unslash($_REQUEST['_wpnonce']);
			// verify the nonce.
			if (!wp_verify_nonce($nonce, 'trash_nonce')) {
				$this->invalid_nonce_redirect();
			} else {
				$layoutId = common::inputGetInt('layout');

				$whereClauseUpdate = new MySQLWhereClause();
				$whereClauseUpdate->addCondition('id', $layoutId);

				database::update('#__customtables_layouts', ['published' => -2], $whereClauseUpdate);
				//echo '<div id="message" class="updated notice is-dismissible"><p>1 layout moved to the Trash.</p></div>';
				$this->graceful_redirect();
			}
		}

		if ('delete' === $the_layout_action) {
			$nonce = wp_unslash($_REQUEST['_wpnonce']);
			// verify the nonce.
			if (!wp_verify_nonce($nonce, 'delete_nonce')) {
				$this->invalid_nonce_redirect();
			} else {
				$layoutId = common::inputGetInt('layout');
				database::setQuery('DELETE FROM #__customtables_layouts WHERE id=' . $layoutId);
				//echo '<div id="message" class="updated notice is-dismissible"><p>1 layout permanently deleted.</p></div>';
				$this->graceful_redirect();
			}
		}

		// check for layout bulk actions

		if ($this->is_layout_action('customtables-layouts-edit'))
			$this->handle_layout_actions_edit();

		if ($this->is_layout_action('customtables-layouts-publish'))
			$this->handle_layout_actions_publish(1);

		if ($this->is_layout_action('customtables-layouts-unpublish') or $this->is_layout_action('customtables-layouts-restore'))
			$this->handle_layout_actions_publish(0);

		if ($this->is_layout_action('customtables-layouts-trash'))
			$this->handle_layout_actions_publish(-2);

		if ($this->is_layout_action('customtables-layouts-delete'))
			$this->handle_layout_actions_delete();
	}

	/**
	 * Die when the nonce check fails.
	 *
	 * @return void
	 * @since    1.0.0
	 *
	 */
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
	 * Stop execution, redirect and exit
	 *
	 * @param ?string $url
	 *
	 * @return void
	 * @since    1.0.0
	 *
	 */
	public function graceful_redirect(?string $url = null)
	{
		if ($url === null)
			$url = 'admin.php?page=customtables-layouts';

		if ($this->current_status != null)
			$url .= '&status=' . $this->current_status;

		ob_start(); // Start output buffering
		ob_end_clean(); // Discard the output buffer
		wp_redirect(admin_url($url));
		exit;
	}

	function is_layout_action($action): bool
	{
		$action1 = common::inputPostCmd('action','','bulk-' . $this->_args['plural']);
		$action2 = common::inputPostCmd('action2','','bulk-' . $this->_args['plural']);
		if ($action1 === $action || $action2 === $action)
			return true;

		return false;
	}

	function handle_layout_actions_edit()
	{
		$nonce = wp_unslash($_REQUEST['_wpnonce']);
		if (!wp_verify_nonce($nonce, 'bulk-' . $this->_args['plural'])) {
			$this->invalid_nonce_redirect();
		} else {
			$layout_id = (int)(isset($_POST['layout']) ? $_POST['layout'][0] : '');
			// Redirect to the edit page with the appropriate parameters
			$this->graceful_redirect('admin.php?page=customtables-layouts-edit&action=edit&layout=' . $layout_id);
		}
	}

	function handle_layout_actions_publish(int $state): void
	{
		$nonce = wp_unslash($_REQUEST['_wpnonce']);
		// verify the nonce.
		if (!wp_verify_nonce($nonce, 'bulk-' . $this->_args['plural'])) {
			$this->invalid_nonce_redirect();
		} else {
			$layouts = ($_POST['layout'] ?? []);

			foreach ($layouts as $layout) {

				$whereClauseUpdate = new MySQLWhereClause();
				$whereClauseUpdate->addCondition('id', (int)$layout);

				database::update('#__customtables_layouts', ['published' => $state], $whereClauseUpdate);
			}

			if (count($layouts) > 0)
				$this->graceful_redirect();

			echo '<div id="message" class="updated error is-dismissible"><p>Layouts not selected.</p></div>';
		}
	}

	function handle_layout_actions_delete()
	{
		$nonce = wp_unslash($_REQUEST['_wpnonce']);
		// verify the nonce.
		if (!wp_verify_nonce($nonce, 'bulk-' . $this->_args['plural'])) {
			$this->invalid_nonce_redirect();
		} else {
			$layouts = ($_POST['layout'] ?? []);
			if (count($layouts) > 0) {
				foreach ($layouts as $layoutId)
					database::setQuery('DELETE FROM #__customtables_layouts WHERE id=' . $layoutId);

				$this->graceful_redirect();
			}
			echo '<div id="message" class="updated error is-dismissible"><p>Layouts not selected.</p></div>';
		}
	}
}
