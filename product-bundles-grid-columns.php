<?php
/**
* Plugin Name: Product Bundles - Grid Columns
* Plugin URI: https://woocommerce.com/products/product-bundles
* Description: Mini-extension for WooCommerce Product Bundles that allows you to use change the number of columns that are displayed on the Grid layout.
* Version: 1.0.0
* Author: SomewhereWarm
* Author URI: https://somewherewarm.com/
*
* Text Domain: woocommerce-product-bundles-grid-columns
* Domain Path: /languages/
*
* Requires at least: 4.4
* Tested up to: 5.7
*
* WC requires at least: 3.3
* WC tested up to: 5.2
*
* Copyright: © 2017-2020 SomewhereWarm SMPC.
* License: GNU General Public License v3.0
* License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main plugin class.
 *
 * @class    WC_PB_Grid_Columns
 * @version  1.0.1
 */
class WC_PB_Grid_Columns {

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	public static $version = '1.0.1';

	/**
	 * Min required PB version.
	 *
	 * @var string
	 */
	public static $req_pb_version = '1.1.5';

	/**
	 * PB URL.
	 *
	 * @var string
	 */
	private static $pb_url = 'https://woocommerce.com/products/product-bundles/';

	/**
	 * Plugin URL.
	 *
	 * @return string
	 */
	public static function plugin_url() {
		return plugins_url( basename( plugin_dir_path(__FILE__) ), basename( __FILE__ ) );
	}

	/**
	 * Plugin path.
	 *
	 * @return string
	 */
	public static function plugin_path() {
		return untrailingslashit( plugin_dir_path( __FILE__ ) );
	}

	/**
	 * Fire in the hole!
	 */
	public static function init() {
		add_action( 'plugins_loaded', array( __CLASS__, 'load_plugin' ) );
	}

	/**
	 * Hooks.
	 */
	public static function load_plugin() {

		if ( ! function_exists( 'WC_PB' ) || version_compare( WC_PB()->version, self::$req_pb_version ) < 0 ) {
			add_action( 'admin_notices', array( __CLASS__, 'pb_admin_notice' ) );
			return false;
		}

		// Localization.
		add_action( 'init', array( __CLASS__, 'localize_plugin' ) );

		// Display number of columns settings in "Bundled Products" tab.
		add_action( 'woocommerce_bundled_products_admin_config', array( __CLASS__, 'display_options' ), 15 );

		// Save number of columns settings.
		add_action( 'woocommerce_admin_process_product_object', array( __CLASS__, 'save_meta' ) );

		// Change the number of grid columns.
		add_filter( 'woocommerce_bundled_items_grid_layout_columns', array( __CLASS__, 'wc_pb_grid_layout_change_number_of_columns'), 10, 2 );


	}

	/**
	 * PB Version check notice.
	 */
	public static function pb_admin_notice() {
	    echo '<div class="error"><p>' . sprintf( __( '<strong>Product Bundles &ndash; Grid Columns</strong> requires <a href="%1$s" target="_blank">WooCommerce Product Bundles</a> version <strong>%2$s</strong> or higher.', 'woocommerce-product-bundles-grid-columns' ), self::$pb_url, self::$req_pb_version ) . '</p></div>';
	}


	/**
	 * Load textdomain.
	 *
	 * @return void
	 */
	public static function localize_plugin() {
		load_plugin_textdomain( 'woocommerce-product-bundles-grid-columns', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Admin number of columns settings.
	 */
	public static function display_options() {

		woocommerce_wp_text_input( array(
			'id'            => '_wcpb_num_cols',
			'wrapper_class' => 'bundled_product_data_field',
			'type'          => 'number',
			'label'         => __( 'Number of grid columns', 'woocommerce-product-bundles-grid-columns' ),
			'desc_tip'      => true,
			'description'   => __( 'Number of grid columns of bundled item thumbnails.', 'woocommerce-product-bundles-grid-columns' )
		) );

		wc_enqueue_js( '
			( function ( $ ) {
				$( document ).ready( function() {
					var $layout   = $( "#_wc_pb_layout_style" ),
				        $num_cols = $( "._wcpb_num_cols_field" );

					var toggle    = function( $layout, $num_cols ){
						if ( $layout.val() != "grid" ){
							$num_cols.hide();
						} else {
							$num_cols.show();
						}
					}

					toggle( $layout, $num_cols );

					layout.on( "change", function() {
						toggle( $layout, $num_cols );
					});
				});
			}( jQuery ) );
		' );

	}

	/**
	 * Save meta.
	 *
	 * @param  WC_Product  $product
	 * @return void
	 */
	public static function save_meta( $product ) {

		if ( ! empty( $_POST[ '_wcpb_num_cols' ] ) && is_numeric( $_POST[ '_wcpb_num_cols' ] ) ) {
			$product-> add_meta_data( '_wcpb_num_cols', stripslashes( $_POST[ '_wcpb_num_cols' ] ), true );
		} else {
			$product->add_meta_data( '_wcpb_num_cols', '3', true );
		}

	}

	/**
	 * Change number of grid columns.
	 *
	 * @param  int        $columns 
	 * @param  WC_Product $bundle
	 * @return int
	 */
	public static function wc_pb_grid_layout_change_number_of_columns( $columns, $bundle ) {

		if ( $bundle->meta_exists( '_wcpb_num_cols' ) ) {
			$num_cols = (int) $bundle->get_meta( '_wcpb_num_cols', true );
		}
		else {
			$num_cols = 3;
		}
		
		return $num_cols;
		
	}
}

WC_PB_Grid_Columns::init();
