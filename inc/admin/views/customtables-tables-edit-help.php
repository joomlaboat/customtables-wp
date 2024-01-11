<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$title = __('Add New Custom Table');
$parent_file = 'customtables-tables.php';

$help = '<p>' . __('To add a new user to your site, fill in the form on this screen and click the Add New User button at the bottom.') . '</p>';

$help .= '<p>' . __('New users are automatically assigned a password, which they can change after logging in. You can view or edit the assigned password by clicking the Show Password button. The username cannot be changed once the user has been added.') . '</p>' .

'<p>' . __('By default, new users will receive an email letting them know they&#8217;ve been added as a user for your site. This email will also contain a password reset link. Uncheck the box if you do not want to send the new user a welcome email.') . '</p>';


$help .= '<p>' . __('Remember to click the Add New User button at the bottom of this screen when you are finished.') . '</p>';

get_current_screen()->add_help_tab(
array(
'id' => 'overview',
'title' => __('Overview'),
'content' => $help,
)
);

get_current_screen()->add_help_tab(
array(
'id' => 'user-roles',
'title' => __('User Roles'),
'content' => '<p>' . __('Here is a basic overview of the different user roles and the permissions associated with each one:') . '</p>' .
'<ul>' .
    '<li>' . __('Subscribers can read comments/comment/receive newsletters, etc. but cannot create regular site content.') . '</li>' .
    '<li>' . __('Contributors can write and manage their posts but not publish posts or upload media files.') . '</li>' .
    '<li>' . __('Authors can publish and manage their own posts, and are able to upload files.') . '</li>' .
    '<li>' . __('Editors can publish posts, manage posts as well as manage other people&#8217;s posts, etc.') . '</li>' .
    '<li>' . __('Administrators have access to all the administration features.') . '</li>' .
    '</ul>',
)
);

get_current_screen()->set_help_sidebar(
'<p><strong>' . __('For more information:') . '</strong></p>' .
'<p>' . __('<a href="https://wordpress.org/documentation/article/users-add-new-screen/">Documentation on Creating New Table</a>') . '</p>' .
'<p>' . __('<a href="https://wordpress.org/support/forums/">Support forums</a>') . '</p>'
);

//wp_enqueue_script('wp-ajax-response');
//wp_enqueue_script('user-profile');
//print_r(get_current_screen());
