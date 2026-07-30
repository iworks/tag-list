<?php

/**
 * Tag List Plugin Class
 *
 * Handles the tag list functionality including shortcode registration,
 * CSS output, and plugin links.
 *
 * @package Iworks_Tag_List
 */
class iworks_tag_list {

	/**
	 * Constructor.
	 *
	 * Initializes the plugin by registering hooks and loading translations.
	 *
	 * @return void
	 */
	public function __construct() {
		add_shortcode( 'tag-list', array( $this, 'get_tag_list' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'action_wp_enqueue_scripts_maybe_enqueue_css' ) );
		/**
		 * load github class
		 */
		$filename = __DIR__ . '/tag-list/class-iworks-tag-list-github.php';
		if ( is_file( $filename ) ) {
			include_once $filename;
			new iworks_tag_list_github();
		}
	}


	/**
	 * Get plugin options.
	 *
	 * Retrieves plugin options from site options or regular options,
	 * with defaults for missing values.
	 *
	 * @return array Plugin options array with keys: tag_list_position,
	 *               tag_list_default_css, tag_list_extra_div,
	 *               tag_list_unused_tags, tag_list_number_of_use
	 */
	public function get_options() {
		if ( function_exists( 'get_site_option' ) ) {
			$options = get_site_option( 'tag_list_params' );
		} else {
			$options = get_option( 'tag_list_params' );
		}
		if ( ! isset( $options['tag_list_position'] ) ) {
			$options['tag_list_position'] = 'both';
		}
		if ( ! isset( $options['tag_list_default_css'] ) ) {
			$options['tag_list_default_css'] = 'on';
		}
		if ( ! isset( $options['tag_list_extra_div'] ) ) {
			$options['tag_list_extra_div'] = 'on';
		}
		if ( ! isset( $options['tag_list_unused_tags'] ) ) {
			$options['tag_list_unused_tags'] = 'off';
		}
		if ( ! isset( $options['tag_list_number_of_use'] ) ) {
			$options['tag_list_number_of_use'] = 'off';
		}
		return $options;
	}

	/**
	 * Generate tag list HTML.
	 *
	 * Creates an alphabetical list of tags with optional table of contents.
	 *
	 * @param array $atts Shortcode attributes. Optional keys:
	 *                    - letter: Filter tags by starting letter
	 *                    - toc: Table of contents position (none|both|top|bottom)
	 * @return string HTML output of the tag list or WP_Error on failure
	 */
	public function get_tag_list( $atts ) {
		global $wpdb;
		$options = $this->get_options();
		extract(
			shortcode_atts(
				array(
					'letter' => null,
					'toc'    => 'both',
				),
				$atts
			)
		);
		$args = array(
			'orderby'        => 'name',
			'order'          => 'ASC',
			#            'fields'       => 'ids',
				'hide_empty' => $options['tag_list_unused_tags'] == 'off',
			'name__like'     => null,
			'execlude'       => null,
			'include'        => null,
			'number'         => null,
			'offset'         => 0,
			'slug'           => null,
			'hierarchical'   => false,
			'search'         => null,
		);
		if ( isset( $atts['letter'] ) && ! empty( $atts['letter'] ) ) {
			$args['name__like'] = $atts['letter'];
		}
		if ( isset( $atts['toc'] ) && ! empty( $atts['toc'] ) && preg_match( '/^(none|both|top|bottom)$/', $toc ) ) {
			$options['tag_list_position'] = $atts['toc'];
		}
		$tags    = get_tags( $args );
		$letter  = '';
		$toc     = '<ul class="tag-toc">';
		$content = '<ul class="tag-list">';
		$count   = 0;
		foreach ( $tags as $t ) {
			$l = mb_substr( $t->name, 0, 1 );
			if ( mb_strtolower( $l ) != $letter ) {
				if ( $letter != '' ) {
					$content .= '</ul>' . "\n" . '</li>' . "\n";
				}
				$letter   = mb_strtolower( $l );
				$archor   = 'tag-' . ( ( preg_match( '/[a-z0-9]/', $letter ) ) ? $letter : 'alt-' . $count );
				$toc     .= sprintf( '<li><a href="#%s">%s</a></li>', $archor, $letter );
				$content .= sprintf( '<li id="%s">' . "\n" . '<h4>%s</h4>' . "\n" . '<ul>' . "\n", $archor, $letter );
			}
			$link = get_tag_link( $t->term_id );
			if ( is_wp_error( $link ) ) {
				return $link;
			}
			$counter_string = '';
			if ( $options['tag_list_number_of_use'] == 'on' ) {
				$counter_string = sprintf( ' <small>(%d)</small>', $t->count );
			}
			$content .= sprintf( '<li><a href="%s">%s%s</a></li>' . "\n", $link, $t->name, $counter_string );
			++$count;
		}
		#
		$content .= '</ul>' . "\n" . '</li>' . "\n" . '</ul>' . "\n";
		$toc     .= '</ul>' . "\n";
		if ( preg_match( '/^(top|both)$/', $options['tag_list_position'] ) ) {
			$content = $toc . $content;
		}
		if ( preg_match( '/^(bottom|both)$/', $options['tag_list_position'] ) ) {
			$content .= $toc;
		}
		if ( $options['tag_list_extra_div'] == 'on' ) {
			return '<div id="tag-list">' . $content . '</div>';
		}
		return $content;
	}

	/**
	 * Register CSS stylesheet.
	 *
	 * Registers the plugin's CSS file with WordPress.
     * 
     * @since 2.0.0
	 *
	 * @return void
	 */
	public function action_wp_enqueue_scripts_maybe_enqueue_css() {
		$options = $this->get_options();
		if ( $options['tag_list_default_css'] == 'on' ) {
            $min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
            $file = plugin_dir_url( dirname( __FILE__, 2 ) ) . 'assets/styles/tag-list-frontend' . $min . '.css';
			wp_register_style(
				'iworks-tag-list',
				$file,
				array(),
				'PLUGIN_VERSION.BUILDTIMESTAMP'
			);
			wp_enqueue_style( 'iworks-tag-list' );
		}
	}

	/**
	 * Plugin activation hook.
	*
	 * Handles database installation and option initialization
	 * when the plugin is activated.
	*
	 * @since 1.0.0
	 * @return void
	 */
	public function register_activation_hook() {
		do_action( 'iworks/tag-list/register_activation_hook' );
	}

	/**
	 * Plugin deactivation hook.
	 *
	 * Handles cleanup tasks when the plugin is deactivated.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_deactivation_hook() {
		do_action( 'iworks/tag-list/register_deactivation_hook' );
	}
}
