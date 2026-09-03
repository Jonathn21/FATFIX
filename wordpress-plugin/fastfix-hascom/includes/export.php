<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Export CSV de tous les rendez-vous, pour ouverture dans Excel / Google Sheets
 * (suivi commercial, comptabilité, relances clients).
 */
add_action( 'admin_post_fastfix_export_bookings', function() {
	if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Non autorisé.' );
	check_admin_referer( 'fastfix_export_bookings' );

	$posts  = get_posts( [ 'post_type' => 'fastfix_booking', 'post_status' => 'publish', 'posts_per_page' => -1 ] );
	$labels = fastfix_booking_statuses();

	nocache_headers();
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=fastfix-rendez-vous-' . wp_date( 'Y-m-d' ) . '.csv' );

	$out = fopen( 'php://output', 'w' );
	fprintf( $out, chr( 0xEF ) . chr( 0xBB ) . chr( 0xBF ) ); // BOM UTF-8 pour Excel

	fputcsv( $out, [
		'Référence', 'Date', 'Statut', 'Appareil', 'Réparation(s)', 'Prix estimé',
		'Nom', 'Téléphone', 'E-mail', 'Type client', 'Entreprise', 'Message',
	], ';' );

	foreach ( $posts as $p ) {
		$status = get_post_meta( $p->ID, '_fastfix_status', true ) ?: 'nouveau';
		fputcsv( $out, [
			get_post_meta( $p->ID, '_fastfix_reference', true ),
			get_the_date( 'd/m/Y H:i', $p ),
			$labels[ $status ] ?? $status,
			get_post_meta( $p->ID, '_fastfix_device_label', true ),
			get_post_meta( $p->ID, '_fastfix_repairs_label', true ),
			get_post_meta( $p->ID, '_fastfix_price_total', true ),
			get_post_meta( $p->ID, '_fastfix_name', true ),
			get_post_meta( $p->ID, '_fastfix_phone', true ),
			get_post_meta( $p->ID, '_fastfix_email', true ),
			get_post_meta( $p->ID, '_fastfix_client_type', true ),
			get_post_meta( $p->ID, '_fastfix_company', true ),
			get_post_meta( $p->ID, '_fastfix_message', true ),
		], ';' );
	}

	fclose( $out );
	exit;
} );
