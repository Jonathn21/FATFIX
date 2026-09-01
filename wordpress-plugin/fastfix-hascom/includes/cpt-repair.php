<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Custom Post Type: fastfix_repair
 * Une fiche = une réparation proposée pour un type d'appareil donné
 * (ex: "Remplacement de la vitre" pour iPhone). Éditable depuis
 * wp-admin → FastFix → Réparations, exposée au frontend via
 * GET /wp-json/fastfix/v1/repairs.
 */
function fastfix_device_type_choices() {
	return [
		'default'      => 'Générique (tout appareil sans fiche dédiée)',
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
	];
}

function fastfix_badge_color_choices() {
	return [
		''        => 'Aucun',
		'red'     => 'Rouge',
		'primary' => 'Doré (couleur principale)',
	];
}

function fastfix_register_repair_cpt() {
	register_post_type( 'fastfix_repair', [
		'label'           => 'Réparations',
		'labels'          => [
			'name'          => 'Réparations',
			'singular_name' => 'Réparation',
			'add_new_item'  => 'Ajouter une réparation',
			'edit_item'     => 'Modifier la réparation',
			'search_items'  => 'Rechercher une réparation',
			'not_found'     => 'Aucune réparation trouvée',
		],
		'public'          => false,
		'show_ui'         => true,
		'show_in_menu'    => 'fastfix-menu',
		'capability_type' => 'post',
		'map_meta_cap'    => true,
		'supports'        => [ 'title', 'page-attributes' ], // page-attributes → champ "Ordre"
		'show_in_rest'    => false,
	] );
}
add_action( 'init', 'fastfix_register_repair_cpt' );

/* ── Colonnes personnalisées ── */
add_filter( 'manage_fastfix_repair_posts_columns', function( $columns ) {
	$new                     = [];
	$new['cb']               = $columns['cb'];
	$new['title']            = 'Réparation';
	$new['fastfix_device']   = 'Famille';
	$new['fastfix_model']    = 'Modèle spécifique';
	$new['fastfix_category'] = 'Catégorie';
	$new['fastfix_price']    = 'Prix';
	$new['fastfix_badge']    = 'Badge';
	$new['fastfix_featured'] = 'Page d\'accueil';
	$new['date']             = $columns['date'];
	return $new;
} );

add_action( 'manage_fastfix_repair_posts_custom_column', function( $column, $post_id ) {
	switch ( $column ) {
		case 'fastfix_device':
			$type   = get_post_meta( $post_id, '_fastfix_device_type', true );
			$labels = fastfix_device_type_choices();
			echo esc_html( $labels[ $type ] ?? $type );
			break;
		case 'fastfix_model':
			$device_id = get_post_meta( $post_id, '_fastfix_device_id', true );
			echo $device_id ? esc_html( get_the_title( $device_id ) ) : '<span style="color:#888;">— toute la famille —</span>';
			break;
		case 'fastfix_category':
			echo esc_html( get_post_meta( $post_id, '_fastfix_category', true ) );
			break;
		case 'fastfix_price':
			$price = get_post_meta( $post_id, '_fastfix_price', true );
			echo ( $price === '0' || $price === 0 ) ? 'Gratuit' : esc_html( $price ) . ' €';
			break;
		case 'fastfix_badge':
			$badge = get_post_meta( $post_id, '_fastfix_badge', true );
			echo $badge ? esc_html( $badge ) : '—';
			break;
		case 'fastfix_featured':
			if ( get_post_meta( $post_id, '_fastfix_featured', true ) === '1' ) {
				echo '<span style="color:#16A34A;font-weight:600;">★ Populaire</span>';
			} else {
				echo '<span style="color:#888;">—</span>';
			}
			break;
	}
}, 10, 2 );

/* ── Filtre par appareil dans la liste ── */
add_action( 'restrict_manage_posts', function( $post_type ) {
	if ( $post_type !== 'fastfix_repair' ) return;
	$selected = $_GET['fastfix_device_filter'] ?? '';
	echo '<select name="fastfix_device_filter">';
	echo '<option value="">Tous les appareils</option>';
	foreach ( fastfix_device_type_choices() as $key => $label ) {
		echo '<option value="' . esc_attr( $key ) . '" ' . selected( $selected, $key, false ) . '>' . esc_html( $label ) . '</option>';
	}
	echo '</select>';
} );

