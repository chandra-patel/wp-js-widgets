=== JS Widgets ===
Contributors:      xwp, westonruter
Tags:              customizer, widgets, rest-api
Requires at least: 4.7.0
Tested up to:      4.7.0
Stable tag:        0.2.0
License:           GPLv2 or later
License URI:       http://www.gnu.org/licenses/gpl-2.0.html

The next generation of widgets in core, embracing JS for UI and powering the Widgets REST API.

== Description ==

Also could be known as Widget Customizer 2.0, Widgets 3.0, or Widgets Next Generation.

This plugin implements:

* [WP-CORE#33507](https://core.trac.wordpress.org/ticket/33507): Allow widget controls to be JS-driven.
* [WP-CORE#35574](https://core.trac.wordpress.org/ticket/35574): Add REST API JSON schema information to WP_Widget.
* [WP-API#19](https://github.com/WP-API/WP-API/issues/19): Add widget endpoints to the WP REST API.

Features:

* Widget instance settings in the Customizer are exported from PHP as regular JSON without any PHP-serialized base64-encoded `encoded_serialized_instance` anywhere to be seen.
* Widgets control forms use JS content templates instead of PHP to render the markup for each control, reducing the weight of the customizer load, especially when there are a lot of widgets in use.
* Widgets employ the JSON Schema from the REST API to define an instance with validation and sanitization of the instance properties, beyond also providing `validate` and `sanitize` methods that work on the instance array as a whole.
* A widget instance can be blocked from being saved by returning a `WP_Error` from its `validate` or `sanitize` method. For example, the RSS widget will show an error message if the feed URL provided is invalid and the widget will block from saving until the URL is corrected.
* Widgets are exposed under the `js-widgets/v1` namespace, for example to list all Recent Posts widgets via the `/js-widgets/v1/widgets/recent-posts` or to get the Text widget with the “ID” (number) of 6, `/js-widgets/v1/widgets/text/6`.
* Customizer settings for widget instances (`widget_{id_base}[{number}]`) are directly mutated via JavaScript instead of needing to do an `update-widget` Admin Ajax roundtrip; this greatly speeds up previewing.
* Widget control forms can be extended to employ any JS framework for managing the UI, allowing Backbone, React, or any other frontend technology to be used.
* Compatible with widgets stored in a custom post type instead of options, via the Widget Posts module in the [Customize Widgets Plus](https://github.com/xwp/wp-customize-widgets-plus) plugin.
* Compatible with [Customize Snapshots](https://github.com/xwp/wp-customize-snapshots), allowing changes made in the Customizer to be applied to requests for widgets via the REST API.
* Includes adaptations of all core widgets using the new `WP_JS_Widget` API.
* The adapted core widgets include additional raw data in their REST API item responses so that JS can render them client-side.
* The Notifications API is utilized to display warnings when a user attempts to provide markup in a core widget title or illegal HTML in a Text widget's content.
* The Pages widget in Core is enhanced to make use of [Customize Object Selector](https://wordpress.org/plugins/customize-object-selector/) if available to display a Select2 UI for selecting pages to exclude instead of providing page IDs.
* An bonus bundled plugin provides a “Post Collection” widget which, if the [Customize Object Selector](https://wordpress.org/plugins/customize-object-selector/) plugin is installed, will provide a UI for curating an arbitrary list of posts to display.

This plugin doesn't yet implement any widgets that use JS templating for _frontend_ rendering of the widgets. For that, please see the [Next Recent Posts Widget](https://github.com/xwp/wp-next-recent-posts-widget) plugin.

Limitations/Caveats:

* Widgets that extend `WP_JS_Widget` will not be editable from widgets admin page. A link to edit the widget in the Customizer will be displayed instead.
* Only widgets that extend `WP_JS_Widget` will be exposed via the REST API. The plugin includes a `WP_JS_Widget` adapter class which demonstrates how to adapt existing `WP_Widget` classes for the new widget functionality.

== Changelog ==

= 0.2.0 - 2017-01-02 =

* Important: Update minimum WordPress core version to 4.7.0.
* Eliminate `Form#embed` JS method in favor of just `Form#render`. Introduce `Form#destruct` to handle unmounting a rendered form.
* Implement ability for sanitize to return error/notification and display in control's notifications.
* Show warning when attempting to add HTML to widget titles and when adding illegal HTML to Text widget content. This is a UX improvement over silently failing.
* Add adapters for all of the core widgets (aside from Links). Include as much raw data as possible in the REST responses so that JS clients can construct widgets using client-side templates.
* Add integration between the Pages widget's `exclude` param and the [Customize Object Selector](https://wordpress.org/plugins/customize-object-selector/) plugin to provide a Select2 UI for selecting pages to exclude instead of listing out page IDs.
* Ensure old encoded instance data setting value format is supported (such as in starter content).
* Move Post Collection widget into separate embedded plugin so that it is not active by default.
* Inject rest_controller object dependency on `WP_JS_Widget` upon `rest_api_init`.
* Ensure that default instance values populate forms for newly-added widgets.
* Remove React/Redux for implementing the Recent Posts widget.
* Reorganize core adapter widgets and introduce `WP_Adapter_JS_Widget` class.
* Eliminate uglification and CSS minification.
* Use widget number as integer ID for widgets of a given type.
* Update integration with REST API to take advantage of sanitization callbacks being able to do validation.
* Replace Backbone implementation for Text widget with Customize `Element` implementation.
* Reduce duplication by moving methods to base classes.
* Add form field template generator helper methods.
* Implement [WP Core Trac #39389](https://core.trac.wordpress.org/ticket/39389): Scroll widget partial into view when control expanded.
* Allow widget instances to be patched without providing full instance.
* Remove prototype strict validity for REST item updates.
* Add support for validating schemas with type arrays and object types; allow strings or objects with `raw`/`rendered` properties for titles & Text widget's text field.
* Eliminate returning data from `WP_JS_Widget::render()` for client templates to render until a clear use case and pattern can be derived.

= 0.1.1 - 2016-10-03 =

* Add 100% width to object-selector.
* Fix typo in sanitizing Post Collection input.
* Fix PHP issue when attempting to assign an undefined array index to another undefined array index.
* Fix styling of post collection widget select2 component.
* Fix accounting for parse_widget_setting_id returning WP_Error not false.

= 0.1.0 - 2016-08-24 =

Initial release.
