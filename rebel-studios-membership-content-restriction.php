<?php
/**
 * Plugin Name:       Rebel Studios Membership & Content Restriction
 * Plugin URI:        https://rebelstudiossoftware.com/membership-content-restriction
 * Description:       Restrict posts, pages and any content to logged-in users or members. Front-end registration and login, a members directory, and a clean "members only" message — no code required.
 * Version:           1.1.1
 * Requires at least: 6.5
 * Requires PHP:      7.4
 * Author:            Rebel Studios Software
 * Author URI:        https://rebelstudiossoftware.com/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       rebel-studios-membership-content-restriction
 *
 * @package RebelStudios\Membership
 */

defined( 'ABSPATH' ) || exit;

define( 'RSMB_VERSION', '1.1.1' );
define( 'RSMB_FILE', __FILE__ );
define( 'RSMB_PATH', plugin_dir_path( __FILE__ ) );
define( 'RSMB_URL', plugin_dir_url( __FILE__ ) );
define( 'RSMB_BASENAME', plugin_basename( __FILE__ ) );

spl_autoload_register(
	function ( $class ) {
		$prefix = 'RebelStudios\\Membership\\';
		if ( 0 !== strpos( $class, $prefix ) ) {
			return;
		}
		$path = RSMB_PATH . 'includes/' . str_replace( '\\', '/', substr( $class, strlen( $prefix ) ) ) . '.php';
		if ( is_readable( $path ) ) {
			require $path;
		}
	}
);

add_action(
	'plugins_loaded',
	function () {
		\RebelStudios\Membership\Plugin::instance()->boot();
	}
);

register_activation_hook(
	__FILE__,
	function () {
		\RebelStudios\Membership\Engine\Settings::seed_defaults();
	}
);
