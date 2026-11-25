<?php
/**
 * Membership: front-end registration (which flags the user as a member), a login
 * form, the member test used by content restriction, and an admin member list.
 *
 * @package RebelStudios\Membership
 */

namespace RebelStudios\Membership;

use RebelStudios\Membership\Engine\Admin;
use RebelStudios\Membership\Engine\Settings;

defined( 'ABSPATH' ) || exit;

class Members {

	const META_MEMBER = 'rsmb_member';
	const ACTION      = 'rsmb_register';
	const JOIN_ACTION = 'rsmb_join_self';  // a logged-in user becoming a member
	const JOIN_PAGE   = 'rsmb_join_page_id'; // cached id of the page carrying [rsmb_register]

	private static $instance;
	public static function instance() { return self::$instance ?: ( self::$instance = new self() ); }

	public function hooks() {
		add_action( 'admin_post_nopriv_' . self::ACTION, array( $this, 'handle_register' ) );
		add_action( 'admin_post_' . self::ACTION, array( $this, 'handle_register' ) );
		add_action( 'admin_post_' . self::JOIN_ACTION, array( $this, 'handle_join_self' ) );
		// A page gaining or losing the sign-up form invalidates the cached join page.
		add_action( 'save_post_page', static function () { delete_option( self::JOIN_PAGE ); } );
		// After the top-level Membership menu (priority 10): a submenu added before its parent
		// exists is orphaned — no sidebar entry, and its screen answers "not allowed".
		add_action( 'admin_menu', array( $this, 'menu' ), 20 );
	}

	public static function is_member( $user_id ) {
		$is_member = (bool) get_user_meta( (int) $user_id, self::META_MEMBER, true );

		/**
		 * Decide whether a user counts as a member.
		 *
		 * The free plugin's answer is a simple flag set at registration. An
		 * add-on (Pro) uses this to expire a lapsed paid membership, so someone
		 * who stopped paying stops being a member without the flag being removed.
		 *
		 * @param bool $is_member Membership flag on the user.
		 * @param int  $user_id   User being tested.
		 */
		return (bool) apply_filters( 'rsmb_is_member', $is_member, (int) $user_id );
	}

	/**
	 * Where "Join" should send someone: the page carrying the sign-up form, else WordPress's own
	 * registration if the site allows it, else nowhere. It used to be wp_registration_url()
	 * unconditionally, and WordPress ships with registration off, so on a default site the
	 * restriction notice's "Join" led to "User registration is currently not allowed".
	 */
	public static function join_url() {
		$page_id = get_option( self::JOIN_PAGE, null );
		if ( null === $page_id || ( $page_id && ! has_shortcode( (string) get_post_field( 'post_content', (int) $page_id ), 'rsmb_register' ) ) ) {
			$page_id = 0;
			$found   = get_posts( array( 'post_type' => 'page', 'post_status' => 'publish', 's' => 'rsmb_register', 'numberposts' => 10, 'fields' => 'ids' ) );
			foreach ( $found as $id ) {
				if ( has_shortcode( (string) get_post_field( 'post_content', $id ), 'rsmb_register' ) ) {
					$page_id = $id;
					break;
				}
			}
			update_option( self::JOIN_PAGE, (int) $page_id, false );
		}
		if ( $page_id && 'publish' === get_post_status( (int) $page_id ) ) {
			return get_permalink( (int) $page_id );
		}
		return get_option( 'users_can_register' ) ? wp_registration_url() : '';
	}

	/* ---------- front-end registration form ---------- */

