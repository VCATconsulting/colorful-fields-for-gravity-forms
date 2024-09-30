/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./src/js/gravity-forms-js/index.js":
/*!******************************************!*\
  !*** ./src/js/gravity-forms-js/index.js ***!
  \******************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__);

(function ($) {
  // eslint-disable-next-line no-undef
  if (!gform.addAction) {
    return;
  }

  /*
   * adding setting to all fields except submit button
   */
  $(document).ready(function () {
    Object.keys(fieldSettings).forEach(key => {
      if (key !== 'submit') {
        fieldSettings[key] += ', .cffgf_label_setting, .cffgf_field_setting';
      }
    });
  });

  /*
   * load field settings
   */
  $(document).on('gform_load_field_settings', function (event, field, form) {
    $("#field_cffgf_label_color").val(field.field_cffgf_label_color);
    $("#field_cffgf_field_color").val(field.field_cffgf_field_color);
  });

  /*
      * Set the color for each field after editor is loaded.
  	 */
  $(document).on("gform_load_form_settings", function (event, form) {
    $(form.fields).each(function (index, field) {
      $("#field_" + field.id + " .gfield_label").css("color", field.field_cffgf_label_color);
      $("#field_" + field.id + " .gsection_title").css("color", field.field_cffgf_label_color);
      $("#field_" + field.id).css("background-color", field.field_cffgf_field_color);
    });
  });

  /*
   * Set the color for each field after the field is updated.
  	 */
  // eslint-disable-next-line no-undef
  gform.addAction('gform_post_set_field_property', function (name, field) {
    /*
     * Check if field is false (submit button).
     */
    if (false === field) {
      return;
    }

    /*
     * Set label color if not exists.
     */
    if (undefined === field.field_cffgf_label_color) {
      field.field_cffgf_label_color = '';
    }

    /*
     * Set field color if not exists.
     */
    if (undefined === field.field_cffgf_field_color) {
      field.field_cffgf_field_color = '';
    }
    $("#field_" + field.id + " .gfield_label").css("color", field.field_cffgf_label_color);
    $("#field_" + field.id + " .gsection_title").css("color", field.field_cffgf_label_color);
    $("#field_" + field.id).css("background-color", field.field_cffgf_field_color);

    /*
     * Reset warning if color was reset.
     */
    /* eslint-disable max-len,no-undef */
    if ('' === field.field_cffgf_label_color && '' === field.field_cffgf_field_color) {
      ResetFieldAccessibilityWarning('cffgf_label_setting');
      ResetFieldAccessibilityWarning('cffgf_field_setting');
    }
    /* eslint-enable */

    /*
     * Add warning if label color was set.
     */
    /* eslint-disable max-len,no-undef */
    if ('' !== field.field_cffgf_label_color && '' === field.field_cffgf_field_color) {
      SetFieldAccessibilityWarning('cffgf_label_setting', 'below', (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Inadequate color contrast between this label and the background may compromise visibility. Please ensure sufficient contrast for optimal readability and accessibility.', 'colorful-fields-for-gravity-forms'));
      ResetFieldAccessibilityWarning('cffgf_field_setting');
    }
    /* eslint-enable */

    /*
     * Checks the contrast and adds a warning if the contrast does not meet the WCAG requirement.
     */
    if ('' !== field.field_cffgf_label_color && '' !== field.field_cffgf_field_color || '' === field.field_cffgf_label_color && '' !== field.field_cffgf_field_color) {
      let labelColor = '';

      /*
       * Set label color to default css color if field setting is empty.
       */
      if ('#' === field.field_cffgf_label_color) {
        labelColor = $("#field_" + field.id + " .gfield_label").css("color");
        labelColor = rgb2hex(labelColor);
      } else {
        labelColor = field.field_cffgf_label_color;
      }
      const colorCcontrast = getContrast(labelColor, field.field_cffgf_field_color);

      /* eslint-disable max-len,no-undef */
      if (colorCcontrast < 4.5) {
        SetFieldAccessibilityWarning('cffgf_field_setting', 'below', (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('This color combination may be hard for people to read. Try using a brighter background color and/or a darker text color.', 'colorful-fields-for-gravity-forms'));
        ResetFieldAccessibilityWarning('cffgf_label_setting');
      } else {
        ResetFieldAccessibilityWarning('cffgf_field_setting');
      }
      /* eslint-enable */
    }
  });
  // eslint-disable-next-line no-undef
})(jQuery);

/*
 * Return the contrast ratio between 2 hex colors.
 */
function getContrast(hexColor1, hexColor2) {
  /*
   * Convert hex colors in rgb.
   */
  function hexToRgb(hex) {
    /*
     * Remove #.
     */
    hex = hex.replace('#', '');

    /*
     * Split hex values in red, green and blue.
     */
    const r = parseInt(hex.substring(0, 2), 16);
    const g = parseInt(hex.substring(2, 4), 16);
    const b = parseInt(hex.substring(4, 6), 16);

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
  function calculateContrast(rgb1, rgb2) {
    const luminance1 = calculateLuminance(rgb1);
    const luminance2 = calculateLuminance(rgb2);

    /*
     * Calculate the contrast according to the formula for relative contrast
     */
    return (Math.max(luminance1, luminance2) + 0.05) / (Math.min(luminance1, luminance2) + 0.05);
  }

  /*
   * Calculate the luminance of a color according to the formula for luminance
   */
  function calculateLuminance(rgb) {
    let R = rgb.r / 255;
    let G = rgb.g / 255;
    let B = rgb.b / 255;
    R = R <= 0.03928 ? R / 12.92 : Math.pow((R + 0.055) / 1.055, 2.4);
    G = G <= 0.03928 ? G / 12.92 : Math.pow((G + 0.055) / 1.055, 2.4);
    B = B <= 0.03928 ? B / 12.92 : Math.pow((B + 0.055) / 1.055, 2.4);
    return 0.2126 * R + 0.7152 * G + 0.0722 * B;
  }

  /*
   * Convert hex to rgb.
   */
  const rgb1 = hexToRgb(hexColor1);
  const rgb2 = hexToRgb(hexColor2);

  /*
   * Calc contrast and return.
   */
  return calculateContrast(rgb1, rgb2);
}

/*
 * Convert rgb 2 hex.
 */
const rgb2hex = rgb => `#${rgb.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/).slice(1).map(n => parseInt(n, 10).toString(16).padStart(2, '0')).join('')}`;

/***/ }),

/***/ "./src/scss/_cffgf.scss":
/*!******************************!*\
  !*** ./src/scss/_cffgf.scss ***!
  \******************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "@wordpress/i18n":
/*!******************************!*\
  !*** external ["wp","i18n"] ***!
  \******************************/
/***/ ((module) => {

module.exports = window["wp"]["i18n"];

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			var getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry need to be wrapped in an IIFE because it need to be isolated against other modules in the chunk.
(() => {
/*!**********************!*\
  !*** ./src/cffgf.js ***!
  \**********************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _scss_cffgf_scss__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./scss/_cffgf.scss */ "./src/scss/_cffgf.scss");
/* harmony import */ var _js_gravity_forms_js_index_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./js/gravity-forms-js/index.js */ "./src/js/gravity-forms-js/index.js");


})();

/******/ })()
;
//# sourceMappingURL=cffgf.js.map