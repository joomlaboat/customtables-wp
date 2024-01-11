<?php

/*
 * Copied from the WordPress core 
 * under /wp-admin/includes/
 */

namespace CustomTablesWP\Inc\Libraries;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use CustomTables\common;

/**
 * Administration API: WP_List_Table class
 *
 * @package WordPress
 * @subpackage List_Table
 * @since 3.1.0
 */

/**
 * Base class for displaying a list of items in an ajaxified HTML table.
 *
 * @since 3.1.0
 * @access private
 */
class WP_List_Table
{

	/**
	 * The current list of items.
	 *
	 * @since 3.1.0
	 * @access public
	 * @var array
	 */
	public $items;

	/**
	 * Various information about the current table.
	 *
	 * @since 3.1.0
	 * @access protected
	 * @var array
	 */
	protected $_args;

	/**
	 * Various information needed for displaying the pagination.
	 *
	 * @since 3.1.0
	 * @access protected
	 * @var array
	 */
	protected $_pagination_args = array();

	/**
	 * The current screen.
	 *
	 * @since 3.1.0
	 * @access protected
	 * @var object
	 */
	protected $screen;
	/**
	 * The view switcher modes.
	 *
	 * @since 4.1.0
	 * @access protected
	 * @var array
	 */
	protected $modes = array();
	/**
	 * Stores the value returned by ->get_column_info().
	 *
	 * @since 4.1.0
	 * @access protected
	 * @var array
	 */
	protected $_column_headers;

	protected $compat_fields = array('_args', '_pagination_args', 'screen', '_actions', '_pagination');
	protected $compat_methods = array('set_pagination_args', 'get_views', 'get_bulk_actions', 'bulk_actions',
		'row_actions', 'months_dropdown', 'view_switcher', 'comments_bubble', 'get_items_per_page', 'pagination',
		'get_sortable_columns', 'get_column_info', 'get_table_classes', 'display_tablenav', 'extra_tablenav',
		'single_row_columns');
	/**
	 * Cached bulk actions.
	 *
	 * @since 3.1.0
	 * @access private
	 * @var array
	 */
	private $_actions;
	/**
	 * Cached pagination output.
	 *
	 * @since 3.1.0
	 * @access private
	 * @var string
	 */
	private $_pagination;

	/**
	 * Constructor.
	 *
	 * The child class should call this constructor from its own constructor to override
	 * the default $args.
	 *
	 * @param array|string $args {
	 *     Array or string of arguments.
	 *
	 * @type string $plural Plural value used for labels and the objects being listed.
	 *                            This affects things such as CSS class-names and nonces used
	 *                            in the list table, e.g. 'posts'. Default empty.
	 * @type string $singular Singular label for an object being listed, e.g. 'post'.
	 *                            Default empty
	 * @type bool $ajax Whether the list table supports Ajax. This includes loading
	 *                            and sorting data, for example. If true, the class will call
	 *                            the _js_vars() method in the footer to provide variables
	 *                            to any scripts handling Ajax events. Default false.
	 * @type string $screen String containing the hook name used to determine the current
	 *                            screen. If left null, the current screen will be automatically set.
	 *                            Default null.
	 * }
	 * @since 3.1.0
	 * @access public
	 *
	 */
	public function __construct($args = array())
	{
		$args = wp_parse_args($args, array(
			'plural' => '',
			'singular' => '',
			'ajax' => false,
			'screen' => null,
		));

		$this->screen = convert_to_screen($args['screen']);

		add_filter("manage_{$this->screen->id}_columns", array($this, 'get_columns'), 0);

		if (!$args['plural'])
			$args['plural'] = $this->screen->base;

		$args['plural'] = sanitize_key($args['plural']);
		$args['singular'] = sanitize_key($args['singular']);

		$this->_args = $args;

		if ($args['ajax']) {
			// wp_enqueue_script( 'list-table' );
			add_action('admin_footer', array($this, '_js_vars'));
		}

		if (empty($this->modes)) {
			$this->modes = array(
				'list' => __('List View'),
				'excerpt' => __('Excerpt View')
			);
		}
	}

	/**
	 * Make private properties readable for backward compatibility.
	 *
	 * @param string $name Property to get.
	 * @return mixed Property.
	 * @since 4.0.0
	 * @access public
	 *
	 */
	public function __get($name)
	{
		if (in_array($name, $this->compat_fields)) {
			return $this->$name;
		}
	}

	/**
	 * Make private properties settable for backward compatibility.
	 *
	 * @param string $name Property to check if set.
	 * @param mixed $value Property value.
	 * @return mixed Newly-set property.
	 * @since 4.0.0
	 * @access public
	 *
	 */
	public function __set($name, $value)
	{
		if (in_array($name, $this->compat_fields)) {
			return $this->$name = $value;
		}
	}

