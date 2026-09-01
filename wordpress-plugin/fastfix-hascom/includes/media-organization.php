<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Sépare visuellement les images FastFix du reste de la médiathèque
 * (Hascom, GVISION, etc.) sans plugin tiers : une taxonomie dédiée sur
 * les pièces jointes, appliquée automatiquement à chaque image utilisée
 * comme photo d'un appareil / réparation / produit reconditionné /
 * demande de RDV, + un filtre "Galerie FastFix" dans Médias.
 */

add_action( 'init', function() {
	register_taxonomy( 'fastfix_media_group', 'attachment', [
		'label'             => 'Galerie',
		'public'            => false,
		'show_ui'           => true,
		'show_in_menu'      => false, // pas de menu séparé, juste le filtre dans Médias
		'show_admin_column' => true,
		'hierarchical'      => false,
	] );

	if ( ! term_exists( 'fastfix', 'fastfix_media_group' ) ) {
		wp_insert_term( 'FastFix', 'fastfix_media_group', [ 'slug' => 'fastfix' ] );
	}
} );

/**
 * Étiquette automatiquement toute image devenue "image mise en avant"
 * d'une fiche FastFix — que ce soit via l'import en masse ou un upload
 * manuel depuis wp-admin.
 */
add_action( 'set_post_thumbnail', function( $post_id, $thumbnail_id ) {
	$fastfix_types = [ 'fastfix_device', 'fastfix_repair', 'fastfix_refurbished', 'fastfix_booking' ];
	if ( in_array( get_post_type( $post_id ), $fastfix_types, true ) ) {
		wp_set_object_terms( $thumbnail_id, 'fastfix', 'fastfix_media_group', false );
	}
}, 10, 2 );

/**
 * Filtre déroulant dans Médias (mode Liste) pour n'afficher que la
 * galerie FastFix.
 */
add_action( 'restrict_manage_posts', function( $post_type ) {
	if ( $post_type !== 'attachment' ) return;
	$selected = $_GET['fastfix_media_group'] ?? '';
	echo '<select name="fastfix_media_group">';
	echo '<option value="">Toute la médiathèque</option>';
	echo '<option value="fastfix" ' . selected( $selected, 'fastfix', false ) . '>Galerie FastFix uniquement</option>';
	echo '</select>';
} );

add_filter( 'parse_query', function( $query ) {
	global $pagenow;
	if ( ! is_admin() || $pagenow !== 'upload.php' ) return;
	if ( ( $_GET['fastfix_media_group'] ?? '' ) === 'fastfix' ) {
		$query->query_vars['tax_query'] = [ [
			'taxonomy' => 'fastfix_media_group',
			'field'    => 'slug',
			'terms'    => 'fastfix',
		] ];
	}
} );

/**
 * Lien rapide en haut de la médiathèque, comme "Tout / Non attachées" :
 * "Galerie FastFix (N)".
 */
add_filter( 'views_upload', function( $views ) {
	$term  = get_term_by( 'slug', 'fastfix', 'fastfix_media_group' );
	$count = $term ? $term->count : 0;
	$class = ( ( $_GET['fastfix_media_group'] ?? '' ) === 'fastfix' ) ? 'current' : '';
	$url   = admin_url( 'upload.php?fastfix_media_group=fastfix&mode=list' );

	$views['fastfix'] = '<a href="' . esc_url( $url ) . '" class="' . esc_attr( $class ) . '">Galerie FastFix <span class="count">(' . (int) $count . ')</span></a>';
	return $views;
} );

/**
 * Migration : étiquette rétroactivement les images déjà en place comme
 * photo d'un appareil / réparation / produit reconditionné, pour les
 * fiches créées avant l'ajout de ce système de galerie.
 */
function fastfix_retag_existing_media() {
	$fastfix_types = [ 'fastfix_device', 'fastfix_repair', 'fastfix_refurbished', 'fastfix_booking' ];
	$posts = get_posts( [
		'post_type'      => $fastfix_types,
		'posts_per_page' => -1,
		'post_status'    => 'any',
	] );
	foreach ( $posts as $post ) {
		$thumb_id = get_post_thumbnail_id( $post->ID );
		if ( $thumb_id ) {
			wp_set_object_terms( $thumb_id, 'fastfix', 'fastfix_media_group', false );
		}
	}
}
