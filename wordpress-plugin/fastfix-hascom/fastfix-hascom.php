<?php
/**
 * Plugin Name: FastFix — Hascom
 * Plugin URI: https://fastfix.be
 * Description: Backend dédié à FastFix (enseigne réparation de Hascom Computer). Gère rendez-vous, catalogue d'appareils (photos incluses), fiches réparations (par famille ou par modèle exact) et produits reconditionnés. Totalement isolé du reste du site — menu "FastFix" dédié dans wp-admin.
 * Version: 1.4.0
 * Author: FastFix / Hascom Computer
 * Text Domain: fastfix-hascom
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'FASTFIX_VERSION', '1.4.0' );
define( 'FASTFIX_PATH', plugin_dir_path( __FILE__ ) );

require_once FASTFIX_PATH . 'includes/cpt-booking.php';
require_once FASTFIX_PATH . 'includes/cpt-repair.php';
require_once FASTFIX_PATH . 'includes/cpt-device.php';
require_once FASTFIX_PATH . 'includes/cpt-refurbished.php';
require_once FASTFIX_PATH . 'includes/image-import.php';
require_once FASTFIX_PATH . 'includes/media-organization.php';
require_once FASTFIX_PATH . 'includes/rest-api.php';
require_once FASTFIX_PATH . 'includes/admin-pages.php';

register_activation_hook( __FILE__, function() {
	fastfix_register_booking_cpt();
	fastfix_register_repair_cpt();
	fastfix_register_device_cpt();
	fastfix_register_refurbished_cpt();
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
	fastfix_seed_default_devices();
	fastfix_seed_default_refurbished();
	fastfix_seed_featured_devices();
	fastfix_retag_existing_media();
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
	if ( ! get_option( 'fastfix_repairs_seeded' ) ) {
		fastfix_seed_default_repairs();
		update_option( 'fastfix_repairs_seeded', 1 );
	}
	if ( ! get_option( 'fastfix_devices_seeded' ) ) {
		fastfix_seed_default_devices();
		update_option( 'fastfix_devices_seeded', 1 );
	}
	if ( ! get_option( 'fastfix_refurbished_seeded' ) ) {
		fastfix_seed_default_refurbished();
		update_option( 'fastfix_refurbished_seeded', 1 );
	}
	if ( ! get_option( 'fastfix_featured_seeded' ) ) {
		fastfix_seed_featured_devices();
		update_option( 'fastfix_featured_seeded', 1 );
	}
	if ( ! get_option( 'fastfix_media_retagged' ) ) {
		fastfix_retag_existing_media();
		update_option( 'fastfix_media_retagged', 1 );
	}
} );
