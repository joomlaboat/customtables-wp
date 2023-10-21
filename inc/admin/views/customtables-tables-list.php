<?php

use CustomTables\common;
use CustomTables\CT;
use CustomTables\database;
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
        if($action == '')
            parent::__construct();
    }

    // Define your table's data source
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

    function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="table[]" value="%s" />',
            $item['id']
        );
    }

// To show bulk action dropdown
    function get_bulk_actions()
    {
        $actions = array(
            'edit' => __('Edit', 'customtables'),
            'trash' => __('Move to trash', 'customtables'),
            'delete' => __('Delete permanently', 'customtables')
        );
        return $actions;
    }

    // Define the columns in your table
    public function process_bulk_action()
    {
        // Check if a bulk action is selected
        $action = $this->current_action();

        if ($action === 'edit') {
            // Assuming $_POST['table'] contains the selected items
            $table_id = (int)(isset($_POST['table']) ? $_POST['table'][0] : '');

            // Redirect to the edit page with the appropriate parameters
            wp_redirect(admin_url('admin.php?page=customtables-tables-edit&action=edit&table=' . $table_id));
            exit();
        }

        if ($action === 'trash') {
// Process the 'Delete' action
// You can access the selected items using $_POST['table']
// Delete the selected items
            echo 't';
        }

        if ($action === 'delete') {
// Process the 'Delete' action
// You can access the selected items using $_POST['table']
// Delete the selected items
            echo 'd';
        }

    }

// Define what each column displays
    function display_table()
    {
        $this->prepare_items();
        $this->display();
    }

// Handle bulk actions (if any)

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

// Handle a bulk action (if implemented)

    function get_data()
    {
// Fetch and return your data here
        $ct = new CT;
        require_once(CUSTOMTABLES_LIBRARIES_PATH . DIRECTORY_SEPARATOR . 'customtables' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'admin-listoftables.php');
        $helperListOfLayout = new CustomTables\listOfTables($ct);

        $orderby = common::inputGetCmd('orderby');
        $order = common::inputGetCmd('order');

        $query = $helperListOfLayout->getListQuery(null, null, null, $orderby, $order);
        $data = database::loadAssocList($query);
        $newData = [];
        foreach ($data as $item) {

//'$item['cb'] = '<input type="checkbox" value="' . $item['id'] . '" />';

            $table_exists = ESTables::checkIfTableExists($item['realtablename']);

            $item['tablename'] = '<a href="?page=customtables-tables-edit&action=edit&table=' . $item['id'] . '">' . $item['tablename'] . '</a>';

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
                        //$item_array[$tableTitle] = '';
                    }

                    if (!array_key_exists($tableTitle, $item)) {
                        Fields::addLanguageField('#__customtables_tables', 'description', $tableDescription);
                        //$item_array[$tableDescription] = '';
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

// Adding action links to column

    function get_columns()
    {
        return array(
            'cb' => '<input type="checkbox" />',
            'tablename' => __('Table Name', $this->plugin_text_domain),
            'tabletitle' => __('Table Title', $this->plugin_text_domain),
            'fieldcount' => __('Fields', $this->plugin_text_domain),
            'recordcount' => __('Records', $this->plugin_text_domain),
            'published' => __('Status', $this->plugin_text_domain),
            'id' => __('Id', $this->plugin_text_domain)
        );
    }

// Display the table

    protected function get_sortable_columns()
    {
        $sortable_columns = array(
            'tablename' => array('tablename', false),
            'status' => array('status', false),
            'id' => array('id', true)
        );
        return $sortable_columns;
    }

    function column_tablename($item)
    {
        $actions = array(
            'edit' => sprintf('<a href="?page=customtables-tables-edit&action=%s&table=%s">' . __('Edit', 'customtables') . '</a>', 'edit', $item['id']),
            'delete' => sprintf('<a href="?page=customtables-tables-edit&action=%s&table=%s">' . __('Delete', 'customtables') . '</a>', 'delete', $item['id']),
        );

        return sprintf('%1$s %2$s', $item['tablename'], $this->row_actions($actions));
    }
}