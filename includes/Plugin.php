<?php
namespace RebelStudios\Membership;

use RebelStudios\Membership\Engine\Admin;

defined( 'ABSPATH' ) || exit;

class Plugin {
	private static $instance;
	public static function instance() { return self::$instance ?: ( self::$instance = new self() ); }
	public function boot() {
		Content_Restriction::instance()->hooks();
		Members::instance()->hooks();
		Shortcodes::hooks();
		( new Settings_Page() )->hooks();
		Admin::enqueue();
	}
}
