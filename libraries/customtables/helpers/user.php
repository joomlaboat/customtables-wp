<?php
/**
 * CustomTables Joomla! 3.x/4.x/5.x Native Component and WordPress 6.x Plugin
 * @package Custom Tables
 * @author Ivan Komlev <support@joomlaboat.com>
 * @link https://joomlaboat.com
 * @copyright (C) 2018-2023 Ivan Komlev
 * @license GNU/GPL Version 2 or later - https://www.gnu.org/licenses/gpl-2.0.html
 **/

namespace CustomTables;

// no direct access
if (!defined('_JEXEC') and !defined('WPINC')) {
    die('Restricted access');
}

use ESTables;
use Exception;
use Joomla\CMS\Version;
use JoomlaBasicMisc;
use JUserHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\Access\Access;
use Joomla\CMS\Language\Text;
use tagProcessor_If;

class CTUser
{
    var int $id;
    var array $groups;
    var ?string $email;
    var bool $isUserAdministrator;
    var ?string $name;
    var ?string $username;

    public function __construct()
    {
        $this->id = 0;
        $this->groups = [];
        $this->email = null;
        $this->name = null;
        $this->username = null;
        $this->isUserAdministrator = false;

        if (defined('_JEXEC')) {
            $version_object = new Version;
            $version = (int)$version_object->getShortVersion();

            if ($version < 4)
                $user = Factory::getUser();
            else
                $user = Factory::getApplication()->getIdentity();

            $this->id = is_null($user) ? 0 : $user->id;

            if ($user !== null) {
                $this->groups = $user->get('groups');
                $this->email = $user->email;
                $this->name = $user->name;
                $this->username = $user->username;
            }
            $this->isUserAdministrator = in_array(8, $this->groups);//8 is Super Users

        } else {
            if (function_exists('get_current_user_id')) {
                $this->id = get_current_user_id();

                if (function_exists('wp_get_current_user')) {
                    $current_user = wp_get_current_user();
                    $this->groups = $current_user->roles;

                    if (current_user_can('activate_plugins'))
                        $this->isUserAdministrator = true;
                    else
                        $this->isUserAdministrator = false;

                    //$current_user = wp_get_current_user();
                    //$has_capability = $current_user->has_cap('capability_name');
                }
            }
        }
    }

    public static function ResetPassword(CT $ct, $listing_id)
    {
        if ($listing_id == 0) {
            Factory::getApplication()->enqueueMessage('Table record selected.', 'error');
            return false;
        }

        if ($ct->Env->clean)
            if (ob_get_contents()) ob_end_clean();

        if ($ct->Table->useridrealfieldname === null or $ct->Table->useridrealfieldname == '') {
            Factory::getApplication()->enqueueMessage('User ID field not found.', 'error');
            return false;
        }

        $ct->Table->loadRecord($listing_id);

        if ($ct->Table->record === null) {
            Factory::getApplication()->enqueueMessage('User record ID: "' . $listing_id . '" not found.', 'error');
            return false;
        }

        $password = strtolower(JUserHelper::genRandomPassword());

        $realUserId = $ct->Table->record[$ct->Table->useridrealfieldname];
        $realUserId = CTUser::SetUserPassword($realUserId, $password);
        $userRow = CTUser::GetUserRow($realUserId);

        $config = Factory::getConfig();
        $sitename = $config->get('sitename');

        if ($userRow !== null) {
            $user_full_name = ucwords(strtolower($userRow['name']));
            $user_name = $userRow['username'];
            $user_email = $userRow['email'];
        } else {

            return false;
            //$user_full_name = 'user: '.$realUserId.' not found.';
            //$user_name = 'user: '.$realUserId.' not found.';
            //$user_email = 'user: '.$realUserId.' not found.';
        }
        $subject = 'Your {SITENAME} password reset request';

        $subject = str_replace('{SITENAME}', $sitename, $subject);

        $uri = URI::getInstance();
        $base = $uri->toString(array('scheme', 'user', 'pass', 'host', 'port')) . '/';
        $siteURL = str_replace('/administrator/', '/', $base);

        $messageBody = 'Hello {NAME},\n\nYou may now log in to {SITEURL} using the following username and password:\n\nUsername: {USERNAME}\nPassword: {PASSWORD_CLEAR}';

        $messageBody = str_replace('\n', '<br/>', $messageBody);
        $messageBody = str_replace('{SITENAME}', $sitename, $messageBody);
        $messageBody = str_replace('{SITEURL}', $siteURL, $messageBody);

        $messageBody = str_replace('{NAME}', $user_full_name, $messageBody);
        $messageBody = str_replace('{USERNAME}', $user_name, $messageBody);
        $messageBody = str_replace('{PASSWORD_CLEAR}', $password, $messageBody);

        if ($ct->Env->clean)
            die;

        if (Email::sendEmail($user_email, $subject, $messageBody, $isHTML = true)) {
            //clean exit

            Factory::getApplication()->enqueueMessage('User password has been reset and sent to the email "' . $user_email . '".');
            return true;
        }

        //clean exit
        return false;
    }

