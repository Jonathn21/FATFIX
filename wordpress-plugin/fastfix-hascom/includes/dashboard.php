<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Tableau de bord FastFix : chiffres clés, derniers rendez-vous,
 * raccourcis vers tous les contenus, export CSV et état de l'API.
 */

/** Compte les rendez-vous par statut. */
function fastfix_count_bookings_by_status() {
	$counts = [ 'nouveau' => 0, 'confirme' => 0, 'termine' => 0, 'annule' => 0 ];
	$posts  = get_posts( [ 'post_type' => 'fastfix_booking', 'post_status' => 'publish', 'posts_per_page' => -1, 'fields' => 'ids' ] );

	foreach ( $posts as $id ) {
		$status = get_post_meta( $id, '_fastfix_status', true ) ?: 'nouveau';
		if ( isset( $counts[ $status ] ) ) $counts[ $status ]++;
	}
	return $counts;
}

/** Nombre de rendez-vous reçus depuis N jours. */
function fastfix_count_bookings_since( $days ) {
	$posts = get_posts( [
		'post_type'      => 'fastfix_booking',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'date_query'     => [ [ 'after' => $days . ' days ago' ] ],
	] );
	return count( $posts );
}

/* ── Pastille "nouveaux RDV" sur le menu FastFix ── */
add_action( 'admin_menu', function() {
	global $menu;
	$counts = fastfix_count_bookings_by_status();
	$new    = $counts['nouveau'];
	if ( ! $new ) return;

	foreach ( $menu as $i => $item ) {
		if ( isset( $item[2] ) && $item[2] === 'fastfix-menu' ) {
			$menu[ $i ][0] .= ' <span class="awaiting-mod"><span class="pending-count">' . (int) $new . '</span></span>';
			break;
		}
	}
}, 999 );

