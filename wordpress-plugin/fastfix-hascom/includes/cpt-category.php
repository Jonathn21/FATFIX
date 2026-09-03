<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Custom Post Type: fastfix_category
 * Les vignettes de la grille « Que faut-il réparer ? » sur la page d'accueil
 * (iPhone, Samsung, PlayStation…). Chaque catégorie a son image et son lien.
 *
 * wp-admin → FastFix → Catégories · API : GET /wp-json/fastfix/v1/categories
 */
function fastfix_register_category_cpt() {
	register_post_type( 'fastfix_category', [
		'label'           => 'Catégories',
		'labels'          => [
			'name'          => 'Catégories',
			'singular_name' => 'Catégorie',
			'add_new_item'  => 'Ajouter une catégorie',
			'edit_item'     => 'Modifier la catégorie',
			'search_items'  => 'Rechercher une catégorie',
			'not_found'     => 'Aucune catégorie trouvée',
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
add_action( 'init', 'fastfix_register_category_cpt' );

add_filter( 'manage_fastfix_category_posts_columns', function( $columns ) {
	$new                  = [];
	$new['cb']            = $columns['cb'];
	$new['fastfix_thumb'] = 'Image';
	$new['title']         = 'Catégorie';
	$new['fastfix_link']  = 'Lien';
	$new['menu_order']    = 'Ordre';
	return $new;
} );

add_action( 'manage_fastfix_category_posts_custom_column', function( $column, $post_id ) {
	switch ( $column ) {
		case 'fastfix_thumb':
			echo has_post_thumbnail( $post_id )
				? get_the_post_thumbnail( $post_id, [ 44, 44 ], [ 'style' => 'object-fit:contain;' ] )
				: '<span style="color:#b32d2e;">Aucune image</span>';
			break;
		case 'fastfix_link':
			echo esc_html( get_post_meta( $post_id, '_fastfix_link', true ) ?: '/reparations' );
			break;
		case 'menu_order':
			echo (int) get_post_field( 'menu_order', $post_id );
			break;
	}
}, 10, 2 );

add_filter( 'manage_edit-fastfix_category_sortable_columns', function( $columns ) {
	$columns['menu_order'] = 'menu_order';
	return $columns;
} );

add_action( 'add_meta_boxes', function() {
	add_meta_box( 'fastfix_category_detail', 'Détails', 'fastfix_render_category_meta_box', 'fastfix_category', 'normal', 'high' );
} );

function fastfix_render_category_meta_box( $post ) {
	wp_nonce_field( 'fastfix_save_category', 'fastfix_category_nonce' );
	$link = get_post_meta( $post->ID, '_fastfix_link', true ) ?: '/reparations';
	?>
	<p>
		<label for="fastfix_link"><strong>Lien au clic</strong></label><br>
		<input type="text" name="fastfix_link" id="fastfix_link" value="<?php echo esc_attr( $link ); ?>" style="width:100%;max-width:400px;" placeholder="/reparations" />
	</p>
	<p class="description">
		Le <strong>titre</strong> est le nom affiché sous l'image (ex. « iPhone »).
		Ajoutez l'image via le module <strong>Image mise en avant</strong> à droite,
		et utilisez <strong>Ordre</strong> (module « Attributs ») pour la position dans la grille.
	</p>
	<?php
}

add_action( 'save_post_fastfix_category', function( $post_id ) {
	if ( ! isset( $_POST['fastfix_category_nonce'] ) || ! wp_verify_nonce( $_POST['fastfix_category_nonce'], 'fastfix_save_category' ) ) return;
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if ( ! current_user_can( 'edit_post', $post_id ) ) return;

	if ( isset( $_POST['fastfix_link'] ) ) {
		update_post_meta( $post_id, '_fastfix_link', sanitize_text_field( wp_unslash( $_POST['fastfix_link'] ) ) );
	}
} );

/** Correspondance nom → image, pour l'import automatique des visuels. */
function fastfix_category_image_map() {
	return [
		'iPhone'             => 'iphone-16.webp',
		'Samsung'            => 'galaxy-s26-ultra.webp',
		'iPad'               => 'ipad-pro.webp',
		'MacBook'            => 'macbook-air.webp',
		'Apple Watch'        => 'apple-watch.webp',
		'iMac'               => 'imac.webp',
		'Samsung Galaxy Tab' => 'galaxy-tab.webp',
		'PlayStation'        => 'ps5.webp',
		'Google Pixel'       => 'pixel-10-pro.webp',
		'OnePlus'            => 'oneplus-13.webp',
		'Xbox'               => 'xbox-series-x.webp',
		'AirPods'            => 'airpods-max.webp',
	];
}

/** Seed initial : les 12 vignettes actuellement affichées sur l'accueil. */
function fastfix_seed_default_categories() {
	$existing = get_posts( [ 'post_type' => 'fastfix_category', 'posts_per_page' => 1, 'post_status' => 'any' ] );
	if ( ! empty( $existing ) ) return;

	$order = 0;
	foreach ( array_keys( fastfix_category_image_map() ) as $name ) {
		$post_id = wp_insert_post( [
			'post_type'   => 'fastfix_category',
			'post_title'  => $name,
			'post_status' => 'publish',
			'menu_order'  => $order++,
		] );
		if ( ! is_wp_error( $post_id ) ) {
			update_post_meta( $post_id, '_fastfix_link', '/reparations' );
		}
	}
}
