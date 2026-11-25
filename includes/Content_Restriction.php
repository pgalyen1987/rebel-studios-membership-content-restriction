<?php
/**
 * The core: restrict any post/page to logged-in users or members. A visibility
 * metabox stores the rule; the content, excerpt and REST filters enforce it,
 * replacing restricted content with a configurable "members only" notice + login/join prompt.
 *
 * @package RebelStudios\Membership
 */

namespace RebelStudios\Membership;

use RebelStudios\Membership\Engine\Settings;

defined( 'ABSPATH' ) || exit;

class Content_Restriction {

	const META = '_rsmb_visibility'; // public | loggedin | members

	private static $instance;
	public static function instance() { return self::$instance ?: ( self::$instance = new self() ); }

	public function hooks() {
		add_action( 'add_meta_boxes', array( $this, 'metabox' ) );
		add_action( 'save_post', array( $this, 'save' ), 10, 2 );
		// Decide by the post, not by the request. The same post reaches visitors through the
		// single view, the blog index, search, feeds, embeds and the REST API; checking only
		// the single view left every other route open (QA 2026-09-29).
		add_filter( 'the_content', array( $this, 'filter_content' ), 20 );
		add_filter( 'get_the_excerpt', array( $this, 'filter_excerpt' ), 20, 2 );
		add_action( 'rest_api_init', array( $this, 'rest_hooks' ) );

		// A restricted post's discussion is restricted with it: comments quote and answer the post.
		add_filter( 'comments_open', array( $this, 'filter_comments_open' ), 20, 2 );
		add_filter( 'comments_array', array( $this, 'filter_comments_array' ), 20, 2 );
		add_filter( 'render_block_core/comments', array( $this, 'filter_comments_block' ), 20, 3 );
		add_filter( 'comment_text', array( $this, 'filter_comment_text' ), 20, 2 );
		add_filter( 'get_comment_text', array( $this, 'filter_comment_text' ), 20, 2 );
		add_filter( 'get_comment_excerpt', array( $this, 'filter_comment_excerpt' ), 20, 3 );
	}

	public function metabox() {
		foreach ( get_post_types( array( 'public' => true ), 'names' ) as $type ) {
			if ( 'attachment' === $type ) {
				continue;
			}
			add_meta_box( 'rsmb-visibility', __( 'Access & Membership', 'rebel-studios-membership-content-restriction' ), array( $this, 'render' ), $type, 'side' );
		}
	}

	public function render( $post ) {
		wp_nonce_field( 'rsmb_save_visibility', 'rsmb_visibility_nonce' );
		$val = get_post_meta( $post->ID, self::META, true );
		$val = $val ? $val : 'public';
		$opts = array(
			'public'   => __( 'Public — anyone', 'rebel-studios-membership-content-restriction' ),
			'loggedin' => __( 'Logged-in users only', 'rebel-studios-membership-content-restriction' ),
			'members'  => __( 'Members only', 'rebel-studios-membership-content-restriction' ),
		);
		echo '<p>';
		foreach ( $opts as $k => $label ) {
			printf(
				'<label style="display:block;margin:.3em 0"><input type="radio" name="rsmb_visibility" value="%s" %s> %s</label>',
				esc_attr( $k ),
				checked( $val, $k, false ),
				esc_html( $label )
			);
		}
		echo '</p>';
		echo '<p class="description">' . esc_html__( 'Restricted content is replaced with your members-only message for anyone who does not qualify.', 'rebel-studios-membership-content-restriction' ) . '</p>';
	}

