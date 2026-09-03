<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Menu admin "FastFix" — regroupe tous les contenus du site en un seul
 * endroit, totalement séparé des menus Hascom / WooCommerce existants.
 *
 * L'ordre des sous-menus suit la fréquence d'usage : rendez-vous d'abord,
 * puis les contenus, puis les réglages.
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
	add_submenu_page( 'fastfix-menu', 'Contenus des pages', 'Contenus des pages', 'manage_options', 'fastfix-contenus', 'fastfix_render_content_page' );
	add_submenu_page( 'fastfix-menu', 'Grille de tarifs', 'Grille de tarifs', 'manage_options', 'fastfix-tarifs', 'fastfix_render_pricing_page' );
	add_submenu_page( 'fastfix-menu', 'Réglages du site', 'Réglages du site', 'manage_options', 'fastfix-reglages', 'fastfix_render_settings_page' );
} );

function fastfix_render_pricing_page() {
	if ( ! current_user_can( 'manage_options' ) ) return;

	if ( isset( $_POST['fastfix_pricing_nonce'] ) && wp_verify_nonce( $_POST['fastfix_pricing_nonce'], 'fastfix_save_pricing' ) ) {
		$pricing = get_option( 'fastfix_pricing', fastfix_default_pricing() );
		foreach ( $pricing['deviceTypes'] as $device_key => $device_label ) {
			foreach ( $pricing['repairTypes'] as $repair_key => $repair_label ) {
				$field = "price_{$device_key}_{$repair_key}";
				if ( isset( $_POST[ $field ] ) ) {
					$val = sanitize_text_field( wp_unslash( $_POST[ $field ] ) );
					$pricing['prices'][ $device_key ][ $repair_key ] = ( $val === '' ) ? '' : (float) $val;
				}
			}
		}
		update_option( 'fastfix_pricing', $pricing );
		echo '<div class="notice notice-success is-dismissible"><p>Tarifs mis à jour.</p></div>';
	}

	$pricing = get_option( 'fastfix_pricing', fastfix_default_pricing() );
	?>
	<div class="wrap">
		<h1>FastFix — Grille de tarifs</h1>
		<p class="description" style="max-width:800px;">
			Vue d'ensemble des prix par famille d'appareil. Laissez une case vide si la réparation n'est pas proposée.
			Pour un tarif au modèle près (ex : écran iPhone 16 ≠ écran iPhone 8), utilisez le champ
			<strong>Modèle spécifique</strong> dans <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=fastfix_repair' ) ); ?>">Réparations</a>.
		</p>
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
