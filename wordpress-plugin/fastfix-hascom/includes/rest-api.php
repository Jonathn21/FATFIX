<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * API REST dédiée FastFix — namespace "fastfix/v1", isolé du reste du site.
 *
 *   GET  /wp-json/fastfix/v1/pricing   → grille des tarifs (public)
 *   GET  /wp-json/fastfix/v1/devices   → catalogue des modèles, avec photos (public)
 *   GET  /wp-json/fastfix/v1/repairs               → fiches génériques par famille (public)
 *   GET  /wp-json/fastfix/v1/repairs?device_id=123 → fiches résolues pour un modèle précis (public)
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

	register_rest_route( 'fastfix/v1', '/devices', [
		'methods'             => 'GET',
		'callback'            => 'fastfix_get_devices_list',
		'permission_callback' => '__return_true',
	] );

	register_rest_route( 'fastfix/v1', '/refurbished', [
		'methods'             => 'GET',
		'callback'            => 'fastfix_get_refurbished_list',
		'permission_callback' => '__return_true',
	] );

	register_rest_route( 'fastfix/v1', '/repairs', [
		'methods'             => 'GET',
		'callback'            => 'fastfix_get_repairs_grouped',
		'permission_callback' => '__return_true',
		'args'                => [
			'device_id' => [ 'required' => false, 'sanitize_callback' => 'absint' ],
		],
	] );

	register_rest_route( 'fastfix/v1', '/repairs/featured', [
		'methods'             => 'GET',
		'callback'            => 'fastfix_get_featured_repairs',
		'permission_callback' => '__return_true',
	] );

	register_rest_route( 'fastfix/v1', '/reviews', [
		'methods'             => 'GET',
		'callback'            => 'fastfix_get_reviews_list',
		'permission_callback' => '__return_true',
	] );

	register_rest_route( 'fastfix/v1', '/faq', [
		'methods'             => 'GET',
		'callback'            => 'fastfix_get_faq_list',
		'permission_callback' => '__return_true',
	] );

	register_rest_route( 'fastfix/v1', '/config', [
		'methods'             => 'GET',
		'callback'            => 'fastfix_get_site_config',
		'permission_callback' => '__return_true',
	] );
} );

/**
 * Réglages du site + avis + FAQ en une seule requête : le frontend n'a
 * besoin que de celle-ci pour afficher coordonnées, horaires, avis et FAQ.
 */
function fastfix_get_site_config() {
	$s      = fastfix_get_settings();
	$status = fastfix_open_status();

	$socials = [];
	foreach ( [ 'facebook', 'instagram', 'linkedin', 'tiktok' ] as $network ) {
		if ( ! empty( $s[ $network ] ) ) $socials[ $network ] = $s[ $network ];
	}

	return rest_ensure_response( [
		'business' => [
			'name'      => $s['business_name'],
			'tagline'   => $s['tagline'],
			'legalName' => $s['legal_name'],
			'intro'     => $s['intro'],
		],
		'contact' => [
			'phone'     => $s['phone'],
			'phoneLink' => $s['phone_link'],
			'whatsapp'  => $s['whatsapp'],
			'email'     => $s['email'],
		],
		'address' => [
			'street'     => $s['address'],
			'postalCode' => $s['postal_code'],
			'city'       => $s['city'],
			'full'       => trim( $s['address'] . ', ' . $s['postal_code'] . ' ' . $s['city'] ),
			'mapsUrl'    => $s['maps_url'],
		],
		'hours' => [
			'days'     => $s['hours'],
			'summary'  => fastfix_hours_summary(),
			'isOpen'   => $status['open'],
			'status'   => $status['label'],
		],
		'socials' => $socials,
		'stats' => [
			'googleRating'  => $s['google_rating'],
			'googleReviews' => $s['google_reviews'],
			'repairsCount'  => $s['repairs_count'],
			'sinceYear'     => $s['since_year'],
		],
		'promises' => array_values( array_filter( [ $s['promise_1'], $s['promise_2'], $s['promise_3'] ] ) ),
		'reviews'  => fastfix_get_reviews_data(),
		'faq'      => fastfix_get_faq_data(),
	] );
}

function fastfix_get_reviews_data() {
	$posts   = get_posts( [
		'post_type'      => 'fastfix_review',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => 'menu_order',
		'order'          => 'ASC',
	] );

	$reviews = [];
	foreach ( $posts as $post ) {
		$reviews[] = [
			'name'   => $post->post_title,
			'text'   => wp_strip_all_tags( $post->post_content ),
			'stars'  => (int) ( get_post_meta( $post->ID, '_fastfix_stars', true ) ?: 5 ),
			'device' => get_post_meta( $post->ID, '_fastfix_review_device', true ),
			'date'   => get_post_meta( $post->ID, '_fastfix_review_date', true ) ?: get_the_date( 'j F Y', $post ),
		];
	}
	return $reviews;
}

