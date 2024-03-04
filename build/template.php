<?php
/**
 * Plugin Name:       CustomTables
 * Plugin URI:        https://ct4.us/
 * GitHub:            https://github.com/joomlaboat/customtables-wp
 * Author:            Ivan Komlev
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 *
 * All the parameters passed to the function where this file is being required are accessible in this scope:
 *
 * @param array $attributes The array of attributes for this block.
 * @param string $content Rendered block output. ie. <InnerBlocks.Content />.
 * @param WP_Block $block_instance The instance of the WP_Block class that represents the block being rendered.
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use CustomTables\common;
use CustomTables\CT;
use CustomTables\Layouts;


/*
 * <p <?php echo get_block_wrapper_attributes(); ?>>BLOCK1</p>
 * */
?>
<div>

	<?php

	if (isset($attributes['table'])) {

		if ($attributes['table'] !== 0) {

			$mixedLayout_safe = '';

			$ct = new CT(null, false);
			try {
				$ct->getTable($attributes['table']);
				if ($ct->Table->tablename !== null) {
					$layoutType = null;
					$view = common::inputGetCmd('view' . $ct->Table->tableid);
					if ($view == 'edititem') {
						$layoutType = 2;
						$layoutId = 0;
					}
                    else
                        $layoutId = (int)$attributes['layout'];

					$layouts = new Layouts($ct);
					$mixedLayout_safe = $layouts->renderMixedLayout($layoutId, $layoutType);
				} else {
					$mixedLayout_safe = 'Table "' . $attributes['table'] . '" not found.';
				}
			} catch (Exception $e) {
				$mixedLayout_safe = 'Error: ' . $e->getMessage();
			}

			$message = get_transient('plugin_error_message');
			if ($message) {
				echo '<blockquote style="background-color: #f8d7da; border-left: 5px solid #dc3545; padding: 10px;"><p>' . esc_html($message) . '</p></blockquote>';
				// Once displayed, clear the transient
				delete_transient('plugin_error_message');
			}

			$success_message = get_transient('plugin_success_message');
			if (!empty($success_message)) {
				echo '<blockquote style="background-color: #d4edda;border-left: 5px solid #28a745;padding: 10px;"><p>' . esc_html($success_message) . '</p></blockquote>';
				// Optionally, you can delete the transient after displaying it
				delete_transient('plugin_success_message');
			}

			echo $mixedLayout_safe;
		} else {
			echo 'Custom Tables: Table Not Selected.';
		}
	}
	?>
</div>