	/**
	 * Make private properties checkable for backward compatibility.
	 *
	 * @param string $name Property to check if set.
	 * @return bool Whether the property is set.
	 * @since 4.0.0
	 * @access public
	 *
	 */
	public function __isset($name)
	{
		if (in_array($name, $this->compat_fields)) {
			return isset($this->$name);
		}
	}

	/**
	 * Make private properties un-settable for backward compatibility.
	 *
	 * @param string $name Property to unset.
	 * @since 4.0.0
	 * @access public
	 *
	 */
	public function __unset($name)
	{
		if (in_array($name, $this->compat_fields)) {
			unset($this->$name);
		}
	}

	/**
	 * Make private/protected methods readable for backward compatibility.
	 *
	 * @param callable $name Method to call.
	 * @param array $arguments Arguments to pass when calling.
	 * @return mixed|bool Return value of the callback, false otherwise.
	 * @since 4.0.0
	 * @access public
	 *
	 */
	public function __call($name, $arguments)
	{
		if (in_array($name, $this->compat_methods)) {
			return call_user_func_array(array($this, $name), $arguments);
		}
		return false;
	}

	/**
	 * Checks the current user's permissions
	 *
	 * @since 3.1.0
	 * @access public
	 * @abstract
	 */
	public function ajax_user_can()
	{
		die('function WP_List_Table::ajax_user_can() must be over-ridden in a sub-class.');
	}

	/**
	 * Access the pagination args.
	 *
	 * @param string $key Pagination argument to retrieve. Common values include 'total_items',
	 *                    'total_pages', 'per_page', or 'infinite_scroll'.
	 * @return int Number of items that correspond to the given pagination argument.
	 * @since 3.1.0
	 * @access public
	 *
	 */
	public function get_pagination_arg($key)
	{
		if ('page' === $key) {
			return $this->get_pagenum();
		}

		if (isset($this->_pagination_args[$key])) {
			return $this->_pagination_args[$key];
		}
	}

	/**
	 * Get the current page number
	 *
	 * @return int
	 * @since 3.1.0
	 * @access public
	 *
	 */
	public function get_pagenum()
	{
		$pagenum = absint(common::inputGetInt('paged', 0));

		if (isset($this->_pagination_args['total_pages']) && $pagenum > $this->_pagination_args['total_pages'])
			$pagenum = $this->_pagination_args['total_pages'];

		return max(1, $pagenum);
	}

	/**
	 * Displays the search box.
	 *
	 * @param string $text The 'submit' button label.
	 * @param string $input_id ID attribute value for the search input field.
	 * @since 3.1.0
	 * @access public
	 *
	 */
	public function search_box($text, $input_id)
	{

		$s = common::inputGetCmd('s');

		if ($s === null && !$this->has_items())
			return;

		$input_id = $input_id . '-search-input';

		$orderby = common::inputGetCmd('orderby');
		$order = common::inputGetCmd('order');

		if (!empty($orderby))
			echo '<input type="hidden" name="orderby" value="' . esc_attr($orderby) . '" />';
		if (!empty($order))
			echo '<input type="hidden" name="order" value="' . esc_attr($order) . '" />';
		/*
		if ( ! empty( $REQUEST['post_mime_type'] ) )
			echo '<input type="hidden" name="post_mime_type" value="' . esc_attr( $REQUEST['post_mime_type'] ) . '" />';
		if ( ! empty( $REQUEST['detached'] ) )
			echo '<input type="hidden" name="detached" value="' . esc_attr( $REQUEST['detached'] ) . '" />';
		*/
		?>
        <p class="search-box">
            <label class="screen-reader-text" for="<?php echo esc_attr($input_id); ?>"><?php echo $text; ?>:</label>
            <input type="search" id="<?php echo esc_attr($input_id); ?>" name="s"
                   value="<?php _admin_search_query(); ?>"/>
			<?php submit_button($text, '', '', false, array('id' => 'search-submit')); ?>
        </p>
		<?php
	}

	/**
	 * Whether the table has items to display or not
	 *
	 * @return bool
	 * @since 3.1.0
	 * @access public
	 *
	 */
	public function has_items()
	{
		return !empty($this->items);
	}