    static protected function SetUserPassword(int $userid, string $password): int
    {
        $query = 'UPDATE #__users SET password=md5("' . $password . '"), requireReset=0 WHERE id=' . $userid;
        database::setQuery($query);
        return $userid;
    }

    static public function GetUserRow(int $userid): ?array
    {
        $query = 'SELECT * FROM #__users WHERE id=' . $userid . ' LIMIT 1';
        $rows = database::loadAssocList($query);
        if (count($rows) == 0)
            return null;
        else
            return $rows[0];
    }

    static public function GetUserGroups(int $userid): string
    {
        $groups = Access::getGroupsByUser($userid);
        $groupIdList = '(' . implode(',', $groups) . ')';
        $query = 'SELECT title FROM #__usergroups WHERE id IN ' . $groupIdList;
        $rows = database::loadRowList($query);
        $groupList = array();
        foreach ($rows as $group)
            $groupList[] = $group[0];

        return implode(',', $groupList);
    }

    public static function CreateUser($realtablename, $realidfieldname, $email, $name, $usergroups, $listing_id, $useridfieldname): bool
    {
        $msg = '';
        $password = strtolower(JUserHelper::genRandomPassword());
        $articleId = 0;

        if (!@Email::checkEmail($email)) {
            Factory::getApplication()->enqueueMessage(JoomlaBasicMisc::JTextExtended('COM_CUSTOMTABLES_INCORRECT_EMAIL') . ' "' . $email . '"', 'error');
            return false;
        }

        $realUserId = CTUser::CreateUserAccount($name, $email, $password, $email, $usergroups, $msg, $articleId);

        if ($msg != '') {
            Factory::getApplication()->enqueueMessage($msg, 'error');
            return false;
        }

        if ($realUserId !== null) {
            CTUser::UpdateUserField($realtablename, $realidfieldname, $useridfieldname, $realUserId, $listing_id);
            Factory::getApplication()->enqueueMessage(JoomlaBasicMisc::JTextExtended('COM_CUSTOMTABLES_USER_CREATE_PSW_SENT'));
        } else {
            $msg = JoomlaBasicMisc::JTextExtended('COM_CUSTOMTABLES_ERROR_USER_NOTCREATED');
            Factory::getApplication()->enqueueMessage($msg, 'error');
        }
        return true;
    }