function fastfix_render_dashboard_page() {
	$counts   = fastfix_count_bookings_by_status();
	$total    = array_sum( $counts );
	$week     = fastfix_count_bookings_since( 7 );
	$month    = fastfix_count_bookings_since( 30 );
	$result   = get_transient( 'fastfix_import_result' );
	if ( $result ) delete_transient( 'fastfix_import_result' );

	$content_counts = [
		'Appareils'      => wp_count_posts( 'fastfix_device' )->publish ?? 0,
		'Réparations'    => wp_count_posts( 'fastfix_repair' )->publish ?? 0,
		'Reconditionnés' => wp_count_posts( 'fastfix_refurbished' )->publish ?? 0,
		'Avis clients'   => wp_count_posts( 'fastfix_review' )->publish ?? 0,
		'Questions FAQ'  => wp_count_posts( 'fastfix_faq' )->publish ?? 0,
		'Catégories'     => wp_count_posts( 'fastfix_category' )->publish ?? 0,
	];

	$recent = get_posts( [ 'post_type' => 'fastfix_booking', 'post_status' => 'publish', 'posts_per_page' => 6 ] );
	?>
	<div class="wrap">
		<h1>FastFix — Tableau de bord</h1>

		<?php if ( isset( $_GET['imported'] ) && $result ) : ?>
			<div class="notice notice-success is-dismissible">
				<p><strong>Import des photos terminé.</strong></p>
				<p>
					Appareils : <?php echo (int) $result['devices']['imported']; ?> importée(s),
					<?php echo (int) $result['devices']['skipped']; ?> déjà en place ·
					Reconditionnés : <?php echo (int) $result['refurbished']['imported']; ?> importée(s),
					<?php echo (int) $result['refurbished']['skipped']; ?> déjà en place ·
					Catégories : <?php echo (int) ( $result['categories']['imported'] ?? 0 ); ?> importée(s),
					<?php echo (int) ( $result['categories']['skipped'] ?? 0 ); ?> déjà en place.
				</p>
				<?php $errors = array_merge( $result['devices']['errors'], $result['refurbished']['errors'], $result['categories']['errors'] ?? [] ); ?>
				<?php if ( $errors ) : ?>
					<p><strong>Erreurs :</strong> <?php echo esc_html( implode( ' | ', array_slice( $errors, 0, 5 ) ) ); ?></p>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<style>
			.ff-cards{display:flex;flex-wrap:wrap;gap:16px;margin:20px 0;}
			.ff-card{background:#fff;border:1px solid #dcdcde;border-radius:8px;padding:18px 22px;min-width:150px;flex:1;}
			.ff-card .ff-num{font-size:30px;font-weight:700;line-height:1.1;}
			.ff-card .ff-lbl{color:#646970;font-size:12px;text-transform:uppercase;letter-spacing:.04em;margin-top:4px;}
			.ff-card.is-alert{border-color:#D4A017;background:#FFFBEB;}
			.ff-grid{display:flex;flex-wrap:wrap;gap:20px;align-items:flex-start;}
			.ff-panel{background:#fff;border:1px solid #dcdcde;border-radius:8px;padding:18px 22px;flex:1;min-width:340px;}
			.ff-panel h2{margin-top:0;}
			.ff-pill{display:inline-block;padding:2px 10px;border-radius:999px;color:#fff;font-size:11px;font-weight:600;}
		</style>

		<div class="ff-cards">
			<div class="ff-card <?php echo $counts['nouveau'] ? 'is-alert' : ''; ?>">
				<div class="ff-num"><?php echo (int) $counts['nouveau']; ?></div>
				<div class="ff-lbl">À traiter</div>
			</div>
			<div class="ff-card">
				<div class="ff-num"><?php echo (int) $week; ?></div>
				<div class="ff-lbl">7 derniers jours</div>
			</div>
			<div class="ff-card">
				<div class="ff-num"><?php echo (int) $month; ?></div>
				<div class="ff-lbl">30 derniers jours</div>
			</div>
			<div class="ff-card">
				<div class="ff-num"><?php echo (int) $counts['confirme']; ?></div>
				<div class="ff-lbl">Confirmés</div>
			</div>
			<div class="ff-card">
				<div class="ff-num"><?php echo (int) $counts['termine']; ?></div>
				<div class="ff-lbl">Terminés</div>
			</div>
			<div class="ff-card">
				<div class="ff-num"><?php echo (int) $total; ?></div>
				<div class="ff-lbl">Total reçu</div>
			</div>
		</div>

		<div class="ff-grid">
			<div class="ff-panel">
				<h2>Derniers rendez-vous</h2>
				<?php if ( empty( $recent ) ) : ?>
					<p class="description">Aucune demande pour l'instant. Elles apparaîtront ici dès qu'un client remplit le formulaire du site.</p>
				<?php else : ?>
					<table class="widefat striped">
						<thead><tr><th>Réf.</th><th>Appareil</th><th>Client</th><th>Statut</th></tr></thead>
						<tbody>
						<?php
						$colors = [ 'nouveau' => '#B08C00', 'confirme' => '#2563EB', 'termine' => '#16A34A', 'annule' => '#DC2626' ];
						$labels = fastfix_booking_statuses();
						foreach ( $recent as $b ) :
							$status = get_post_meta( $b->ID, '_fastfix_status', true ) ?: 'nouveau';
						?>
							<tr>
								<td><a href="<?php echo esc_url( get_edit_post_link( $b->ID ) ); ?>"><?php echo esc_html( get_post_meta( $b->ID, '_fastfix_reference', true ) ); ?></a></td>
								<td><?php echo esc_html( get_post_meta( $b->ID, '_fastfix_device_label', true ) ); ?></td>
								<td><?php echo esc_html( get_post_meta( $b->ID, '_fastfix_name', true ) ); ?></td>
								<td><span class="ff-pill" style="background:<?php echo esc_attr( $colors[ $status ] ?? '#666' ); ?>"><?php echo esc_html( $labels[ $status ] ?? $status ); ?></span></td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
					<p style="margin-bottom:0;">
						<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=fastfix_booking' ) ); ?>" class="button button-primary">Tous les rendez-vous</a>
						<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=fastfix_export_bookings' ), 'fastfix_export_bookings' ) ); ?>" class="button">Exporter en CSV</a>
					</p>
				<?php endif; ?>
			</div>

			<div class="ff-panel">
				<h2>Contenus du site</h2>
				<table class="widefat striped">
					<tbody>
						<?php
						$links = [
							'Appareils'      => 'edit.php?post_type=fastfix_device',
							'Réparations'    => 'edit.php?post_type=fastfix_repair',
							'Reconditionnés' => 'edit.php?post_type=fastfix_refurbished',
							'Avis clients'   => 'edit.php?post_type=fastfix_review',
							'Questions FAQ'  => 'edit.php?post_type=fastfix_faq',
							'Catégories'     => 'edit.php?post_type=fastfix_category',
						];
						foreach ( $content_counts as $label => $count ) :
						?>
							<tr>
								<td><strong><?php echo esc_html( $label ); ?></strong></td>
								<td style="width:60px;"><?php echo (int) $count; ?></td>
								<td style="width:100px;text-align:right;"><a href="<?php echo esc_url( admin_url( $links[ $label ] ) ); ?>">Gérer →</a></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
				<p style="margin-bottom:0;">
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=fastfix-reglages' ) ); ?>" class="button button-primary">Réglages du site</a>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=fastfix-tarifs' ) ); ?>" class="button">Grille de tarifs</a>
					<a href="<?php echo esc_url( admin_url( 'upload.php?fastfix_media_group=fastfix&mode=list' ) ); ?>" class="button">Galerie FastFix</a>
				</p>
			</div>
		</div>

		<div class="ff-panel" style="margin-top:20px;">
			<h2>Importer les photos manquantes</h2>
			<p class="description">
				Récupère automatiquement les photos publiées sur <code><?php echo esc_html( FASTFIX_IMAGE_BASE_URL ); ?></code>
				et les attache aux appareils / produits qui n'en ont pas encore. Ne remplace jamais une photo que vous avez ajoutée vous-même.
			</p>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<?php wp_nonce_field( 'fastfix_import_images' ); ?>
				<input type="hidden" name="action" value="fastfix_import_images" />
				<label style="display:block;margin:10px 0;">
					<input type="checkbox" name="force" value="1" /> Remplacer aussi les photos déjà en place
				</label>
				<button type="submit" class="button">Lancer l'import</button>
			</form>
		</div>

		<div class="ff-panel" style="margin-top:20px;">
			<h2>API utilisée par le site</h2>
			<p class="description">Le site fastfix.be lit ces données en direct — toute modification ici est visible immédiatement.</p>
			<ul style="list-style:disc;margin-left:20px;">
				<li><a href="<?php echo esc_url( rest_url( 'fastfix/v1/config' ) ); ?>" target="_blank"><code>/config</code></a> — réglages, horaires, avis, FAQ (tout en une requête)</li>
				<li><a href="<?php echo esc_url( rest_url( 'fastfix/v1/devices' ) ); ?>" target="_blank"><code>/devices</code></a> — catalogue des modèles + photos</li>
				<li><a href="<?php echo esc_url( rest_url( 'fastfix/v1/repairs' ) ); ?>" target="_blank"><code>/repairs</code></a> — fiches réparations</li>
				<li><a href="<?php echo esc_url( rest_url( 'fastfix/v1/refurbished' ) ); ?>" target="_blank"><code>/refurbished</code></a> — produits reconditionnés</li>
				<li><a href="<?php echo esc_url( rest_url( 'fastfix/v1/pricing' ) ); ?>" target="_blank"><code>/pricing</code></a> — grille de tarifs</li>
				<li><code>/booking</code> — réception des demandes de rendez-vous (POST)</li>
			</ul>
		</div>
	</div>
	<?php
}
