<?php
/**
 * Tag List Admin Class
 *
 * Handles the admin interface for the Tag List plugin,
 * including options page and settings management.
 *
 * @package Iworks_Tag_List
 */
class iworks_tag_list_admin {

private $tag_list = null;

	/**
	 * Initialize admin functionality.
	 *
	 * Registers the admin menu hook.
	 *
	 * @return void
	 */
	public function __construct( $tag_list ) {
		$this->tag_list = $tag_list;
		add_action( 'admin_menu', array( $this, 'add_option_page' ) );
		add_filter( 'plugin_row_meta', array( $this, 'register_plugin_links' ), 10, 2 );
	}


	/**
	 * Add options page to admin menu.
	 *
	 * Registers a submenu page under the Posts menu for tag list options.
	 *
	 * @return void
	 */
	public function add_option_page() {
		add_submenu_page(
			'edit.php',
			__( 'Tag List Options', 'PLUGIN_NAME' ),
			__( 'Tag List Options', 'PLUGIN_NAME' ),
			'edit_posts',
			'iworks-tag-list',
			array( $this, 'display_options' )
		);
	}


	/**
	 * Update plugin options.
	 *
	 * Saves the submitted options to either site options or regular options
	 * depending on the WordPress installation type.
	 *
	 * @return void
	 */
	public function update_options() {
		check_admin_referer( 'tag_list_action_update' );
		if ( function_exists( 'update_site_option' ) && ( function_exists( 'is_site_admin' ) && is_site_admin() ) ) {
			update_site_option( 'tag_list_params', $_POST['tag_list'] );
		} else {
			update_option( 'tag_list_params', $_POST['tag_list'] );
		}
	}

