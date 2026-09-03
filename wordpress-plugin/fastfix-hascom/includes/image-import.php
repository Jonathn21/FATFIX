<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Import en masse des photos déjà publiées sur le site fastfix.pages.dev
 * vers la médiathèque WordPress, en une seule action depuis wp-admin.
 * Évite d'avoir à uploader ~90 images une par une : ça part d'une base
 * cohérente (souvent une photo générique par génération d'appareil),
 * que l'équipe peut ensuite affiner modèle par modèle si besoin.
 */

// À mettre à jour vers https://fastfix.be une fois le domaine final branché.
define( 'FASTFIX_IMAGE_BASE_URL', 'https://fatfix.pages.dev' );

/**
 * Correspondance nom de fiche → fichier image, pour les appareils.
 * Reprend exactement les images déjà utilisées sur le site Astro.
 */
function fastfix_device_image_map() {
	return [
		'iPhone 17 Pro Max' => 'iphone-17-pro.webp', 'iPhone 17 Pro' => 'iphone-17-pro.webp', 'iPhone 17' => 'iphone-17.webp',
		'iPhone 16 Pro Max' => 'iphone-16-pro-max.webp', 'iPhone 16 Pro' => 'iphone-16-pro-max.webp', 'iPhone 16 Plus' => 'iphone-16.webp', 'iPhone 16' => 'iphone-16.webp',
		'iPhone 15 Pro Max' => 'iphone-16-pro-max.webp', 'iPhone 15 Pro' => 'iphone-16-pro-max.webp', 'iPhone 15 Plus' => 'iphone-16.webp', 'iPhone 15' => 'iphone-16.webp',
		'iPhone 14 Pro Max' => 'iphone-16-pro-max.webp', 'iPhone 14 Pro' => 'iphone-16-pro-max.webp', 'iPhone 14 Plus' => 'iphone-16.webp', 'iPhone 14' => 'iphone-16.webp',
		'iPhone 13 Pro Max' => 'iphone-16-pro-max.webp', 'iPhone 13 Pro' => 'iphone-16-pro-max.webp', 'iPhone 13' => 'iphone-16.webp',
		'iPhone 12 Pro Max' => 'iphone-16-pro-max.webp', 'iPhone 12 Pro' => 'iphone-16-pro-max.webp', 'iPhone 12' => 'iphone-16.webp',
		'iPhone 11 Pro Max' => 'iphone-16-pro-max.webp', 'iPhone 11 Pro' => 'iphone-16-pro-max.webp', 'iPhone 11' => 'iphone-16.webp',
		'iPhone XS Max' => 'iphone-16-pro-max.webp', 'iPhone XS' => 'iphone-16.webp', 'iPhone XR' => 'iphone-16.webp', 'iPhone X' => 'iphone-16.webp',
		'iPhone 8 Plus' => 'iphone-16.webp', 'iPhone 8' => 'iphone-16.webp',

		'iPad Pro 13" M5' => 'ipad-pro-m5.webp', 'iPad Pro 11" M5' => 'ipad-pro-m5.webp', 'iPad Pro 13" M4' => 'ipad-pro.webp', 'iPad Pro 11" M4' => 'ipad-pro.webp',
		'iPad Air M3' => 'ipad-pro.webp', 'iPad Air M2' => 'ipad-pro.webp', 'iPad 10e gén.' => 'ipad-pro.webp', 'iPad Mini 7' => 'ipad-pro.webp',

		'Apple Watch Ultra 2' => 'apple-watch.webp', 'Apple Watch Series 10' => 'apple-watch.webp', 'Apple Watch Series 9' => 'apple-watch.webp', 'Apple Watch SE (2e)' => 'apple-watch.webp',

		'MacBook Air 15" M4' => 'macbook-air.webp', 'MacBook Air 13" M4' => 'macbook-air.webp', 'MacBook Pro 16" M4 Pro/Max' => 'macbook-air.webp',
		'MacBook Pro 14" M4' => 'macbook-air.webp', 'MacBook Air 15" M3' => 'macbook-air.webp', 'MacBook Air 13" M3' => 'macbook-air.webp',

		'Galaxy S26 Ultra' => 'galaxy-s26-ultra.webp', 'Galaxy S26+' => 'galaxy-s26-plus.webp', 'Galaxy S26' => 'galaxy-s26.webp',
		'Galaxy S25 Ultra' => 'galaxy-s26-ultra.webp', 'Galaxy S25+' => 'galaxy-s26-plus.webp', 'Galaxy S25' => 'galaxy-s26.webp',
		'Galaxy S24 Ultra' => 'galaxy-s26-ultra.webp', 'Galaxy S24+' => 'galaxy-s24.webp', 'Galaxy S24' => 'galaxy-s24.webp',
		'Galaxy S23 Ultra' => 'galaxy-s23-ultra.webp', 'Galaxy S23+' => 'galaxy-s24.webp', 'Galaxy S23' => 'galaxy-s24.webp',

		'Galaxy A56' => 'galaxy-s24.webp', 'Galaxy A36' => 'galaxy-s24.webp', 'Galaxy A16' => 'galaxy-s24.webp',
		'Galaxy A55' => 'galaxy-s24.webp', 'Galaxy A35' => 'galaxy-s24.webp', 'Galaxy A15' => 'galaxy-s24.webp',

		'Galaxy Tab S10 Ultra' => 'galaxy-tab.webp', 'Galaxy Tab S10+' => 'galaxy-tab.webp', 'Galaxy Tab S9 FE' => 'galaxy-tab.webp',

		'Galaxy Z Fold 6' => 'galaxy-s24.webp', 'Galaxy Z Flip 6' => 'galaxy-s24.webp', 'Galaxy Z Fold 5' => 'galaxy-s24.webp', 'Galaxy Z Flip 5' => 'galaxy-s24.webp',

		'Pixel 10 Pro' => 'pixel-10-pro.webp', 'Pixel 10' => 'pixel-10-pro.webp', 'Pixel 9 Pro XL' => 'pixel-10-pro.webp', 'Pixel 9 Pro' => 'pixel-10-pro.webp',
		'Pixel 9' => 'pixel-10-pro.webp', 'Pixel 8 Pro' => 'pixel-10-pro.webp', 'Pixel 8' => 'pixel-10-pro.webp',
		'Pixel Tablet' => 'pixel-10-pro.webp',
	];
}

function fastfix_refurbished_image_map() {
	return [
		'iPhone 16 Pro Max' => 'iphone-16-pro-max.webp',
		'iPhone 16'         => 'iphone-16.webp',
		'iPhone 17 Pro'     => 'iphone-17-pro.webp',
		'Galaxy S26 Ultra'  => 'galaxy-s26-ultra.webp',
		'Galaxy S26'        => 'galaxy-s26.webp',
		'Galaxy S24'        => 'galaxy-s24.webp',
		'iPhone 17'         => 'iphone-17.webp',
		'Galaxy S23 Ultra'  => 'galaxy-s23-ultra.webp',
	];
}

/**
 * Télécharge une image depuis une URL publique et l'attache comme image
 * mise en avant d'un article. Ne fait rien si l'article a déjà une photo
 * (sauf si $force est vrai).
 */
function fastfix_sideload_featured_image( $post_id, $image_url, $title, $force = false ) {
	if ( ! $force && has_post_thumbnail( $post_id ) ) {
		return 'skipped';
	}

	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$attachment_id = media_sideload_image( $image_url, $post_id, $title, 'id' );

	if ( is_wp_error( $attachment_id ) ) {
		return $attachment_id->get_error_message();
	}

	set_post_thumbnail( $post_id, $attachment_id );
	return 'imported';
}

/**
 * Lance l'import pour un post_type donné, en utilisant une table de
 * correspondance nom → fichier, servies depuis FASTFIX_IMAGE_BASE_URL/$folder/.
 */
function fastfix_run_bulk_image_import( $post_type, array $image_map, $folder, $force = false ) {
	$posts   = get_posts( [ 'post_type' => $post_type, 'post_status' => 'publish', 'posts_per_page' => -1 ] );
	$results = [ 'imported' => 0, 'skipped' => 0, 'not_mapped' => 0, 'errors' => [] ];

	foreach ( $posts as $post ) {
		$filename = $image_map[ $post->post_title ] ?? null;
		if ( ! $filename ) {
			$results['not_mapped']++;
			continue;
		}
		$url    = trailingslashit( FASTFIX_IMAGE_BASE_URL ) . 'images/' . $folder . '/' . $filename;
		$status = fastfix_sideload_featured_image( $post->ID, $url, $post->post_title, $force );

		if ( $status === 'imported' ) {
			$results['imported']++;
		} elseif ( $status === 'skipped' ) {
			$results['skipped']++;
		} else {
			$results['errors'][] = $post->post_title . ' : ' . $status;
		}
	}

	return $results;
}

/* ── Action déclenchée par le bouton "Importer les photos" ── */
add_action( 'admin_post_fastfix_import_images', function() {
	if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Non autorisé.' );
	check_admin_referer( 'fastfix_import_images' );

	$force = ! empty( $_POST['force'] );

	$devices_result     = fastfix_run_bulk_image_import( 'fastfix_device', fastfix_device_image_map(), 'devices', $force );
	$refurbished_result = fastfix_run_bulk_image_import( 'fastfix_refurbished', fastfix_refurbished_image_map(), 'devices', $force );
	$category_result    = fastfix_run_bulk_image_import( 'fastfix_category', fastfix_category_image_map(), 'devices', $force );

	set_transient( 'fastfix_import_result', [
		'devices'     => $devices_result,
		'refurbished' => $refurbished_result,
		'categories'  => $category_result,
	], 60 );

	wp_safe_redirect( admin_url( 'admin.php?page=fastfix-menu&imported=1' ) );
	exit;
} );