    static public function CreateUserAccount($fullname, $username, $password, $email, $group_names, &$msg): ?int
    {
        //Get group IDs
        $group_ids = CTUser::getUserGroupIDsByName($group_names);

        if ($group_ids === null)
            return null;

        //Creates active user
        $useractivation = 0;//already activated

        $config = Factory::getConfig();

        // Initialise the table with JUser.
        $user = Factory::getUser(0);

        $data = array();

        // Prepare the data for the user object.
        $data['name'] = $fullname;
        $data['username'] = $username;
        $data['email'] = $email;
        $data['password'] = $password;
        $data['password2'] = $password;


        // Override the base user data with any
        if (($useractivation == 1) || ($useractivation == 2)) {
            jimport('joomla.user.helper');

            $data['activation'] = \JApplicationHelper::getHash(JUserHelper::genRandomPassword());
            $data['block'] = 1;
        }

        // Bind the data.
        if (!$user->bind($data)) {
            $msg = JoomlaBasicMisc::JTextExtended('COM_USERS_REGISTRATION_BIND_FAILED', $user->getError());
            return null;
        }

        // Load the users' plugin group.
        //JPluginHelper::importPlugin('user');

        // Store the data.
        if (!$user->save()) {

            $msg = Text::sprintf('COM_USERS_REGISTRATION_SAVE_FAILED', $user->getError());
            return null;
        }

        //Apply group

        foreach ($group_ids as $group_id) {
            $query = 'INSERT #__user_usergroup_map SET user_id=' . $user->id . ', group_id=' . $group_id;
            database::setQuery($query);
        }

        // Compile the notification mail values.
        $data = $user->getProperties();
        $data['fromname'] = $config->get('fromname');
        $data['mailfrom'] = $config->get('mailfrom');
        $data['sitename'] = $config->get('sitename');

        $uri = URI::getInstance();
        $base = $uri->toString(array('scheme', 'user', 'pass', 'host', 'port')) . '/';

        $base = str_replace('/administrator/', '/', $base);


        $data['siteurl'] = $base;//JUri::base();

        // Handle account activation/confirmation emails.
        if ($useractivation == 1 or $useractivation == 2) {
            // Set the link to activate the user account.
            $data['activate'] = $base . 'index.php?option=com_users&task=registration.activate&token=' . $data['activation'];
        }

        $config = Factory::getConfig();
        $siteName = $config->get('sitename');
        $subject = JoomlaBasicMisc::JTextExtended('COM_USERS_EMAIL_ACCOUNT_DETAILS');

        $emailSubject = Text::sprintf(
            $subject,
            $fullname,
            $siteName
        );

        $emailSubject = str_replace('{NAME}', $fullname, $emailSubject);
        $emailSubject = str_replace('{SITENAME}', $siteName, $emailSubject);

        $body = JoomlaBasicMisc::JTextExtended('COM_USERS_EMAIL_REGISTERED_BODY');
        $UriBase = Uri::base();

        $emailBody = Text::sprintf(
            $body,
            $fullname,
            $siteName,
            $UriBase,
            $username,
            $password
        );

        $emailBody = str_replace('{NAME}', $fullname, $emailBody);
        $emailBody = str_replace('{SITENAME}', $siteName, $emailBody);
        $emailBody = str_replace('{SITEURL}', $UriBase, $emailBody);
        $emailBody = str_replace('{USERNAME}', $username, $emailBody);
        $emailBody = str_replace('{PASSWORD_CLEAR}', $password, $emailBody);
        $emailBody = str_replace("\n", '<br/>', $emailBody);

        Email::sendEmail($email, $emailSubject, $emailBody);
        return $user->id;
    }

    //------------- USER CREATION

    static protected function getUserGroupIDsByName($group_names): ?array
    {
        $new_names = array();
        $names = explode(',', $group_names);
        foreach ($names as $name) {
            $n = preg_replace("/[^[:alnum:][:space:]]/u", '', trim($name));
            if ($n != '')
                $new_names[] = 'title=' . database::quote($n);
        }

        if (count($new_names) == 0)
            return null;

        $query = 'SELECT id FROM #__usergroups WHERE ' . implode(' OR ', $new_names);

        try {
            $rows = database::loadObjectList($query);
        } catch (Exception $e) {
            return null;
        }

        $usergroup_ids = array();
        foreach ($rows as $row) {
            $usergroup_ids[] = $row->id;
        }
        return $usergroup_ids;
    }

    static public function UpdateUserField(string $realtablename, string $realidfieldname, string $useridfieldname, string $existing_user_id, $listing_id)
    {
        $query = 'UPDATE ' . $realtablename . ' SET ' . $useridfieldname . '=' . $existing_user_id . ' WHERE ' . $realidfieldname . '=' . database::quote($listing_id) . ' LIMIT 1';
        database::setQuery($query);
    }

    static public function CheckIfUserNameExist(string $username): bool
    {
        $query = 'SELECT id FROM #__users WHERE username=' . database::quote($username) . ' LIMIT 1';
        $rows = database::loadAssocList($query);
        if (count($rows) == 1)
            return true;

        return false;
    }

    static public function CheckIfUserExist(string $username, string $email)
    {
        $query = 'SELECT id FROM #__users WHERE username=' . database::quote($username) . ' AND email=' . database::quote($email) . ' LIMIT 1';
        $rows = database::loadAssocList($query);
        if (count($rows) != 1)
            return 0;

        $row = $rows[0];
        return $row['id'];
    }

    static public function CheckIfEmailExist(string $email, &$existing_user, &$existing_name)
    {
        $existing_user = '';
        $existing_name = '';
        $query = 'SELECT id, username, name FROM #__users WHERE email=' . database::quote($email) . ' LIMIT 1';
        $rows = database::loadAssocList($query);
        if (count($rows) == 1) {
            $row = $rows[0];
            $existing_user = $row['username'];
            $existing_name = $row['name'];
            return $row['id'];
        }
        return false;
    }

