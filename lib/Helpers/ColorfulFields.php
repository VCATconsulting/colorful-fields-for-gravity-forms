<?php
/**
 * Class to add colorful fields to form fields.
 *
 * @package CFFGF\Helpers
 */

namespace CFFGF\Helpers;

/**
 * Class ColorfulFields
 */
class ColorfulFields {

	/**
	 * Init all filter and action hooks so that they can be used.
	 *
	 * @see https://wordpress.org/gutenberg/handbook/blocks/writing-your-first-block-type/#enqueuing-block-scripts
	 */
	public function init() {
		add_action( 'gform_field_standard_settings', [ $this, 'cffgf_add_standard_field' ], 10, 1 );
		add_filter( 'gform_field_content', [ $this, 'cffgf_frontend_labels' ], 10, 2 );
		if ( method_exists( '\WP_HTML_Tag_Processor', 'has_class' ) ) {
			add_filter( 'gform_field_content', [ $this, 'cffgf_frontend_description' ], 10, 5 );
			add_filter( 'gform_field_container', [ $this, 'cffgf_field_container' ], 10, 2 );
		} else {
			add_filter( 'gform_get_form_filter', [ $this, 'cffgf_legacy_form_colors' ], 10, 2 );
			add_filter( 'gform_field_css_class', [ $this, 'cffgf_legacy_padding_class' ], 10, 2 );
		}
		add_filter( 'gform_tooltips', [ $this, 'cffgf_add_tooltips' ] );
	}

	/**
	 * Add field-specific styles on WordPress versions without the required HTML API.
	 *
	 * @param string $html The rendered form.
	 * @param array  $form The form settings.
	 *
	 * @return string The rendered form with its color rules.
	 */
	public function cffgf_legacy_form_colors( $html, $form ) {
		$rules = [];
		foreach ( $form['fields'] as $field ) {
			$suffix      = sprintf( '%d_%d', $form['id'], $field->id );
			$container   = '#field_' . $suffix;
			$background  = $this->cffgf_sanitize_color_value( $field->field_cffgf_field_color ?? '' );
			$description = $this->cffgf_sanitize_color_value( $field->field_cffgf_description_color ?? '' );

			if ( '' !== $background ) {
				$rules[] = $container . '{background-color:' . $background . '}';
			}
			if ( '' !== $description ) {
				$selectors = [
					$container . ' #gfield_description_' . $suffix,
					$container . ' .gform_fileupload_rules',
					$container . ' .gform-field-label--type-sub',
					$container . ' .ginput_complex label[for^="input_' . $suffix . '_"]',
				];
				if ( 'section' === $field->type ) {
					$selectors[] = $container . ' .gsection_description';
				}
				$rules[] = implode( ',', $selectors ) . '{color:' . $description . '}';
			}
		}

		return $rules ? '<style>' . implode( "\n", $rules ) . '</style>' . $html : $html;
	}

	/**
	 * Add the padding class when a field has a background color.
	 *
	 * @param string $classes The field container classes.
	 * @param object $field The field object.
	 *
	 * @return string The field container classes.
	 */
	public function cffgf_legacy_padding_class( $classes, $field ) {
		return '' === $this->cffgf_sanitize_color_value( $field->field_cffgf_field_color ?? '' ) ? $classes : $classes . ' cffgf_padding';
	}

	/**
	 * Update the label color in the frontend.
	 *
	 * @param string $content The content of the field.
	 * @param object $field The field object.
	 *
	 * @return string The content of the field.
	 */
	public function cffgf_frontend_labels( $content, $field ) {
		if ( is_admin() ) {
			return $content;
		}

		$label_color = $this->cffgf_sanitize_color_value( $field->field_cffgf_label_color ?? '' );

		if ( '' === $label_color || '' === (string) ( $field->label ?? '' ) ) {
			return $content;
		}

		$label      = esc_html( $field->label );
		$label_html = '<span class="cffgf-label" style="color: ' . esc_attr( $label_color ) . '">' . $label . '</span>';

		$pattern = '/(<(?:label|legend|h[1-6])\b[^>]*\bclass=(["\'])[^"\']*\b(?:gfield_label|gsection_title)\b[^"\']*\2[^>]*>)\s*' . preg_quote( $label, '/' ) . '/';

		$updated_content = preg_replace_callback(
			$pattern,
			static function ( $matches ) use ( $label_html ) {
				return $matches[1] . $label_html;
			},
			$content,
			1
		);

		return null === $updated_content ? $content : $updated_content;
	}

	/**
	 * Apply the description color to descriptions, upload rules and sub-labels.
	 *
	 * @param string $content The content of the field.
	 * @param object $field The field object.
	 * @param mixed  $value The field value.
	 * @param int    $entry_id The entry ID.
	 * @param int    $form_id The form ID.
	 *
	 * @return string The content of the field.
	 */
	public function cffgf_frontend_description( $content, $field, $value, $entry_id, $form_id ) {
		$color = $this->cffgf_sanitize_color_value( $field->field_cffgf_description_color ?? '' );
		if ( is_admin() || '' === $color || ! class_exists( '\WP_HTML_Tag_Processor' ) ) {
			return $content;
		}

		$html = new \WP_HTML_Tag_Processor( $content );
		$id   = sprintf( 'gfield_description_%d_%d', $form_id, $field->id );
		while ( $html->next_tag() ) {
			$is_sublabel = $html->has_class( 'gform-field-label--type-sub' ) || (
				'LABEL' === $html->get_tag() &&
				preg_match( sprintf( '/^input_%d_%d_\d+$/D', $form_id, $field->id ), (string) $html->get_attribute( 'for' ) )
			);
			if ( $id !== $html->get_attribute( 'id' ) && ! $html->has_class( 'gform_fileupload_rules' ) &&
				! $is_sublabel && ! ( 'section' === $field->type && $html->has_class( 'gsection_description' ) ) ) {
				continue;
			}
			$style = $html->get_attribute( 'style' );
			$html->set_attribute( 'style', ( is_string( $style ) ? rtrim( $style, '; ' ) . '; ' : '' ) . 'color: ' . $color . ';' );
		}
		return $html->get_updated_html();
	}

