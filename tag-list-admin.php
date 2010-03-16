<?php
class tag_list_admin
{
    #
    # init()
    #

    function init()
    {
        add_action('admin_menu', array('tag_list_admin', 'add_option_page'));
    } # init()


    #
    # add_option_page()
    #

    function add_option_page()
    {
        add_submenu_page
        (
            'edit.php',
            __('Tag list options', 'tag_list'),
            __('Tag list options', 'tag_list'),
            9,
            basename(__FILE__),
            array( 'tag_list_admin', 'display_options' )
        );
    } # add_option_page()


    #
    # update_options()
    #

    function update_options()
    {
        check_admin_referer('tag_list_action_update');
        if ( function_exists('update_site_option') && ( function_exists('is_site_admin') && is_site_admin() ) )
        {
            update_site_option( 'tag_list_params', $_POST['tag_list'] );
        }
        else
        {
            update_option( 'tag_list_params', $_POST['tag_list']);
        }
    } # update_options()

    #
    # display_options()
    #

    function display_options()
    {
        # check for wp_head
        $templates = array();
        $templates[] = "header.php";
        $file = file_get_contents( locate_template( $templates ) );
        // Check for wp_head
        preg_match('/.*( wp_head\(\);).*/',$file,$matches );
        if ( !$matches[1] )
        {
            echo '<div id="message" class="error"><p><strong>';
            _e('Warning', 'tag_list' );
            echo '</strong> ';
            _e( 'wp_head(); not found in your header.php file, this might mean this plugin will not work!', 'tag_list' );
            echo "</p></div>\n";
        }
        # Process updates, if any

        if ( isset($_POST['action'])
            && ( $_POST['action'] == 'update' )
        )
        {
            tag_list_admin::update_options();

            echo '<div class="updated">' . "\n"
            . '<p>'
            . '<strong>'
            . __('Options saved.', 'tag_list')
            . '</strong>'
            . '</p>' . "\n"
            . '</div>' . "\n";
        }

        $options = tag_list::get_options();
        # Display admin page
?>
<div class="wrap">
    <h2><?php _e('Tag list options', 'tag_list') ?></h2>
    <form method="post" action="">
        <input type="hidden" name="action" value="update" />
        <input type="hidden" name="page_options" value="tag_list" />
        <?php if ( function_exists('wp_nonce_field') ) wp_nonce_field('tag_list_action_update') ?>
        <fieldset class="options">
            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row" valign="top"><label for="position"><?php _e('Use default CSS?', 'tag_list'); ?></label></th>
                        <td>
                            <ul>
                                <li><input type="radio" name="tag_list[tag_list_default_css]" id="tag_list_default_css_on"<?php if ($options['tag_list_default_css'] == 'on' )  { echo ' checked="checked"';} ?> value="on"  /> <label for="tag_list_default_css_on"><?php _e('yes', 'tag_list') ?></label></li>
                                <li><input type="radio" name="tag_list[tag_list_default_css]" id="tag_list_default_css_on"<?php if ($options['tag_list_default_css'] == 'off' ) { echo ' checked="checked"';} ?> value="off" /> <label for="tag_list_default_css_on"><?php _e('no', 'tag_list') ?></label></li>
                            </ul>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row" valign="top"><label for="position"><?php _e('Where should the short list be placed?', 'tag_list'); ?></label></th>
                        <td>
                            <select name="tag_list[tag_list_position]" id="tag_list_position">
                                <option value="both"<?php   if ($options['tag_list_position'] == 'both')   { echo ' selected="selected"';} ?>><?php _e('on top and on bottom (default)', 'tag_list') ?></option>
                                <option value="top"<?php    if ($options['tag_list_position'] == 'top')    { echo ' selected="selected"';} ?>><?php _e('only on top', 'tag_list') ?></option>
                                <option value="bottom"<?php if ($options['tag_list_position'] == 'bottom') { echo ' selected="selected"';} ?>><?php _e('only on bottom', 'tag_list'); ?></option>
                                <option value="none"<?php   if ($options['tag_list_position'] == 'hide')   { echo ' selected="selected"';} ?>><?php _e('hide', 'tag_list') ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row" valign="top"><label for="position"><?php _e('Use extra div width ID?', 'tag_list'); ?></label></th>
                        <td>
                            <ul>
                                <li><input type="radio" name="tag_list[tag_list_extra_div]" id="tag_list_extra_div_on"<?php if ($options['tag_list_extra_div'] == 'on' )  { echo ' checked="checked"';} ?> value="on"  /> <label for="tag_list_extra_div_on"><?php _e('yes', 'tag_list'); ?></label></li>
                                <li><input type="radio" name="tag_list[tag_list_extra_div]" id="tag_list_extra_div_on"<?php if ($options['tag_list_extra_div'] == 'off' ) { echo ' checked="checked"';} ?> value="off" /> <label for="tag_list_extra_div_on"><?php _e('no', 'tag_list'); ?></label></li>
                            </ul>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row" valign="top"><label for="position"><?php _e('Show unused tag to?', 'tag_list'); ?></label></th>
                        <td>
                            <ul>
                                <li><input type="radio" name="tag_list[tag_list_unused_tags]" id="tag_list_unused_tags_on"<?php if ($options['tag_list_unused_tags'] == 'on' )  { echo ' checked="checked"';} ?> value="on"  /> <label for="tag_list_unused_tags_on"><?php _e('yes', 'tag_list'); ?></label></li>
                                <li><input type="radio" name="tag_list[tag_list_unused_tags]" id="tag_list_unused_tags_on"<?php if ($options['tag_list_unused_tags'] == 'off' ) { echo ' checked="checked"';} ?> value="off" /> <label for="tag_list_unused_tags_on"><?php _e('no (default)', 'tag_list'); ?></label></li>
                            </ul>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row" valign="top"><label for="position"><?php _e('Show number of use?', 'tag_list'); ?></label></th>
                        <td>
                            <ul>
                                <li><input type="radio" name="tag_list[tag_list_number_of_use]" id="tag_list_number_of_use_on"<?php if ($options['tag_list_number_of_use'] == 'on' )  { echo ' checked="checked"';} ?> value="on"  /> <label for="tag_list_number_of_use_on"><?php _e('yes', 'tag_list') ?></label></li>
                                <li><input type="radio" name="tag_list[tag_list_number_of_use]" id="tag_list_number_of_use_on"<?php if ($options['tag_list_number_of_use'] == 'off' ) { echo ' checked="checked"';} ?> value="off" /> <label for="tag_list_number_of_use_on"><?php _e('no (default)', 'tag_list') ?></label></li>
                            </ul>
                        </td>
                    </tr>
                </tbody>
            </table>
        </fieldset>
        <p class="submit"><input type="submit" value="<?php _e('Save Changes', 'tag_list', 'tag_list'); ?>" /></p>
    </form>
    <h2><?php _e('Donation', 'tag_list') ?></h2>
    <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
        <input type="hidden" name="cmd" value="_donations">
        <input type="hidden" name="business" value="marcin@iworks.pl">
        <input type="hidden" name="item_name" value="tag list plugin">
        <input type="hidden" name="no_shipping" value="0">
        <input type="hidden" name="no_note" value="1">
        <input type="hidden" name="currency_code" value="PLN">
        <input type="hidden" name="tax" value="0">
        <input type="hidden" name="lc" value="PL">
        <input type="hidden" name="bn" value="PP-DonationsBF">
        <input type="image" src="https://www.paypalobjects.com/WEBSCR-620-20100302-1/en_US/i/logo/PayPal_mark_60x38.gif" border="0"
        name="submit" alt="PayPal - Wygodne i bezpieczne płatności internetowe!">
        <img alt="" border="0" src="https://www.paypal.com/pl_PL/i/scr/pixel.gif" width="1" height="1">
    </form>
</div>
<?php
    } # display_options()
} # tag_list_admin

tag_list_admin::init();
?>