    public static function checkIfRecordBelongsToUser(CT &$ct, int $ug)
    {
        if (!isset($ct->Env->isUserAdministrator))
            return false;

        if ($ug == 1)
            $usergroups = array();
        else
            $usergroups = $ct->Env->user->groups;

        $isOk = false;

        if ($ct->Env->isUserAdministrator or in_array($ug, $usergroups)) {
            $isOk = true;
        } else {
            if (isset($ct->Table->record) and isset($ct->Table->record['listing_published']) and $ct->Table->useridfieldname != '') {
                $uid = $ct->Table->record[$ct->Table->useridrealfieldname];

                if ($uid == $ct->Env->user->id and $ct->Env->user->id != 0)
                    $isOk = true;
            }
        }
        return $isOk;
    }

    public static function CheckAuthorization(CT &$ct, int $action = 1)
    {
        if ($action == 0)
            return true;

        if ($action == 5) //force edit
        {
            $action = 1;
        } else {
            if ($action == 1 and $ct->Params->listing_id == 0)
                $action = 4; //add new
        }

        if ($ct->Params->guestCanAddNew == 1)
            return true;

        if ($ct->Params->guestCanAddNew == -1 and $ct->Params->listing_id == 0)
            return false;

        //check is authorized or not
        if ($action == 1)
            $userGroup = $ct->Params->editUserGroups;
        elseif ($action == 2)
            $userGroup = $ct->Params->publishUserGroups;
        elseif ($action == 3)
            $userGroup = $ct->Params->deleteUserGroups;
        elseif ($action == 4)
            $userGroup = $ct->Params->addUserGroups;
        else
            $userGroup = null;

        if ($ct->Env->user->id == 0)
            return false;

        if ($ct->Env->isUserAdministrator) {
            //Super Users have access to everything
            return true;
        }

        if ($ct->Params->listing_id == 0 or $ct->Params->userIdField == '')
            return JoomlaBasicMisc::checkUserGroupAccess($userGroup);

        $theAnswerIs = false;

        if ($ct->Params->userIdField != '')
            $theAnswerIs = self::checkIfItemBelongsToUser($ct, $ct->Params->userIdField, $ct->Params->listing_id);

        if (!$theAnswerIs)
            return JoomlaBasicMisc::checkUserGroupAccess($userGroup);

        return true;
    }

    //checkAccess

    public static function checkIfItemBelongsToUser(CT &$ct, string $userIdField, string $listing_id): bool
    {
        $wheres = self::UserIDField_BuildWheres($ct, $userIdField, $listing_id);
        $query = 'SELECT c.' . $ct->Table->realidfieldname . ' FROM ' . $ct->Table->realtablename . ' AS c WHERE ' . implode(' AND ', $wheres) . ' LIMIT 1';

        if (database::getNumRowsOnly($query) == 1) {
            return true;
        }
        return false;
    }

