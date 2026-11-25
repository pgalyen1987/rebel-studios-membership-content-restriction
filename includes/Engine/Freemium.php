<?php
/**
 * Free / Pro gate.
 *
 * wp.org rule: the free plugin ships NO paid code. The Pro tier is a separate
 * add-on plugin sold off-site; when active and licensed it answers the
 * `rsmb_is_pro` filter true and may register its own features on the hooks the
 * free plugin exposes. So `is_pro()` is false for the free plugin alone, and
 * every gated call degrades to the free behaviour -- never to a broken screen.
 *
 * Engine class -- lifts to other plugins by changing the three constants.
 *
 * @package RebelStudios\Membership\Engine
 */

namespace RebelStudios\Membership\Engine;

defined( 'ABSPATH' ) || exit;

class Freemium {

	const IS_PRO_FILTER = 'rsmb_is_pro';
	const UPGRADE_URL   = 'https://rebelstudiossoftware.com/membership-content-restriction';
	const PRODUCT       = 'rebel-studios-membership-content-restriction';

	public static function is_pro() {
		return (bool) apply_filters( self::IS_PRO_FILTER, false );
	}

	public static function upgrade_url( $utm_content = 'generic' ) {
		return add_query_arg(
			array(
				'utm_source'  => 'plugin',
				'utm_medium'  => 'admin',
				'utm_campaign'=> self::PRODUCT,
				'utm_content' => sanitize_key( $utm_content ),
			),
			self::UPGRADE_URL
		);
	}

	/** A small inline "Pro" pill for gated controls. */
	public static function badge( $utm_content = 'badge' ) {
		return sprintf(
			' <a class="wcinv-pro-pill" target="_blank" rel="noopener" href="%s">%s</a>',
			esc_url( self::upgrade_url( $utm_content ) ),
			esc_html__( 'Pro', 'rebel-studios-membership-content-restriction' )
		);
	}
}