add_filter( 'parse_query', function( $query ) {
	global $pagenow;
	if ( ! is_admin() || $pagenow !== 'edit.php' ) return;
	if ( ( $query->query['post_type'] ?? '' ) !== 'fastfix_repair' ) return;
	if ( ! empty( $_GET['fastfix_device_filter'] ) ) {
		$query->query_vars['meta_key']   = '_fastfix_device_type';
		$query->query_vars['meta_value'] = sanitize_key( $_GET['fastfix_device_filter'] );
	}
} );

/* ── Meta box d'édition ── */
add_action( 'add_meta_boxes', function() {
	add_meta_box( 'fastfix_repair_detail', 'Détails de la réparation', 'fastfix_render_repair_meta_box', 'fastfix_repair', 'normal', 'high' );
} );

function fastfix_render_repair_meta_box( $post ) {
	wp_nonce_field( 'fastfix_save_repair', 'fastfix_repair_nonce' );

	$device_type  = get_post_meta( $post->ID, '_fastfix_device_type', true ) ?: 'iphone';
	$category     = get_post_meta( $post->ID, '_fastfix_category', true );
	$icon         = get_post_meta( $post->ID, '_fastfix_icon', true ) ?: '🔧';
	$desc         = get_post_meta( $post->ID, '_fastfix_desc', true );
	$price        = get_post_meta( $post->ID, '_fastfix_price', true );
	$old_price    = get_post_meta( $post->ID, '_fastfix_old_price', true );
	$badge        = get_post_meta( $post->ID, '_fastfix_badge', true );
	$badge_color  = get_post_meta( $post->ID, '_fastfix_badge_color', true );
	$features     = get_post_meta( $post->ID, '_fastfix_features', true );
	$time         = get_post_meta( $post->ID, '_fastfix_time', true );
	$warranty     = get_post_meta( $post->ID, '_fastfix_warranty', true ) ?: '6 mois de garantie';
	$attention    = get_post_meta( $post->ID, '_fastfix_attention', true );
	$featured     = get_post_meta( $post->ID, '_fastfix_featured', true );
	?>
	<style>.fastfix-field{margin-bottom:16px;}.fastfix-field label{display:block;font-weight:600;margin-bottom:4px;}.fastfix-field .description{margin-top:4px;}.fastfix-row{display:flex;gap:16px;}.fastfix-row>.fastfix-field{flex:1;}</style>

	<div class="fastfix-row">
		<div class="fastfix-field">
			<label for="fastfix_device_type">Famille d'appareil</label>
			<select name="fastfix_device_type" id="fastfix_device_type" style="width:100%;">
				<?php foreach ( fastfix_device_type_choices() as $key => $label ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $device_type, $key ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="fastfix-field">
			<label for="fastfix_category">Catégorie (regroupement)</label>
			<input type="text" name="fastfix_category" id="fastfix_category" value="<?php echo esc_attr( $category ); ?>" style="width:100%;" placeholder="ex: Écran et vitre" />
		</div>
		<div class="fastfix-field" style="max-width:100px;">
			<label for="fastfix_icon">Icône</label>
			<input type="text" name="fastfix_icon" id="fastfix_icon" value="<?php echo esc_attr( $icon ); ?>" style="width:100%;" placeholder="📱" />
		</div>
	</div>

	<div class="fastfix-field">
		<?php
		$device_id = get_post_meta( $post->ID, '_fastfix_device_id', true );
		$models    = get_posts( [ 'post_type' => 'fastfix_device', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC' ] );
		?>
		<label for="fastfix_device_id">Modèle spécifique (optionnel)</label>
		<select name="fastfix_device_id" id="fastfix_device_id" style="width:100%;max-width:400px;">
			<option value="">— Toute la famille "<?php echo esc_html( fastfix_device_type_choices()[ $device_type ] ?? $device_type ); ?>" (par défaut) —</option>
			<?php foreach ( $models as $model ) : ?>
				<option value="<?php echo esc_attr( $model->ID ); ?>" <?php selected( (string) $device_id, (string) $model->ID ); ?>><?php echo esc_html( $model->post_title ); ?></option>
			<?php endforeach; ?>
		</select>
		<p class="description">
			Laissez sur la valeur par défaut pour que cette fiche s'applique à toute la famille (ex: tous les iPhone).
			Choisissez un modèle précis pour <strong>remplacer</strong> le prix/la description de cette réparation
			uniquement pour ce modèle (ex: écran iPhone 16 différent d'écran iPhone 8).
		</p>
	</div>

	<hr>
	<p>
		<label>
			<input type="checkbox" name="fastfix_featured" value="1" <?php checked( $featured, '1' ); ?> />
			<strong>Réparation populaire</strong> — l'afficher dans la section "Réparations populaires" de la page d'accueil
		</label>
	</p>

	<div class="fastfix-field">
		<label for="fastfix_desc">Description ("Idéal pour :")</label>
		<textarea name="fastfix_desc" id="fastfix_desc" rows="2" style="width:100%;"><?php echo esc_textarea( $desc ); ?></textarea>
	</div>

	<div class="fastfix-row">
		<div class="fastfix-field">
			<label for="fastfix_price">Prix (€, 0 = Gratuit)</label>
			<input type="number" step="1" min="0" name="fastfix_price" id="fastfix_price" value="<?php echo esc_attr( $price ); ?>" style="width:100%;" />
		</div>
		<div class="fastfix-field">
			<label for="fastfix_old_price">Ancien prix (optionnel, barré)</label>
			<input type="number" step="1" min="0" name="fastfix_old_price" id="fastfix_old_price" value="<?php echo esc_attr( $old_price ); ?>" style="width:100%;" />
		</div>
	</div>

	<div class="fastfix-row">
		<div class="fastfix-field">
			<label for="fastfix_badge">Texte du badge (optionnel)</label>
			<input type="text" name="fastfix_badge" id="fastfix_badge" value="<?php echo esc_attr( $badge ); ?>" style="width:100%;" placeholder="ex: CHOIX LE PLUS POPULAIRE" />
		</div>
		<div class="fastfix-field">
			<label for="fastfix_badge_color">Couleur du badge</label>
			<select name="fastfix_badge_color" id="fastfix_badge_color" style="width:100%;">
				<?php foreach ( fastfix_badge_color_choices() as $key => $label ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $badge_color, $key ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
	</div>

	<div class="fastfix-field">
		<label for="fastfix_features">Fonctionnalités (une par ligne)</label>
		<textarea name="fastfix_features" id="fastfix_features" rows="4" style="width:100%;"><?php echo esc_textarea( $features ); ?></textarea>
		<p class="description">Une ligne = un point affiché avec une coche ✓.</p>
	</div>

	<div class="fastfix-row">
		<div class="fastfix-field">
			<label for="fastfix_time">Durée estimée</label>
			<input type="text" name="fastfix_time" id="fastfix_time" value="<?php echo esc_attr( $time ); ?>" style="width:100%;" placeholder="ex: 90 min" />
		</div>
		<div class="fastfix-field">
			<label for="fastfix_warranty">Garantie</label>
			<input type="text" name="fastfix_warranty" id="fastfix_warranty" value="<?php echo esc_attr( $warranty ); ?>" style="width:100%;" placeholder="ex: 6 mois de garantie" />
		</div>
	</div>

	<div class="fastfix-field">
		<label for="fastfix_attention">Remarque "Attention" (optionnel)</label>
		<textarea name="fastfix_attention" id="fastfix_attention" rows="2" style="width:100%;"><?php echo esc_textarea( $attention ); ?></textarea>
	</div>

	<p class="description">
		Astuce : le champ <strong>Ordre</strong> (dans le module "Attributs" à droite) contrôle l'ordre d'affichage
		au sein d'une même catégorie et appareil.
	</p>
	<?php
}

add_action( 'save_post_fastfix_repair', function( $post_id ) {
	if ( ! isset( $_POST['fastfix_repair_nonce'] ) || ! wp_verify_nonce( $_POST['fastfix_repair_nonce'], 'fastfix_save_repair' ) ) return;
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if ( ! current_user_can( 'edit_post', $post_id ) ) return;

	$fields = [
		'fastfix_device_type' => 'sanitize_key',
		'fastfix_device_id'   => 'sanitize_text_field',
		'fastfix_category'    => 'sanitize_text_field',
		'fastfix_icon'        => 'sanitize_text_field',
		'fastfix_desc'        => 'sanitize_textarea_field',
		'fastfix_price'       => 'sanitize_text_field',
		'fastfix_old_price'   => 'sanitize_text_field',
		'fastfix_badge'       => 'sanitize_text_field',
		'fastfix_badge_color' => 'sanitize_key',
		'fastfix_features'    => 'sanitize_textarea_field',
		'fastfix_time'        => 'sanitize_text_field',
		'fastfix_warranty'    => 'sanitize_text_field',
		'fastfix_attention'   => 'sanitize_textarea_field',
	];

	foreach ( $fields as $field => $sanitizer ) {
		if ( isset( $_POST[ $field ] ) ) {
			update_post_meta( $post_id, '_' . $field, call_user_func( $sanitizer, $_POST[ $field ] ) );
		}
	}
	update_post_meta( $post_id, '_fastfix_featured', isset( $_POST['fastfix_featured'] ) ? '1' : '' );
} );

/**
 * Données par défaut, migrées depuis BookingWizard.tsx, pour ne pas casser
 * le site au moment du passage à l'API. N'insère rien si des fiches existent déjà.
 */
function fastfix_seed_default_repairs() {
	$existing = get_posts( [ 'post_type' => 'fastfix_repair', 'posts_per_page' => 1, 'post_status' => 'any' ] );
	if ( ! empty( $existing ) ) return;

	$seed = [
		'iphone' => [
			[ 'category' => 'Écran et vitre', 'icon' => '📱', 'repairs' => [
				[ 'name' => 'Remplacement de la vitre', 'desc' => "La vitre est fissurée, mais l'écran fonctionne encore parfaitement.", 'price' => 119, 'old_price' => 149, 'badge' => 'CHOIX LE PLUS POPULAIRE', 'badge_color' => 'red', 'features' => "Conservation de votre écran original\nQualité identique à un écran neuf\nMoins cher qu'un remplacement complet", 'time' => '90 min', 'warranty' => '6 mois de garantie', 'attention' => "L'image et le tactile doivent fonctionner à 100%." ],
				[ 'name' => 'Écran complet OLED original', 'desc' => "Si l'écran ne fonctionne plus ou présente des taches ou des lignes.", 'price' => 499, 'old_price' => 549, 'badge' => 'QUALITÉ PREMIUM', 'badge_color' => 'primary', 'features' => "Qualité Apple 100% originale\nModule écran complet neuf\nRésultat comme neuf garanti", 'time' => '60 min', 'warranty' => '6 mois de garantie' ],
			] ],
			[ 'category' => 'Batterie & Charge', 'icon' => '🔋', 'repairs' => [
				[ 'name' => 'Batterie', 'desc' => "Le téléphone s'éteint, se vide vite ou le pourcentage n'est pas correct.", 'price' => 129, 'old_price' => 159, 'badge' => '100% ORIGINAL', 'badge_color' => 'red', 'features' => "Votre appareil tient toute la journée\nCapacité et durée de vie d'origine\nInstallation professionnelle", 'time' => '60 min', 'warranty' => '6 mois de garantie' ],
				[ 'name' => 'Port de charge', 'desc' => "Le câble de charge tient mal ou l'appareil charge lentement.", 'price' => 59, 'badge' => 'RÉPARATION RAPIDE', 'badge_color' => 'primary', 'features' => "Le câble se clipse à nouveau fermement\nCharge sûre et stable\nTesté après montage", 'time' => '60 min', 'warranty' => '6 mois de garantie' ],
			] ],
			[ 'category' => 'Diagnostic & Logiciel', 'icon' => '🔍', 'repairs' => [
				[ 'name' => 'Diagnostic', 'desc' => "Vous ne savez pas ce qui est cassé, ou l'appareil ne fonctionne plus.", 'price' => 0, 'badge' => 'DIAGNOSTIC CLAIR', 'badge_color' => 'primary', 'features' => "Gratuit en cas de réparation\nEstimation transparente\nRésultat en 30 minutes", 'time' => '30 min', 'warranty' => 'Sans engagement', 'attention' => "Si l'appareil n'est pas réparable, le diagnostic coûte 30 EUR." ],
			] ],
			[ 'category' => 'Vitre arrière & Châssis', 'icon' => '🔧', 'repairs' => [
				[ 'name' => 'Vitre arrière', 'desc' => "La face arrière de votre iPhone est fissurée.", 'price' => 89, 'old_price' => 109, 'badge' => 'RÉPARATION POPULAIRE', 'badge_color' => 'red', 'features' => "Vitre arrière identique à l'original\nAdhésif étanche remplacé\nFinition impeccable", 'time' => '90 min', 'warranty' => '6 mois de garantie' ],
			] ],
			[ 'category' => 'Dégât des eaux', 'icon' => '💧', 'repairs' => [
				[ 'name' => 'Traitement dégât des eaux', 'desc' => "Votre appareil est tombé dans l'eau ou a été exposé à l'humidité.", 'price' => 79, 'features' => "Nettoyage ultrasonique complet\nSéchage professionnel\nDiagnostic de tous les composants", 'time' => '24-48h', 'warranty' => 'Selon résultat' ],
			] ],
		],
		'ipad' => [
			[ 'category' => 'Écran', 'icon' => '📱', 'repairs' => [
				[ 'name' => 'Écran complet iPad', 'desc' => "L'écran est fissuré ou ne fonctionne plus.", 'price' => 199, 'old_price' => 249, 'badge' => 'RÉPARATION COURANTE', 'badge_color' => 'red', 'features' => "Écran de qualité originale\nTactile parfait\nGarantie incluse", 'time' => '120 min', 'warranty' => '6 mois de garantie' ],
			] ],
			[ 'category' => 'Batterie', 'icon' => '🔋', 'repairs' => [
				[ 'name' => 'Batterie iPad', 'desc' => "L'iPad ne tient plus la journée.", 'price' => 149, 'features' => "Batterie neuve\nCapacité d'origine restaurée\nInstallation professionnelle", 'time' => '90 min', 'warranty' => '6 mois de garantie' ],
			] ],
		],
		'default' => [
			[ 'category' => 'Écran', 'icon' => '📱', 'repairs' => [
				[ 'name' => 'Remplacement écran', 'desc' => "L'écran est fissuré ou ne fonctionne plus.", 'price' => 149, 'badge' => 'RÉPARATION COURANTE', 'badge_color' => 'red', 'features' => "Écran de qualité\nTactile parfait\nGarantie incluse", 'time' => '60-120 min', 'warranty' => '6 mois de garantie' ],
			] ],
			[ 'category' => 'Batterie', 'icon' => '🔋', 'repairs' => [
				[ 'name' => 'Remplacement batterie', 'desc' => "L'appareil se décharge rapidement.", 'price' => 99, 'features' => "Batterie neuve\nCapacité restaurée\nInstallation pro", 'time' => '60 min', 'warranty' => '6 mois de garantie' ],
			] ],
			[ 'category' => 'Diagnostic', 'icon' => '🔍', 'repairs' => [
				[ 'name' => 'Diagnostic complet', 'desc' => "Vous ne savez pas ce qui ne va pas.", 'price' => 0, 'badge' => 'GRATUIT*', 'badge_color' => 'primary', 'features' => "Gratuit si réparation\nEstimation transparente\nRésultat rapide", 'time' => '30 min', 'warranty' => 'Sans engagement', 'attention' => "30 EUR si l'appareil n'est pas réparé." ],
			] ],
		],
	];

	$order = 0;
	foreach ( $seed as $device_type => $categories ) {
		foreach ( $categories as $cat ) {
			foreach ( $cat['repairs'] as $r ) {
				$post_id = wp_insert_post( [
					'post_type'   => 'fastfix_repair',
					'post_title'  => $r['name'],
					'post_status' => 'publish',
					'menu_order'  => $order++,
				] );
				if ( is_wp_error( $post_id ) ) continue;

				update_post_meta( $post_id, '_fastfix_device_type', $device_type );
				update_post_meta( $post_id, '_fastfix_category', $cat['category'] );
				update_post_meta( $post_id, '_fastfix_icon', $cat['icon'] );
				update_post_meta( $post_id, '_fastfix_desc', $r['desc'] ?? '' );
				update_post_meta( $post_id, '_fastfix_price', $r['price'] ?? 0 );
				update_post_meta( $post_id, '_fastfix_old_price', $r['old_price'] ?? '' );
				update_post_meta( $post_id, '_fastfix_badge', $r['badge'] ?? '' );
				update_post_meta( $post_id, '_fastfix_badge_color', $r['badge_color'] ?? '' );
				update_post_meta( $post_id, '_fastfix_features', $r['features'] ?? '' );
				update_post_meta( $post_id, '_fastfix_time', $r['time'] ?? '' );
				update_post_meta( $post_id, '_fastfix_warranty', $r['warranty'] ?? '' );
				update_post_meta( $post_id, '_fastfix_attention', $r['attention'] ?? '' );
			}
		}
	}
}

/**
 * Marque comme "populaire" une sélection de réparations existantes, pour la
 * section "Réparations populaires" de la page d'accueil. Ne modifie jamais
 * une réparation déjà marquée/démarquée manuellement dans wp-admin.
 */
function fastfix_seed_featured_repairs() {
	$featured_names = [
		'Remplacement de la vitre',
		'Batterie',
		'Diagnostic',
		'Vitre arrière',
		'Écran complet iPad',
		'Traitement dégât des eaux',
	];

	foreach ( $featured_names as $name ) {
		$posts = get_posts( [
			'post_type'      => 'fastfix_repair',
			'title'          => $name,
			'posts_per_page' => 1,
			'post_status'    => 'any',
		] );
		if ( empty( $posts ) ) continue;

		$post_id = $posts[0]->ID;
		if ( get_post_meta( $post_id, '_fastfix_featured', true ) === '' ) {
			update_post_meta( $post_id, '_fastfix_featured', '1' );
		}
	}
}