	/**
	 * Display the list of views available on this table.
	 *
	 * @since 3.1.0
	 * @access public
	 */
	public function views()
	{
		$views = $this->get_views();
		/**
		 * Filters the list of available list table views.
		 *
		 * The dynamic portion of the hook name, `$this->screen->id`, refers
		 * to the ID of the current screen, usually a string.
		 *
		 * @param array $views An array of available list table views.
		 * @since 3.5.0
		 *
		 */
		$views = apply_filters("views_{$this->screen->id}", $views);

		if (empty($views))
			return;

		$this->screen->render_screen_reader_content('heading_views');

		echo "<ul class='subsubsub'>\n";
		foreach ($views as $class => $view) {
			$views[$class] = "\t<li class='$class'>$view";
		}
		echo implode(" |</li>\n", $views) . "</li>\n";
		echo "</ul>";
	}

	/**
	 * Get an associative array ( id => link ) with the list
	 * of views available on this table.
	 *
	 * @return array
	 * @since 3.1.0
	 * @access protected
	 *
	 */
	protected function get_views()
	{
		return array();
	}

	/**
	 * Get the current action selected from the bulk actions dropdown.
	 *
	 * @return string|false The action name or False if no action was selected
	 * @since 3.1.0
	 * @access public
	 *
	 */
	public function current_action(?string $filter_action, ?string $action, ?string $action2): ?string
	{
		if ($filter_action != null)
			return null;

		if ($action !== null)
			return $action;

		if ($action2 != null)
			return $action2;

		return null;
	}

	/**
	 * Public wrapper for WP_List_Table::get_default_primary_column_name().
	 *
	 * @return string Name of the default primary column.
	 * @since 4.4.0
	 * @access public
	 *
	 */
	public function get_primary_column()
	{
		return $this->get_primary_column_name();
	}

	/**
	 * Gets the name of the primary column.
	 *
	 * @return string The name of the primary column.
	 * @since 4.3.0
	 * @access protected
	 *
	 */
	protected function get_primary_column_name()
	{
		$columns = get_column_headers($this->screen);
		$default = $this->get_default_primary_column_name();

		// If the primary column doesn't exist fall back to the
		// first non-checkbox column.
		if (!isset($columns[$default])) {
			$default = WP_List_Table::get_default_primary_column_name();
		}

		/**
		 * Filters the name of the primary column for the current list table.
		 *
		 * @param string $default Column name default for the specific list table, e.g. 'name'.
		 * @param string $context Screen ID for specific list table, e.g. 'plugins'.
		 * @since 4.3.0
		 *
		 */
		$column = apply_filters('list_table_primary_column', $default, $this->screen->id);

		if (empty($column) || !isset($columns[$column])) {
			$column = $default;
		}

		return $column;
	}

	/**
	 * Gets the name of the default primary column.
	 *
	 * @return string Name of the default primary column, in this case, an empty string.
	 * @since 4.3.0
	 * @access protected
	 *
	 */
	protected function get_default_primary_column_name()
	{
		$columns = $this->get_columns();
		$column = '';

		if (empty($columns)) {
			return $column;
		}

		// We need a primary defined so responsive views show something,
		// so let's fall back to the first non-checkbox column.
		foreach ($columns as $col => $column_name) {
			if ('cb' === $col) {
				continue;
			}

			$column = $col;
			break;
		}

		return $column;
	}

	/**
	 * Get a list of columns. The format is:
	 * 'internal-name' => 'Title'
	 *
	 * @return array
	 * @since 3.1.0
	 * @access public
	 * @abstract
	 *
	 */
	public function get_columns()
	{
		die('function WP_List_Table::get_columns() must be over-ridden in a sub-class.');
	}

	/**
	 * Display the table
	 *
	 * @since 3.1.0
	 * @access public
	 */
	public function display()
	{
		$singular = $this->_args['singular'];

		$this->display_tablenav('top');

		$this->screen->render_screen_reader_content('heading_list');
		?>
        <table class="wp-list-table <?php echo implode(' ', $this->get_table_classes()); ?>">
            <thead>
            <tr>
				<?php $this->print_column_headers(); ?>
            </tr>
            </thead>

            <tbody id="the-list"<?php
			if ($singular) {
				echo " data-wp-lists='list:$singular'";
			} ?>>
			<?php $this->display_rows_or_placeholder(); ?>
            </tbody>

            <tfoot>
            <tr>
				<?php $this->print_column_headers(false); ?>
            </tr>
            </tfoot>

        </table>
		<?php
		$this->display_tablenav('bottom');
	}

	/**
	 * Generate the table navigation above or below the table
	 *
	 * @param string $which
	 * @since 3.1.0
	 * @access protected
	 */
	protected function display_tablenav($which)
	{
		if ('top' === $which) {
			wp_nonce_field('bulk-' . $this->_args['plural']);
		}
		?>
        <div class="tablenav <?php echo esc_attr($which); ?>">

			<?php if ($this->has_items()): ?>
                <div class="alignleft actions bulkactions">
					<?php $this->bulk_actions($which); ?>
                </div>
			<?php endif;
			$this->extra_tablenav($which);
			$this->pagination($which);
			?>

            <br class="clear"/>
        </div>
		<?php
	}

