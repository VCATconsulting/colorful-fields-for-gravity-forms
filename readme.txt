=== Colorful Fields for Gravity Forms ===

Contributors: VCATconsulting, shogathu, nida78
Requires at least: 5.0
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 1.3.0
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.txt

Choose colors for field labels, backgrounds, descriptions, upload hints, and sub-labels in Gravity Forms.

== Description ==

This plugin is an add-on to the [Gravity Forms](https://www.gravityforms.com/ "visit Gravity Forms website") form builder plugin.
You can set a label color and a background color for each supported field. Use **Description and sub-label color** to style descriptions, upload hints such as the maximum file size, and sub-labels such as First and Last in a Name field.


== Installation ==

1. Install and configure Gravity Forms plugin
2. Find this Colorful Fields plugin in the "Add Plugins" page within your WordPress installation or Upload the Colorful Fields plugin to your blog
3. Activate it
4. Select a field in the form editor and open the General tab. The description and sub-label color picker appears when the field has a description or shows an upload hint or sub-label.

== Screenshots ==

1. The Color for the field with contrast check.
2. The Color for the field without contrast check.
3. Frontend view of the form.
4. The Color for the field with description and sub-label color picker.

== Frequently Asked Questions ==

= Can I use any filter or action?

No, this plugin does not offer any filter or action.

= Does the plugin check color contrast? =

Yes. The editor checks label contrast and evaluates each visible description, upload hint, and sub-label against its background, including text that uses the default color. It warns when contrast falls below 4.5:1. If the background cannot be determined, it prompts you to check the contrast manually.

== Changelog ==

= 1.3.0 =

* Added a field-specific CSS fallback for WordPress 5.0 through 6.3 so background, description, upload hint, and sub-label colors can be applied without the newer WordPress HTML API.
* Fixed frontend label coloring when the label text is "0".
* Improved handling of missing editor asset metadata, a missing Composer autoloader, and invalid color values in imported fields.
* Added the **Description and sub-label color** setting for field descriptions, upload hints, and sub-labels in the editor and on published forms.
* The editor now warns about low contrast for these texts even when they use the default text color.
* Label, description, and background color pickers now show the current preview colors when field settings open or a color is reset. Resetting clears the saved override so future theme color changes still apply.

= 1.2.0 =

* FIX: Gravity Forms editor reset behavior for label and field background colors.
* Reset buttons now clear the visible color input and reset the saved field property directly.
* Removed redundant delegated JavaScript reset click handlers.

= 1.1.0 =

* optimize the code
* update contrast check
* update security
* add better assets handlers

= 1.0.4 =

* fix default background color issue (now white is default)

= 1.0.3 =

* remove unused text string

= 1.0.2 =

* update some code comments

= 1.0.1 =

* move inline JS to external file
* fix some JS errors

= 1.0.0 =

* First stable version
