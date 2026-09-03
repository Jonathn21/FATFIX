<?php
/**
 * Plugin Name: FastFix — Hascom
 * Plugin URI: https://fastfix.be
 * Description: Gestion complète du site FastFix depuis WordPress : rendez-vous, catalogue d'appareils avec photos, fiches réparations (par famille ou modèle exact), produits reconditionnés, avis clients, FAQ, grille de tarifs et coordonnées de l'entreprise. Menu "FastFix" dédié, totalement isolé du reste du site.
 * Version: 2.3.0
 * Author: FastFix / Hascom Computer
 * Text Domain: fastfix-hascom
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'FASTFIX_VERSION', '2.3.0' );
define( 'FASTFIX_PATH', plugin_dir_path( __FILE__ ) );

/* Réglages en premier : les autres modules s'appuient sur ses helpers. */
require_once FASTFIX_PATH . 'includes/settings.php';
require_once FASTFIX_PATH . 'includes/content.php';

/* Contenus */
require_once FASTFIX_PATH . 'includes/cpt-booking.php';
require_once FASTFIX_PATH . 'includes/cpt-repair.php';
require_once FASTFIX_PATH . 'includes/cpt-device.php';
require_once FASTFIX_PATH . 'includes/cpt-refurbished.php';
require_once FASTFIX_PATH . 'includes/cpt-review.php';
require_once FASTFIX_PATH . 'includes/cpt-faq.php';
require_once FASTFIX_PATH . 'includes/cpt-category.php';

/* Outils */
require_once FASTFIX_PATH . 'includes/image-import.php';
require_once FASTFIX_PATH . 'includes/media-organization.php';
require_once FASTFIX_PATH . 'includes/export.php';

/* Interface & API */
require_once FASTFIX_PATH . 'includes/rest-api.php';
require_once FASTFIX_PATH . 'includes/dashboard.php';
require_once FASTFIX_PATH . 'includes/admin-pages.php';

/**
 * Toutes les initialisations de données, regroupées.
 * Chaque fonction de seed est idempotente : elle ne fait rien si le contenu
 * existe déjà, donc elle peut être rejouée sans risque de doublon.
 */
function fastfix_run_setup() {
	if ( false === get_option( 'fastfix_pricing' ) ) {
		update_option( 'fastfix_pricing', fastfix_default_pricing() );
	}
	if ( false === get_option( 'fastfix_settings' ) ) {
		$defaults = fastfix_default_settings();
		$defaults['notify_email'] = get_option( 'admin_email' );
		update_option( 'fastfix_settings', $defaults );
	}

	fastfix_seed_default_repairs();
	fastfix_seed_default_devices();
	fastfix_seed_default_refurbished();
	fastfix_seed_default_reviews();
	fastfix_seed_default_faq();
	fastfix_seed_default_categories();
	fastfix_seed_featured_devices();
	fastfix_seed_featured_repairs();
	fastfix_retag_existing_media();
}

register_activation_hook( __FILE__, function() {
	fastfix_register_booking_cpt();
	fastfix_register_repair_cpt();
	fastfix_register_device_cpt();
	fastfix_register_refurbished_cpt();
	fastfix_register_review_cpt();
	fastfix_register_faq_cpt();
	fastfix_register_category_cpt();
	flush_rewrite_rules();

	fastfix_run_setup();
	update_option( 'fastfix_setup_version', FASTFIX_VERSION );
} );

register_deactivation_hook( __FILE__, function() {
	flush_rewrite_rules();
} );

/**
 * Filet de sécurité : si le plugin est mis à jour par simple remplacement de
 * fichiers (sans désactivation/réactivation), le hook d'activation ne se
 * redéclenche pas. On rejoue donc la configuration une fois par version.
 */
add_action( 'admin_init', function() {
	if ( get_option( 'fastfix_setup_version' ) === FASTFIX_VERSION ) return;
	fastfix_run_setup();
	update_option( 'fastfix_setup_version', FASTFIX_VERSION );
} );

/* Lien "Réglages" directement depuis la liste des extensions. */
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), function( $links ) {
	$url = admin_url( 'admin.php?page=fastfix-menu' );
	array_unshift( $links, '<a href="' . esc_url( $url ) . '">Tableau de bord</a>' );
	return $links;
} );