	/**
	 * Update the field container in the frontend.
	 *
	 * @param string $field_container The container of the field.
	 * @param object $field The field object.
	 *
	 * @return string The container of the field.
	 */
	public function cffgf_field_container( $field_container, $field ) {
		$field_color = $this->cffgf_sanitize_color_value( $field->field_cffgf_field_color ?? '' );

		if ( '' === $field_color || ! class_exists( '\WP_HTML_Tag_Processor' ) ) {
			return $field_container;
		}

		$container = new \WP_HTML_Tag_Processor( $field_container );
		if ( ! $container->next_tag() ) {
			return $field_container;
		}

		$style = $container->get_attribute( 'style' );
		$style = is_string( $style ) ? rtrim( $style, "; \t\r\n" ) : '';
		$container->add_class( 'cffgf_padding' );
		$container->set_attribute( 'style', ( '' === $style ? '' : $style . '; ' ) . 'background-color: ' . $field_color . ';' );

		return $container->get_updated_html();
	}

	/**
	 * Add the field to the form editor.
	 *
	 * @param int $position The position of the field.
	 */
	public function cffgf_add_standard_field( $position ) {
		if ( 0 === $position ) {
			?>
			<li class="cffgf_label_setting field_setting">
				<label class="section_label" for="field_cffgf_label_color">
					<?php echo esc_html__( 'Choose a color for the label', 'colorful-fields-for-gravity-forms' ); ?>
					<?php gform_tooltip( 'form_field_cffgf_label_value' ); ?>
				</label>
				<div class="cffgf_button_wrapper">
					<input
						type="color"
						id="field_cffgf_label_color">
					<input
						type="button" id="field_cffgf_label_reset_color"
						value="<?php echo esc_html__( 'Reset label color', 'colorful-fields-for-gravity-forms' ); ?>"
						onClick="document.getElementById('field_cffgf_label_color').value = ''; SetFieldProperty('field_cffgf_label_color', '');">
				</div>
			</li>
			<li class="cffgf_description_setting field_setting">
				<label class="section_label" for="field_cffgf_description_color">
					<?php echo esc_html__( 'Choose a color for description and sub-label', 'colorful-fields-for-gravity-forms' ); ?>
					<?php gform_tooltip( 'form_field_cffgf_description_value' ); ?>
				</label>
				<div class="cffgf_button_wrapper">
					<input type="color" id="field_cffgf_description_color">
					<input type="button" value="<?php echo esc_attr__( 'Reset description and sub-label color', 'colorful-fields-for-gravity-forms' ); ?>"
						onClick="document.getElementById('field_cffgf_description_color').value = ''; SetFieldProperty('field_cffgf_description_color', '');">
				</div>
			</li>
			<li class="cffgf_field_setting field_setting">
				<label class="section_label" for="field_cffgf_field_color">
					<?php echo esc_html__( 'Choose a background color for the field', 'colorful-fields-for-gravity-forms' ); ?>
					<?php gform_tooltip( 'form_field_cffgf_field_value' ); ?>
				</label>
				<div class="cffgf_button_wrapper">
					<input
						type="color"
						id="field_cffgf_field_color">
					<input
						type="button" id="field_cffgf_field_reset_color"
						value="<?php echo esc_html__( 'Reset background color', 'colorful-fields-for-gravity-forms' ); ?>"
						onClick="document.getElementById('field_cffgf_field_color').value = ''; SetFieldProperty('field_cffgf_field_color', '');">
				</div>
			</li>
			<?php
		}
	}

	/**
	 * Add the tooltip to the color.
	 *
	 * @param array $tooltips The array of tooltips.
	 *
	 * @return array The array of tooltips.
	 */
	public function cffgf_add_tooltips( $tooltips ) {
		$tooltips['form_field_cffgf_description_value'] = '<h6>' . esc_html__( 'Description and sub-label color', 'colorful-fields-for-gravity-forms' ) . '</h6>' . esc_html__( 'Pick a color for descriptions, upload hints and sub-labels.', 'colorful-fields-for-gravity-forms' );
		$tooltips['form_field_cffgf_label_value']       = '<h6>' . esc_html__( 'Label color', 'colorful-fields-for-gravity-forms' ) . '</h6>' . esc_html__( 'Pick a color for the label', 'colorful-fields-for-gravity-forms' );
		$tooltips['form_field_cffgf_field_value']       = '<h6>' . esc_html__( 'Background color', 'colorful-fields-for-gravity-forms' ) . '</h6>' . esc_html__( 'Pick a color for the background', 'colorful-fields-for-gravity-forms' );

		return $tooltips;
	}

	/**
	 * Sanitize a color value used by Colorful Fields.
	 *
	 * @param mixed $value Raw color value.
	 *
	 * @return string Sanitized hex color or empty string.
	 */
	private function cffgf_sanitize_color_value( $value ) {
		$value = is_string( $value ) ? trim( $value ) : '';

		if ( '' === $value ) {
			return '';
		}

		return preg_match( '/^#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $value ) ? $value : '';
	}
}
