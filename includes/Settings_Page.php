<?php
namespace RebelStudios\Membership;

use RebelStudios\Membership\Engine\Admin;
use RebelStudios\Membership\Engine\Freemium;
use RebelStudios\Membership\Engine\Settings;

defined( 'ABSPATH' ) || exit;

class Settings_Page {
	const SAVE = 'rsmb_save_settings';

	public function hooks() {
		Admin::register_page( array( $this, 'render' ) );
		add_action( 'admin_post_' . self::SAVE, array( $this, 'save' ) );
	}

	public function save() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Not allowed.', 'rebel-studios-membership-content-restriction' ), 403 );
		}
		check_admin_referer( self::SAVE );
		$in = wp_unslash( $_POST );
		Settings::update(
			array(
				'restricted_message'   => $in['restricted_message'] ?? '',
				'member_label'         => $in['member_label'] ?? 'Member',
				'redirect_after_login' => $in['redirect_after_login'] ?? '',
				'directory_show_bio'   => $in['directory_show_bio'] ?? 'no',
				'accent_color'         => $in['accent_color'] ?? '#2563eb',
			)
		);
		wp_safe_redirect( add_query_arg( array( 'page' => Admin::MENU_SLUG, 'updated' => 1 ), admin_url( 'admin.php' ) ) );
		exit;
	}

	public function render() {
		$s = Settings::all();
		?>
		<div class="wrap">
			<?php Admin::header( __( 'Membership & Content Restriction', 'rebel-studios-membership-content-restriction' ) ); ?>
			<?php if ( isset( $_GET['updated'] ) ) { echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Saved.', 'rebel-studios-membership-content-restriction' ) . '</p></div>'; } ?>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="<?php echo esc_attr( self::SAVE ); ?>">
				<?php wp_nonce_field( self::SAVE ); ?>
				<table class="form-table" role="presentation">
					<tr><th><label for="restricted_message"><?php esc_html_e( 'Members-only message', 'rebel-studios-membership-content-restriction' ); ?></label></th>
						<td><textarea name="restricted_message" id="restricted_message" class="large-text" rows="3"><?php echo esc_textarea( $s['restricted_message'] ); ?></textarea>
						<p class="description"><?php esc_html_e( 'Shown in place of restricted content to visitors who cannot view it.', 'rebel-studios-membership-content-restriction' ); ?></p></td></tr>
					<tr><th><label for="redirect_after_login"><?php esc_html_e( 'Redirect after joining', 'rebel-studios-membership-content-restriction' ); ?></label></th>
						<td><input type="url" name="redirect_after_login" id="redirect_after_login" class="regular-text" value="<?php echo esc_attr( $s['redirect_after_login'] ); ?>" placeholder="https://"></td></tr>
					<tr><th><?php esc_html_e( 'Member directory', 'rebel-studios-membership-content-restriction' ); ?></th>
						<td><label><input type="checkbox" name="directory_show_bio" value="yes" <?php checked( $s['directory_show_bio'], 'yes' ); ?>> <?php esc_html_e( 'Show member bios in the directory', 'rebel-studios-membership-content-restriction' ); ?></label></td></tr>
					<tr><th><label for="accent_color"><?php esc_html_e( 'Accent color', 'rebel-studios-membership-content-restriction' ); ?></label></th>
						<td><input type="text" name="accent_color" id="accent_color" value="<?php echo esc_attr( $s['accent_color'] ); ?>" placeholder="#2563eb"></td></tr>
				</table>
				<h2><?php esc_html_e( 'Paid memberships', 'rebel-studios-membership-content-restriction' ); ?> <?php echo wp_kses_post( Freemium::is_pro() ? '' : Freemium::badge( 'settings' ) ); ?></h2>
				<p class="description"><?php esc_html_e( 'Paid membership plans through your own WooCommerce checkout, access that expires, and tiers are in Pro. The free plugin handles unlimited free members and content restriction.', 'rebel-studios-membership-content-restriction' ); ?></p>
				<?php submit_button( __( 'Save settings', 'rebel-studios-membership-content-restriction' ) ); ?>
			</form>
			<hr>
			<h2><?php esc_html_e( 'Shortcodes', 'rebel-studios-membership-content-restriction' ); ?></h2>
			<p><code>[rsmb_register]</code> — <?php esc_html_e( 'membership registration form.', 'rebel-studios-membership-content-restriction' ); ?><br>
			<code>[rsmb_login]</code> — <?php esc_html_e( 'login form.', 'rebel-studios-membership-content-restriction' ); ?><br>
			<code>[rsmb_directory]</code> — <?php esc_html_e( 'the members directory.', 'rebel-studios-membership-content-restriction' ); ?></p>
			<p class="description"><?php esc_html_e( 'Restrict any post or page from its editor: the "Access & Membership" box in the sidebar.', 'rebel-studios-membership-content-restriction' ); ?></p>
		</div>
		<?php
	}
}
