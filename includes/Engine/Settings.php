<?php
/**
 * Options wrapper. One serialized option row, typed defaults, sanitized writes.
 * Engine class -- shared verbatim across Rebel Studios plugins; the only
 * per-plugin coupling is the OPTION_KEY and the $defaults map.
 *
 * @package RebelStudios\Membership\Engine
 */

namespace RebelStudios\Membership\Engine;

defined( 'ABSPATH' ) || exit;

class Settings {

	const OPTION_KEY = 'rsmb_settings';

	/**
	 * Field key => default value. Also the allow-list: a key absent here is
	 * never read or written, so a crafted POST cannot smuggle options in.
	 */
	public static function defaults() {
		return array(
			'restricted_message' => 'This content is available to members only. Please log in or join to view it.',
			'member_label'       => 'Member',
			'redirect_after_login' => '',
			'directory_show_bio' => 'yes',
			'accent_color'       => '#2563eb',
				);
	}

	public static function all() {
		$saved = get_option( self::OPTION_KEY, array() );
		if ( ! is_array( $saved ) ) {
			$saved = array();
		}
		return array_merge( self::defaults(), $saved );
	}

	public static function get( $key, $fallback = null ) {
		$all = self::all();
		if ( array_key_exists( $key, $all ) ) {
			return $all[ $key ];
		}
		return $fallback;
	}

	public static function update( array $values ) {
		$current = self::all();
		$clean   = array();
		foreach ( self::defaults() as $key => $default ) {
			if ( ! array_key_exists( $key, $values ) ) {
				$clean[ $key ] = $current[ $key ];
				continue;
			}
			$clean[ $key ] = self::sanitize_field( $key, $values[ $key ], $default );
		}
		update_option( self::OPTION_KEY, $clean );
		return $clean;
	}

	public static function seed_defaults() {
		if ( false === get_option( self::OPTION_KEY, false ) ) {
			add_option( self::OPTION_KEY, self::defaults() );
		}
	}

	private static function sanitize_field( $key, $value, $default ) {
		switch ( $key ) {
			case 'restricted_message':
				return sanitize_textarea_field( wp_unslash( $value ) );
			case 'accent_color':
				$c = sanitize_hex_color( wp_unslash( $value ) );
				return $c ? $c : $default;
			case 'directory_show_bio':
				return 'yes' === $value ? 'yes' : 'no';
			case 'redirect_after_login':
				return esc_url_raw( wp_unslash( $value ) );
			default:
				return sanitize_text_field( wp_unslash( $value ) );
				}
	}
}
