<?php
/*
Plugin Name: Tag List
Text Domain: tag-list
Plugin URI: PLUGIN_URI
Description: PLUGIN_TAGLINE
Version: PLUGIN_VERSION
Author: Marcin Pietrzak
Author URI: http://iworks.pl/
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Copyright 2010-PLUGIN_TILL_YEAR Marcin Pietrzak (marcin@iworks.pl)

this program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 3, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

 */
defined( 'ABSPATH' ) || exit; // Exit if accessed directly
/**
 * Load the main plugin class if it doesn't exist
 * This is the core class that handles all plugin functionality
 */
if ( ! class_exists( 'iworks_tag_list' ) ) {
	// Load the main plugin class from the includes directory
	require_once __DIR__ . '/includes/iworks/class-tag-list.php';
}
// Initialize the main plugin class
$iworks_tag_list = new iworks_tag_list();

/**
 * Register plugin activation and deactivation hooks
 */
// Register activation hook to run when plugin is activated
register_activation_hook( __FILE__, array( $iworks_tag_list, 'register_activation_hook' ) );
// Register deactivation hook to run when plugin is deactivated
register_deactivation_hook( __FILE__, array( $iworks_tag_list, 'register_deactivation_hook' ) );
