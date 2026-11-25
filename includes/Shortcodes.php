<?php
namespace RebelStudios\Membership;

use RebelStudios\Membership\Engine\Settings;

defined( 'ABSPATH' ) || exit;

class Shortcodes {
	public static function hooks() {
		add_shortcode( 'rsmb_register', array( __CLASS__, 'register' ) );
		add_shortcode( 'rsmb_login', array( __CLASS__, 'login' ) );
		add_shortcode( 'rsmb_directory', array( __CLASS__, 'directory' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'assets' ) );
	}
	/**
	 * Styles load only where the plugin's markup appears, not on every page of the site: pages with
	 * a membership shortcode, and restricted posts (which may show the members-only notice). Each
	 * output point also calls enqueue(), covering markup rendered somewhere unpredictable.
	 */
	public static function assets() {
		$restricted = is_singular() && ! in_array( (string) get_post_meta( get_queried_object_id(), Content_Restriction::META, true ), array( '', 'public' ), true );
		if ( $restricted || self::content_has( array( 'rsmb_register', 'rsmb_login', 'rsmb_directory' ) ) ) {
			self::enqueue();
		}
	}

	public static function enqueue() {
		static $done = false; // the inline accent rule must not be appended twice
		if ( $done ) {
			return;
		}
		$done = true;
		wp_enqueue_style( 'rebel-studios-membership-content-restriction', RSMB_URL . 'assets/public/membership.css', array(), RSMB_VERSION );
		$accent = Settings::get( 'accent_color', '#2563eb' );
		wp_add_inline_style( 'rebel-studios-membership-content-restriction', ':root{--rsmb-accent:' . ( preg_match( '/^#[0-9a-fA-F]{3,6}$/', $accent ) ? $accent : '#2563eb' ) . '}' );
	}

	/** True when the singular post being viewed contains any of these shortcodes. */
	private static function content_has( array $tags ) {
		$post = is_singular() ? get_post() : null;
		if ( ! $post ) {
			return false;
		}
		foreach ( $tags as $tag ) {
			if ( has_shortcode( $post->post_content, $tag ) ) {
				return true;
			}
		}
		return false;
	}

	public static function register() { self::enqueue(); return Members::register_form(); }
	public static function login() { self::enqueue(); return Members::login_form(); }
	public static function directory() { self::enqueue(); return Directory::render(); }
}
