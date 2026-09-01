<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * API REST dédiée FastFix — namespace "fastfix/v1", isolé du reste du site.
 *
 *   GET  /wp-json/fastfix/v1/pricing   → grille des tarifs (public)
 *   GET  /wp-json/fastfix/v1/repairs   → fiches réparations groupées par appareil (public)
 *   POST /wp-json/fastfix/v1/booking   → soumission d'une demande de RDV (public)
 */
add_action( 'rest_api_init', function() {
	register_rest_route( 'fastfix/v1', '/booking', [
		'methods'             => 'POST',
		'callback'            => 'fastfix_handle_booking_submission',
		'permission_callback' => '__return_true',
	] );

	register_rest_route( 'fastfix/v1', '/pricing', [
		'methods'             => 'GET',
		'callback'            => function() {
			return rest_ensure_response( get_option( 'fastfix_pricing', fastfix_default_pricing() ) );
		},
		'permission_callback' => '__return_true',
	] );

	register_rest_route( 'fastfix/v1', '/repairs', [
		'methods'             => 'GET',
		'callback'            => 'fastfix_get_repairs_grouped',
		'permission_callback' => '__return_true',
	] );
} );

/**
 * Regroupe les fiches fastfix_repair par appareil puis par catégorie,
 * dans le même format que le frontend Astro consomme :
 *   { iphone: [ { icon, title, repairs: [...] } ], default: [...], ... }
 */
function fastfix_get_repairs_grouped() {
	$posts = get_posts( [
		'post_type'      => 'fastfix_repair',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => 'menu_order',
		'order'          => 'ASC',
	] );

	$grouped = [];

	foreach ( $posts as $post ) {
		$device_type = get_post_meta( $post->ID, '_fastfix_device_type', true ) ?: 'default';
		$category    = get_post_meta( $post->ID, '_fastfix_category', true ) ?: 'Réparations';
		$icon        = get_post_meta( $post->ID, '_fastfix_icon', true ) ?: '🔧';

		if ( ! isset( $grouped[ $device_type ] ) ) {
			$grouped[ $device_type ] = [];
		}

		// Cherche un groupe existant pour cette catégorie au sein de cet appareil.
		$group_index = null;
		foreach ( $grouped[ $device_type ] as $i => $group ) {
			if ( $group['title'] === $category ) {
				$group_index = $i;
				break;
			}
		}
		if ( $group_index === null ) {
			$grouped[ $device_type ][] = [ 'icon' => $icon, 'title' => $category, 'repairs' => [] ];
			$group_index = count( $grouped[ $device_type ] ) - 1;
		}

		$price     = get_post_meta( $post->ID, '_fastfix_price', true );
		$old_price = get_post_meta( $post->ID, '_fastfix_old_price', true );
		$features  = get_post_meta( $post->ID, '_fastfix_features', true );

		$repair = [
			'name'     => $post->post_title,
			'desc'     => get_post_meta( $post->ID, '_fastfix_desc', true ),
			'price'    => $price === '' ? 0 : (float) $price,
			'features' => $features ? array_values( array_filter( array_map( 'trim', explode( "\n", $features ) ) ) ) : [],
			'time'     => get_post_meta( $post->ID, '_fastfix_time', true ),
			'warranty' => get_post_meta( $post->ID, '_fastfix_warranty', true ),
		];

		if ( $old_price !== '' ) {
			$repair['oldPrice'] = (float) $old_price;
		}
		$badge = get_post_meta( $post->ID, '_fastfix_badge', true );
		if ( $badge !== '' ) {
			$repair['badge']      = $badge;
			$repair['badgeColor'] = get_post_meta( $post->ID, '_fastfix_badge_color', true ) ?: 'primary';
		}
		$attention = get_post_meta( $post->ID, '_fastfix_attention', true );
		if ( $attention !== '' ) {
			$repair['attention'] = $attention;
		}

		$grouped[ $device_type ][ $group_index ]['repairs'][] = $repair;
	}

	return rest_ensure_response( $grouped );
}