    public static function UserIDField_BuildWheres(CT &$ct, string $userIdField, string $listing_id): array
    {
        $wheres = [];

        $statement_items = tagProcessor_If::ExplodeSmartParams($userIdField); //"and" and "or" as separators

        $wheres_owner = array();

        foreach ($statement_items as $item) {
            $field = $item[1];
            if (!str_contains($field, '.')) {
                //example: user
                //check if the record belong to the current user
                $user_field_row = Fields::FieldRowByName($field, $ct->Table->fields);
                $wheres_owner[] = [$item[0], $user_field_row['realfieldname'] . '=' . $ct->Env->user->id];
            } else {
                //example: parents(children).user
                $statement_parts = explode('.', $field);
                if (count($statement_parts) != 2) {
                    $ct->app->enqueueMessage(JoomlaBasicMisc::JTextExtended('Menu Item - "UserID Field name" parameter has a syntax error. Error is about "." character - only one is permitted. Correct example: parent(children).user'), 'error');
                    return [];
                }

                $table_parts = explode('(', $statement_parts[0]);
                if (count($table_parts) != 2) {
                    $ct->app->enqueueMessage(JoomlaBasicMisc::JTextExtended('Menu Item - "UserID Field name" parameter has a syntax error. Error is about "(" character. Correct example: parent(children).user'), 'error');
                    return [];
                }

                $parent_tablename = $table_parts[0];
                $parent_join_field = str_replace(')', '', $table_parts[1]);
                $parent_user_field = $statement_parts[1];

                $parent_table_row = ESTables::getTableRowByName($parent_tablename);

                if (!is_object($parent_table_row)) {
                    $ct->app->enqueueMessage(JoomlaBasicMisc::JTextExtended('Menu Item - "UserID Field name" parameter has an error: Table "' . $parent_tablename . '" not found.'), 'error');
                    return [];
                }

                $parent_table_fields = Fields::getFields($parent_table_row->id);

                $parent_join_field_row = Fields::FieldRowByName($parent_join_field, $parent_table_fields);

                if (count($parent_join_field_row) == 0) {
                    $ct->app->enqueueMessage(JoomlaBasicMisc::JTextExtended('Menu Item - "UserID Field name" parameter has an error: Join field "' . $parent_join_field . '" not found.'), 'error');
                    return [];
                }

                if ($parent_join_field_row['type'] != 'sqljoin' and $parent_join_field_row['type'] != 'records') {
                    $ct->app->enqueueMessage(JoomlaBasicMisc::JTextExtended('Menu Item - "UserID Field name" parameter has an error: Wrong join field type "' . $parent_join_field_row['type'] . '". Accepted types: "sqljoin" and "records" .'), 'error');
                    return [];
                }

                //User field

                $parent_user_field_row = Fields::FieldRowByName($parent_user_field, $parent_table_fields);

                if (count($parent_user_field_row) == 0) {
                    $ct->app->enqueueMessage(JoomlaBasicMisc::JTextExtended('Menu Item - "UserID Field name" parameter has an error: User field "' . $parent_user_field . '" not found.'), 'error');
                    return [];
                }

                if ($parent_user_field_row['type'] != 'userid' and $parent_user_field_row['type'] != 'user') {
                    $ct->app->enqueueMessage(JoomlaBasicMisc::JTextExtended('Menu Item - "UserID Field name" parameter has an error: Wrong user field type "' . $parent_join_field_row['type'] . '". Accepted types: "userid" and "user" .'), 'error');
                    return [];
                }

                $parent_wheres = [];

                $parent_wheres[] = 'p.' . $parent_user_field_row['realfieldname'] . '=' . $ct->Env->user->id;

                $fieldType = $parent_join_field_row['type'];
                if ($fieldType != 'sqljoin' and $fieldType != 'records')
                    return [];

                if ($fieldType == 'sqljoin')
                    $parent_wheres[] = 'p.' . $parent_join_field_row['realfieldname'] . '=c.listing_id';

                if ($fieldType == 'records')
                    $parent_wheres[] = 'INSTR(p.' . $parent_join_field_row['realfieldname'] . ',CONCAT(",",c.' . $ct->Table->realidfieldname . ',","))';

                $q = '(SELECT p.' . $parent_table_row->realidfieldname . ' FROM ' . $parent_table_row->realtablename . ' AS p WHERE ' . implode(' AND ', $parent_wheres) . ' LIMIT 1) IS NOT NULL';

                $wheres_owner[] = [$item[0], $q];
            }
        }

        $wheres_owner_str = '';
        $index = 0;
        foreach ($wheres_owner as $field) {
            if ($index == 0)
                $wheres_owner_str .= $field[1];
            else
                $wheres_owner_str .= ' ' . strtoupper($field[0]) . ' ' . $field[1];

            $index += 1;
        }

        if ($listing_id != '' and $listing_id != 0)
            $wheres[] = $ct->Table->realidfieldname . '=' . database::quote($listing_id);

        if ($wheres_owner_str != '')
            $wheres[] = '(' . $wheres_owner_str . ')';

        return $wheres;
    }

    public static function showUserGroup(int $userid): string
    {
        $query = 'SELECT title FROM #__usergroups WHERE id=' . $userid . ' LIMIT 1';
        $options = database::loadAssocList($query);
        if (count($options) != 0)
            return $options[0]['title'];

        return '';
    }

    public static function showUserGroups(?string $valueArrayString): string
    {
        if ($valueArrayString == '')
            return '';

        $where = array();
        $valueArray = explode(',', $valueArrayString);
        foreach ($valueArray as $value) {
            if ($value != '') {
                $where[] = 'id=' . (int)$value;
            }
        }

        $query = 'SELECT title FROM #__usergroups WHERE ' . implode(' OR ', $where) . ' ORDER BY title';
        $options = database::loadAssocList($query);

        if (count($options) == 0)
            return '';

        $groups = array();
        foreach ($options as $opt)
            $groups[] = $opt['title'];

        return implode(',', $groups);
    }

    function authorise(string $action, ?string $assetName): bool
    {
        if (defined('_JEXEC')) {
            $user = Factory::getUser();
            return $user->authorise($action, $assetName);
        } else {

        }
        return false;
    }
}
