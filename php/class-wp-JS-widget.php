<?php
/**
 * Class WP_Customize_Widget.
 *
 * @package JSWidgets
 */

/**
 * Class WP_Customize_Widget.
 *
 * @package JSWidgets
 */
abstract class WP_JS_Widget extends WP_Widget {

	/**
	 * Widget constructor.
	 *
	 * @param string $id_base         Optional Base ID for the widget, lowercase and unique. If left empty,
	 *                                a portion of the widget's class name will be used Has to be unique.
	 * @param string $name            Name for the widget displayed on the configuration page.
	 * @param array  $widget_options  Optional. Widget options. See {@see wp_register_sidebar_widget()} for
	 *                                information on accepted arguments. Default empty array.
	 * @param array  $control_options Optional. Widget control options. See {@see wp_register_widget_control()}
	 *                                for information on accepted arguments. Default empty array.
	 */
	public function __construct( $id_base = null, $name = null, $widget_options = array(), $control_options = array() ) {
		if ( ! isset( $name ) ) {
			$name = $this->name;
		}
		if ( ! isset( $id_base ) ) {
			$id_base = $this->id_base;
		}
		$widget_options = array_merge(
			array(
				'customize_selective_refresh' => true,
			),
			$widget_options
		);

		if ( ! empty( $this->widget_options ) ) {
			$widget_options = array_merge( $this->widget_options, $widget_options );
		}
		if ( ! empty( $this->control_options ) ) {
			$control_options = array_merge( $this->control_options, $control_options );
		}
		parent::__construct( $id_base, $name, $widget_options, $control_options );
	}

	/**
	 * Get instance schema.
	 *
	 * Subclasses are required to implement this method since it is used for sanitization.
	 *
	 * @return array
	 */
	abstract public function get_instance_schema();

	/**
	 * Prepare links for the response.
	 *
	 * Subclasses should override this method to provide links as appropriate.
	 *
	 * @param int             $widget_number Widget number.
	 * @param array           $instance Instance data.
	 * @param WP_REST_Request $request  Request.
	 * @return array Links for the given post.
	 */
	public function get_rest_response_links( $widget_number, $instance, $request ) {
		unset( $widget_number, $instance, $request );
		return array();
	}

	/**
	 * Enqueue scripts needed for the control.s
	 */
	public function enqueue_control_scripts() {}

	/**
	 * Render the form.
	 *
	 * This renders an empty string if not in the Customizer since the form is
	 * injected via a JS template. On the widgets admin page, the form displays
	 * a deep link to the Customizer control for the widget.
	 *
	 * @param array $instance Instance.
	 * @return string
	 */
	final public function form( $instance ) {
		global $wp_customize;
		unset( $instance );

		if ( empty( $wp_customize ) ) {
			// Note that %s used instead of %d for number because widget "template" sets $this->number to __i__.
			$customize_id = sprintf( 'widget_%s[%s]', $this->id_base, $this->number );
			$customize_url = add_query_arg( array( 'autofocus[control]' => $customize_id ), wp_customize_url() );
			?>
			<p>
				<?php echo sprintf( __( 'This widget can only be <a href="%s">edited in the Customizer</a>.' ), esc_url( $customize_url ) ); // WPCS: xss ok. ?>
			</p>
			<?php
			return 'noform';
		}
		return '';
	}

	/**
	 * Updates a particular instance of a widget.
	 *
	 * This method is now deprecated in favor of `WP_Customize_Widget::sanitize()`,
	 * as `sanitize` is a more accurate name than `update` for what this method does.
	 * The actual logic for updating the instance value into the database is directed
	 * by `WP_Customize_Setting::update()`. The `WP_Widget::update()` method merely
	 * sanitizes and should not have any side-effects.
	 *
	 * @inheritdoc
	 * @see WP_Customize_Setting::update()
	 * @see WP_Customize_Setting::save()
	 *
	 * @param array $new_instance New settings for this instance as input by the user via `WP_Widget::form()`.
	 * @param array $old_instance Old settings for this instance.
	 *
	 * @return array Settings to save or bool false to cancel saving.
	 */
	final public function update( $new_instance, $old_instance = array() ) {
		return $this->sanitize( $new_instance, array(
			'old_instance' => $old_instance,
		) );
	}

	/**
	 * Sanitize instance data.
	 *
	 * This function should check that `$new_instance` is set correctly. The newly-calculated
	 * value of `$instance` should be returned. If anything other than an `array` is returned,
	 * the instance won't be saved/updated. By default this method sanitizes the data via the
	 * JSON schema returned by `WP_Customize_Widget::get_instance_schema()`.
	 *
	 * @param array $new_instance  New instance.
	 * @param array $args {
	 *     Additional context for sanitization.
	 *
	 *     @type array                $old_instance  Old instance.
	 *     @type WP_Customize_Setting $setting       Setting.
	 *     @type bool                 $strict        Validate.
	 * }
	 *
	 * @return array|null|WP_Error Array instance if sanitization (and validation) passed. Returns `WP_Error` on failure if `$strict`, and `null` otherwise.
	 */
	public function sanitize( $new_instance, $args = array() ) {
		unset( $args );

		// @todo This should look at the instance schema and validate based on that.

		return $new_instance;
	}

	/**
	 * Echoes the widget content.
	 *
	 * This method is now deprecated in favor of `WP_Customize_Widget::render()`,
	 * as `render` is a more accurate name than `widget` for what this method does.
	 *
	 * @todo The output of this function could be used in a REST API response as the rendered property of the entire widget.
	 *
	 * @inheritdoc
	 *
	 * @access public
	 *
	 * @param array $args {
	 *     Display arguments.
	 *
	 *     @type string $before_title  Before title.
	 *     @type string $after_title   After title.
	 *     @type string $before_widget Before widget.
	 *     @type string $after_widget  After widget.
	 * }
	 * @param array $instance The settings for the particular instance of the widget.
	 */
	final public function widget( $args, $instance ) {
		ob_start();
		$data = $this->render( $args, $instance );
		$rendered = ob_get_clean();
		if ( $rendered ) {
			echo $rendered; // XSS OK.
		} else {
			echo $args['before_widget']; // WPCS: XSS OK.
			echo '<script type="application/json">';
			echo wp_json_encode( $data );
			echo '</script>';
			echo $args['after_widget']; // WPCS: XSS OK.
		}
	}

	/**
	 * Render the widget content or return the data for the widget to render.
	 *
	 * @param array $args {
	 *     Display arguments.
	 *
	 *     @type string $before_title  Before title.
	 *     @type string $after_title   After title.
	 *     @type string $before_widget Before widget.
	 *     @type string $after_widget  After widget.
	 * }
	 * @param array $instance The settings for the particular instance of the widget.
	 * @return void|array Return nothing if rendering, otherwise return data to be rendered on the client via JS template.
	 */
	abstract public function render( $args, $instance );

	/**
	 * Render JS Template.
	 *
	 * @todo The JS template needs to be agnostic.
	 * @todo Should this even be here? Should it be in a Customizer control instead.
	 */
	public function form_template() {}

	/**
	 * Get data to pass to the JS form.
	 *
	 * This can include information such as whether the user can do `unfiltered_html`.
	 *
	 * @return array
	 */
	public function get_form_args() {
		return array();
	}
}