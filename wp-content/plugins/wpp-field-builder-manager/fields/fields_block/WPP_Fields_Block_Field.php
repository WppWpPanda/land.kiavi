<?php
/**
 * Class WPP_Fields_Block_Field
 *
 * Represents a block of fields with a common label.
 */

if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('WPP_Fields_Block_Field') && class_exists('WPP_Form_Field')) :

	class WPP_Fields_Block_Field extends WPP_Form_Field {

		/**
		 * Renders the field block with multiple fields inside
		 */
		public function render() {
			$this->render_wrapper_start();

			// Render the main label for the block
			if ($this->args['label']) {
				echo '<label class="wpp-fields-block-label">' . $this->args['label'] . '</label>';
			}

			echo '<div class="wpp-fields-block row">';

			// Render individual fields
			foreach ($this->args['fields'] as $field_name => $field_config) {
				$class_name = 'WPP_' . ucfirst($field_config['type']) . '_Field';

				if (class_exists($class_name)) {
					$field = new $class_name(array_merge($field_config, ['name' => $field_name]));
					$field->render();
				}
			}

			echo '</div>';

			$this->render_wrapper_end();
		}
	}

endif;