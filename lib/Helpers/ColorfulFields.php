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
		add_filter( 'gform_field_container', [ $this, 'cffgf_field_container' ], 10, 2 );
		add_filter( 'gform_tooltips', [ $this, 'cffgf_add_tooltips' ] );
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

		if ( '' === $label_color || empty( $field->label ) ) {
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
	 * Update the field container in the frontend.
	 *
	 * @param string $field_container The container of the field.
	 * @param object $field The field object.
	 *
	 * @return string The container of the field.
	 */
	public function cffgf_field_container( $field_container, $field ) {
		$field_color = $this->cffgf_sanitize_color_value( $field->field_cffgf_field_color ?? '' );

		if ( '' === $field_color ) {
			return $field_container;
		}

		$field_container = str_replace( ' class="', ' class="cffgf_padding ', $field_container );

		return str_replace( '" class', '" style="background-color: ' . esc_attr( $field_color ) . '" class', $field_container );
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
		$tooltips['form_field_cffgf_label_value'] = '<h6>' . esc_html__( 'Label color', 'colorful-fields-for-gravity-forms' ) . '</h6>' . esc_html__( 'Pick a color for the label', 'colorful-fields-for-gravity-forms' );
		$tooltips['form_field_cffgf_field_value'] = '<h6>' . esc_html__( 'Background color', 'colorful-fields-for-gravity-forms' ) . '</h6>' . esc_html__( 'Pick a color for the background', 'colorful-fields-for-gravity-forms' );

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
