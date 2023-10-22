<?php
/**
 * New User Administration Screen.
 *
 * @package WordPress
 * @subpackage Administration
 */

use CustomTables\common;
use CustomTables\CT;
use CustomTables\database;
use CustomTables\Fields;
use CustomTables\listOfTables;

/*
$editable_roles = get_editable_roles();
foreach ($editable_roles as $role => $details) {
    echo $role . '<br>';
}
*/
$ct = new CT;
$tableId = common::inputGetInt('table');
/*
if (isset($_REQUEST['action']) && ('createtable' === $_REQUEST['action'] || 'savetable' === $_REQUEST['action'])) {
    require_once(CUSTOMTABLES_LIBRARIES_PATH . DIRECTORY_SEPARATOR . 'customtables' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'admin-listoftables.php');
    $helperListOfLayout = new listOfTables($ct);
    $messages = $helperListOfLayout->save($tableId);

    $url = 'admin.php?page=customtables-tables';
    wp_redirect(admin_url($url));
}
*/

//include ('customtables-tables-edit-help.php');

require_once ABSPATH . 'wp-admin/admin-header.php';

if (defined('WPINC')) {
    //check_admin_referer('edit', '_wpnonce_edit');
}
if($tableId !== null)
    $ct->getTable($tableId);

?>
    <div class="wrap">
        <h1 id="add-new-user">
            <?php
            if($tableId == 0)
                _e('Add New Custom Table');
            else
                _e('Edit Custom Table');
            ?>
        </h1>

        <?php if (isset($errors) && is_wp_error($errors)) : ?>
            <div class="error">
                <ul>
                    <?php
                    foreach ($errors->get_error_messages() as $err) {
                        echo "<li>$err</li>\n";
                    }
                    ?>
                </ul>
            </div>
        <?php
        endif;

        if (!empty($messages)) {
            foreach ($messages as $msg) {
                echo '<div id="message" class="updated notice is-dismissible"><p>' . $msg . '</p></div>';
            }
        }
        ?>

        <?php if (isset($add_user_errors) && is_wp_error($add_user_errors)) : ?>
            <div class="error">
                <?php
                foreach ($add_user_errors->get_error_messages() as $message) {
                    echo "<p>$message</p>";
                }
                ?>
            </div>
        <?php endif; ?>
        <div id="ajax-response"></div>

        <?php
        if (current_user_can('install_plugins')) {
            ?>
            <p><?php

                if($tableId == 0)
                    _e('Create a brand new custom table.');
                else
                    _e('Edit custom table.');
                ?>
                </p>
            <form method="post" name="createtable" id="createtable" class="validate" novalidate="novalidate">
                <input name="action" type="hidden" value="createtable"/>
                <?php wp_nonce_field('create-table', '_wpnonce_create-table'); ?>
                <?php
                // Load up the passed data, else set to a default.
                //$creating = isset($_POST['createtable']);
                $new_tablename = ($ct->Table !== null ? $ct->Table->tablename : '');

                ?>
                <table class="form-table" role="presentation">
                    <tr class="form-field form-required">
                        <th scope="row"><label for="tablename"><?php echo __('Table Name',$this->plugin_text_domain); ?> <span
                                        class="description">(<?php echo __('required',$this->plugin_text_domain); ?>)</span></label></th>
                        <td><input name="tablename" type="text" id="tablename"
                                   value="<?php echo esc_attr($new_tablename); ?>" aria-required="true"
                                   autocapitalize="none" autocorrect="off" autocomplete="off" maxlength="60"/></td>
                    </tr>

                    <?php
                    $moreThanOneLang = false;
                    foreach ($ct->Languages->LanguageList as $lang) {
                        $id = 'tabletitle';
                        if ($moreThanOneLang) {
                            $id .= '_' . $lang->sef;

                            $cssclass = 'form-control valid form-control-success';
                            $att = '';
                        } else {
                            $cssclass = 'form-control required valid form-control-success';
                            $att = ' required ';
                        }

                        $item_array = [];//(array)$this->item;
                        $vlu = '';

                        if (isset($item_array[$id]))
                            $vlu = $item_array[$id];

                        $vlu = ($ct->Table !== null ? $ct->Table->tablerow[$id] : '');

                        echo '

                    <tr class="form-field' . (!$moreThanOneLang ? ' form-required' : '') . '">
                        <th scope="row"><label for="' . $id . '">' . __('Table Title', $this->plugin_text_domain) . (!$moreThanOneLang ? ' <span class="description">(' . __('required', $this->plugin_text_domain) . ')</span>' : '') . '
                        <br/><b>' . $lang->title . '</b></label></th>
                        <td><input name="' . $id . '" type="text" id="' . $id . '" value="' . $vlu . '" maxlength="255" /></td>
                    </tr>
					';
                        //placeholder="' . __('Table Title', $this->plugin_text_domain) . ' - ' . $lang->title . '"
                        $moreThanOneLang = true; //More than one language installed
                    }
                    ?>

                </table>

                <?php
                if($tableId == 0)
                    submit_button(__('Add New Table'), 'primary', 'createtable', true, array('id' => 'createtablesub'));
                else
                    submit_button(__('Save Table'), 'primary', 'savetable', true, array('id' => 'createtablesub'));
                ?>

            </form>
        <?php } // End if (current_user_can('install_plugins')) ?>
    </div>
<?php
require_once ABSPATH . 'wp-admin/admin-footer.php';
