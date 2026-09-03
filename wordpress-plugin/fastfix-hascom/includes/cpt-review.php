<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Custom Post Type: fastfix_review
 * Avis clients affichés sur la page d'accueil.
 * wp-admin → FastFix → Avis clients · API : GET /wp-json/fastfix/v1/reviews
 */
function fastfix_register_review_cpt() {
	register_post_type( 'fastfix_review', [
		'label'           => 'Avis clients',
		'labels'          => [
			'name'          => 'Avis clients',
			'singular_name' => 'Avis client',
			'add_new_item'  => 'Ajouter un avis',
			'edit_item'     => "Modifier l'avis",
			'search_items'  => 'Rechercher un avis',
			'not_found'     => 'Aucun avis trouvé',
		],
		'public'          => false,
		'show_ui'         => true,
		'show_in_menu'    => 'fastfix-menu',
		'capability_type' => 'post',
		'map_meta_cap'    => true,
		'supports'        => [ 'title', 'editor', 'page-attributes' ],
		'show_in_rest'    => false,
	] );
}
add_action( 'init', 'fastfix_register_review_cpt' );

add_filter( 'manage_fastfix_review_posts_columns', function( $columns ) {
	$new                    = [];
	$new['cb']              = $columns['cb'];
	$new['title']           = 'Client';
	$new['fastfix_stars']   = 'Note';
	$new['fastfix_device']  = 'Appareil';
	$new['fastfix_excerpt'] = 'Avis';
	$new['date']            = $columns['date'];
	return $new;
} );

add_action( 'manage_fastfix_review_posts_custom_column', function( $column, $post_id ) {
	switch ( $column ) {
		case 'fastfix_stars':
			$stars = (int) ( get_post_meta( $post_id, '_fastfix_stars', true ) ?: 5 );
			echo '<span style="color:#D4A017;font-size:15px;">' . str_repeat( '★', $stars ) . '<span style="color:#ddd;">' . str_repeat( '★', 5 - $stars ) . '</span></span>';
			break;
		case 'fastfix_device':
			echo esc_html( get_post_meta( $post_id, '_fastfix_review_device', true ) ?: '—' );
			break;
		case 'fastfix_excerpt':
			echo esc_html( wp_trim_words( get_post_field( 'post_content', $post_id ), 14 ) );
			break;
	}
}, 10, 2 );

add_action( 'add_meta_boxes', function() {
	add_meta_box( 'fastfix_review_detail', "Détails de l'avis", 'fastfix_render_review_meta_box', 'fastfix_review', 'side', 'high' );
} );

function fastfix_render_review_meta_box( $post ) {
	wp_nonce_field( 'fastfix_save_review', 'fastfix_review_nonce' );
	$stars  = get_post_meta( $post->ID, '_fastfix_stars', true ) ?: '5';
	$device = get_post_meta( $post->ID, '_fastfix_review_device', true );
	$date   = get_post_meta( $post->ID, '_fastfix_review_date', true );
	?>
	<p>
		<label for="fastfix_stars"><strong>Note</strong></label><br>
		<select name="fastfix_stars" id="fastfix_stars" style="width:100%;">
			<?php for ( $i = 5; $i >= 1; $i-- ) : ?>
				<option value="<?php echo $i; ?>" <?php selected( (string) $stars, (string) $i ); ?>><?php echo str_repeat( '★', $i ); ?> (<?php echo $i; ?>/5)</option>
			<?php endfor; ?>
		</select>
	</p>
	<p>
		<label for="fastfix_review_device"><strong>Appareil réparé</strong></label><br>
		<input type="text" name="fastfix_review_device" id="fastfix_review_device" value="<?php echo esc_attr( $device ); ?>" style="width:100%;" placeholder="ex: iPhone 15" />
	</p>
	<p>
		<label for="fastfix_review_date"><strong>Date affichée</strong></label><br>
		<input type="text" name="fastfix_review_date" id="fastfix_review_date" value="<?php echo esc_attr( $date ); ?>" style="width:100%;" placeholder="ex: 28 août 2026" />
		<span class="description">Laissez vide pour utiliser la date de publication.</span>
	</p>
	<p class="description">
		Le <strong>titre</strong> est le nom du client (ex: "Sophie M.").<br>
		Le <strong>contenu</strong> est le texte de l'avis.
	</p>
	<?php
}

add_action( 'save_post_fastfix_review', function( $post_id ) {
	if ( ! isset( $_POST['fastfix_review_nonce'] ) || ! wp_verify_nonce( $_POST['fastfix_review_nonce'], 'fastfix_save_review' ) ) return;
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if ( ! current_user_can( 'edit_post', $post_id ) ) return;

	foreach ( [ 'fastfix_stars', 'fastfix_review_device', 'fastfix_review_date' ] as $field ) {
		if ( isset( $_POST[ $field ] ) ) {
			update_post_meta( $post_id, '_' . $field, sanitize_text_field( wp_unslash( $_POST[ $field ] ) ) );
		}
	}
} );

/** Seed initial : reprend les avis codés en dur dans ReviewCarousel.tsx. */
function fastfix_seed_default_reviews() {
	$existing = get_posts( [ 'post_type' => 'fastfix_review', 'posts_per_page' => 1, 'post_status' => 'any' ] );
	if ( ! empty( $existing ) ) return;

	$seed = [
		[ 'Sophie M.', 5, '28 août 2026', 'iPhone 15', "Écran de mon iPhone 15 remplacé en 45 minutes, comme neuf ! Personnel très sympathique et professionnel." ],
		[ 'Karim B.', 5, '27 août 2026', 'Galaxy S23', "Batterie de mon Samsung S23 changée rapidement. Le téléphone tient toute la journée maintenant. Merci FastFix !" ],
		[ 'Julie D.', 5, '26 août 2026', 'MacBook', "Mon MacBook avait un problème de surchauffe. Diagnostic gratuit et réparation le jour même. Je recommande à 100%." ],
		[ 'Thomas L.', 5, '25 août 2026', 'iPad', "Excellent rapport qualité/prix. Mon iPad est réparé et ils m'ont même mis un film de protection offert." ],
		[ 'Amira K.', 5, '24 août 2026', 'iPhone', "Service rapide et soigné. J'ai récupéré mon téléphone en moins d'une heure. La garantie d'un an me rassure." ],
		[ 'Lucas R.', 4, '23 août 2026', 'iPhone 14 Pro', "Réparation de la vitre arrière de mon iPhone 14 Pro. Travail impeccable, on ne voit plus rien !" ],
	];

	$order = 0;
	foreach ( $seed as [ $name, $stars, $date, $device, $text ] ) {
		$post_id = wp_insert_post( [
			'post_type'    => 'fastfix_review',
			'post_title'   => $name,
			'post_content' => $text,
			'post_status'  => 'publish',
			'menu_order'   => $order++,
		] );
		if ( is_wp_error( $post_id ) ) continue;

		update_post_meta( $post_id, '_fastfix_stars', $stars );
		update_post_meta( $post_id, '_fastfix_review_date', $date );
		update_post_meta( $post_id, '_fastfix_review_device', $device );
	}
}
