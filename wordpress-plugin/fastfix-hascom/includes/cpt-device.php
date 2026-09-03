<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Custom Post Type: fastfix_device
 * Une fiche = un modèle d'appareil exact (ex: "iPhone 16 Pro Max"),
 * avec sa photo (média WordPress) et ses numéros de modèle.
 * Éditable depuis wp-admin → FastFix → Appareils, exposé au frontend
 * via GET /wp-json/fastfix/v1/devices.
 */
function fastfix_brand_for_device_type( $device_type ) {
	$map = [
		'iphone'       => 'apple',
		'ipad'         => 'apple',
		'apple-watch'  => 'apple',
		'macbook'      => 'apple',
		'galaxy-s'     => 'samsung',
		'galaxy-a'     => 'samsung',
		'galaxy-tab'   => 'samsung',
		'galaxy-z'     => 'samsung',
		'pixel'        => 'google',
		'pixel-tablet' => 'google',
	];
	return $map[ $device_type ] ?? 'apple';
}

function fastfix_brand_choices() {
	return [ 'apple' => 'Apple', 'samsung' => 'Samsung', 'google' => 'Google' ];
}

function fastfix_register_device_cpt() {
	add_theme_support( 'post-thumbnails' ); // au cas où le thème du site ne l'active pas déjà

	register_post_type( 'fastfix_device', [
		'label'           => 'Appareils',
		'labels'          => [
			'name'          => 'Appareils',
			'singular_name' => 'Appareil',
			'add_new_item'  => 'Ajouter un appareil',
			'edit_item'     => 'Modifier l\'appareil',
			'search_items'  => 'Rechercher un appareil',
			'not_found'     => 'Aucun appareil trouvé',
		],
		'public'          => false,
		'show_ui'         => true,
		'show_in_menu'    => 'fastfix-menu',
		'capability_type' => 'post',
		'map_meta_cap'    => true,
		'supports'        => [ 'title', 'thumbnail', 'page-attributes' ],
		'show_in_rest'    => false,
	] );
}
add_action( 'init', 'fastfix_register_device_cpt' );

/* ── Colonnes personnalisées ── */
add_filter( 'manage_fastfix_device_posts_columns', function( $columns ) {
	$new                    = [];
	$new['cb']              = $columns['cb'];
	$new['fastfix_thumb']   = 'Photo';
	$new['title']           = 'Modèle';
	$new['fastfix_brand']   = 'Marque';
	$new['fastfix_type']     = 'Type';
	$new['fastfix_numbers']  = 'Numéros de modèle';
	$new['fastfix_featured'] = 'Page d\'accueil';
	$new['date']             = $columns['date'];
	return $new;
} );

add_action( 'manage_fastfix_device_posts_custom_column', function( $column, $post_id ) {
	switch ( $column ) {
		case 'fastfix_thumb':
			echo has_post_thumbnail( $post_id )
				? get_the_post_thumbnail( $post_id, [ 40, 40 ], [ 'style' => 'object-fit:contain;' ] )
				: '<span style="color:#b32d2e;">Aucune photo</span>';
			break;
		case 'fastfix_brand':
			$labels = fastfix_brand_choices();
			echo esc_html( $labels[ get_post_meta( $post_id, '_fastfix_brand', true ) ] ?? '—' );
			break;
		case 'fastfix_type':
			$labels = fastfix_device_type_choices();
			$type   = get_post_meta( $post_id, '_fastfix_device_type', true );
			echo esc_html( $labels[ $type ] ?? $type );
			break;
		case 'fastfix_numbers':
			echo esc_html( get_post_meta( $post_id, '_fastfix_model_numbers', true ) );
			break;
		case 'fastfix_featured':
			if ( get_post_meta( $post_id, '_fastfix_featured', true ) === '1' ) {
				$price = get_post_meta( $post_id, '_fastfix_starting_price', true );
				echo '<span style="color:#16A34A;font-weight:600;">★ Populaire</span>' . ( $price !== '' ? ' (' . esc_html( $price ) . ' €)' : '' );
			} else {
				echo '<span style="color:#888;">—</span>';
			}
			break;
	}
}, 10, 2 );

/* ── Filtre par type dans la liste ── */
add_action( 'restrict_manage_posts', function( $post_type ) {
	if ( $post_type !== 'fastfix_device' ) return;
	$selected = $_GET['fastfix_device_filter'] ?? '';
	echo '<select name="fastfix_device_filter">';
	echo '<option value="">Tous les types</option>';
	foreach ( fastfix_device_type_choices() as $key => $label ) {
		if ( $key === 'default' ) continue;
		echo '<option value="' . esc_attr( $key ) . '" ' . selected( $selected, $key, false ) . '>' . esc_html( $label ) . '</option>';
	}
	echo '</select>';
} );