	/**
	 * Display the options page.
	 *
	 * Renders the admin settings page with form fields for plugin options.
	 * Also checks for wp_head() in the theme's header.php file.
	 *
	 * @return void
	 */
	public function display_options() {
		// check for wp_head
		$templates   = array();
		$templates[] = 'header.php';
		$file        = file_get_contents( locate_template( $templates ) );
		// Check for wp_head
		preg_match( '/.*([\t ]wp_head\(\);).*/', $file, $matches );
		if ( sizeof( $matches ) < 2 or ! $matches[1] ) {
			echo '<div id="message" class="error"><p><strong>';
			esc_html_e( 'Warning', 'PLUGIN_NAME' );
			echo '</strong> ';
			esc_html_e( 'wp_head(); not found in your header.php file, this might mean this plugin will not work!', 'PLUGIN_NAME' );
			echo "</p></div>\n";
		}
		// Process updates, if any

		if ( isset( $_POST['action'] )
			&& ( $_POST['action'] == 'update' )
		) {
			$this->update_options();

			echo '<div class="updated">' . "\n"
			. '<p>'
			. '<strong>'
			. esc_html__( 'Options saved.', 'PLUGIN_NAME' )
			. '</strong>'
			. '</p>' . "\n"
			. '</div>' . "\n";
		}

		$options = $this->tag_list->get_options();
		// Display admin page
		?>
<div class="wrap">
	<h2><?php esc_html_e( 'Tag List Options', 'PLUGIN_NAME' ); ?></h2>
    <p><?php esc_html_e( 'Configure how the tag list shortcode behaves on your site. Use the shortcode [tag-list] in your posts or pages to display the tag list.', 'PLUGIN_NAME' ); ?></p>
	<form method="post" action="">
		<input type="hidden" name="action" value="update" />
		<input type="hidden" name="page_options" value="tag_list" />
		<?php
		if ( function_exists( 'wp_nonce_field' ) ) {
			wp_nonce_field( 'tag_list_action_update' );}
		?>
		<fieldset class="options">
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row" valign="top"><label for="position"><?php esc_html_e( 'Use default CSS?', 'PLUGIN_NAME' ); ?></label></th>
						<td>
							<ul>
								<li><input type="radio" name="tag_list[tag_list_default_css]" id="tag_list_default_css_on"
								<?php
								if ( $options['tag_list_default_css'] == 'on' ) {
									echo ' checked="checked"';}
								?>
								value="on"  /> <label for="tag_list_default_css_on"><?php esc_html_e( 'yes', 'PLUGIN_NAME' ); ?></label></li>
								<li><input type="radio" name="tag_list[tag_list_default_css]" id="tag_list_default_css_on"
								<?php
								if ( $options['tag_list_default_css'] == 'off' ) {
									echo ' checked="checked"';}
								?>
								value="off" /> <label for="tag_list_default_css_on"><?php esc_html_e( 'no', 'PLUGIN_NAME' ); ?></label></li>
							</ul>
						</td>
					</tr>
					<tr>
						<th scope="row" valign="top"><label for="position"><?php esc_html_e( 'Where should the short list be placed?', 'PLUGIN_NAME' ); ?></label></th>
						<td>
							<select name="tag_list[tag_list_position]" id="tag_list_position">
								<option value="both"
								<?php
								if ( $options['tag_list_position'] == 'both' ) {
									echo ' selected="selected"';}
								?>
								><?php esc_html_e( 'on top and on bottom (default)', 'PLUGIN_NAME' ); ?></option>
								<option value="top"
								<?php
								if ( $options['tag_list_position'] == 'top' ) {
									echo ' selected="selected"';}
								?>
								><?php esc_html_e( 'only on top', 'PLUGIN_NAME' ); ?></option>
								<option value="bottom"
								<?php
								if ( $options['tag_list_position'] == 'bottom' ) {
									echo ' selected="selected"';}
								?>
								><?php esc_html_e( 'only on bottom', 'PLUGIN_NAME' ); ?></option>
								<option value="none"
								<?php
								if ( $options['tag_list_position'] == 'hide' ) {
									echo ' selected="selected"';}
								?>
								><?php esc_html_e( 'hide', 'PLUGIN_NAME' ); ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row" valign="top"><label for="position"><?php esc_html_e( 'Use extra div width ID?', 'PLUGIN_NAME' ); ?></label></th>
						<td>
							<ul>
								<li><input type="radio" name="tag_list[tag_list_extra_div]" id="tag_list_extra_div_on"
								<?php
								if ( $options['tag_list_extra_div'] == 'on' ) {
									echo ' checked="checked"';}
								?>
								value="on"  /> <label for="tag_list_extra_div_on"><?php esc_html_e( 'yes', 'PLUGIN_NAME' ); ?></label></li>
								<li><input type="radio" name="tag_list[tag_list_extra_div]" id="tag_list_extra_div_on"
								<?php
								if ( $options['tag_list_extra_div'] == 'off' ) {
									echo ' checked="checked"';}
								?>
								value="off" /> <label for="tag_list_extra_div_on"><?php esc_html_e( 'no', 'PLUGIN_NAME' ); ?></label></li>
							</ul>
						</td>
					</tr>
					<tr>
						<th scope="row" valign="top"><label for="position"><?php esc_html_e( 'Show unused tag to?', 'PLUGIN_NAME' ); ?></label></th>
						<td>
							<ul>
								<li><input type="radio" name="tag_list[tag_list_unused_tags]" id="tag_list_unused_tags_on"
								<?php
								if ( $options['tag_list_unused_tags'] == 'on' ) {
									echo ' checked="checked"';}
								?>
								value="on"  /> <label for="tag_list_unused_tags_on"><?php esc_html_e( 'yes', 'PLUGIN_NAME' ); ?></label></li>
								<li><input type="radio" name="tag_list[tag_list_unused_tags]" id="tag_list_unused_tags_on"
								<?php
								if ( $options['tag_list_unused_tags'] == 'off' ) {
									echo ' checked="checked"';}
								?>
								value="off" /> <label for="tag_list_unused_tags_on"><?php esc_html_e( 'no (default)', 'PLUGIN_NAME' ); ?></label></li>
							</ul>
						</td>
					</tr>
					<tr>
						<th scope="row" valign="top"><label for="position"><?php esc_html_e( 'Show number of use?', 'PLUGIN_NAME' ); ?></label></th>
						<td>
							<ul>
								<li><input type="radio" name="tag_list[tag_list_number_of_use]" id="tag_list_number_of_use_on"
								<?php
								if ( $options['tag_list_number_of_use'] == 'on' ) {
									echo ' checked="checked"';}
								?>
								value="on"  /> <label for="tag_list_number_of_use_on"><?php esc_html_e( 'yes', 'PLUGIN_NAME' ); ?></label></li>
								<li><input type="radio" name="tag_list[tag_list_number_of_use]" id="tag_list_number_of_use_on"
								<?php
								if ( $options['tag_list_number_of_use'] == 'off' ) {
									echo ' checked="checked"';}
								?>
								value="off" /> <label for="tag_list_number_of_use_on"><?php esc_html_e( 'no (default)', 'PLUGIN_NAME' ); ?></label></li>
							</ul>
						</td>
					</tr>
				</tbody>
			</table>
		</fieldset>
		<p class="submit"><input type="submit" value="<?php esc_attr_e( 'Save Changes', 'PLUGIN_NAME' ); ?>" /></p>
	</form>
</div>
		<?php
	}
	/**
	 * Add settings link to plugin row.
	 *
	 * Adds a settings link to the plugin action links in the plugins list.
	 *
	 * @param array  $links Existing plugin action links
	 * @param string $file  Plugin file path
	 * @return array Modified plugin action links
	 */
	public function register_plugin_links( $links, $file ) {
		if ( preg_match( '/tag-list.php$/', $file ) ) {
			$links[] = sprintf(
				'<a href="%s">%s</a>',
				add_query_arg( 'page', 'iworks-tag-list', admin_url( 'edit.php' ) ),
				esc_html__( 'Settings', 'PLUGIN_NAME' )
			);
		}
		return $links;
	}
}
