<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Custom Post Type: fastfix_booking
 * Stocke chaque demande de rendez-vous soumise depuis le site fastfix.be.
 * Nesté sous le menu "fastfix-menu" (voir admin-pages.php) — n'apparaît
 * jamais dans les listes de contenu habituelles du site Hascom.
 */
function fastfix_register_booking_cpt() {
	register_post_type( 'fastfix_booking', [
		'label'           => 'Rendez-vous',
		'labels'          => [
			'name'          => 'Rendez-vous',
			'singular_name' => 'Rendez-vous',
			'add_new_item'  => 'Ajouter un rendez-vous',
			'edit_item'     => 'Détail du rendez-vous',
			'search_items'  => 'Rechercher un rendez-vous',
			'not_found'     => 'Aucun rendez-vous trouvé',
		],
		'public'          => false,
		'show_ui'         => true,
		'show_in_menu'    => 'fastfix-menu',
		'capability_type' => 'post',
		'map_meta_cap'    => true,
		'supports'        => [ 'title' ],
		'show_in_rest'    => false, // namespace REST dédié dans rest-api.php
	] );
}
add_action( 'init', 'fastfix_register_booking_cpt' );

function fastfix_booking_statuses() {
	return [
		'nouveau'  => 'Nouveau',
		'confirme' => 'Confirmé',
		'termine'  => 'Terminé',
		'annule'   => 'Annulé',
	];
}

/* ── Colonnes personnalisées dans la liste ── */
add_filter( 'manage_fastfix_booking_posts_columns', function( $columns ) {
	$new                       = [];
	$new['cb']                 = $columns['cb'];
	$new['title']              = 'Référence';
	$new['fastfix_device']     = 'Appareil';
	$new['fastfix_repairs']    = 'Réparation(s)';
	$new['fastfix_contact']    = 'Contact';
	$new['fastfix_status']     = 'Statut';
	$new['date']               = $columns['date'];
	return $new;
} );

add_action( 'manage_fastfix_booking_posts_custom_column', function( $column, $post_id ) {
	switch ( $column ) {
		case 'fastfix_device':
			echo esc_html( get_post_meta( $post_id, '_fastfix_device_label', true ) );
			break;
		case 'fastfix_repairs':
			echo esc_html( get_post_meta( $post_id, '_fastfix_repairs_label', true ) );
			break;
		case 'fastfix_contact':
			$name  = get_post_meta( $post_id, '_fastfix_name', true );
			$phone = get_post_meta( $post_id, '_fastfix_phone', true );
			$email = get_post_meta( $post_id, '_fastfix_email', true );
			echo esc_html( $name ) . '<br><small>' . esc_html( $phone ) . ' · ' . esc_html( $email ) . '</small>';
			break;
		case 'fastfix_status':
			$status = get_post_meta( $post_id, '_fastfix_status', true ) ?: 'nouveau';
			$labels = fastfix_booking_statuses();
			$colors = [ 'nouveau' => '#B08C00', 'confirme' => '#2563EB', 'termine' => '#16A34A', 'annule' => '#DC2626' ];
			$color  = $colors[ $status ] ?? '#666';
			echo '<span style="display:inline-block;padding:3px 10px;border-radius:999px;background:' . esc_attr( $color ) . ';color:#fff;font-size:11px;font-weight:600;">' . esc_html( $labels[ $status ] ?? $status ) . '</span>';
			break;
	}
}, 10, 2 );

/* ── Détail + changement de statut ── */
add_action( 'add_meta_boxes', function() {
	add_meta_box( 'fastfix_booking_detail', 'Détail de la demande', 'fastfix_render_booking_meta_box', 'fastfix_booking', 'normal', 'high' );
} );

function fastfix_render_booking_meta_box( $post ) {
	wp_nonce_field( 'fastfix_save_booking', 'fastfix_booking_nonce' );

	$fields = [
		'Référence'     => get_post_meta( $post->ID, '_fastfix_reference', true ),
		'Appareil'      => get_post_meta( $post->ID, '_fastfix_device_label', true ),
		'Réparation(s)' => get_post_meta( $post->ID, '_fastfix_repairs_label', true ),
		'Prix estimé'   => ( get_post_meta( $post->ID, '_fastfix_price_total', true ) !== '' ) ? get_post_meta( $post->ID, '_fastfix_price_total', true ) . ' €' : '',
		'Nom'           => get_post_meta( $post->ID, '_fastfix_name', true ),
		'Téléphone'     => get_post_meta( $post->ID, '_fastfix_phone', true ),
		'Email'         => get_post_meta( $post->ID, '_fastfix_email', true ),
		'Type client'   => get_post_meta( $post->ID, '_fastfix_client_type', true ),
		'Entreprise'    => get_post_meta( $post->ID, '_fastfix_company', true ),
		'Message'       => get_post_meta( $post->ID, '_fastfix_message', true ),
	];

	echo '<table class="form-table">';
	foreach ( $fields as $label => $value ) {
		if ( $value === '' ) continue;
		echo '<tr><th style="width:160px;">' . esc_html( $label ) . '</th><td>' . nl2br( esc_html( $value ) ) . '</td></tr>';
	}
	echo '</table>';

	$status = get_post_meta( $post->ID, '_fastfix_status', true ) ?: 'nouveau';
	echo '<p><label for="fastfix_status"><strong>Statut :</strong></label> ';
	echo '<select name="fastfix_status" id="fastfix_status">';
	foreach ( fastfix_booking_statuses() as $key => $label ) {
		echo '<option value="' . esc_attr( $key ) . '" ' . selected( $status, $key, false ) . '>' . esc_html( $label ) . '</option>';
	}
	echo '</select></p>';
}

add_action( 'save_post_fastfix_booking', function( $post_id ) {
	if ( ! isset( $_POST['fastfix_booking_nonce'] ) || ! wp_verify_nonce( $_POST['fastfix_booking_nonce'], 'fastfix_save_booking' ) ) return;
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if ( ! current_user_can( 'edit_post', $post_id ) ) return;

	if ( isset( $_POST['fastfix_status'] ) ) {
		update_post_meta( $post_id, '_fastfix_status', sanitize_key( $_POST['fastfix_status'] ) );
	}
} );