add_filter( 'parse_query', function( $query ) {
	global $pagenow;
	if ( ! is_admin() || $pagenow !== 'edit.php' ) return;
	if ( ( $query->query['post_type'] ?? '' ) !== 'fastfix_device' ) return;
	if ( ! empty( $_GET['fastfix_device_filter'] ) ) {
		$query->query_vars['meta_key']   = '_fastfix_device_type';
		$query->query_vars['meta_value'] = sanitize_key( $_GET['fastfix_device_filter'] );
	}
} );

/* ── Meta box d'édition ── */
add_action( 'add_meta_boxes', function() {
	add_meta_box( 'fastfix_device_detail', 'Détails de l\'appareil', 'fastfix_render_device_meta_box', 'fastfix_device', 'normal', 'high' );
} );

function fastfix_render_device_meta_box( $post ) {
	wp_nonce_field( 'fastfix_save_device', 'fastfix_device_nonce' );

	$device_type    = get_post_meta( $post->ID, '_fastfix_device_type', true ) ?: 'iphone';
	$model_numbers  = get_post_meta( $post->ID, '_fastfix_model_numbers', true );
	$featured       = get_post_meta( $post->ID, '_fastfix_featured', true );
	$starting_price = get_post_meta( $post->ID, '_fastfix_starting_price', true );
	$choices        = fastfix_device_type_choices();
	unset( $choices['default'] );
	?>
	<p>
		<label for="fastfix_device_type"><strong>Type d'appareil</strong></label><br>
		<select name="fastfix_device_type" id="fastfix_device_type" style="width:100%;max-width:300px;">
			<?php foreach ( $choices as $key => $label ) : ?>
				<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $device_type, $key ); ?>><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>
		<span class="description">La marque (Apple/Samsung/Google) est déduite automatiquement du type.</span>
	</p>
	<p>
		<label for="fastfix_model_numbers"><strong>Numéros de modèle</strong></label><br>
		<input type="text" name="fastfix_model_numbers" id="fastfix_model_numbers" value="<?php echo esc_attr( $model_numbers ); ?>" style="width:100%;max-width:400px;" placeholder="ex: A3526, A3527" />
	</p>
	<hr>
	<p>
		<label>
			<input type="checkbox" name="fastfix_featured" value="1" <?php checked( $featured, '1' ); ?> />
			<strong>Appareil populaire</strong> — l'afficher dans la section "Appareils populaires" de la page d'accueil
		</label>
	</p>
	<p>
		<label for="fastfix_starting_price"><strong>Prix affiché sur la page d'accueil</strong> (€, "à partir de")</label><br>
		<input type="number" step="1" min="0" name="fastfix_starting_price" id="fastfix_starting_price" value="<?php echo esc_attr( $starting_price ); ?>" style="width:100%;max-width:150px;" placeholder="ex: 79" />
	</p>
	<p class="description">
		Ajoutez la <strong>photo</strong> de l'appareil via le module "Image mise en avant" dans la colonne de droite.
		Le champ <strong>Ordre</strong> (module "Attributs") contrôle l'ordre d'affichage dans la liste des modèles
		et dans la section "Appareils populaires".
	</p>
	<?php
}

add_action( 'save_post_fastfix_device', function( $post_id ) {
	if ( ! isset( $_POST['fastfix_device_nonce'] ) || ! wp_verify_nonce( $_POST['fastfix_device_nonce'], 'fastfix_save_device' ) ) return;
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if ( ! current_user_can( 'edit_post', $post_id ) ) return;

	if ( isset( $_POST['fastfix_device_type'] ) ) {
		$type = sanitize_key( $_POST['fastfix_device_type'] );
		update_post_meta( $post_id, '_fastfix_device_type', $type );
		update_post_meta( $post_id, '_fastfix_brand', fastfix_brand_for_device_type( $type ) );
	}
	if ( isset( $_POST['fastfix_model_numbers'] ) ) {
		update_post_meta( $post_id, '_fastfix_model_numbers', sanitize_text_field( $_POST['fastfix_model_numbers'] ) );
	}
	// '0' (et non '') pour distinguer "décoché volontairement" de "jamais configuré",
	// afin que la migration ne recoche pas un appareil que vous avez retiré.
	update_post_meta( $post_id, '_fastfix_featured', isset( $_POST['fastfix_featured'] ) ? '1' : '0' );
	if ( isset( $_POST['fastfix_starting_price'] ) ) {
		update_post_meta( $post_id, '_fastfix_starting_price', sanitize_text_field( $_POST['fastfix_starting_price'] ) );
	}
} );