function fastfix_get_reviews_list() {
	return rest_ensure_response( fastfix_get_reviews_data() );
}

function fastfix_get_faq_data() {
	$posts = get_posts( [
		'post_type'      => 'fastfix_faq',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => 'menu_order',
		'order'          => 'ASC',
	] );

	$faq = [];
	foreach ( $posts as $post ) {
		$faq[] = [
			'q' => $post->post_title,
			'a' => wp_strip_all_tags( $post->post_content ),
		];
	}
	return $faq;
}

function fastfix_get_faq_list() {
	return rest_ensure_response( fastfix_get_faq_data() );
}

/**
 * Liste plate des réparations marquées "populaire", pour la section
 * "Réparations populaires" de la page d'accueil.
 */
function fastfix_get_featured_repairs() {
	$posts = get_posts( [
		'post_type'      => 'fastfix_repair',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => 'menu_order',
		'order'          => 'ASC',
		'meta_key'       => '_fastfix_featured',
		'meta_value'     => '1',
	] );

	$repairs = [];
	foreach ( $posts as $post ) {
		$repair           = fastfix_format_repair_post( $post );
		$repair['icon']   = get_post_meta( $post->ID, '_fastfix_icon', true ) ?: '🔧';
		$device_type      = get_post_meta( $post->ID, '_fastfix_device_type', true );
		$type_labels      = fastfix_device_type_choices();
		$repair['device'] = $type_labels[ $device_type ] ?? $device_type;
		$repairs[]        = $repair;
	}

	return rest_ensure_response( $repairs );
}

/**
 * Catalogue des appareils — un modèle par entrée, avec photo (média WordPress).
 */
function fastfix_get_devices_list() {
	$posts = get_posts( [
		'post_type'      => 'fastfix_device',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => 'menu_order',
		'order'          => 'ASC',
	] );

	$devices = [];
	foreach ( $posts as $post ) {
		$thumb_id       = get_post_thumbnail_id( $post->ID );
		$starting_price = get_post_meta( $post->ID, '_fastfix_starting_price', true );
		$devices[] = [
			'id'            => $post->ID,
			'name'          => $post->post_title,
			'modelNumbers'  => get_post_meta( $post->ID, '_fastfix_model_numbers', true ),
			'brand'         => get_post_meta( $post->ID, '_fastfix_brand', true ),
			'deviceType'    => get_post_meta( $post->ID, '_fastfix_device_type', true ),
			'image'         => $thumb_id ? wp_get_attachment_image_url( $thumb_id, 'medium' ) : '',
			'featured'      => get_post_meta( $post->ID, '_fastfix_featured', true ) === '1',
			'startingPrice' => $starting_price === '' ? null : (float) $starting_price,
		];
	}

	return rest_ensure_response( $devices );
}

/**
 * Catalogue des produits reconditionnés en vente.
 */
function fastfix_get_refurbished_list() {
	$posts = get_posts( [
		'post_type'      => 'fastfix_refurbished',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => 'menu_order',
		'order'          => 'ASC',
	] );

	$products = [];
	foreach ( $posts as $post ) {
		$thumb_id  = get_post_thumbnail_id( $post->ID );
		$price     = get_post_meta( $post->ID, '_fastfix_price', true );
		$old_price = get_post_meta( $post->ID, '_fastfix_old_price', true );
		$badge     = get_post_meta( $post->ID, '_fastfix_badge', true );

		$product = [
			'id'      => $post->ID,
			'name'    => $post->post_title,
			'grade'   => get_post_meta( $post->ID, '_fastfix_grade', true ),
			'price'   => $price === '' ? 0 : (float) $price,
			'color'   => get_post_meta( $post->ID, '_fastfix_color', true ),
			'storage' => get_post_meta( $post->ID, '_fastfix_storage', true ),
			'warranty'=> get_post_meta( $post->ID, '_fastfix_warranty', true ),
			'image'   => $thumb_id ? wp_get_attachment_image_url( $thumb_id, 'medium' ) : '',
		];
		if ( $old_price !== '' ) {
			$product['oldPrice'] = (float) $old_price;
		}
		if ( $badge !== '' ) {
			$product['badge'] = $badge;
		}
		$products[] = $product;
	}

	return rest_ensure_response( $products );
}

/**
 * Convertit une fiche fastfix_repair en tableau exploitable par le frontend.
 */
function fastfix_format_repair_post( $post ) {
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

	return $repair;
}

/**
 * Insère/remplace une réparation dans une structure groupée par catégorie,
 * en conservant l'ordre d'apparition des catégories.
 */
