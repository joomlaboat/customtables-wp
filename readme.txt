=== Custom Tables - Create, Read, Update and Delete ===

Contributors: @ivankomlev
Donate link: https://www.patreon.com/joomlaboat
Tags: custom tables, custom database tables, database, catalog, forms
Requires at least: 6.0
Tested up to: 6.7.1
Stable tag: 1.3.7
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

The Custom Tables plugin allows you to create and manage custom database tables, display catalogs, forms, and tables using Twig templating language.


== Description ==

**Unlock Custom Data Structures and Dynamic Layouts with Custom Tables**

Take your WordPress site to the next level with Custom Tables, a powerful plugin that lets you create custom database tables, fields, and layouts. With its versatility and flexibility, you can build anything from catalogs to edit forms, detail pages, and more.

**Demo:** [Try Custom Tables in action](https://tastewp.org/plugins/customtables/) (right-click to open in a new tab)

**Key Features**:

Twig Template Language Support: Create dynamic layouts with ease using the modern Twig template language.
31 Field Types: Choose from Integer, Decimal, Text String, Date, Email, Color, Image, and more to create complex data structures.
Layout Editor: Simplify layout creation with the Auto-Create button, which generates a layout based on your table fields.
Secure and Sanitized: All tables are stored in MySQL, with queries and field values properly sanitized for added security.

= Introduction =

[youtube https://www.youtube.com/watch?v=Dq3jbk9JaJY]
[youtube https://www.youtube.com/watch?v=qehcUdr7vk0]

= More information =
Visit [ct4.us](https://ct4.us/) for more information, take a look at [wiki](https://github.com/joomlaboat/custom-tables/wiki).


== Screenshots ==

1. "Create Table" screen
2. "Add Fields" screen
3. "Add Custom Tables Block" screen
4. "Select Layout Type and Table" screen

== Frequently Asked Questions ==

= Do you have any questions? =

[Please contact us here with your query.](https://ct4.us/contact-us/)

== Changelog ==

= 1.3.7
- Added {{ document.config }} parameters support for WordPress
- Introduced {{ fieldname.required(v1,v2) }} tag with conditional output
- Implemented {{ fieldname.input }} tag with comprehensive field details
- Enhanced language prefix handling for WordPress compatibility
- Fixed Layout Tag button class for WordPress
- Resolved content loading issues by removing unnecessary slashes
- Added Parameters tab with filtering capability for Catalog View
- Enhanced Layout Auto Creator with proper JSON value formatting
- Implemented “Start with” and “End with” search options
- Hidden unnecessary “%” characters in search interface
- Added minimum search string length parameter
- Fixed Image field type preview functionality
- Enhanced Catalog Item delete JavaScript functionality
- Improved date field type with enhanced description and UNIX format conversion

= 1.3.6
- Added {{ document.config() }} tag
- Enabled Server field type for WordPress
- Improved Table Join description
- Added Google Map With Markers to Layout Auto Creator
- Added Edit form shortcode view (WordPress)
- Fixed Text Area field type description
- Resolved WordPress edit form issues

= 1.3.5
- Server Info field type added.

= 1.3.4
- Shortcode tag added. Example [customtables table="countries"]

= 1.3.3
- Image Gallery field type added.

= 1.3.2
- Listing IDs now stored as strings.
- Back-end: Record lists display processed values.
- Fixed Table Join field params config bug.
- Added Table Join List field type.
- Resolved table import issues.

= 1.3.1
- Custom field prefixes are set automatically for ol tables
- Custom Tables Library updated - code cleaned.

= 1.3.0
- Custom field prefix can be set.

= 1.2.9
- {{ url.getwhere('param') }} tag added.
- Added support for the 'Filter' parameter, now functional with Custom Tables Block.
- Implemented 'Group By' functionality for the {{ tables.getrecords() }} tag.
- Optimized CSS handling by merging styles across multiple blocks, ensuring only unique styles are retained.
- Updated Layout Editor: When 'Catalog Layout' is selected, the following layout types can now be chosen: Catalog, XML, CSV, or JSON.

= 1.2.8 =
Bags fixed. CT Lib updated.

= 1.2.7 =
Layout select box only contains layouts that have the same type as the selected above.
Tech-Support links added. Sub menu links fixed.
Full link Url to images fixed. On some websites trailing slash is needed.
Back-end forms improved. Cancel buttons added. Go back to Tables buttons added.
Record edit form layout improved - unnecessary elements deleted, such as legend and {{ html.goback() }}
Table Join field type added.

= 1.2.6 =
Block property panel: Layout selection depends on the Type.
Field names may include uppercase characters.

= 1.2.5 =
CSV file import feature added to the List of Records page.

= 1.2.4 =
The following field types have been added: User Group, User Groups, User Author (record author), File Link, Log, Auto-increment ID, Color, and Google Maps (GPS coordinates).

= 1.2.3 =
New field types added: File, Blob, Creation Time, Change Time, MD5, and Virtual.

= 1.2.2 =
Image field type added.

= 1.2.1 =
Date method bugs fixed.

= 1.2.0 =
Layout Auto Create feature added.

= 1.1.9 =
CT Library updated, and the main plugin file renamed - fixed activation bug.

= 1.1.8 =
Date Time Field type option added. DatePicker replaced with DateTimePicker for enhanced functionality.

= 1.1.7 =
Twig Library updated, CT Library updated.
Date Field type added.
CSS style class file loads properly. Field type property null check added.

= 1.1.6 =
{{ html.captcha() }} Twig Tag has been added.

= 1.1.5 =
First public WordPress plugin release.


== Upgrade Notice ==
= 1.1.5 =
* First public WordPress plugin release.

= 1.1.9 =
Main plugin file renamed - activation bug fixed.