	/**
	 * Display the bulk actions dropdown.
	 *
	 * @param string $which The location of the bulk actions: 'top' or 'bottom'.
	 *                      This is designated as optional for backward compatibility.
	 * @since 3.1.0
	 * @access protected
	 *
	 */
	protected function bulk_actions($which = '')
	{
		if (is_null($this->_actions)) {
			$this->_actions = $this->get_bulk_actions();
			/**
			 * Filters the list table Bulk Actions drop-down.
			 *
			 * The dynamic portion of the hook name, `$this->screen->id`, refers
			 * to the ID of the current screen, usually a string.
			 *
			 * This filter can currently only be used to remove bulk actions.
			 *
			 * @param array $actions An array of the available bulk actions.
			 * @since 3.5.0
			 *
			 */
			$this->_actions = apply_filters("bulk_actions-{$this->screen->id}", $this->_actions);
			$two = '';
		} else {
			$two = '2';
		}

		if (empty($this->_actions))
			return;

		echo '<label for="bulk-action-selector-' . esc_attr($which) . '" class="screen-reader-text">' . __('Select bulk action') . '</label>';
		echo '<select name="action' . $two . '" id="bulk-action-selector-' . esc_attr($which) . "\">\n";
		echo '<option value="-1">' . __('Bulk Actions') . "</option>\n";

		foreach ($this->_actions as $name => $title) {
			$class = 'edit' === $name ? ' class="hide-if-no-js"' : '';

			echo "\t" . '<option value="' . $name . '"' . $class . '>' . $title . "</option>\n";
		}

		echo "</select>\n";

		submit_button(__('Apply'), 'action', '', false, array('id' => "doaction$two"));
		echo "\n";
	}

	/**
	 * Get an associative array ( option_name => option_title ) with the list
	 * of bulk actions available on this table.
	 *
	 * @return array
	 * @since 3.1.0
	 * @access protected
	 *
	 */
	protected function get_bulk_actions()
	{
		return array();
	}

	/**
	 * Extra controls to be displayed between bulk actions and pagination
	 *
	 * @param string $which
	 * @since 3.1.0
	 * @access protected
	 *
	 */
	protected function extra_tablenav($which)
	{
	}

	/**
	 * Display the pagination.
	 *
	 * @param string $which
	 * @since 3.1.0
	 * @access protected
	 *
	 */
	protected function pagination($which)
	{
		if (empty($this->_pagination_args)) {
			return;
		}

		$total_items = $this->_pagination_args['total_items'];
		$total_pages = $this->_pagination_args['total_pages'];
		$infinite_scroll = false;
		if (isset($this->_pagination_args['infinite_scroll'])) {
			$infinite_scroll = $this->_pagination_args['infinite_scroll'];
		}

		if ('top' === $which && $total_pages > 1) {
			$this->screen->render_screen_reader_content('heading_pagination');
		}

		$output = '<span class="displaying-num">' . sprintf(_n('%s item', '%s items', $total_items), number_format_i18n($total_items)) . '</span>';

		$current = $this->get_pagenum();
		$removable_query_args = wp_removable_query_args();

		$current_url = common::curPageURL();//set_url_scheme('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);

		$current_url = remove_query_arg($removable_query_args, $current_url);

		$page_links = array();

		$total_pages_before = '<span class="paging-input">';
		$total_pages_after = '</span></span>';

		$disable_first = $disable_last = $disable_prev = $disable_next = false;

		if ($current == 1) {
			$disable_first = true;
			$disable_prev = true;
		}
		if ($current == 2) {
			$disable_first = true;
		}
		if ($current == $total_pages) {
			$disable_last = true;
			$disable_next = true;
		}
		if ($current == $total_pages - 1) {
			$disable_last = true;
		}

		if ($disable_first) {
			$page_links[] = '<span class="tablenav-pages-navspan" aria-hidden="true">&laquo;</span>';
		} else {
			$page_links[] = sprintf("<a class='first-page' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
				esc_url(remove_query_arg('paged', $current_url)),
				__('First page'),
				'&laquo;'
			);
		}

		if ($disable_prev) {
			$page_links[] = '<span class="tablenav-pages-navspan" aria-hidden="true">&lsaquo;</span>';
		} else {
			$page_links[] = sprintf("<a class='prev-page' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
				esc_url(add_query_arg('paged', max(1, $current - 1), $current_url)),
				__('Previous page'),
				'&lsaquo;'
			);
		}

		if ('bottom' === $which) {
			$html_current_page = $current;
			$total_pages_before = '<span class="screen-reader-text">' . __('Current Page') . '</span><span id="table-paging" class="paging-input"><span class="tablenav-paging-text">';
		} else {
			$html_current_page = sprintf("%s<input class='current-page' id='current-page-selector' type='text' name='paged' value='%s' size='%d' aria-describedby='table-paging' /><span class='tablenav-paging-text'>",
				'<label for="current-page-selector" class="screen-reader-text">' . __('Current Page') . '</label>',
				$current,
				strlen($total_pages)
			);
		}
		$html_total_pages = sprintf("<span class='total-pages'>%s</span>", number_format_i18n($total_pages));
		$page_links[] = $total_pages_before . sprintf(_x('%1$s of %2$s', 'paging'), $html_current_page, $html_total_pages) . $total_pages_after;

		if ($disable_next) {
			$page_links[] = '<span class="tablenav-pages-navspan" aria-hidden="true">&rsaquo;</span>';
		} else {
			$page_links[] = sprintf("<a class='next-page' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
				esc_url(add_query_arg('paged', min($total_pages, $current + 1), $current_url)),
				__('Next page'),
				'&rsaquo;'
			);
		}

		if ($disable_last) {
			$page_links[] = '<span class="tablenav-pages-navspan" aria-hidden="true">&raquo;</span>';
		} else {
			$page_links[] = sprintf("<a class='last-page' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
				esc_url(add_query_arg('paged', $total_pages, $current_url)),
				__('Last page'),
				'&raquo;'
			);
		}

		$pagination_links_class = 'pagination-links';
		if (!empty($infinite_scroll)) {
			$pagination_links_class = ' hide-if-js';
		}
		$output .= "\n<span class='$pagination_links_class'>" . join("\n", $page_links) . '</span>';

		if ($total_pages) {
			$page_class = $total_pages < 2 ? ' one-page' : '';
		} else {
			$page_class = ' no-pages';
		}
		$this->_pagination = "<div class='tablenav-pages{$page_class}'>$output</div>";

		echo $this->_pagination;
	}

