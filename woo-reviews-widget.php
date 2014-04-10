<?php
/*
	Plugin Name: WooCommerce - Reviews Widget
	Plugin URI: http://wordpress.org/plugins/woocommerce-reviews-widget/
	Description: WooCommerce widget automatically displays associated reviews for products on WooCommerce shop pages.
	Version: 1.1
	Author: fruitfulcode
	Author URI: http://fruitfulcode.com
	License: GPL2
*/
/*  Copyright 2013  Fruitful Code  (email : support@fruitfulcode.com)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as 
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


class WOO_REVIEWS_WIDGET {

		function __construct() {
			add_action( 'plugins_loaded', array( &$this, 'constants'), 	1);
			add_action( 'plugins_loaded', array( &$this, 'lang'),		2);
			add_action( 'plugins_loaded', array( &$this, 'includes'), 	3);
			add_action('admin_notices',  array( &$this, 'plugin_notice_message' ) ) ;
			
			/*	
				register_activation_hook  ( __FILE__, array( &$this,  'activation' ));
				register_deactivation_hook( __FILE__, array( &$this,'deactivation') );
			*/
			add_action('widgets_init', array( &$this, 'reviews_widget_register'));
			add_action('wp_print_style', array( &$this, 'reviews_widget_styles'));
		}
		
		function constants() {
			define( 'WOO_REVIEWS_WIDGET_VERSION', '1.0.0' );
			define( 'WOO_REVIEWS_WIDGET_DB_VERSION', 1 );
			define( 'WOO_REVIEWS_WIDGET_WP_VERSION', get_bloginfo( 'version' ));
			define( 'WOO_REVIEWS_WIDGET_DIR', trailingslashit( plugin_dir_path( __FILE__ ) ) );
			define( 'WOO_REVIEWS_WIDGET_URI', trailingslashit( plugin_dir_url( __FILE__ ) ) );
			define( 'WOO_REVIEWS_WIDGET_INCLUDES', WOO_REVIEWS_WIDGET_DIR . trailingslashit( 'includes' ) );
		}
		
		function lang() {
			load_plugin_textdomain( 'woo-reviews-widget', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );		
		}	
		
		function includes() {
			require_once( WOO_REVIEWS_WIDGET_INCLUDES . 'reviews-widget.php' ); 
		}
		
		function reviews_widget_register() {
			register_widget( 'WooCommerce_Widget_Products_Reviews' );
			wp_enqueue_style('woo-reviews-widget-css', esc_url( WOO_REVIEWS_WIDGET_URI. '/includes/reviews-widget.css'));
		}
		
		function activate () {
			$plugin = plugin_basename( __FILE__ );		
			if ( !in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
				if( is_plugin_active($plugin) ) {
					 deactivate_plugins( $plugin );
				 }
			}
		}
		
		function plugin_notice_message () {
			if ( !in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
				 include_once( WOO_REVIEWS_WIDGET_INCLUDES . 'reviews-widget-error.php' ); 
			}
		}
}
$WOO_REVIEWS_WIDGET = new WOO_REVIEWS_WIDGET;
