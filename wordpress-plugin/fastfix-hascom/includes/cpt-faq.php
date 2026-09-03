<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Custom Post Type: fastfix_faq
 * Questions fréquentes affichées sur la page d'accueil.
 * wp-admin → FastFix → FAQ · API : GET /wp-json/fastfix/v1/faq
 */
function fastfix_register_faq_cpt() {
	register_post_type( 'fastfix_faq', [
		'label'           => 'FAQ',
		'labels'          => [
			'name'          => 'FAQ',
			'singular_name' => 'Question',
			'add_new_item'  => 'Ajouter une question',
			'edit_item'     => 'Modifier la question',
			'search_items'  => 'Rechercher une question',
			'not_found'     => 'Aucune question trouvée',
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
add_action( 'init', 'fastfix_register_faq_cpt' );

add_filter( 'manage_fastfix_faq_posts_columns', function( $columns ) {
	$new                   = [];
	$new['cb']             = $columns['cb'];
	$new['title']          = 'Question';
	$new['fastfix_answer'] = 'Réponse';
	$new['menu_order']     = 'Ordre';
	return $new;
} );

add_action( 'manage_fastfix_faq_posts_custom_column', function( $column, $post_id ) {
	switch ( $column ) {
		case 'fastfix_answer':
			echo esc_html( wp_trim_words( get_post_field( 'post_content', $post_id ), 18 ) );
			break;
		case 'menu_order':
			echo (int) get_post_field( 'menu_order', $post_id );
			break;
	}
}, 10, 2 );

add_filter( 'manage_edit-fastfix_faq_sortable_columns', function( $columns ) {
	$columns['menu_order'] = 'menu_order';
	return $columns;
} );

add_action( 'add_meta_boxes', function() {
	add_meta_box( 'fastfix_faq_help', 'Aide', function() {
		echo '<p class="description">Le <strong>titre</strong> est la question, le <strong>contenu</strong> est la réponse.<br><br>';
		echo 'Le champ <strong>Ordre</strong> (module "Attributs") contrôle la position dans la liste affichée sur le site.</p>';
	}, 'fastfix_faq', 'side', 'low' );
} );

/** Seed initial : reprend la FAQ codée en dur dans FAQ.tsx. */
function fastfix_seed_default_faq() {
	$existing = get_posts( [ 'post_type' => 'fastfix_faq', 'posts_per_page' => 1, 'post_status' => 'any' ] );
	if ( ! empty( $existing ) ) return;

	$seed = [
		[ 'Combien de temps dure une réparation ?', "La plupart des réparations de smartphones (écran, batterie) sont terminées en 60 minutes maximum. Pour les MacBooks et tablettes, le délai dépend de la réparation — chaque fiche indique le temps estimé." ],
		[ "Utilisez-vous des pièces d'origine ?", "Oui, nous utilisons des pièces d'origine ou certifiées constructeur chaque fois qu'elles sont disponibles. Pour chaque réparation, nous vous précisons la qualité de la pièce avant de commencer." ],
		[ 'Quelle garantie offrez-vous ?', "Toutes nos réparations sont couvertes par une garantie allant jusqu'à 1 an. En cas de problème lié à notre intervention, nous reprenons votre appareil sans frais supplémentaires." ],
		[ 'Faut-il prendre rendez-vous ?', "Non, vous pouvez passer sans rendez-vous pendant nos heures d'ouverture. Le rendez-vous en ligne permet simplement de réduire votre attente — vous êtes prioritaire à votre arrivée." ],
		[ 'Puis-je envoyer mon appareil par la poste ?', "Absolument. Sélectionnez votre appareil et votre réparation, nous vous envoyons une étiquette prépayée par e-mail. Vous postez quand ça vous arrange et recevez votre appareil réparé sous 3 à 5 jours ouvrés." ],
		[ 'Combien ça coûte ?', "Les prix dépendent du modèle et de la réparation. Consultez notre grille tarifaire pour un devis instantané. Pas de surprise : le prix affiché est le prix final." ],
	];

	$order = 0;
	foreach ( $seed as [ $question, $answer ] ) {
		wp_insert_post( [
			'post_type'    => 'fastfix_faq',
			'post_title'   => $question,
			'post_content' => $answer,
			'post_status'  => 'publish',
			'menu_order'   => $order++,
		] );
	}
}