	/**
	 * Get a list of CSS classes for the WP_List_Table table tag.
	 *
	 * @return array List of CSS classes for the table tag.
	 * @since 3.1.0
	 * @access protected
	 *
	 */
	protected function get_table_classes()
	{
		return array('widefat', 'fixed', 'striped', $this->_args['plural']);
	}

	/**
	 * Print column headers, accounting for hidden and sortable columns.
	 *
	 * @param bool $with_id Whether to set the id attribute or not
	 * @since 3.1.0
	 * @access public
	 *
	 * @staticvar int $cb_counter
	 *
	 */
	public function print_column_headers($with_id = true)
	{
		list($columns, $hidden, $sortable, $primary) = $this->get_column_info();

		$current_url = set_url_scheme('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
		$current_url = remove_query_arg('paged', $current_url);

		$orderBy = common::inputGetCmd('orderby');
		if ($orderBy !== null) {
			$current_orderby = $orderBy;
		} else {
			$current_orderby = '';
		}

		$order = common::inputGetCmd('orderby');
		if ('desc' === $order) {
			$current_order = 'desc';
		} else {
			$current_order = 'asc';
		}

		if (!empty($columns['cb'])) {
			static $cb_counter = 1;
			$columns['cb'] = '<label class="screen-reader-text" for="cb-select-all-' . $cb_counter . '">' . __('Select All') . '</label>'
				. '<input id="cb-select-all-' . $cb_counter . '" type="checkbox" />';
			$cb_counter++;
		}

		foreach ($columns as $column_key => $column_display_name) {
			$class = array('manage-column', "column-$column_key");

			if (in_array($column_key, $hidden)) {
				$class[] = 'hidden';
			}

			if ('cb' === $column_key)
				$class[] = 'check-column';
            elseif (in_array($column_key, array('posts', 'comments', 'links')))
				$class[] = 'num';

			if ($column_key === $primary) {
				$class[] = 'column-primary';
			}

			if (isset($sortable[$column_key])) {
				list($orderby, $desc_first) = $sortable[$column_key];

				if ($current_orderby === $orderby) {
					$order = 'asc' === $current_order ? 'desc' : 'asc';
					$class[] = 'sorted';
					$class[] = $current_order;
				} else {
					$order = $desc_first ? 'desc' : 'asc';
					$class[] = 'sortable';
					$class[] = $desc_first ? 'asc' : 'desc';
				}

				$column_display_name = '<a href="' . esc_url(add_query_arg(compact('orderby', 'order'), $current_url)) . '"><span>' . $column_display_name . '</span><span class="sorting-indicator"></span></a>';
			}

			$tag = ('cb' === $column_key) ? 'td' : 'th';
			$scope = ('th' === $tag) ? 'scope="col"' : '';
			$id = $with_id ? "id='$column_key'" : '';

			if (!empty($class))
				$class = "class='" . join(' ', $class) . "'";

			echo "<$tag $scope $id $class>$column_display_name</$tag>";
		}
	}

	/**
	 * Get a list of all, hidden and sortable columns, with filter applied
	 *
	 * @return array
	 * @since 3.1.0
	 * @access protected
	 *
	 */
	protected function get_column_info()
	{
		// $_column_headers is already set / cached
		if (isset($this->_column_headers) && is_array($this->_column_headers)) {
			// Back-compat for list tables that have been manually setting $_column_headers for horse reasons.
			// In 4.3, we added a fourth argument for primary column.
			$column_headers = array(array(), array(), array(), $this->get_primary_column_name());
			foreach ($this->_column_headers as $key => $value) {
				$column_headers[$key] = $value;
			}

			return $column_headers;
		}

		$columns = get_column_headers($this->screen);
		$hidden = get_hidden_columns($this->screen);

		$sortable_columns = $this->get_sortable_columns();
		/**
		 * Filters the list table sortable columns for a specific screen.
		 *
		 * The dynamic portion of the hook name, `$this->screen->id`, refers
		 * to the ID of the current screen, usually a string.
		 *
		 * @param array $sortable_columns An array of sortable columns.
		 * @since 3.5.0
		 *
		 */
		$_sortable = apply_filters("manage_{$this->screen->id}_sortable_columns", $sortable_columns);

		$sortable = array();
		foreach ($_sortable as $id => $data) {
			if (empty($data))
				continue;

			$data = (array)$data;
			if (!isset($data[1]))
				$data[1] = false;

			$sortable[$id] = $data;
		}

		$primary = $this->get_primary_column_name();
		$this->_column_headers = array($columns, $hidden, $sortable, $primary);

		return $this->_column_headers;
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
	 * @since 3.1.0
	 * @access protected
	 *
	 */
	protected function get_sortable_columns()
	{
		return array();
	}

	/**
	 * Generate the tbody element for the list table.
	 *
	 * @since 3.1.0
	 * @access public
	 */
	public function display_rows_or_placeholder()
	{
		if ($this->has_items()) {
			$this->display_rows();
		} else {
			echo '<tr class="no-items"><td class="colspanchange" colspan="' . $this->get_column_count() . '">';
			$this->no_items();
			echo '</td></tr>';
		}
	}

	/**
	 * Generate the table rows
	 *
	 * @since 3.1.0
	 * @access public
	 */
	public function display_rows()
	{
		foreach ($this->items as $item)
			$this->single_row($item);
	}

	/**
	 * Generates content for a single row of the table
	 *
	 * @param object $item The current item
	 * @since 3.1.0
	 * @access public
	 *
	 */
	public function single_row($item)
	{
		echo '<tr>';
		$this->single_row_columns($item);
		echo '</tr>';
	}

	/**
	 * Generates the columns for a single row of the table
	 *
	 * @param object $item The current item
	 * @since 3.1.0
	 * @access protected
	 *
	 */
	protected function single_row_columns($item)
	{
		list($columns, $hidden, $sortable, $primary) = $this->get_column_info();

		foreach ($columns as $column_name => $column_display_name) {
			$classes = "$column_name column-$column_name";
			if ($primary === $column_name) {
				$classes .= ' has-row-actions column-primary';
			}

			if (in_array($column_name, $hidden)) {
				$classes .= ' hidden';
			}

			// Comments column uses HTML in the display name with screen reader text.
			// Instead of using esc_attr(), we strip tags to get closer to a user-friendly string.
			$data = 'data-colname="' . wp_strip_all_tags($column_display_name) . '"';

			$attributes = "class='$classes' $data";

			if ('cb' === $column_name) {
				echo '<th scope="row" class="check-column">';
				echo $this->column_cb($item);
				echo '</th>';
			} elseif (method_exists($this, '_column_' . $column_name)) {
				echo call_user_func(
					array($this, '_column_' . $column_name),
					$item,
					$classes,
					$data,
					$primary
				);
			} elseif (method_exists($this, 'column_' . $column_name)) {
				echo "<td $attributes>";
				echo call_user_func(array($this, 'column_' . $column_name), $item);
				echo $this->handle_row_actions($item, $column_name, $primary);
				echo "</td>";
			} else {
				echo "<td $attributes>";
				echo $this->column_default($item, $column_name);
				echo $this->handle_row_actions($item, $column_name, $primary);
				echo "</td>";
			}
		}
	}

	/**
	 *
	 * @param object $item
	 */
	protected function column_cb($item)
	{
	}

	/**
	 * Generates and display row actions links for the list table.
	 *
	 * @param object $item The item being acted upon.
	 * @param string $column_name Current column name.
	 * @param string $primary Primary column name.
	 * @return string The row actions HTML, or an empty string if the current column is the primary column.
	 * @since 4.3.0
	 * @access protected
	 *
	 */
	protected function handle_row_actions($item, $column_name, $primary)
	{
		return $column_name === $primary ? '<button type="button" class="toggle-row"><span class="screen-reader-text">' . __('Show more details') . '</span></button>' : '';
	}

	/**
	 *
	 * @param object $item
	 * @param string $column_name
	 */
	protected function column_default($item, $column_name)
	{
	}

	/**
	 * Return number of visible columns
	 *
	 * @return int
	 * @since 3.1.0
	 * @access public
	 *
	 */
	public function get_column_count()
	{
		list ($columns, $hidden) = $this->get_column_info();
		$hidden = array_intersect(array_keys($columns), array_filter($hidden));
		return count($columns) - count($hidden);
	}

	/**
	 * Message to be displayed when there are no items
	 *
	 * @since 3.1.0
	 * @access public
	 */
	public function no_items()
	{
		_e('No items found.');
	}

	/**
	 * Handle an incoming ajax request (called from admin-ajax.php)
	 *
	 * @since 3.1.0
	 * @access public
	 */
	public function ajax_response()
	{
		$this->prepare_items();

		ob_start();

		$no_placeholder = common::inputGetCmd('no_placeholder');

		if ($no_placeholder !== null) {
			$this->display_rows();
		} else {
			$this->display_rows_or_placeholder();
		}

		$rows = ob_get_clean();

		$response = array('rows' => $rows);

		if (isset($this->_pagination_args['total_items'])) {
			$response['total_items_i18n'] = sprintf(
				_n('%s item', '%s items', $this->_pagination_args['total_items']),
				number_format_i18n($this->_pagination_args['total_items'])
			);
		}
		if (isset($this->_pagination_args['total_pages'])) {
			$response['total_pages'] = $this->_pagination_args['total_pages'];
			$response['total_pages_i18n'] = number_format_i18n($this->_pagination_args['total_pages']);
		}

		die(wp_json_encode($response));
	}

	/**
	 * Prepares the list of items for displaying.
	 * @uses WP_List_Table::set_pagination_args()
	 *
	 * @since 3.1.0
	 * @access public
	 * @abstract
	 */
	public function prepare_items()
	{
		die('function WP_List_Table::prepare_items() must be over-ridden in a sub-class.');
	}

	/**
	 * Send required variables to JavaScript land
	 *
	 * @access public
	 */
	public function _js_vars()
	{
		$args = array(
			'class' => get_class($this),
			'screen' => array(
				'id' => $this->screen->id,
				'base' => $this->screen->base,
			)
		);

		printf("<script type='text/javascript'>list_args = %s;</script>\n", wp_json_encode($args));
	}

	/**
	 * An internal method that sets all the necessary pagination arguments
	 *
	 * @param array|string $args Array or string of arguments with information about the pagination.
	 * @since 3.1.0
	 * @access protected
	 *
	 */
	protected function set_pagination_args($args)
	{
		$args = wp_parse_args($args, array(
			'total_items' => 0,
			'total_pages' => 0,
			'per_page' => 0,
		));

		if (!$args['total_pages'] && $args['per_page'] > 0)
			$args['total_pages'] = ceil($args['total_items'] / $args['per_page']);

		// Redirect if page number is invalid and headers are not already sent.
		if (!headers_sent() && !wp_doing_ajax() && $args['total_pages'] > 0 && $this->get_pagenum() > $args['total_pages']) {
			wp_redirect(add_query_arg('paged', $args['total_pages']));
			exit;
		}

		$this->_pagination_args = $args;
	}

	/**
	 * Generate row actions div
	 *
	 * @param array $actions The list of actions
	 * @param bool $always_visible Whether the actions should be always visible
	 * @return string
	 * @since 3.1.0
	 * @access protected
	 *
	 */
	protected function row_actions($actions, $always_visible = false)
	{
		$action_count = count($actions);
		$i = 0;

		if (!$action_count)
			return '';

		$out = '<div class="' . ($always_visible ? 'row-actions visible' : 'row-actions') . '">';
		foreach ($actions as $action => $link) {
			++$i;
			($i == $action_count) ? $sep = '' : $sep = ' | ';
			$out .= "<span class='$action'>$link$sep</span>";
		}
		$out .= '</div>';

		$out .= '<button type="button" class="toggle-row"><span class="screen-reader-text">' . __('Show more details') . '</span></button>';

		return $out;
	}

	/**
	 * Display a view switcher
	 *
	 * @param string $current_mode
	 * @since 3.1.0
	 * @access protected
	 *
	 */
	protected function view_switcher($current_mode)
	{
		?>
        <input type="hidden" name="mode" value="<?php echo esc_attr($current_mode); ?>"/>
        <div class="view-switch">
			<?php
			foreach ($this->modes as $mode => $title) {
				$classes = array('view-' . $mode);
				if ($current_mode === $mode)
					$classes[] = 'current';
				printf(
					"<a href='%s' class='%s' id='view-switch-$mode'><span class='screen-reader-text'>%s</span></a>\n",
					esc_url(add_query_arg('mode', $mode)),
					implode(' ', $classes),
					$title
				);
			}
			?>
        </div>
		<?php
	}

	/**
	 * Display a comment count bubble
	 *
	 * @param int $post_id The post ID.
	 * @param int $pending_comments Number of pending comments.
	 * @since 3.1.0
	 * @access protected
	 *
	 */
	protected function comments_bubble($post_id, $pending_comments)
	{
		$approved_comments = get_comments_number();

		$approved_comments_number = number_format_i18n($approved_comments);
		$pending_comments_number = number_format_i18n($pending_comments);

		$approved_only_phrase = sprintf(_n('%s comment', '%s comments', $approved_comments), $approved_comments_number);
		$approved_phrase = sprintf(_n('%s approved comment', '%s approved comments', $approved_comments), $approved_comments_number);
		$pending_phrase = sprintf(_n('%s pending comment', '%s pending comments', $pending_comments), $pending_comments_number);

		// No comments at all.
		if (!$approved_comments && !$pending_comments) {
			printf('<span aria-hidden="true">—</span><span class="screen-reader-text">%s</span>',
				__('No comments')
			);
			// Approved comments have different display depending on some conditions.
		} elseif ($approved_comments) {
			printf('<a href="%s" class="post-com-count post-com-count-approved"><span class="comment-count-approved" aria-hidden="true">%s</span><span class="screen-reader-text">%s</span></a>',
				esc_url(add_query_arg(array('p' => $post_id, 'comment_status' => 'approved'), admin_url('edit-comments.php'))),
				$approved_comments_number,
				$pending_comments ? $approved_phrase : $approved_only_phrase
			);
		} else {
			printf('<span class="post-com-count post-com-count-no-comments"><span class="comment-count comment-count-no-comments" aria-hidden="true">%s</span><span class="screen-reader-text">%s</span></span>',
				$approved_comments_number,
				$pending_comments ? __('No approved comments') : __('No comments')
			);
		}

		if ($pending_comments) {
			printf('<a href="%s" class="post-com-count post-com-count-pending"><span class="comment-count-pending" aria-hidden="true">%s</span><span class="screen-reader-text">%s</span></a>',
				esc_url(add_query_arg(array('p' => $post_id, 'comment_status' => 'moderated'), admin_url('edit-comments.php'))),
				$pending_comments_number,
				$pending_phrase
			);
		} else {
			printf('<span class="post-com-count post-com-count-pending post-com-count-no-pending"><span class="comment-count comment-count-no-pending" aria-hidden="true">%s</span><span class="screen-reader-text">%s</span></span>',
				$pending_comments_number,
				$approved_comments ? __('No pending comments') : __('No comments')
			);
		}
	}

	/**
	 * Get number of items to display on a single page
	 *
	 * @param string $option
	 * @param int $default
	 * @return int
	 * @since 3.1.0
	 * @access protected
	 *
	 */
	protected function get_items_per_page($option, $default = 20)
	{
		$per_page = (int)get_user_option($option);
		if (empty($per_page) || $per_page < 1)
			$per_page = $default;

		/**
		 * Filters the number of items to be displayed on each page of the list table.
		 *
		 * The dynamic hook name, $option, refers to the `per_page` option depending
		 * on the type of list table in use. Possible values include: 'edit_comments_per_page',
		 * 'sites_network_per_page', 'site_themes_network_per_page', 'themes_network_per_page',
		 * 'users_network_per_page', 'edit_post_per_page', 'edit_page_per_page',
		 * 'edit_{$post_type}_per_page', etc.
		 *
		 * @param int $per_page Number of items to be displayed. Default 20.
		 * @since 2.9.0
		 *
		 */
		return (int)apply_filters("{$option}", $per_page);
	}

}