/* ── CORS restreint au namespace fastfix/v1 uniquement ── */
add_filter( 'rest_pre_serve_request', function( $value, $result, $request ) {
	if ( strpos( $request->get_route(), '/fastfix/v1' ) !== 0 ) {
		return $value;
	}

	$origins_setting = get_option( 'fastfix_cors_origins', '*' );
	$origin          = get_http_origin();

	if ( $origins_setting === '*' ) {
		header( 'Access-Control-Allow-Origin: *' );
	} elseif ( $origin ) {
		$allowed = array_map( 'trim', explode( ',', $origins_setting ) );
		if ( in_array( $origin, $allowed, true ) ) {
			header( 'Access-Control-Allow-Origin: ' . esc_url_raw( $origin ) );
		}
	}
	header( 'Access-Control-Allow-Methods: GET, POST, OPTIONS' );
	header( 'Access-Control-Allow-Headers: Content-Type' );

	return $value;
}, 10, 3 );

/* ── Grille de tarifs par défaut (éditable ensuite depuis wp-admin → FastFix → Tarifs) ── */
function fastfix_default_pricing() {
	return [
		'deviceTypes' => [
			'iphone'       => 'iPhone',
			'ipad'         => 'iPad',
			'apple-watch'  => 'Apple Watch',
			'macbook'      => 'MacBook',
			'galaxy-s'     => 'Galaxy S',
			'galaxy-a'     => 'Galaxy A',
			'galaxy-tab'   => 'Galaxy Tab',
			'galaxy-z'     => 'Galaxy Z',
			'pixel'        => 'Pixel',
			'pixel-tablet' => 'Pixel Tablet',
		],
		'repairTypes' => [
			'ecran'        => "Réparation d'écran",
			'batterie'     => 'Remplacement de la batterie',
			'face-arriere' => 'Remplacement de la face arrière',
			'degat-eau'    => 'Réparation dégât des eaux',
			'polissage'    => "Polissage de l'écran",
			'diagnostic'   => 'Diagnostic',
		],
		'prices' => [
			'iphone'       => [ 'ecran' => 89,  'batterie' => 49, 'face-arriere' => 69, 'degat-eau' => 59, 'polissage' => 29, 'diagnostic' => 0 ],
			'ipad'         => [ 'ecran' => 119, 'batterie' => 69, 'face-arriere' => '', 'degat-eau' => 69, 'polissage' => 29, 'diagnostic' => 0 ],
			'apple-watch'  => [ 'ecran' => 79,  'batterie' => 59, 'face-arriere' => '', 'degat-eau' => '', 'polissage' => '', 'diagnostic' => 0 ],
			'macbook'      => [ 'ecran' => 179, 'batterie' => 99, 'face-arriere' => '', 'degat-eau' => 99, 'polissage' => '', 'diagnostic' => 0 ],
			'galaxy-s'     => [ 'ecran' => 99,  'batterie' => 49, 'face-arriere' => 69, 'degat-eau' => 59, 'polissage' => 29, 'diagnostic' => 0 ],
			'galaxy-a'     => [ 'ecran' => 69,  'batterie' => 39, 'face-arriere' => 49, 'degat-eau' => 49, 'polissage' => 25, 'diagnostic' => 0 ],
			'galaxy-tab'   => [ 'ecran' => 99,  'batterie' => 59, 'face-arriere' => '', 'degat-eau' => 59, 'polissage' => 25, 'diagnostic' => 0 ],
			'galaxy-z'     => [ 'ecran' => 199, 'batterie' => 79, 'face-arriere' => '', 'degat-eau' => '', 'polissage' => '', 'diagnostic' => 0 ],
			'pixel'        => [ 'ecran' => 89,  'batterie' => 49, 'face-arriere' => 59, 'degat-eau' => 55, 'polissage' => 25, 'diagnostic' => 0 ],
			'pixel-tablet' => [ 'ecran' => 99,  'batterie' => 59, 'face-arriere' => '', 'degat-eau' => 55, 'polissage' => 25, 'diagnostic' => 0 ],
		],
	];
}

