<?php
/*
Plugin Name: Tag list
Plugin URI: http://iworks.pl/tag-list/
Description: Display all used tags with or without TOC.
Author: Marcin Pietrzak
Version: 1.1.1
Author URI: http://iworks.pl
*/

/*
Copyright 2010  Marcin Pietrzak  (email : marcin.pietrzak@iworks.pl )

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc.,
51 Franklin St, Fifth Floor, Boston, MA 02110-1301  USA
*/

# include admin stuff when relevant
if ( strpos($_SERVER['REQUEST_URI'], 'wp-admin') !== false )
{
    include_once dirname(__FILE__) . '/tag-list-admin.php';
}


class tag_list
{
    #
    # init()
    #
    function init()
    {
        add_shortcode( 'tag-list',  Array( 'tag_list', 'get_tag_list' ) );
        add_action( 'wp_head', Array( 'tag_list', 'get_css') );
        add_filter( 'plugin_row_meta', Array( 'tag_list', 'register_plugin_links' ), 10, 2 );
        // load language file
        $current_locale = get_locale();
        if(!empty($current_locale)) {
            $mo_file = dirname(__FILE__) . "/lang/tag-list-" . $current_locale . ".mo";
            if( @file_exists( $mo_file ) && is_readable( $mo_file ) )
            {
                load_textdomain('tag_list', $mo_file);
            }
        }
    } # init()


    #
    # get_options()
    #
    function get_options()
    {
        if ( function_exists('get_site_option') )
        {
            $options = get_site_option( 'tag_list_params' );
        }
        else
        {
            $options = get_option( 'tag_list_params' );
        }
        if ( !isset($options['tag_list_position'] ) )
        {
            $options['tag_list_position'] = 'both';
        }
        if ( !isset($options['tag_list_default_css'] ) )
        {
            $options['tag_list_default_css'] = 'on';
        }
        if ( !isset($options['tag_list_extra_div'] ) )
        {
            $options['tag_list_extra_div'] = 'on';
        }
        if ( !isset($options['tag_list_unused_tags'] ) )
        {
            $options['tag_list_unused_tags'] = 'off';
        }
        if ( !isset($options['tag_list_number_of_use'] ) )
        {
            $options['tag_list_number_of_use'] = 'off';
        }
        return $options;
    } # get_options()

    #
    # get_iworks_tag_list()
    #
    function get_tag_list()
    {
        global $wpdb;
        #
        $options = tag_list::get_options();
        #
        $sql  = 'SELECT T.term_id, T.name, X.count ';
        $sql .= 'FROM ' . $wpdb->terms . ' T ';
        $sql .= 'LEFT JOIN ' . $wpdb->term_taxonomy . ' X ON T.term_id = X.term_id ';
        $sql .= 'WHERE X.taxonomy = \'post_tag\' ';
        if ( $options['tag_list_unused_tags'] == 'off' )
        {
            $sql .= 'AND X.count > 0 ';
        }
        $sql .= 'ORDER BY T.name ASC';
        $tags = $wpdb->get_results( $sql );
        #
        $letter = '';
        $toc = '<ul class="tag-toc">';
        $content = '<ul class="tag-list">';
        $count = 0;
        foreach ($tags as $t)
        {
            $l = mb_substr ( $t->name, 0, 1 );
            if (strtolower($l) != $letter)
            {
                if ($letter != '')
                {
                    $content .= '</ul>'."\n".'</li>'."\n";
                }
                $letter = strtolower($l);
                $archor = 'tag-'.((preg_match('/[a-z0-9]/', $letter))? $letter:'alt-'.$count);
                $toc .= sprintf('<li><a href="#%s">%s</a></li>', $archor, $letter);
                $content .= sprintf('<li id="%s">'."\n".'<h4>%s</h4>'."\n".'<ul>'."\n", $archor, $letter);
            }
            $link = get_tag_link($t->term_id);
            if ( is_wp_error( $link ) )
            {
                return $link;
            }
            $counter_string = '';
            if ( $options['tag_list_number_of_use'] == 'on' )
            {
                $counter_string = sprintf( ' <small>(%d)</small>', $t->count );
            }
            $content .= sprintf('<li><a href="%s">%s%s</a></li>'."\n", $link, $t->name, $counter_string);
            $count++;
        }
        $content .= '</ul>'."\n".'</li>'."\n".'</ul>'."\n";
        $toc .= '</ul>'."\n";
        if ( preg_match( '/^(top|both)$/', $options['tag_list_position'] ) ) {
            $content = $toc.$content;
        }
        if ( preg_match( '/^(bottom|both)$/', $options['tag_list_position'] ) ) {
            $content .= $toc;
        }
        if ( $options['tag_list_extra_div'] == 'on' )
        {
            return '<div id="tag-list">'.$content.'</div>';
        }
        return $content;
    }#get_iworks_tag_list()

    #
    # get_css()
    #
    function get_css()
    {
        $options = tag_list::get_options();
        if ( $options['tag_list_default_css'] == 'on' )
        {
            print '<style type="text/css" >';
            include_once('default.css');
            print '</style>';
        }
    } # get_css()

    #
    # register_plugin_links()
    #
    function register_plugin_links($links, $file)
    {
        if ( preg_match( '/tag-list.php$/', $file ) )
        {
            $links[] = '<a href="edit.php?page=tag-list-admin.php">' . __( 'Settings', 'tag_list' ) . '</a>';
        }
        return $links;
    }# register_plugin_links()
}

tag_list::init();
?>