/**
 * Seed initial : migre les ~80 modèles codés en dur dans BookingWizard.tsx.
 * Aucune image n'est importée (elles vivaient dans le projet Astro, pas sur
 * ce serveur) — à uploader manuellement depuis wp-admin, un placeholder
 * générique est utilisé côté frontend en attendant.
 */
function fastfix_seed_default_devices() {
	$existing = get_posts( [ 'post_type' => 'fastfix_device', 'posts_per_page' => 1, 'post_status' => 'any' ] );
	if ( ! empty( $existing ) ) return;

	$seed = [
		'iphone' => [
			[ 'iPhone 17 Pro Max', 'A3526, A3527' ], [ 'iPhone 17 Pro', 'A3518, A3523' ], [ 'iPhone 17', 'A3520, A3526, A3518' ],
			[ 'iPhone 16 Pro Max', 'A3396, A3004, A3295' ], [ 'iPhone 16 Pro', 'A3293, A3083' ], [ 'iPhone 16 Plus', 'A3190, A3082, A3288' ],
			[ 'iPhone 16', 'A3287, A3081, A3286' ], [ 'iPhone 15 Pro Max', 'A2849, A3105, A3106' ], [ 'iPhone 15 Pro', 'A2848, A3101, A3103' ],
			[ 'iPhone 15 Plus', 'A2884, A2847, A3093' ], [ 'iPhone 15', 'A2846, A3090, A3089' ], [ 'iPhone 14 Pro Max', 'A2894, A2651, A2893' ],
			[ 'iPhone 14 Pro', 'A2890, A2650, A2889' ], [ 'iPhone 14 Plus', 'A2886, A2632, A2885' ], [ 'iPhone 14', 'A2882, A2649, A2881' ],
			[ 'iPhone 13 Pro Max', 'A2643, A2484, A2641' ], [ 'iPhone 13 Pro', 'A2638, A2483, A2636' ], [ 'iPhone 13', 'A2633, A2482, A2631' ],
			[ 'iPhone 12 Pro Max', 'A2411, A2342, A2410' ], [ 'iPhone 12 Pro', 'A2407, A2341, A2406' ], [ 'iPhone 12', 'A2403, A2172, A2402' ],
			[ 'iPhone 11 Pro Max', 'A2218, A2161, A2220' ], [ 'iPhone 11 Pro', 'A2215, A2160, A2217' ], [ 'iPhone 11', 'A2221, A2111, A2223' ],
			[ 'iPhone XS Max', 'A2097, A1920, A2100' ], [ 'iPhone XS', 'A2097, A1920, A2100' ], [ 'iPhone XR', 'A2105, A1984, A2107' ],
			[ 'iPhone X', 'A1865, A1901, A1902' ], [ 'iPhone 8 Plus', 'A1864, A1897, A1898' ], [ 'iPhone 8', 'A1863, A1905, A1906' ],
		],
		'ipad' => [
			[ 'iPad Pro 13" M5', 'A3456' ], [ 'iPad Pro 11" M5', 'A3455' ], [ 'iPad Pro 13" M4', 'A2926, A2930' ], [ 'iPad Pro 11" M4', 'A2836, A2837' ],
			[ 'iPad Air M3', 'A3006' ], [ 'iPad Air M2', 'A2898, A2899' ], [ 'iPad 10e gén.', 'A2696, A2757' ], [ 'iPad Mini 7', 'A2993, A2994' ],
		],
		'apple-watch' => [
			[ 'Apple Watch Ultra 2', 'A2703' ], [ 'Apple Watch Series 10', 'A3000' ], [ 'Apple Watch Series 9', 'A2978' ], [ 'Apple Watch SE (2e)', 'A2725' ],
		],
		'macbook' => [
			[ 'MacBook Air 15" M4', '2025' ], [ 'MacBook Air 13" M4', '2025' ], [ 'MacBook Pro 16" M4 Pro/Max', '2024' ],
			[ 'MacBook Pro 14" M4', '2024' ], [ 'MacBook Air 15" M3', '2024' ], [ 'MacBook Air 13" M3', '2024' ],
		],
		'galaxy-s' => [
			[ 'Galaxy S26 Ultra', 'SM-S938B' ], [ 'Galaxy S26+', 'SM-S936B' ], [ 'Galaxy S26', 'SM-S931B' ], [ 'Galaxy S25 Ultra', 'SM-S928B' ],
			[ 'Galaxy S25+', 'SM-S926B' ], [ 'Galaxy S25', 'SM-S921B' ], [ 'Galaxy S24 Ultra', 'SM-S928B' ], [ 'Galaxy S24+', 'SM-S926B' ],
			[ 'Galaxy S24', 'SM-S921B' ], [ 'Galaxy S23 Ultra', 'SM-S918B' ], [ 'Galaxy S23+', 'SM-S916B' ], [ 'Galaxy S23', 'SM-S911B' ],
		],
		'galaxy-a' => [
			[ 'Galaxy A56', 'SM-A566B' ], [ 'Galaxy A36', 'SM-A366B' ], [ 'Galaxy A16', 'SM-A166B' ],
			[ 'Galaxy A55', 'SM-A556B' ], [ 'Galaxy A35', 'SM-A356B' ], [ 'Galaxy A15', 'SM-A156B' ],
		],
		'galaxy-tab' => [
			[ 'Galaxy Tab S10 Ultra', 'SM-X920' ], [ 'Galaxy Tab S10+', 'SM-X820' ], [ 'Galaxy Tab S9 FE', 'SM-X510' ],
		],
		'galaxy-z' => [
			[ 'Galaxy Z Fold 6', 'SM-F956B' ], [ 'Galaxy Z Flip 6', 'SM-F741B' ], [ 'Galaxy Z Fold 5', 'SM-F946B' ], [ 'Galaxy Z Flip 5', 'SM-F731B' ],
		],
		'pixel' => [
			[ 'Pixel 10 Pro', '2025' ], [ 'Pixel 10', '2025' ], [ 'Pixel 9 Pro XL', '2024' ], [ 'Pixel 9 Pro', '2024' ],
			[ 'Pixel 9', '2024' ], [ 'Pixel 8 Pro', '2023' ], [ 'Pixel 8', '2023' ],
		],
		'pixel-tablet' => [
			[ 'Pixel Tablet', '2023' ],
		],
	];

	// Modèles à marquer "populaire" pour la section "Appareils populaires" de
	// la page d'accueil, avec le prix affiché ("à partir de").
	$featured = [
		'iPhone 16'          => 79,
		'iPhone 17 Pro'      => 89,
		'iPhone 16 Pro Max'  => 89,
		'iPhone 17'          => 79,
		'Galaxy S26 Ultra'   => 89,
		'Galaxy S26'         => 79,
		'Galaxy S24'         => 69,
		'iPad Pro 13" M5'    => 99,
		'MacBook Air 15" M4' => 129,
	];

	$order = 0;
	foreach ( $seed as $device_type => $models ) {
		foreach ( $models as [ $name, $numbers ] ) {
			$post_id = wp_insert_post( [
				'post_type'   => 'fastfix_device',
				'post_title'  => $name,
				'post_status' => 'publish',
				'menu_order'  => $order++,
			] );
			if ( is_wp_error( $post_id ) ) continue;

			update_post_meta( $post_id, '_fastfix_device_type', $device_type );
			update_post_meta( $post_id, '_fastfix_brand', fastfix_brand_for_device_type( $device_type ) );
			update_post_meta( $post_id, '_fastfix_model_numbers', $numbers );

			if ( isset( $featured[ $name ] ) ) {
				update_post_meta( $post_id, '_fastfix_featured', '1' );
				update_post_meta( $post_id, '_fastfix_starting_price', $featured[ $name ] );
			}
		}
	}
}

