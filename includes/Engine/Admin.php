<?php
/**
 * Admin chrome: the settings submenu under WooCommerce, shared asset enqueue,
 * and a consistent page header carrying the upgrade button. Engine class.
 *
 * @package RebelStudios\Membership\Engine
 */

namespace RebelStudios\Membership\Engine;

defined( 'ABSPATH' ) || exit;

class Admin {

	const MENU_SLUG = 'rebel-studios-membership-content-restriction';

	public static function register_page( $render_cb ) {
		add_action(
			'admin_menu',
			function () use ( $render_cb ) {
				add_menu_page(
					__( 'Membership', 'rebel-studios-membership-content-restriction' ),
					__( 'Membership', 'rebel-studios-membership-content-restriction' ),
					'manage_options',
					self::MENU_SLUG,
					$render_cb,
					'dashicons-groups',
					71
				);
				// Without an explicit first submenu, WordPress points the top-level link at whichever
				// submenu registers first (Members) instead of this settings screen.
				add_submenu_page(
					self::MENU_SLUG,
					__( 'Settings', 'rebel-studios-membership-content-restriction' ),
					__( 'Settings', 'rebel-studios-membership-content-restriction' ),
					'manage_options',
					self::MENU_SLUG,
					$render_cb
				);
			}
		);
	}

	public static function is_own_screen() {
		if ( ! function_exists( 'get_current_screen' ) ) {
			return false;
		}
		$screen = get_current_screen();
		return $screen && false !== strpos( (string) $screen->id, self::MENU_SLUG );
	}

	public static function enqueue() {
		add_action(
			'admin_enqueue_scripts',
			function () {
				if ( ! self::is_own_screen() ) {
					return;
				}
				wp_enqueue_media();
				wp_enqueue_style(
					'rsev-admin',
					RSMB_URL . 'assets/admin/admin.css',
					array(),
					RSMB_VERSION
				);
				wp_enqueue_script(
					'rsev-admin',
					RSMB_URL . 'assets/admin/admin.js',
					array( 'jquery' ),
					RSMB_VERSION,
					true
				);
			}
		);
	}

	/** Consistent page header with the (honest) upgrade call to action. */
	public static function header( $title ) {
		echo '<div class="wcinv-header">';
		echo '<h1>' . esc_html( $title ) . '</h1>';
		if ( ! Freemium::is_pro() ) {
			printf(
				'<a class="button button-primary wcinv-upgrade" target="_blank" rel="noopener" href="%s">%s</a>',
				esc_url( Freemium::upgrade_url( 'header' ) ),
				esc_html__( 'Get Pro', 'rebel-studios-membership-content-restriction' )
			);
		}
		echo '</div>';
	}
}
