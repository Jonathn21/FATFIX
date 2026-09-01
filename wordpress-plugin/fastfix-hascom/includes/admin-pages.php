<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Menu admin "FastFix" — totalement séparé des menus Hascom/WooCommerce existants.
 */
add_action( 'admin_menu', function() {
	add_menu_page(
		'FastFix',
		'FastFix',
		'manage_options',
		'fastfix-menu',
		'fastfix_render_dashboard_page',
		'dashicons-smartphone',
		26
	);
	add_submenu_page( 'fastfix-menu', 'Tableau de bord', 'Tableau de bord', 'manage_options', 'fastfix-menu', 'fastfix_render_dashboard_page' );
	add_submenu_page( 'fastfix-menu', 'Tarifs', 'Tarifs', 'manage_options', 'fastfix-tarifs', 'fastfix_render_pricing_page' );
	add_submenu_page( 'fastfix-menu', 'Réglages', 'Réglages', 'manage_options', 'fastfix-reglages', 'fastfix_render_settings_page' );
} );

function fastfix_render_dashboard_page() {
	$counts = wp_count_posts( 'fastfix_booking' );
	$total  = isset( $counts->publish ) ? $counts->publish : 0;
	$result = get_transient( 'fastfix_import_result' );
	if ( $result ) delete_transient( 'fastfix_import_result' );
	?>
	<div class="wrap">
		<h1>FastFix — Tableau de bord</h1>
		<p><?php echo esc_html( $total ); ?> demande(s) de rendez-vous enregistrée(s).</p>
		<p>
			<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=fastfix_booking' ) ); ?>" class="button button-primary">Voir les rendez-vous</a>
			<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=fastfix_device' ) ); ?>" class="button">Gérer les appareils</a>
			<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=fastfix_repair' ) ); ?>" class="button">Gérer les réparations</a>
			<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=fastfix_refurbished' ) ); ?>" class="button">Gérer les reconditionnés</a>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=fastfix-tarifs' ) ); ?>" class="button">Gérer les tarifs</a>
			<a href="<?php echo esc_url( admin_url( 'upload.php?fastfix_media_group=fastfix&mode=list' ) ); ?>" class="button">Voir la galerie FastFix</a>
		</p>
		<hr>

		<?php if ( isset( $_GET['imported'] ) && $result ) : ?>
			<div class="notice notice-success">
				<p><strong>Import terminé.</strong></p>
				<p>
					Appareils : <?php echo (int) $result['devices']['imported']; ?> importée(s),
					<?php echo (int) $result['devices']['skipped']; ?> déjà en place,
					<?php echo (int) $result['devices']['not_mapped']; ?> sans correspondance.
				</p>
				<p>
					Reconditionnés : <?php echo (int) $result['refurbished']['imported']; ?> importée(s),
					<?php echo (int) $result['refurbished']['skipped']; ?> déjà en place,
					<?php echo (int) $result['refurbished']['not_mapped']; ?> sans correspondance.
				</p>
				<?php $errors = array_merge( $result['devices']['errors'], $result['refurbished']['errors'] ); ?>
				<?php if ( $errors ) : ?>
					<p><strong>Erreurs :</strong></p>
					<ul style="list-style:disc;margin-left:20px;"><?php foreach ( $errors as $e ) : ?><li><?php echo esc_html( $e ); ?></li><?php endforeach; ?></ul>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<h2>Importer les photos depuis le site</h2>
		<p>
			Récupère automatiquement les photos déjà publiées sur <code><?php echo esc_html( FASTFIX_IMAGE_BASE_URL ); ?></code>
			et les attache comme image mise en avant à chaque appareil / produit reconditionné qui n'en a pas encore.
			Souvent une photo générique par génération d'appareil — libre à vous d'affiner ensuite modèle par modèle.
		</p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'fastfix_import_images' ); ?>
			<input type="hidden" name="action" value="fastfix_import_images" />
			<label style="display:block;margin-bottom:10px;">
				<input type="checkbox" name="force" value="1" />
				Remplacer aussi les photos déjà en place (réimport complet)
			</label>
			<button type="submit" class="button button-primary">Importer les photos maintenant</button>
		</form>

		<hr>
		<h2>API utilisée par le site fastfix.be</h2>
		<p>Le frontend Astro communique avec ces routes :</p>
		<ul style="list-style:disc;margin-left:20px;">
			<li><code><?php echo esc_html( rest_url( 'fastfix/v1/pricing' ) ); ?></code> — GET, tarifs actuels</li>
			<li><code><?php echo esc_html( rest_url( 'fastfix/v1/devices' ) ); ?></code> — GET, catalogue des modèles + photos</li>
			<li><code><?php echo esc_html( rest_url( 'fastfix/v1/refurbished' ) ); ?></code> — GET, produits reconditionnés + photos</li>
			<li><code><?php echo esc_html( rest_url( 'fastfix/v1/repairs' ) ); ?></code> — GET, fiches réparations (par famille, ou <code>?device_id=</code> pour un modèle précis)</li>
			<li><code><?php echo esc_html( rest_url( 'fastfix/v1/repairs/featured' ) ); ?></code> — GET, réparations populaires (page d'accueil)</li>
			<li><code><?php echo esc_html( rest_url( 'fastfix/v1/booking' ) ); ?></code> — POST, soumission d'une demande de RDV</li>
		</ul>
	</div>
	<?php
}

