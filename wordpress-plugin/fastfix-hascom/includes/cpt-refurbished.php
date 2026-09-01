<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Custom Post Type: fastfix_refurbished
 * Un produit reconditionné en vente (ex: "iPhone 16 Pro Max — Grade A+").
 * Éditable depuis wp-admin → FastFix → Reconditionnés, exposé au frontend
 * via GET /wp-json/fastfix/v1/refurbished.
 */
function fastfix_grade_choices() {
	return [ 'A+' => 'A+ (comme neuf)', 'A' => 'A (excellent)', 'B+' => 'B+ (très bon)', 'B' => 'B (bon)' ];
}

function fastfix_register_refurbished_cpt() {
	register_post_type( 'fastfix_refurbished', [
		'label'           => 'Reconditionnés',
		'labels'          => [
			'name'          => 'Reconditionnés',
			'singular_name' => 'Produit reconditionné',
			'add_new_item'  => 'Ajouter un produit reconditionné',
			'edit_item'     => 'Modifier le produit',
			'search_items'  => 'Rechercher un produit',
			'not_found'     => 'Aucun produit trouvé',
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
add_action( 'init', 'fastfix_register_refurbished_cpt' );

/* ── Colonnes personnalisées ── */
add_filter( 'manage_fastfix_refurbished_posts_columns', function( $columns ) {
	$new                  = [];
	$new['cb']            = $columns['cb'];
	$new['fastfix_thumb'] = 'Photo';
	$new['title']         = 'Produit';
	$new['fastfix_grade'] = 'Grade';
	$new['fastfix_price'] = 'Prix';
	$new['fastfix_badge'] = 'Badge';
	$new['date']          = $columns['date'];
	return $new;
} );

add_action( 'manage_fastfix_refurbished_posts_custom_column', function( $column, $post_id ) {
	switch ( $column ) {
		case 'fastfix_thumb':
			echo has_post_thumbnail( $post_id )
				? get_the_post_thumbnail( $post_id, [ 40, 40 ], [ 'style' => 'object-fit:contain;' ] )
				: '<span style="color:#b32d2e;">Aucune photo</span>';
			break;
		case 'fastfix_grade':
			echo esc_html( get_post_meta( $post_id, '_fastfix_grade', true ) );
			break;
		case 'fastfix_price':
			echo esc_html( get_post_meta( $post_id, '_fastfix_price', true ) ) . ' €';
			break;
		case 'fastfix_badge':
			$badge = get_post_meta( $post_id, '_fastfix_badge', true );
			echo $badge ? esc_html( $badge ) : '—';
			break;
	}
}, 10, 2 );

/* ── Meta box d'édition ── */
add_action( 'add_meta_boxes', function() {
	add_meta_box( 'fastfix_refurbished_detail', 'Détails du produit', 'fastfix_render_refurbished_meta_box', 'fastfix_refurbished', 'normal', 'high' );
} );

function fastfix_render_refurbished_meta_box( $post ) {
	wp_nonce_field( 'fastfix_save_refurbished', 'fastfix_refurbished_nonce' );

	$grade     = get_post_meta( $post->ID, '_fastfix_grade', true ) ?: 'A';
	$price     = get_post_meta( $post->ID, '_fastfix_price', true );
	$old_price = get_post_meta( $post->ID, '_fastfix_old_price', true );
	$badge     = get_post_meta( $post->ID, '_fastfix_badge', true );
	$color     = get_post_meta( $post->ID, '_fastfix_color', true );
	$storage   = get_post_meta( $post->ID, '_fastfix_storage', true );
	$warranty  = get_post_meta( $post->ID, '_fastfix_warranty', true ) ?: '12 mois';
	?>
	<style>.fastfix-field{margin-bottom:16px;}.fastfix-field label{display:block;font-weight:600;margin-bottom:4px;}.fastfix-row{display:flex;gap:16px;}.fastfix-row>.fastfix-field{flex:1;}</style>

	<div class="fastfix-row">
		<div class="fastfix-field">
			<label for="fastfix_grade">Grade</label>
			<select name="fastfix_grade" id="fastfix_grade" style="width:100%;">
				<?php foreach ( fastfix_grade_choices() as $key => $label ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $grade, $key ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="fastfix-field">
			<label for="fastfix_price">Prix (€)</label>
			<input type="number" step="1" min="0" name="fastfix_price" id="fastfix_price" value="<?php echo esc_attr( $price ); ?>" style="width:100%;" />
		</div>
		<div class="fastfix-field">
			<label for="fastfix_old_price">Prix neuf (barré)</label>
			<input type="number" step="1" min="0" name="fastfix_old_price" id="fastfix_old_price" value="<?php echo esc_attr( $old_price ); ?>" style="width:100%;" />
		</div>
	</div>

	<div class="fastfix-row">
		<div class="fastfix-field">
			<label for="fastfix_color">Couleur</label>
			<input type="text" name="fastfix_color" id="fastfix_color" value="<?php echo esc_attr( $color ); ?>" style="width:100%;" placeholder="ex: Titane Naturel" />
		</div>
		<div class="fastfix-field">
			<label for="fastfix_storage">Stockage</label>
			<input type="text" name="fastfix_storage" id="fastfix_storage" value="<?php echo esc_attr( $storage ); ?>" style="width:100%;" placeholder="ex: 256 Go" />
		</div>
		<div class="fastfix-field">
			<label for="fastfix_warranty">Garantie</label>
			<input type="text" name="fastfix_warranty" id="fastfix_warranty" value="<?php echo esc_attr( $warranty ); ?>" style="width:100%;" placeholder="ex: 12 mois" />
		</div>
	</div>

	<div class="fastfix-field">
		<label for="fastfix_badge">Badge (optionnel)</label>
		<input type="text" name="fastfix_badge" id="fastfix_badge" value="<?php echo esc_attr( $badge ); ?>" style="width:100%;max-width:300px;" placeholder="ex: Best Seller, Nouveau, Petit prix" />
	</div>

	<p class="description">Ajoutez la photo via le module "Image mise en avant" dans la colonne de droite. Le champ "Ordre" contrôle la position dans la liste.</p>
	<?php
}

add_action( 'save_post_fastfix_refurbished', function( $post_id ) {
	if ( ! isset( $_POST['fastfix_refurbished_nonce'] ) || ! wp_verify_nonce( $_POST['fastfix_refurbished_nonce'], 'fastfix_save_refurbished' ) ) return;
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if ( ! current_user_can( 'edit_post', $post_id ) ) return;

	$fields = [
		'fastfix_grade'     => 'sanitize_text_field',
		'fastfix_price'     => 'sanitize_text_field',
		'fastfix_old_price' => 'sanitize_text_field',
		'fastfix_badge'     => 'sanitize_text_field',
		'fastfix_color'     => 'sanitize_text_field',
		'fastfix_storage'   => 'sanitize_text_field',
		'fastfix_warranty'  => 'sanitize_text_field',
	];
	foreach ( $fields as $field => $sanitizer ) {
		if ( isset( $_POST[ $field ] ) ) {
			update_post_meta( $post_id, '_' . $field, call_user_func( $sanitizer, $_POST[ $field ] ) );
		}
	}
} );

/**
 * Seed initial : migre les 8 produits codés en dur dans reconditionnes.astro.
 * Retourne la liste [post_id => nom] pour permettre l'import d'images ensuite.
 */
function fastfix_seed_default_refurbished() {
	$existing = get_posts( [ 'post_type' => 'fastfix_refurbished', 'posts_per_page' => 1, 'post_status' => 'any' ] );
	if ( ! empty( $existing ) ) return;

	$seed = [
		[ 'name' => 'iPhone 16 Pro Max', 'grade' => 'A+', 'price' => 899, 'old_price' => 1299, 'badge' => 'Best Seller', 'color' => 'Titane Naturel', 'storage' => '256 Go' ],
		[ 'name' => 'iPhone 16', 'grade' => 'A', 'price' => 629, 'old_price' => 969, 'badge' => '', 'color' => 'Noir', 'storage' => '128 Go' ],
		[ 'name' => 'iPhone 17 Pro', 'grade' => 'A+', 'price' => 979, 'old_price' => 1399, 'badge' => 'Nouveau', 'color' => 'Noir Sidéral', 'storage' => '256 Go' ],
		[ 'name' => 'Galaxy S26 Ultra', 'grade' => 'A', 'price' => 849, 'old_price' => 1299, 'badge' => '', 'color' => 'Noir Fantôme', 'storage' => '256 Go' ],
		[ 'name' => 'Galaxy S26', 'grade' => 'A+', 'price' => 599, 'old_price' => 899, 'badge' => 'Petit prix', 'color' => 'Crème', 'storage' => '128 Go' ],
		[ 'name' => 'Galaxy S24', 'grade' => 'B+', 'price' => 449, 'old_price' => 799, 'badge' => '', 'color' => 'Violet', 'storage' => '128 Go' ],
		[ 'name' => 'iPhone 17', 'grade' => 'A', 'price' => 699, 'old_price' => 1059, 'badge' => '', 'color' => 'Blanc', 'storage' => '128 Go' ],
		[ 'name' => 'Galaxy S23 Ultra', 'grade' => 'B+', 'price' => 399, 'old_price' => 749, 'badge' => 'Prix choc', 'color' => 'Vert', 'storage' => '256 Go' ],
	];

	$order = 0;
	foreach ( $seed as $p ) {
		$post_id = wp_insert_post( [
			'post_type'   => 'fastfix_refurbished',
			'post_title'  => $p['name'],
			'post_status' => 'publish',
			'menu_order'  => $order++,
		] );
		if ( is_wp_error( $post_id ) ) continue;

		update_post_meta( $post_id, '_fastfix_grade', $p['grade'] );
		update_post_meta( $post_id, '_fastfix_price', $p['price'] );
		update_post_meta( $post_id, '_fastfix_old_price', $p['old_price'] );
		update_post_meta( $post_id, '_fastfix_badge', $p['badge'] );
		update_post_meta( $post_id, '_fastfix_color', $p['color'] );
		update_post_meta( $post_id, '_fastfix_storage', $p['storage'] );
		update_post_meta( $post_id, '_fastfix_warranty', '12 mois' );
	}
}