/**
 * Marque comme "populaire" les modèles déjà existants qui correspondent à
 * l'ancienne liste codée en dur de la page d'accueil — utile quand les
 * appareils ont été seedés AVANT l'ajout du champ "populaire" (donc que
 * fastfix_seed_default_devices() ne s'exécutera plus, ses posts existant déjà).
 * Ne modifie jamais un appareil déjà marqué manuellement dans wp-admin.
 */
function fastfix_seed_featured_devices() {
	$featured = [
		'iPhone 16'          => 79,
		'iPhone 17 Pro'      => 89,
		'iPhone 16 Pro Max'  => 89,
		'iPhone 17'          => 79,
		'Galaxy S26 Ultra'   => 89,
		'Galaxy S26'         => 79,
		'Galaxy S24'         => 69,
		'iPad Pro 13" M5'    => 99,
		'MacBook Air 15" M4' => 129,
	];

	foreach ( $featured as $name => $price ) {
		$posts = get_posts( [
			'post_type'      => 'fastfix_device',
			'title'          => $name,
			'posts_per_page' => 1,
			'post_status'    => 'any',
		] );
		if ( empty( $posts ) ) continue;

		$post_id = $posts[0]->ID;
		if ( get_post_meta( $post_id, '_fastfix_featured', true ) === '' ) {
			update_post_meta( $post_id, '_fastfix_featured', '1' );
			update_post_meta( $post_id, '_fastfix_starting_price', $price );
		}
	}
}