function fastfix_upsert_into_groups( array &$groups, $category, $icon, array $repair ) {
	$group_index = null;
	foreach ( $groups as $i => $group ) {
		if ( $group['title'] === $category ) {
			$group_index = $i;
			break;
		}
	}
	if ( $group_index === null ) {
		$groups[]    = [ 'icon' => $icon, 'title' => $category, 'repairs' => [] ];
		$group_index = count( $groups ) - 1;
	}

	// Si une réparation du même nom existe déjà dans cette catégorie
	// (cas d'une surcharge par modèle), on la remplace plutôt que de dupliquer.
	$repair_index = null;
	foreach ( $groups[ $group_index ]['repairs'] as $i => $r ) {
		if ( $r['name'] === $repair['name'] ) {
			$repair_index = $i;
			break;
		}
	}
	if ( $repair_index === null ) {
		$groups[ $group_index ]['repairs'][] = $repair;
	} else {
		$groups[ $group_index ]['repairs'][ $repair_index ] = $repair;
	}
}

/**
 * Regroupe les fiches fastfix_repair par appareil puis par catégorie.
 *
 * Sans paramètre : renvoie les fiches génériques par famille, format
 *   { iphone: [ { icon, title, repairs: [...] } ], default: [...], ... }
 *
 * Avec ?device_id=123 : renvoie uniquement les catégories de la famille de
 * ce modèle, avec les éventuelles fiches spécifiques à ce modèle exact
 * appliquées par-dessus (remplacent une fiche générique du même nom, ou
 * s'ajoutent si elles n'existent pas génériquement).
 */
function fastfix_get_repairs_grouped( WP_REST_Request $request ) {
	$device_id = (int) $request->get_param( 'device_id' );

	$all_posts = get_posts( [
		'post_type'      => 'fastfix_repair',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => 'menu_order',
		'order'          => 'ASC',
	] );
	$generic_posts = array_filter( $all_posts, function( $p ) {
		return (string) get_post_meta( $p->ID, '_fastfix_device_id', true ) === '';
	} );

	$grouped = [];
	foreach ( $generic_posts as $post ) {
		$device_type = get_post_meta( $post->ID, '_fastfix_device_type', true ) ?: 'default';
		$category    = get_post_meta( $post->ID, '_fastfix_category', true ) ?: 'Réparations';
		$icon        = get_post_meta( $post->ID, '_fastfix_icon', true ) ?: '🔧';

		if ( ! isset( $grouped[ $device_type ] ) ) {
			$grouped[ $device_type ] = [];
		}
		fastfix_upsert_into_groups( $grouped[ $device_type ], $category, $icon, fastfix_format_repair_post( $post ) );
	}

	// Sans device_id : comportement générique par famille (rétro-compatible).
	if ( ! $device_id ) {
		return rest_ensure_response( $grouped );
	}

	// Avec device_id : on résout pour ce modèle précis.
	$device = get_post( $device_id );
	if ( ! $device || $device->post_type !== 'fastfix_device' ) {
		return rest_ensure_response( $grouped );
	}
	$device_type = get_post_meta( $device_id, '_fastfix_device_type', true ) ?: 'default';
	$resolved    = $grouped[ $device_type ] ?? ( $grouped['default'] ?? [] );

	$specific_posts = array_filter( $all_posts, function( $p ) use ( $device_id ) {
		return (int) get_post_meta( $p->ID, '_fastfix_device_id', true ) === $device_id;
	} );

	foreach ( $specific_posts as $post ) {
		$category = get_post_meta( $post->ID, '_fastfix_category', true ) ?: 'Réparations';
		$icon     = get_post_meta( $post->ID, '_fastfix_icon', true ) ?: '🔧';
		fastfix_upsert_into_groups( $resolved, $category, $icon, fastfix_format_repair_post( $post ) );
	}

	return rest_ensure_response( [ $device_type => $resolved ] );
}

/* ── CORS + no-cache, restreint au namespace fastfix/v1 uniquement ──
 * Le cache proxy SiteGround (Speed Optimizer) met en cache les réponses
 * GET par défaut, y compris les routes REST dynamiques. On force donc
 * explicitement "no-store" pour que /pricing et /repairs reflètent
 * toujours les données actuelles de wp-admin, sans devoir vider le cache
 * manuellement à chaque modification.
 */
add_filter( 'rest_pre_serve_request', function( $value, $result, $request ) {
	if ( strpos( $request->get_route(), '/fastfix/v1' ) !== 0 ) {
		return $value;
	}

	$origins_setting = fastfix_get_setting( 'cors_origins', '*' );
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
	header( 'Cache-Control: no-store, no-cache, must-revalidate, max-age=0' );
	header( 'Pragma: no-cache' );

	return $value;
}, 10, 3 );

/* ── Empêche aussi SiteGround Speed Optimizer de mettre en cache ces routes,
 * indépendamment des headers, via son propre filtre d'exclusion d'URL. ── */
add_filter( 'sgo_noptimize_urls', function( $urls ) {
	$urls[] = 'wp-json/fastfix';
	return $urls;
} );
add_filter( 'sgo_dynamic_cache_exception_regex', function( $patterns ) {
	$patterns[] = '#/wp-json/fastfix/#';
	return $patterns;
} );

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

	$to      = fastfix_get_setting( 'notify_email' ) ?: get_option( 'admin_email' );
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