/* ── Réception d'une demande de rendez-vous ── */
function fastfix_handle_booking_submission( WP_REST_Request $request ) {
	$params = $request->get_json_params();
	if ( empty( $params ) ) {
		$params = $request->get_params();
	}

	// Honeypot anti-spam (champ caché côté formulaire, doit rester vide)
	if ( ! empty( $params['website'] ) ) {
		return new WP_Error( 'spam_detected', 'Requête rejetée.', [ 'status' => 400 ] );
	}

	// Limite anti-abus : 5 soumissions max / 10 min / IP
	$ip      = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( $_SERVER['REMOTE_ADDR'] ) : 'unknown';
	$rl_key  = 'fastfix_rl_' . md5( $ip );
	$count   = (int) get_transient( $rl_key );
	if ( $count >= 5 ) {
		return new WP_Error( 'rate_limited', 'Trop de demandes, réessayez plus tard.', [ 'status' => 429 ] );
	}
	set_transient( $rl_key, $count + 1, 10 * MINUTE_IN_SECONDS );

	$name         = sanitize_text_field( $params['name'] ?? '' );
	$email        = sanitize_email( $params['email'] ?? '' );
	$phone        = sanitize_text_field( $params['phone'] ?? '' );
	$message      = sanitize_textarea_field( $params['message'] ?? '' );
	$client_type  = sanitize_text_field( $params['clientType'] ?? 'particulier' );
	$company      = sanitize_text_field( $params['company'] ?? '' );
	$device_label = sanitize_text_field( $params['deviceLabel'] ?? '' );
	$repairs_label = sanitize_text_field( $params['repairsLabel'] ?? '' );
	$price_total  = sanitize_text_field( $params['priceTotal'] ?? '' );

	if ( ! $name || ! $email || ! $phone || ! $device_label ) {
		return new WP_Error( 'missing_fields', 'Champs obligatoires manquants (nom, email, téléphone, appareil).', [ 'status' => 400 ] );
	}
	if ( ! is_email( $email ) ) {
		return new WP_Error( 'invalid_email', 'Email invalide.', [ 'status' => 400 ] );
	}

	$post_id = wp_insert_post( [
		'post_type'   => 'fastfix_booking',
		'post_title'  => sprintf( '%s — %s', $device_label, $name ),
		'post_status' => 'publish',
	] );

	if ( is_wp_error( $post_id ) ) {
		return new WP_Error( 'insert_failed', "Impossible d'enregistrer la demande.", [ 'status' => 500 ] );
	}

	update_post_meta( $post_id, '_fastfix_device_label', $device_label );
	update_post_meta( $post_id, '_fastfix_repairs_label', $repairs_label );
	update_post_meta( $post_id, '_fastfix_price_total', $price_total );
	update_post_meta( $post_id, '_fastfix_name', $name );
	update_post_meta( $post_id, '_fastfix_email', $email );
	update_post_meta( $post_id, '_fastfix_phone', $phone );
	update_post_meta( $post_id, '_fastfix_message', $message );
	update_post_meta( $post_id, '_fastfix_client_type', $client_type );
	update_post_meta( $post_id, '_fastfix_company', $company );
	update_post_meta( $post_id, '_fastfix_status', 'nouveau' );

	$reference = 'FF-' . str_pad( $post_id, 5, '0', STR_PAD_LEFT );
	update_post_meta( $post_id, '_fastfix_reference', $reference );

	$to      = get_option( 'fastfix_notify_email', get_option( 'admin_email' ) );
	$subject = 'Nouvelle demande de RDV FastFix — ' . $reference;
	$body    = "Nouvelle demande de rendez-vous reçue sur fastfix.be\n\n"
		. "Référence : $reference\n"
		. "Appareil : $device_label\n"
		. "Réparation(s) : $repairs_label\n"
		. ( $price_total !== '' ? "Prix estimé : $price_total €\n\n" : "\n" )
		. "Nom : $name\n"
		. "Type client : $client_type" . ( $company ? " ($company)" : '' ) . "\n"
		. "Téléphone : $phone\n"
		. "Email : $email\n"
		. "Message : $message\n\n"
		. 'Voir dans WordPress : ' . admin_url( 'post.php?post=' . $post_id . '&action=edit' );
	wp_mail( $to, $subject, $body );

	return rest_ensure_response( [
		'success'   => true,
		'bookingId' => $post_id,
		'reference' => $reference,
	] );
}
