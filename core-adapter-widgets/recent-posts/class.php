<?php
/**
 * Class WP_JS_Widget_Recent_Posts.
 *
 * @package JS_Widgets
 */

/**
 * Class WP_JS_Widget_Recent_Posts
 *
 * @package JS_Widgets
 */
class WP_JS_Widget_Recent_Posts extends WP_Adapter_JS_Widget {

	/**
	 * WP_JS_Widget_Recent_Posts constructor.
	 *
	 * @param JS_Widgets_Plugin      $plugin         Plugin.
	 * @param WP_Widget_Recent_Posts $adapted_widget Adapted/wrapped core widget.
	 */
	public function __construct( JS_Widgets_Plugin $plugin, WP_Widget_Recent_Posts $adapted_widget ) {
		parent::__construct( $plugin, $adapted_widget );
	}

	/**
	 * Get instance schema properties.
	 *
	 * @return array Schema.
	 */
	public function get_item_schema() {
		$schema = array_merge(
			parent::get_item_schema(),
			array(
				'number' => array(
					'description' => __( 'The number of posts to display.', 'js-widgets' ),
					'type' => 'integer',
					'context' => array( 'view', 'edit', 'embed' ),
					'default' => 5,
					'minimum' => 1,
					'arg_options' => array(
						'validate_callback' => 'rest_validate_request_arg',
					),
				),
				'show_date' => array(
					'description' => __( 'Whether the date should be shown.', 'js-widgets' ),
					'type' => 'boolean',
					'default' => false,
					'context' => array( 'view', 'edit', 'embed' ),
					'arg_options' => array(
						'validate_callback' => 'rest_validate_request_arg',
					),
				),
				'posts' => array(
					'description' => __( 'The IDs for the recent posts.', 'js-widgets' ),
					'type' => 'array',
					'items' => array(
						'type' => 'integer',
					),
					'context' => array( 'view', 'edit', 'embed' ),
					'readonly' => true,
					'default' => array(),
				),
			)
		);
		return $schema;
	}

	/**
	 * Render a widget instance for a REST API response.
	 *
	 * Map the instance data to the REST resource fields and add rendered fields.
	 * The Text widget stores the `content` field in `text` and `auto_paragraph` in `filter`.
	 *
	 * @inheritdoc
	 *
	 * @param array           $instance Raw database instance.
	 * @param WP_REST_Request $request  REST request.
	 * @return array Widget item.
	 */
	public function prepare_item_for_response( $instance, $request ) {
		$schema = $this->get_item_schema();
		$instance = array_merge( $this->get_default_instance(), $instance );

		$number = max( intval( $instance['number'] ), $schema['number']['minimum'] );

		/** This filter is documented in src/wp-includes/widgets/class-wp-widget-recent-posts.php */
		$query = new WP_Query( apply_filters( 'widget_posts_args', array(
			'posts_per_page' => $number,
			'no_found_rows' => true,
			'post_status' => 'publish',
			'ignore_sticky_posts' => true,
			'update_post_meta_cache' => false,
			'update_term_meta_cache' => false,
		) ) );

		$item = array_merge(
			parent::prepare_item_for_response( $instance, $request ),
			array(
				'number' => $number,
				'show_date' => (bool) $instance['show_date'],
				'posts' => wp_list_pluck( $query->posts, 'ID' ),
			)
		);

		return $item;
	}

	/**
	 * Prepare links for the response.
	 *
	 * @param WP_REST_Response           $response   Response.
	 * @param WP_REST_Request            $request    Request.
	 * @param JS_Widgets_REST_Controller $controller Controller.
	 * @return array Links for the given post.
	 */
	public function get_rest_response_links( $response, $request, $controller ) {
		$links = array();

		$links['wp:post'] = array();
		foreach ( $response->data['posts'] as $post_id ) {
			$post = get_post( $post_id );
			if ( empty( $post ) ) {
				continue;
			}
			$obj = get_post_type_object( $post->post_type );
			if ( empty( $obj ) ) {
				continue;
			}

			$rest_base = ! empty( $obj->rest_base ) ? $obj->rest_base : $obj->name;
			$base = sprintf( '/wp/v2/%s', $rest_base );

			$links['wp:post'][] = array(
				'href'       => rest_url( trailingslashit( $base ) . $post_id ),
				'embeddable' => true,
				'post_type'  => $post->post_type,
			);
		}
		return $links;
	}

	/**
	 * Get configuration data for the form.
	 *
	 * @return array
	 */
	public function get_form_args() {
		$item_schema = $this->get_item_schema();
		$args = parent::get_form_args();
		$args['minimum_number'] = $item_schema['number']['minimum'];
		$args['l10n'] = array_merge(
			$args['l10n'],
			array(
				'text_unfiltered_html_invalid' => __( 'Protected HTML such as script tags will be stripped from the content.', 'js-widgets' ),
			)
		);
		return $args;
	}

	/**
	 * Render JS Template.
	 */
	public function form_template() {
		$item_schema = $this->get_item_schema();
		?>
		<script id="tmpl-customize-widget-form-<?php echo esc_attr( $this->id_base ) ?>" type="text/template">
			<?php
			$this->render_title_form_field_template( array(
				'placeholder' => $item_schema['title']['properties']['raw']['default'],
			) );
			$this->render_form_field_template( array(
				'name' => 'number',
				'label' => __( 'Number of posts to show:', 'default' ),
				'type' => 'number',
				'min' => 1,
				'step' => 1,
			) );
			$this->render_form_field_template( array(
				'name' => 'show_date',
				'label' => __( 'Display post date?', 'default' ),
				'type' => 'checkbox',
				'min' => 1,
				'step' => 1,
			) );
			?>
		</script>
		<?php
	}
}
