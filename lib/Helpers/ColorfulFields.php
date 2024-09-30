<?php
/**
 * Class to add coloful fields to form fields.
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
		add_action( 'gform_field_standard_settings', [ $this, 'cffgf_add_standard_field' ], 10, 2 );
		add_filter( 'gform_field_content', [ $this, 'cffgf_frontend_labels' ], 10, 5 );
		add_filter( 'gform_field_container', [ $this, 'cffgf_field_container' ], 10, 6 );
		add_filter( 'gform_tooltips', [ $this, 'cffgf_add_tooltips' ] );
	}

	/**
	 * Update the label color in the frontend.
	 *
	 * @param string $content The content of the field.
	 * @param object $field The field object.
	 * @param string $value The value of the field.
	 * @param int    $lead_id The ID of the lead.
	 * @param int    $form_id The ID of the form.
	 *
	 * @return string The content of the field.
	 */
	public function cffgf_frontend_labels( $content, $field, $value, $lead_id, $form_id ) {
		if ( ! is_admin() ) {
			if ( isset( $field->field_cffgf_label_color ) && ! empty( $field->field_cffgf_label_color ) ) {
				if ( 1 === (int) $field->isRequired ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
						$content = str_replace( $field->label . '<span class="gfield_required">', '<span class="cffgf-label" style="color: ' . $field->field_cffgf_label_color . '">' . $field->label . '</span><span class="gfield_required">', $content );
				} else {
					if ( 'list' === $field->type ) {
						$content = str_replace( $field->label . '</legend><div', '<span class="cffgf-label" style="color: ' . $field->field_cffgf_label_color . '">' . $field->label . '</span></legend><div', $content );
					} else {
						$content = str_replace( $field->label, '<span class="cffgf-label" style="color: ' . $field->field_cffgf_label_color . '">' . $field->label . '</span>', $content );
					}
				}
			}
		}

		return $content;
	}

	/**
	 * Update the field container in the frontend.
	 *
	 * @param string $field_container The container of the field.
	 * @param object $field The field object.
	 * @param object $form The form object.
	 * @param string $css_class The CSS class of the field.
	 * @param string $style The style of the field.
	 * @param string $field_content The content of the field.
	 *
	 * @return string The container of the field.
	 */
	public function cffgf_field_container( $field_container, $field, $form, $css_class, $style, $field_content ) {
		if ( ! empty( $field->field_cffgf_field_color ) ) {
			$field_container = str_replace( ' class="', ' class="cffgf_padding ', $field_container );

			return str_replace( '" class', '" style="background-color: ' . $field->field_cffgf_field_color . '" class', $field_container );
		}

		return $field_container;
	}

	/**
	 * Add the field to the form editor.
	 *
	 * @param int $position The position of the field.
	 * @param int $form_id The ID of the form.
	 */
	public function cffgf_add_standard_field( $position, $form_id ) {
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
						id="field_cffgf_label_color"
						oninput="SetFieldProperty('field_cffgf_label_color', this.value);">
					<input
						type="button" id="field_cffgf_label_reset_color"
						value="<?php echo esc_html__( 'Reset label color', 'colorful-fields-for-gravity-forms' ); ?>"
						onClick="SetFieldProperty('field_cffgf_label_color', '');">
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
						id="field_cffgf_field_color"
						oninput="SetFieldProperty('field_cffgf_field_color', this.value)">
					<input
						type="button" id="field_cffgf_field_reset_color"
						value="<?php echo esc_html__( 'Reset background color', 'colorful-fields-for-gravity-forms' ); ?>"
						onClick="SetFieldProperty('field_cffgf_field_color', '');">
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
}
