<?php
namespace RebelStudios\Membership;

use RebelStudios\Membership\Engine\Settings;

defined( 'ABSPATH' ) || exit;

class Directory {
	public static function render() {
		$members = get_users( array( 'meta_key' => Members::META_MEMBER, 'meta_value' => 1, 'number' => 200, 'orderby' => 'display_name', 'order' => 'ASC' ) );
		// The flag outlives a lapsed paid membership; is_member() is what add-ons expire.
		$members = array_values( array_filter( $members, static function ( $m ) { return Members::is_member( $m->ID ); } ) );
		if ( ! $members ) {
			return '<p class="rsmb-empty">' . esc_html__( 'No members yet.', 'rebel-studios-membership-content-restriction' ) . '</p>';
		}
		$show_bio = 'yes' === Settings::get( 'directory_show_bio' );
		ob_start();
		echo '<ul class="rsmb-directory">';
		foreach ( $members as $m ) {
			echo '<li class="rsmb-member">';
			// Initials sit under a transparent default avatar, so a member with no Gravatar gets
			// their initials instead of a row of identical grey silhouettes.
			$words    = preg_split( '/\s+/', trim( $m->display_name ) );
			$initials = mb_strtoupper( mb_substr( $words[0], 0, 1 ) . ( count( $words ) > 1 ? mb_substr( end( $words ), 0, 1 ) : '' ) );
			echo '<span class="rsmb-avatar" data-initials="' . esc_attr( $initials ) . '">' . get_avatar( $m->ID, 128, apply_filters( 'rsmb_directory_avatar_default', 'blank' ), '' ) . '</span>';
			echo '<span class="rsmb-member-name">' . esc_html( $m->display_name ) . '</span>';
			if ( $show_bio && $m->description ) {
				echo '<span class="rsmb-member-bio">' . esc_html( wp_trim_words( $m->description, 30 ) ) . '</span>';
			}
			echo '</li>';
		}
		echo '</ul>';
		return (string) ob_get_clean();
	}
}