function fastfix_render_pricing_page() {
	if ( ! current_user_can( 'manage_options' ) ) return;

	if ( isset( $_POST['fastfix_pricing_nonce'] ) && wp_verify_nonce( $_POST['fastfix_pricing_nonce'], 'fastfix_save_pricing' ) ) {
		$pricing = get_option( 'fastfix_pricing', fastfix_default_pricing() );
		foreach ( $pricing['deviceTypes'] as $device_key => $device_label ) {
			foreach ( $pricing['repairTypes'] as $repair_key => $repair_label ) {
				$field = "price_{$device_key}_{$repair_key}";
				if ( isset( $_POST[ $field ] ) ) {
					$val = sanitize_text_field( $_POST[ $field ] );
					$pricing['prices'][ $device_key ][ $repair_key ] = ( $val === '' ) ? '' : (float) $val;
				}
			}
		}
		update_option( 'fastfix_pricing', $pricing );
		echo '<div class="notice notice-success"><p>Tarifs mis à jour.</p></div>';
	}

	$pricing = get_option( 'fastfix_pricing', fastfix_default_pricing() );
	?>
	<div class="wrap">
		<h1>FastFix — Tarifs des réparations</h1>
		<p>Modifiez les prix par appareil et type de réparation. Laissez vide si la réparation n'est pas proposée pour cet appareil.</p>
		<form method="post">
			<?php wp_nonce_field( 'fastfix_save_pricing', 'fastfix_pricing_nonce' ); ?>
			<table class="widefat striped" style="max-width:1100px;">
				<thead>
					<tr>
						<th>Appareil</th>
						<?php foreach ( $pricing['repairTypes'] as $repair_label ) : ?>
							<th><?php echo esc_html( $repair_label ); ?></th>
						<?php endforeach; ?>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $pricing['deviceTypes'] as $device_key => $device_label ) : ?>
						<tr>
							<td><strong><?php echo esc_html( $device_label ); ?></strong></td>
							<?php foreach ( $pricing['repairTypes'] as $repair_key => $repair_label ) :
								$val = $pricing['prices'][ $device_key ][ $repair_key ] ?? '';
							?>
								<td>
									<input type="number" step="1" min="0" style="width:80px;"
										name="price_<?php echo esc_attr( $device_key ); ?>_<?php echo esc_attr( $repair_key ); ?>"
										value="<?php echo esc_attr( $val ); ?>" placeholder="—" /> €
								</td>
							<?php endforeach; ?>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<p class="submit"><button type="submit" class="button button-primary">Enregistrer les tarifs</button></p>
		</form>
	</div>
	<?php
}

function fastfix_render_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) return;

	if ( isset( $_POST['fastfix_settings_nonce'] ) && wp_verify_nonce( $_POST['fastfix_settings_nonce'], 'fastfix_save_settings' ) ) {
		update_option( 'fastfix_notify_email', sanitize_email( $_POST['fastfix_notify_email'] ?? get_option( 'admin_email' ) ) );
		update_option( 'fastfix_cors_origins', sanitize_text_field( $_POST['fastfix_cors_origins'] ?? '*' ) );
		echo '<div class="notice notice-success"><p>Réglages enregistrés.</p></div>';
	}

	$email   = get_option( 'fastfix_notify_email', get_option( 'admin_email' ) );
	$origins = get_option( 'fastfix_cors_origins', '*' );
	?>
	<div class="wrap">
		<h1>FastFix — Réglages</h1>
		<form method="post">
			<?php wp_nonce_field( 'fastfix_save_settings', 'fastfix_settings_nonce' ); ?>
			<table class="form-table">
				<tr>
					<th><label for="fastfix_notify_email">Email de notification</label></th>
					<td><input type="email" id="fastfix_notify_email" name="fastfix_notify_email" value="<?php echo esc_attr( $email ); ?>" class="regular-text" /></td>
				</tr>
				<tr>
					<th><label for="fastfix_cors_origins">Origines autorisées (CORS)</label></th>
					<td>
						<input type="text" id="fastfix_cors_origins" name="fastfix_cors_origins" value="<?php echo esc_attr( $origins ); ?>" class="regular-text" />
						<p class="description">
							<code>*</code> autorise tous les domaines (par défaut). Pour restreindre, indiquez une liste séparée par des virgules,
							ex : <code>https://fastfix.be,https://fastfix.pages.dev</code>.
						</p>
					</td>
				</tr>
			</table>
			<p class="submit"><button type="submit" class="button button-primary">Enregistrer</button></p>
		</form>
	</div>
	<?php
}
