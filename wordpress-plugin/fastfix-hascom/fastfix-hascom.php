<?php
/**
 * Plugin Name: FastFix — Hascom
 * Plugin URI: https://fastfix.be
 * Description: Backend dédié à FastFix (enseigne réparation de Hascom Computer). Reçoit les demandes de rendez-vous depuis le site fastfix.be (Astro) et gère les tarifs de réparation. Totalement isolé du reste du site — menu "FastFix" dédié dans wp-admin.
 * Version: 1.0.0
 * Author: FastFix / Hascom Computer
 * Text Domain: fastfix-hascom
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'FASTFIX_VERSION', '1.0.0' );
define( 'FASTFIX_PATH', plugin_dir_path( __FILE__ ) );

require_once FASTFIX_PATH . 'includes/cpt-booking.php';
require_once FASTFIX_PATH . 'includes/cpt-repair.php';
require_once FASTFIX_PATH . 'includes/rest-api.php';
require_once FASTFIX_PATH . 'includes/admin-pages.php';

register_activation_hook( __FILE__, function() {
	fastfix_register_booking_cpt();
	fastfix_register_repair_cpt();
	flush_rewrite_rules();

	if ( false === get_option( 'fastfix_pricing' ) ) {
		update_option( 'fastfix_pricing', fastfix_default_pricing() );
	}
	if ( false === get_option( 'fastfix_notify_email' ) ) {
		update_option( 'fastfix_notify_email', get_option( 'admin_email' ) );
	}
	if ( false === get_option( 'fastfix_cors_origins' ) ) {
		update_option( 'fastfix_cors_origins', '*' );
	}

	fastfix_seed_default_repairs();
} );

register_deactivation_hook( __FILE__, function() {
	flush_rewrite_rules();
} );

/**
 * Filet de sécurité : si le plugin est mis à jour par simple remplacement de
 * fichiers (sans désactivation/réactivation), le hook d'activation ne se
 * redéclenche pas. On exécute donc le seeding une fois via admin_init.
 */
add_action( 'admin_init', function() {
	if ( get_option( 'fastfix_repairs_seeded' ) ) return;
	fastfix_seed_default_repairs();
	update_option( 'fastfix_repairs_seeded', 1 );
} );
