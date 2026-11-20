import { __ } from '@wordpress/i18n';

/* global fieldSettings, SetFieldProperty, ResetFieldAccessibilityWarning, SetFieldAccessibilityWarning */

const isValidHexColor = color => typeof color === 'string' && /^#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/.test( color );

const normalizeColor = color => isValidHexColor( color ) ? color : '';

( function ( $ ) {
	if ( typeof window.gform?.addAction !== 'function' ) {
		return;
	}

	/*
	 * adding setting to all fields except submit button
	 */
	$( document ).ready( function () {
		Object.keys(fieldSettings).forEach(key => {
			if (key !== 'submit') {
				fieldSettings[key] += ', .cffgf_label_setting, .cffgf_field_setting, .cffgf_description_setting';
			}
		});
	} );

	$( document ).on( 'input', '#field_cffgf_label_color', function () {
		SetFieldProperty( 'field_cffgf_label_color', this.value );
	} );

	$( document ).on( 'input', '#field_cffgf_field_color', function () {
		SetFieldProperty( 'field_cffgf_field_color', this.value );
	} );

	$( document ).on( 'input', '#field_cffgf_description_color', function () {
		SetFieldProperty( 'field_cffgf_description_color', this.value );
	} );

	const descriptionSelector = '.gfield_description:not(.validation_message):not(.gfield_validation_message):not(.ginput_counter), .gsection_description, .gform_fileupload_rules, .gform-field-label--type-sub, .ginput_complex label[for^="input_"]';

	function previewBackground( element ) {
		while ( element ) {
			const color = rgb2hex( window.getComputedStyle( element ).backgroundColor );
			if ( color ) {
				return color;
			}
			element = element.parentElement;
		}
		return '#ffffff';
	}

	function syncColorPickers( field ) {
		const container = document.getElementById( 'field_' + parseInt( field.id, 10 ) );

		function setPicker( property, fallback ) {
			let color = normalizeColor( field[ property ] ) || fallback;
			if ( color.length === 4 ) {
				color = '#' + color.slice( 1 ).split( '' ).map( digit => digit + digit ).join( '' );
			}
			$( '#' + property ).val( color );
		}

		const label = container?.querySelector( '.gfield_label, .gsection_title' ) || container;
		const description = container?.querySelector( descriptionSelector ) || container;
		setPicker( 'field_cffgf_label_color', label ? rgb2hex( window.getComputedStyle( label ).color ) : '' );
		setPicker( 'field_cffgf_description_color', description ? rgb2hex( window.getComputedStyle( description ).color ) : '' );
		setPicker( 'field_cffgf_field_color', previewBackground( container ) );
	}

	function updateDescription( field, showSettings ) {
		const container = $( '#field_' + parseInt( field.id, 10 ) );
		const targets = container.find( descriptionSelector );
		targets.css( 'color', normalizeColor( field.field_cffgf_description_color ) );

		if ( ! showSettings ) {
			return;
		}
		const visible = targets.filter( ':visible' ).filter( function () {
			return this.textContent.trim() && ! this.closest( '.screen-reader-text, .gform-screen-reader-text' ) &&
				window.getComputedStyle( this ).visibility === 'visible';
		} );

		const hasDescription = Boolean( ( field.description || '' ).trim() );
		$( '.cffgf_description_setting' ).toggle( hasDescription || visible.length > 0 );

		let lowContrast = false;
		let uncertain = false;
		visible.each( function () {
			const text = rgb2hex( window.getComputedStyle( this ).color );
			let background = '';
			for ( let node = this; node; node = node.parentElement ) {
				const style = window.getComputedStyle( node );
				if ( Number( style.opacity ) < 1 ) {
					background = '';
					break;
				}
				if ( background ) {
					continue;
				}
				if ( style.backgroundImage !== 'none' ) {
					break;
				}
				background = rgb2hex( style.backgroundColor );
				if ( ! background && style.backgroundColor !== 'rgba(0, 0, 0, 0)' && style.backgroundColor !== 'transparent' ) {
					break;
				}
			}
			if ( text && background ) {
				lowContrast = lowContrast || getContrast( text, background ) < 4.5;
			} else {
				uncertain = true;
			}
		} );
		if ( lowContrast || uncertain ) {
			const message = lowContrast
				? __( 'A description, upload hint or sub-label has a contrast ratio below 4.5:1.', 'colorful-fields-for-gravity-forms' )
				: __( 'Check the contrast of descriptions, upload hints and sub-labels against their background.', 'colorful-fields-for-gravity-forms' );
			SetFieldAccessibilityWarning( 'cffgf_description_setting', 'below', message );
		} else {
			ResetFieldAccessibilityWarning( 'cffgf_description_setting' );
		}
	}

	/*
	 * load field settings
	 */
	$( document ).on( 'gform_load_field_settings', function ( event, field ) {
		updateDescription( field, true );
		syncColorPickers( field );
	} );

	/*
     * Set the color for each field after editor is loaded.
 	 */
	$( document ).on( "gform_load_form_settings", function ( event, form ) {
		$( form.fields ).each( function ( index, field ) {
			updateDescription( field, false );
			const labelColor = normalizeColor( field.field_cffgf_label_color );
			const fieldColor = normalizeColor( field.field_cffgf_field_color );
			const fieldSelector = "#field_" + parseInt( field.id, 10 );

			$( fieldSelector + " .gfield_label" ).css( "color", labelColor );
			$( fieldSelector + " .gsection_title" ).css( "color", labelColor );
			$( fieldSelector ).css( "background-color", fieldColor );

		} );
	} );

	/*
	 * Set the color for each field after the field is updated.
 	 */
	// eslint-disable-next-line no-undef
	gform.addAction( 'gform_post_set_field_property', function ( name, field) {
		/*
		 * Check if field is false (submit button).
		 */
		if ( false === field) {
			return;
		}

		/*
		 * Set label color if not exists.
		 */
		if ( undefined === field.field_cffgf_label_color ) {
			field.field_cffgf_label_color = '' ;
		}

		/*
		 * Set field color if not exists.
		 */
		if ( undefined === field.field_cffgf_field_color) {
			field.field_cffgf_field_color = '';
		}

		field.field_cffgf_label_color = normalizeColor( field.field_cffgf_label_color );
		field.field_cffgf_field_color = normalizeColor( field.field_cffgf_field_color );

		const fieldSelector = "#field_" + parseInt( field.id, 10 );

		$( fieldSelector + " .gfield_label" ).css( "color", field.field_cffgf_label_color );
		$( fieldSelector + " .gsection_title" ).css( "color", field.field_cffgf_label_color );
		$( fieldSelector ).css( "background-color", field.field_cffgf_field_color );
		updateDescription( field, true );
		if ( name === 'field_cffgf_label_color' || name === 'field_cffgf_description_color' || name === 'field_cffgf_field_color' ) {
			syncColorPickers( field );
		}

		/*
		 * Reset warning if color was reset.
		 */
		/* eslint-disable max-len,no-undef */
		if ( '' === field.field_cffgf_label_color && '' === field.field_cffgf_field_color ) {
			ResetFieldAccessibilityWarning( 'cffgf_label_setting' );
			ResetFieldAccessibilityWarning( 'cffgf_field_setting' );
		}
		/* eslint-enable */

		/*
		 * Add warning if label color was set.
		 */
		/* eslint-disable max-len,no-undef */
		if ( '' !== field.field_cffgf_label_color && '' === field.field_cffgf_field_color ) {
			SetFieldAccessibilityWarning( 'cffgf_label_setting', 'below', __( 'Inadequate color contrast between this label and the background may compromise visibility. Please ensure sufficient contrast for optimal readability and accessibility.', 'colorful-fields-for-gravity-forms' ) );
			ResetFieldAccessibilityWarning( 'cffgf_field_setting' );
		}
		/* eslint-enable */

		/*
		 * Checks the contrast and adds a warning if the contrast does not meet the WCAG requirement.
		 */
		if ( '' !== field.field_cffgf_field_color ) {
			ResetFieldAccessibilityWarning( 'cffgf_label_setting' );

			let labelColor = '';

			/*
			 * Set label color to default css color if field setting is empty.
			 */
			if ( '' === field.field_cffgf_label_color ) {
				labelColor = $( fieldSelector + " .gfield_label" ).css( "color" );
				labelColor = rgb2hex( labelColor );
			} else {
				labelColor = field.field_cffgf_label_color;
			}

			if ( ! isValidHexColor( labelColor ) || ! isValidHexColor( field.field_cffgf_field_color ) ) {
				ResetFieldAccessibilityWarning( 'cffgf_field_setting' );
				return;
			}

			const colorContrast = getContrast( labelColor, field.field_cffgf_field_color );

			/* eslint-disable max-len,no-undef */
			if ( colorContrast < 4.5 ) {
				SetFieldAccessibilityWarning( 'cffgf_field_setting', 'below', __( 'This color combination may be hard for people to read. Try using a brighter background color and/or a darker text color.', 'colorful-fields-for-gravity-forms' ) );
			} else {
				ResetFieldAccessibilityWarning( 'cffgf_field_setting' );
			}
			/* eslint-enable */
		}
	} );
// eslint-disable-next-line no-undef
} )( jQuery );