	public static function register_form() {
		if ( is_user_logged_in() ) {
			if ( self::is_member( get_current_user_id() ) ) {
				return '<div class="rsmb-box"><p>' . esc_html__( 'You are already a member. Thanks for being here!', 'rebel-studios-membership-content-restriction' ) . '</p></div>';
			}
			// Logged in but not a member (a store customer, say): one click, no second account.
			ob_start();
			?>
			<div class="rsmb-box">
				<form class="rsmb-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="<?php echo esc_attr( self::JOIN_ACTION ); ?>">
					<?php wp_nonce_field( self::JOIN_ACTION ); ?>
					<p><?php echo esc_html( sprintf( /* translators: %s: the user's display name */ __( 'You are logged in as %s.', 'rebel-studios-membership-content-restriction' ), wp_get_current_user()->display_name ) ); ?></p>
					<p><button type="submit" class="rsmb-btn"><?php esc_html_e( 'Become a member', 'rebel-studios-membership-content-restriction' ); ?></button></p>
				</form>
			</div>
			<?php
			return (string) ob_get_clean();
		}
		$notice = '';
		if ( isset( $_GET['rsmb'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only status flag.
			$f = sanitize_key( wp_unslash( $_GET['rsmb'] ) );
			if ( 'err' === $f ) {
				$notice = '<div class="rsmb-note rsmb-err">' . esc_html__( 'Please enter a valid email and a password of at least 8 characters, and make sure the username is free.', 'rebel-studios-membership-content-restriction' ) . '</div>';
			}
		}
		ob_start();
		echo '<div class="rsmb-box">';
		echo wp_kses_post( $notice );
		?>
		<h3><?php esc_html_e( 'Become a member', 'rebel-studios-membership-content-restriction' ); ?></h3>
		<form class="rsmb-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="<?php echo esc_attr( self::ACTION ); ?>">
			<?php wp_nonce_field( self::ACTION ); ?>
			<p><label><?php esc_html_e( 'Username', 'rebel-studios-membership-content-restriction' ); ?><br><input type="text" name="rsmb_username" required></label></p>
			<p><label><?php esc_html_e( 'Email', 'rebel-studios-membership-content-restriction' ); ?><br><input type="email" name="rsmb_email" required></label></p>
			<p><label><?php esc_html_e( 'Password', 'rebel-studios-membership-content-restriction' ); ?><br><input type="password" name="rsmb_password" required minlength="8"></label></p>
			<p><button type="submit" class="rsmb-btn"><?php esc_html_e( 'Join', 'rebel-studios-membership-content-restriction' ); ?></button></p>
		</form>
		<?php
		echo '</div>';
		return (string) ob_get_clean();
	}

	public static function login_form() {
		if ( is_user_logged_in() ) {
			return '<div class="rsmb-box"><p>' . esc_html__( 'You are logged in.', 'rebel-studios-membership-content-restriction' ) . ' <a href="' . esc_url( wp_logout_url() ) . '">' . esc_html__( 'Log out', 'rebel-studios-membership-content-restriction' ) . '</a></p></div>';
		}
		return '<div class="rsmb-box">' . wp_login_form( array( 'echo' => false ) ) . '</div>';
	}

	public function handle_join_self() {
		$back = wp_get_referer() ? wp_get_referer() : home_url();
		if ( ! is_user_logged_in() || ! check_admin_referer( self::JOIN_ACTION ) ) {
			wp_safe_redirect( $back );
			exit;
		}
		update_user_meta( get_current_user_id(), self::META_MEMBER, 1 );
		$redirect = Settings::get( 'redirect_after_login' );
		wp_safe_redirect( $redirect ? $redirect : $back );
		exit;
	}

	public function handle_register() {
		$back  = wp_get_referer() ? wp_get_referer() : home_url();
		$nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, self::ACTION ) ) {
			wp_safe_redirect( add_query_arg( 'rsmb', 'err', $back ) );
			exit;
		}
		$username = isset( $_POST['rsmb_username'] ) ? sanitize_user( wp_unslash( $_POST['rsmb_username'] ) ) : '';
		$email    = isset( $_POST['rsmb_email'] ) ? sanitize_email( wp_unslash( $_POST['rsmb_email'] ) ) : '';
		$password = isset( $_POST['rsmb_password'] ) ? (string) wp_unslash( $_POST['rsmb_password'] ) : '';

		if ( '' === $username || ! is_email( $email ) || strlen( $password ) < 8 || username_exists( $username ) || email_exists( $email ) ) {
			wp_safe_redirect( add_query_arg( 'rsmb', 'err', $back ) );
			exit;
		}
		$user_id = wp_insert_user(
			array(
				'user_login' => $username,
				'user_email' => $email,
				'user_pass'  => $password,
				'role'       => 'subscriber',
			)
		);
		if ( is_wp_error( $user_id ) ) {
			wp_safe_redirect( add_query_arg( 'rsmb', 'err', $back ) );
			exit;
		}
		update_user_meta( $user_id, self::META_MEMBER, 1 );
		wp_signon( array( 'user_login' => $username, 'user_password' => $password, 'remember' => true ) );

		$redirect = Settings::get( 'redirect_after_login' );
		wp_safe_redirect( $redirect ? $redirect : $back );
		exit;
	}

	/* ---------- admin member list ---------- */

	public function menu() {
		add_submenu_page(
			Admin::MENU_SLUG,
			__( 'Members', 'rebel-studios-membership-content-restriction' ),
			__( 'Members', 'rebel-studios-membership-content-restriction' ),
			'manage_options',
			'rsmb-members',
			array( $this, 'render_members' )
		);
	}

	public function render_members() {
		$members = get_users( array( 'meta_key' => self::META_MEMBER, 'meta_value' => 1, 'number' => 500 ) );
		echo '<div class="wrap">';
		Admin::header( __( 'Members', 'rebel-studios-membership-content-restriction' ) );
		/* translators: %d: number of members */
		echo '<p>' . esc_html( sprintf( _n( '%d member', '%d members', count( $members ), 'rebel-studios-membership-content-restriction' ), count( $members ) ) ) . '</p>';
		echo '<table class="widefat striped"><thead><tr><th>' . esc_html__( 'Name', 'rebel-studios-membership-content-restriction' ) . '</th><th>' . esc_html__( 'Email', 'rebel-studios-membership-content-restriction' ) . '</th><th>' . esc_html__( 'Joined', 'rebel-studios-membership-content-restriction' ) . '</th></tr></thead><tbody>';
		if ( ! $members ) {
			echo '<tr><td colspan="3">' . esc_html__( 'No members yet.', 'rebel-studios-membership-content-restriction' ) . '</td></tr>';
		}
		foreach ( $members as $m ) {
			printf(
				'<tr><td>%s</td><td>%s</td><td>%s</td></tr>',
				esc_html( $m->display_name ),
				esc_html( $m->user_email ),
				esc_html( mysql2date( get_option( 'date_format' ), $m->user_registered ) )
			);
		}
		echo '</tbody></table></div>';
	}
}
