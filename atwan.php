<?php

/**
 * High Speed
 *
 * @package           PluginPackage
 * @author            Mohammadreza Atwan
 * @copyright         Mohammadreza Atwan
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       High Speed
 * Plugin URI:        https://www.atwan.ir/projects/wordpress/plugin/high-speed-wordpress
 * Description:       Turn your WordPress into a fast website.
 * Version:           1.0.0
 * Author:            Mohammadreza Atwan
 * Author URI:        https://www.atwan.ir
 * Text Domain:       high_speed
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Update URI:        https://github.com/mratwan/high-speed-wordpress
 */

defined( 'ABSPATH' ) || exit;

class Atwan_High_Speed_WP {

	private $blocked_url = [];

	public function __construct() {
		$this->blocked_url['woocommerce.com/wp-json/wccom-extensions/1.0/featured'] = '[]';
		$this->blocked_url['woocommerce.com/wp-json/wccom-extensions/2.0/featured'] = '[]';
		$this->blocked_url['woocommerce.com/wp-json/wccom-extensions/1.0/search'] = '[]';
		$this->blocked_url['woocommerce.com/wp-json/wccom/obw-free-extensions/3.0/extensions.json'] = '[]';
		$this->blocked_url['woocommerce.com/wp-json/wccom/payment-gateway-suggestions/1.0/payment-method/promotions.json'] = '[]';
		$this->blocked_url['woocommerce.com/wp-json/wccom/payment-gateway-suggestions/1.0/suggestions.json'] = '[]';

		$this->blocked_url['api.wordpress.org/core/browse-happy/1.1'] = '[]';
		$this->blocked_url['api.wordpress.org/core/serve-happy/1.0'] = '[]';

		if ( $this->blocked_url ) {
			add_filter( 'pre_http_request', [ $this, 'pre_http_request' ], 1000, 3 );
		}

		add_filter( 'pre_site_transient_update_core', [ $this, '__return_null' ] );
		remove_action( 'admin_init', '_maybe_update_core' );
		remove_action( 'wp_version_check', 'wp_version_check' );

		add_filter( 'admin_menu', function () {
			remove_submenu_page( 'index.php', 'update-core.php' );
		} );

		remove_action( 'load-plugins.php', 'wp_update_plugins' );
		remove_action( 'load-update.php', 'wp_update_plugins' );
		remove_action( 'load-update-core.php', 'wp_update_plugins' );
		remove_action( 'admin_init', '_maybe_update_plugins' );
		remove_action( 'wp_update_plugins', 'wp_update_plugins' );
		add_filter( 'pre_site_transient_update_plugins', [ $this, '__return_null' ] );

		remove_action( 'load-themes.php', 'wp_update_themes' );
		remove_action( 'load-update.php', 'wp_update_themes' );
		remove_action( 'load-update-core.php', 'wp_update_themes' );
		remove_action( 'admin_init', '_maybe_update_themes' );
		remove_action( 'wp_update_themes', 'wp_update_themes' );
		add_filter( 'pre_site_transient_update_themes', [ $this, '__return_null' ] );
	}

	public function __return_null() {
		return null;
	}

	public function pre_http_request( $preempt, $parsed_args, $url ) {

		$url = trim( parse_url( $url, PHP_URL_HOST ) . parse_url( $url, PHP_URL_PATH ), '/' );

		if ( isset( $this->blocked_url[ $url ] ) ) {
			return [ 
				'headers' => [],
				'body' => $this->blocked_url[ $url ],
				'response' => [ 
					'code' => 200,
					'message' => false,
				],
				'cookies' => [],
				'http_response' => null,
			];
		}

		return $preempt;
	}
}

new Atwan_High_Speed_WP();