/*
 * Return the contrast ratio between 2 hex colors.
 */
function getContrast( hexColor1, hexColor2 ) {
	/*
	 * Convert hex colors in rgb.
	 */
	function hexToRgb( hex ) {
		if ( ! isValidHexColor( hex ) ) {
			return null;
		}

		/*
		 * Remove #.
		 */
		hex = hex.replace( '#', '' );

		if ( 3 === hex.length ) {
			hex = hex.split( '' ).map( char => char + char ).join( '' );
		}

		/*
		 * Split hex values in red, green and blue.
		 */
		const r = parseInt( hex.substring( 0, 2 ), 16 );
		const g = parseInt( hex.substring( 2, 4 ), 16 );
		const b = parseInt( hex.substring( 4, 6 ), 16 );

		/*
	     * Return object with rgb values.
	     */
		return {
			r,
			g,
			b
		};
	}

	/*
	 * Calculate the contrast based on rgb values.
	 */
	function calculateContrast( rgb1, rgb2 ) {
		const luminance1 = calculateLuminance( rgb1 );
		const luminance2 = calculateLuminance( rgb2 );

		/*
		 * Calculate the contrast according to the formula for relative contrast
		 */
		return ( Math.max( luminance1, luminance2 ) + 0.05 ) / ( Math.min( luminance1, luminance2 ) + 0.05 );
	}

	/*
	 * Calculate the luminance of a color according to the formula for luminance
	 */
	function calculateLuminance( rgb ) {
		let R = rgb.r / 255;
		let G = rgb.g / 255;
		let B = rgb.b / 255;

		R = ( R <= 0.03928 ) ? R / 12.92 : Math.pow( ( R + 0.055 ) / 1.055, 2.4 );
		G = ( G <= 0.03928 ) ? G / 12.92 : Math.pow( ( G + 0.055 ) / 1.055, 2.4 );
		B = ( B <= 0.03928 ) ? B / 12.92 : Math.pow( ( B + 0.055 ) / 1.055, 2.4 );

		return 0.2126 * R + 0.7152 * G + 0.0722 * B;
	}

	/*
	 * Convert hex to rgb.
	 */
	const rgb1 = hexToRgb( hexColor1 );
	const rgb2 = hexToRgb( hexColor2 );

	if ( ! rgb1 || ! rgb2 ) {
		return 1;
	}

	/*
	 * Calc contrast and return.
	 */
	return calculateContrast( rgb1, rgb2 );
}

/*
 * Convert rgb 2 hex.
 */
const rgb2hex = ( rgb ) => {
	const match = String( rgb || '' ).match( /^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/ );

	if ( ! match ) {
		return '';
	}

	return `#${ match
		.slice( 1 )
		.map( n => parseInt( n, 10 )
			.toString( 16 )
			.padStart( 2, '0' ) )
		.join( '' )
	}`;
};
