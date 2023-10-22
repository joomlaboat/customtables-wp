<?php

use CustomTables\common;
use CustomTables\CT;
use CustomTables\database;
use CustomTables\Fields;
use CustomTables\listOfTables;

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

require_once ABSPATH . 'wp-includes/pluggable.php';

class CustomTableList extends WP_List_Table
{
    function __construct()
    {
        $action = $this->current_action();
        if (!str_contains($action, 'customtables-') and !str_contains($action, 'createtable') and !str_contains($action, 'savetable'))
            parent::__construct();
    }

    function tableSave()
    {
        $ct = new CT;
        require_once(CUSTOMTABLES_LIBRARIES_PATH . DIRECTORY_SEPARATOR . 'customtables' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'admin-listoftables.php');
        $helperListOfLayout = new listOfTables($ct);
        $tableId = common::inputGetInt('table');
        $messages = $helperListOfLayout->save($tableId);
        $url = 'admin.php?page=customtables-tables';
        wp_redirect(admin_url($url));
        exit;
    }

    function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'tablename':
                return $item[$column_name];
            case 'tabletitle':
                return $item[$column_name];
            case 'fieldcount':
                return $item[$column_name];
            case 'recordcount':
                return $item[$column_name];
            case 'published':
                return $item[$column_name];
            case 'id':
                return $item[$column_name];
            default:
                return print_r($item, true);
        }
    }

    // Define your table's data source
    function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="table[]" value="%s" />',
            $item['id']
        );
    }

    function get_bulk_actions()
    {
        $current_status = common::inputGetCMD('status');
        $actions = [];

        if ($current_status != 'trash')
            $actions['customtables-tables-edit'] = __('Edit', 'customtables');

        if ($current_status == '' or $current_status == 'all') {
            $actions['customtables-tables-publish'] = __('Publish', 'customtables');
            $actions['customtables-tables-unpublish'] = __('Draft', 'customtables');
        } elseif ($current_status == 'unpublished')
            $actions['customtables-tables-publish'] = __('Publish', 'customtables');
        elseif ($current_status == 'published')
            $actions['customtables-tables-unpublish'] = __('Draft', 'customtables');

        if ($current_status != 'trash')
            $actions['customtables-tables-trash'] = __('Move to Trash', 'customtables');

        if ($current_status == 'trash') {
            $actions['customtables-tables-restore'] = __('Restore', 'customtables');
            $actions['customtables-tables-delete'] = __('Delete Permanently', 'customtables');
        }
        return $actions;
    }

    // To show bulk action dropdown
    function process_bulk_action()
    {
        // Check if a bulk action is selected
        $action = $this->current_action();

        if ($action === 'customtables-tables-edit') {
            // Assuming $_POST['table'] contains the selected items
            $table_id = (int)(isset($_POST['table']) ? $_POST['table'][0] : '');

            // Redirect to the edit page with the appropriate parameters
            wp_redirect(admin_url('admin.php?page=customtables-tables-edit&action=edit&table=' . $table_id));
            exit();
        }

        if ($action === 'customtables-tables-publish') {
            $tables = (isset($_POST['table']) ? $_POST['table'] : []);
            $sets = [];
            $wheres = [];
            foreach ($tables as $table) {
                $sets[] = 'published=1';
                $wheres[] = 'id=' . (int)$table;
            }

            database::updateSets('#__customtables_tables', $sets, ['(' . implode(' OR ', $wheres) . ')']);
        }

        if ($action === 'customtables-tables-unpublish' or $action === 'customtables-tables-restore') {
            $tables = (isset($_POST['table']) ? $_POST['table'] : []);
            $sets = [];
            $wheres = [];
            foreach ($tables as $table) {
                $sets[] = 'published=0';
                $wheres[] = 'id=' . (int)$table;
            }

            database::updateSets('#__customtables_tables', $sets, ['(' . implode(' OR ', $wheres) . ')']);
        }

        if ($action === 'customtables-tables-trash') {
            $tables = (isset($_POST['table']) ? $_POST['table'] : []);
            $sets = [];
            $wheres = [];
            foreach ($tables as $table) {
                $sets[] = 'published=-2';
                $wheres[] = 'id=' . (int)$table;
            }

            database::updateSets('#__customtables_tables', $sets, ['(' . implode(' OR ', $wheres) . ')']);
        }

        if ($action === 'customtables-tables-delete') {
            $tables = (isset($_POST['table']) ? $_POST['table'] : []);

            $ct = new CT;
            require_once(CUSTOMTABLES_LIBRARIES_PATH . DIRECTORY_SEPARATOR . 'customtables' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'admin-listoftables.php');
            $helperListOfLayout = new CustomTables\listOfTables($ct);

            foreach ($tables as $tableId)
                $helperListOfLayout->deleteTable($tableId);
        }

        // Redirect to the edit page with the appropriate parameters
        $current_status = common::inputGetCMD('status');
        $url = 'admin.php?page=customtables-tables';
        if ($current_status != null)
            $url .= '&status=' . $current_status;

        wp_redirect(admin_url($url));
        exit();
    }

    // Define the columns in your table
    function display_table()
    {
        $this->views();
        $this->prepare_items();
        $this->display();
    }

    // Define what each column displays
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

    // Handle bulk actions (if any)
    public function get_views()
    {
        $current_status = common::inputGetCMD('status');

        $count_all = database::loadColumn('SELECT COUNT(id) FROM #__customtables_tables WHERE published!=-2')[0];
        $count_trashed = database::loadColumn('SELECT COUNT(id) FROM #__customtables_tables WHERE published=-2')[0];
        $count_published = database::loadColumn('SELECT COUNT(id) FROM #__customtables_tables WHERE published=1')[0];
        $count_unpublished = $count_all - $count_published;

        $link = 'admin.php?page=customtables-tables';

        $views = [];
        if ($count_all > 0)
            $views['all'] = '<a href="' . admin_url($link) . '" class="' . (($current_status === 'all' or $current_status === null) ? 'current' : '') . '">' . __('All') . ' <span class="count">(' . $count_all . ')</span></a>';

        if ($count_published > 0)
            $views['published'] = '<a href="' . admin_url($link . '&status=published') . '" class="' . ($current_status === 'published' ? 'current' : '') . '">' . __('Published') . ' <span class="count">(' . $count_published . ')</span></a>';

        if ($count_unpublished > 0)
            $views['unpublished'] = '<a href="' . admin_url($link . '&status=unpublished') . '" class="' . ($current_status === 'unpublished' ? 'current' : '') . '">' . __('Draft') . ' <span class="count">(' . $count_unpublished . ')</span></a>';

        if ($count_trashed > 0)
            $views['trash'] = '<a href="' . admin_url($link . '&status=trash') . '" class="' . ($current_status === 'trash' ? 'current' : '') . '">' . __('Trash') . ' <span class="count">(' . $count_trashed . ')</span></a>';

        return $views;
    }

    // Handle a bulk action (if implemented)
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

    // Adding action links to column
    function get_data()
    {
        // Fetch and return your data here
        $ct = new CT;
        require_once(CUSTOMTABLES_LIBRARIES_PATH . DIRECTORY_SEPARATOR . 'customtables' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'admin-listoftables.php');
        $helperListOfLayout = new CustomTables\listOfTables($ct);

        $orderby = common::inputGetCmd('orderby');
        $order = common::inputGetCmd('order');
        $current_status = common::inputGetCMD('status');

        $published = match ($current_status) {
            'published' => 1,
            'unpublished' => 0,
            'trash' => -2,
            default => null
        };

        $current_status = common::inputGetCMD('status');
        $query = $helperListOfLayout->getListQuery($published, null, null, $orderby, $order);
        $data = database::loadAssocList($query);
        $newData = [];
        foreach ($data as $item) {
            $table_exists = ESTables::checkIfTableExists($item['realtablename']);

            if ($item['published'] == -2)
                $label = '<span>'.$item['tablename'].'</span>';
            else
                $label = '<a class="row-title" href="?page=customtables-tables-edit&action=edit&table=' . $item['id'] . '">' . $item['tablename'] . '</a>'
                . (($current_status != 'unpublished' and $item['published'] == 0) ? ' — <span class="post-state">Draft</span>' : '');

            $item['tablename'] = '<strong>'.$label.'</strong>';

            $result = '<ul style="list-style: none !important;margin-left:0;padding-left:0;">';
            $moreThanOneLang = false;
            foreach ($ct->Languages->LanguageList as $lang) {
                $tableTitle = 'tabletitle';
                $tableDescription = 'description';

                if ($moreThanOneLang) {
                    $tableTitle .= '_' . $lang->sef;
                    $tableDescription .= '_' . $lang->sef;

                    if (!array_key_exists($tableTitle, $item)) {
                        Fields::addLanguageField('#__customtables_tables', 'tabletitle', $tableTitle);
                    }

                    if (!array_key_exists($tableTitle, $item)) {
                        Fields::addLanguageField('#__customtables_tables', 'description', $tableDescription);
                    }
                }
                $result .= '<li>' . (count($ct->Languages->LanguageList) > 1 ? $lang->title . ': ' : '') . '<b>' . $item[$tableTitle] . '</b></li>';
                $moreThanOneLang = true; //More than one language installed
            }

            $result .= '</ul>';
            $item['tabletitle'] = $result;

            if (!$table_exists)
                $item['recordcount'] = __('No Table', $this->plugin_text_domain);
            elseif (($item['customtablename'] !== null and $item['customtablename'] != '') and ($item['customidfield'] === null or $item['customidfield'] == ''))
                $item['recordcount'] = __('No Primary Key', $this->plugin_text_domain);
            else {
                $item['recordcount'] = '<a class="btn btn-secondary" aria-describedby="tip-tablerecords' . $item['id'] . '" href="'
                    . common::curPageURL() . '/administrator/index.php?option=com_customtables&view=listofrecords&tableid=' . $item['id'] . '">'
                    . listOfTables::getNumberOfRecords($item['realtablename'], $item['realidfieldname'])
                    . ' ' . __('Records', $this->plugin_text_domain) . '</a>';
            }

            $newData[] = $item;
        }
        return $newData;
    }

    // Display the table
    function get_columns()
    {
        return array(
            'cb' => '<input type="checkbox" />',
            'tablename' => __('Table Name', $this->plugin_text_domain),
            'tabletitle' => __('Table Title', $this->plugin_text_domain),
            'fieldcount' => __('Fields', $this->plugin_text_domain),
            'recordcount' => __('Records', $this->plugin_text_domain),
            //'published' => __('Status', $this->plugin_text_domain),
            'id' => __('Id', $this->plugin_text_domain)
        );
    }

    protected function get_sortable_columns()
    {
        $sortable_columns = array(
            'tablename' => array('tablename', false),
            //'status' => array('status', false),
            'id' => array('id', true)
        );
        return $sortable_columns;
    }

    function column_tablename($item)
    {
        $current_status = common::inputGetCMD('status');
        $actions = [];
        $nonce = wp_create_nonce( 'widgets-access' );

        $url = 'admin.php?page=customtables-tables';
        if ($current_status != null)
            $url .= '&status=' . $current_status;

        if ($current_status == 'trash') {
            $actions['restore'] = sprintf('<a href="'.$url.'&action=restore&table=%s&_wpnonce=%s">' . __('Restore', 'customtables') . '</a>', $item['id'],urlencode( $nonce ));
            $actions['delete'] = sprintf('<a href="'.$url.'&action=delete&table=%s&_wpnonce=%s">' . __('Delete Permanently', 'customtables') . '</a>', $item['id'],urlencode( $nonce ));
        } else {
            $actions['edit'] = sprintf('<a href="?page=customtables-tables-edit&action=edit&table=%s">' . __('Edit', 'customtables') . '</a>', $item['id']);
            $actions['trash'] = sprintf('<a href="'.$url.'&action=trash&table=%s&_wpnonce=%s">' . __('Trash', 'customtables') . '</a>', $item['id'],urlencode( $nonce ));
        }
        return sprintf('%1$s %2$s', $item['tablename'], $this->row_actions($actions));
    }
}