	public function save( $post_id, $post ) {
		if ( ! isset( $_POST['rsmb_visibility_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['rsmb_visibility_nonce'] ) ), 'rsmb_save_visibility' ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		$v = isset( $_POST['rsmb_visibility'] ) ? sanitize_text_field( wp_unslash( $_POST['rsmb_visibility'] ) ) : 'public';
		update_post_meta( $post_id, self::META, in_array( $v, array( 'public', 'loggedin', 'members' ), true ) ? $v : 'public' );
	}

	/** True if the current user may see this post's content. Its editors always may. */
	public static function can_view_post( $post ) {
		$post = get_post( $post );
		if ( ! $post || current_user_can( 'edit_post', $post->ID ) ) {
			return true;
		}
		return self::user_can_view( get_post_meta( $post->ID, self::META, true ), $post->ID );
	}

	/** True if the current user may see content with the given rule. */
	public static function user_can_view( $rule, $post_id = null ) {
		if ( 'public' === $rule || ! $rule ) {
			return true;
		}
		if ( current_user_can( 'edit_others_posts' ) ) {
			return true; // editors/admins always see
		}
		if ( 'loggedin' === $rule ) {
			return is_user_logged_in();
		}
		if ( 'members' === $rule ) {
			$can = is_user_logged_in() && Members::is_member( get_current_user_id() );

			/**
			 * Final say on members-only access for the current post.
			 *
			 * An add-on (Pro) uses this to require a particular membership tier
			 * on top of plain membership. Editors and admins have already been
			 * allowed through above, so this only narrows access for members.
			 *
			 * @param bool $can     Whether the current user may view.
			 * @param int  $post_id Post being viewed (0 if unknown).
			 */
			return (bool) apply_filters( 'rsmb_user_can_view', $can, null === $post_id ? (int) get_the_ID() : (int) $post_id );
		}
		return true;
	}

	public function filter_content( $content ) {
		return self::can_view_post( get_post() ) ? $content : $this->notice();
	}

	/** A hand-written excerpt never passes through the_content, so it needs its own check. */
	public function filter_excerpt( $excerpt, $post = null ) {
		return self::can_view_post( $post ? $post : get_post() ) ? $excerpt : Settings::get( 'restricted_message' );
	}

	public function rest_hooks() {
		foreach ( get_post_types( array( 'show_in_rest' => true ), 'names' ) as $type ) {
			add_filter( "rest_prepare_{$type}", array( $this, 'filter_rest' ), 10, 2 );
		}
	}

	/**
	 * The REST API is a data API, so it is closed here directly rather than trusting that
	 * the_content still carries our filter by the time the response is built.
	 */
	public function filter_rest( $response, $post ) {
		if ( self::can_view_post( $post ) ) {
			return $response;
		}
		$data = $response->get_data();
		if ( isset( $data['content'] ) && is_array( $data['content'] ) ) {
			$data['content'] = array( 'rendered' => $this->notice(), 'protected' => true );
		}
		if ( isset( $data['excerpt'] ) && is_array( $data['excerpt'] ) ) {
			$data['excerpt'] = array( 'rendered' => '<p>' . esc_html( Settings::get( 'restricted_message' ) ) . '</p>', 'protected' => true );
		}
		$response->set_data( $data );
		return $response;
	}

	public function filter_comments_open( $open, $post_id ) {
		return $open && self::can_view_post( $post_id );
	}

	/** Classic themes: no thread under the notice. */
	public function filter_comments_array( $comments, $post_id ) {
		return self::can_view_post( $post_id ) ? $comments : array();
	}

	/** Block themes: the Comments block queries on its own, so it is dropped whole. */
	public function filter_comments_block( $html, $block, $instance = null ) {
		$post_id = ( $instance && ! empty( $instance->context['postId'] ) ) ? $instance->context['postId'] : get_the_ID();
		return self::can_view_post( $post_id ) ? $html : '';
	}

	/** Everywhere else a comment is printed: REST, the comments feed, widgets. */
	public function filter_comment_text( $text, $comment = null ) {
		$comment = get_comment( $comment );
		return ( ! $comment || self::can_view_post( $comment->comment_post_ID ) ) ? $text : '';
	}

	public function filter_comment_excerpt( $excerpt, $comment_id = 0, $comment = null ) {
		return $this->filter_comment_text( $excerpt, $comment ? $comment : $comment_id );
	}

	private function notice() {
		Shortcodes::enqueue();
		$msg = Settings::get( 'restricted_message' );
		ob_start();
		echo '<div class="rsmb-restricted">';
		echo '<svg class="rsmb-lock" viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/></svg>';
		echo '<p class="rsmb-restricted-msg">' . esc_html( $msg ) . '</p>';
		echo '<p class="rsmb-restricted-actions">';
		$join = Members::join_url();
		if ( ! is_user_logged_in() ) {
			// Joining is what the visitor came to decide, so it leads; existing members log in.
			if ( $join ) {
				printf( '<a class="rsmb-btn" href="%s">%s</a>', esc_url( $join ), esc_html__( 'Become a member', 'rebel-studios-membership-content-restriction' ) );
			}
			printf(
				'<a class="rsmb-btn%s" href="%s">%s</a>',
				$join ? ' rsmb-btn-quiet' : '',
				esc_url( wp_login_url( get_permalink() ) ),
				esc_html__( 'Log in', 'rebel-studios-membership-content-restriction' )
			);
		} elseif ( $join && ! Members::is_member( get_current_user_id() ) ) {
			printf( '<a class="rsmb-btn" href="%s">%s</a>', esc_url( $join ), esc_html__( 'Become a member', 'rebel-studios-membership-content-restriction' ) );
		}
		echo '</p></div>';
		return (string) ob_get_clean();
	}
}
