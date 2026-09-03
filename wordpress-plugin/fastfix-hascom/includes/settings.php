<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Réglages du site FastFix — toutes les informations d'entreprise qui
 * apparaissent sur le site (coordonnées, horaires, réseaux, preuve sociale)
 * sont éditables ici et diffusées au frontend via /wp-json/fastfix/v1/config.
 * Tout est stocké dans une seule option : fastfix_settings.
 */

function fastfix_default_settings() {
	return [
		// Identité
		'business_name' => 'FastFix',
		'tagline'       => 'We can fix it, and fix it fast',
		'legal_name'    => 'FastFix / Hascom Computer',
		'intro'         => 'Réparation de smartphones, tablettes, ordinateurs et consoles.',

		// Contact
		'phone'         => '02 219 49 16',
		'phone_link'    => '+3222194916',
		'whatsapp'      => '3222194916',
		'email'         => 'info@fastfix.be',

		// Adresse
		'address'       => 'Chaussée de Haecht 163',
		'postal_code'   => '1030',
		'city'          => 'Schaerbeek',
		'maps_url'      => 'https://maps.google.com/?q=Chauss%C3%A9e+de+Haecht+163+1030+Schaerbeek',

		// Horaires
		'hours' => [
			'monday'    => [ 'closed' => false, 'open' => '10:00', 'close' => '18:30' ],
			'tuesday'   => [ 'closed' => false, 'open' => '10:00', 'close' => '18:30' ],
			'wednesday' => [ 'closed' => false, 'open' => '10:00', 'close' => '18:30' ],
			'thursday'  => [ 'closed' => false, 'open' => '10:00', 'close' => '18:30' ],
			'friday'    => [ 'closed' => false, 'open' => '10:00', 'close' => '18:30' ],
			'saturday'  => [ 'closed' => false, 'open' => '12:00', 'close' => '17:00' ],
			'sunday'    => [ 'closed' => true,  'open' => '',      'close' => '' ],
		],

		// Réseaux sociaux
		'facebook'  => 'https://www.facebook.com/hascomcomputer/',
		'instagram' => 'https://www.instagram.com/hascomcomputer/',
		'linkedin'  => 'https://www.linkedin.com/company/hascom-computers/',
		'tiktok'    => '',

		// Preuve sociale
		'google_rating'  => '4,9',
		'google_reviews' => '2 000+',
		'repairs_count'  => '30 000+',
		'since_year'     => '2002',

		// Promesses (barre de confiance en haut du site)
		'promise_1' => "Jusqu'à 1 an de garantie",
		'promise_2' => 'Prêt en 60 min',
		'promise_3' => 'Joignable 7j/7',

		// Technique
		'notify_email' => '',
		'cors_origins' => '*',
	];
}

function fastfix_get_settings() {
	$saved = get_option( 'fastfix_settings', [] );
	if ( ! is_array( $saved ) ) $saved = [];
	$settings = array_merge( fastfix_default_settings(), $saved );

	// Rétro-compatibilité avec les anciennes options séparées.
	if ( empty( $settings['notify_email'] ) ) {
		$settings['notify_email'] = get_option( 'fastfix_notify_email', get_option( 'admin_email' ) );
	}
	if ( empty( $settings['cors_origins'] ) ) {
		$settings['cors_origins'] = get_option( 'fastfix_cors_origins', '*' );
	}
	return $settings;
}

function fastfix_get_setting( $key, $default = '' ) {
	$settings = fastfix_get_settings();
	return $settings[ $key ] ?? $default;
}

function fastfix_day_labels() {
	return [
		'monday'    => 'Lundi',
		'tuesday'   => 'Mardi',
		'wednesday' => 'Mercredi',
		'thursday'  => 'Jeudi',
		'friday'    => 'Vendredi',
		'saturday'  => 'Samedi',
		'sunday'    => 'Dimanche',
	];
}

/**
 * Regroupe les jours consécutifs ayant les mêmes horaires, pour un affichage
 * compact du type "Lun–Ven 10:00–18:30 · Sam 12:00–17:00".
 */
function fastfix_hours_summary() {
	$hours  = fastfix_get_setting( 'hours', [] );
	$labels = fastfix_day_labels();
	$short  = [ 'monday' => 'Lun', 'tuesday' => 'Mar', 'wednesday' => 'Mer', 'thursday' => 'Jeu', 'friday' => 'Ven', 'saturday' => 'Sam', 'sunday' => 'Dim' ];

	$groups  = [];
	$current = null;

	foreach ( $labels as $key => $label ) {
		$day       = $hours[ $key ] ?? [ 'closed' => true ];
		$signature = ! empty( $day['closed'] ) ? 'closed' : ( $day['open'] ?? '' ) . '-' . ( $day['close'] ?? '' );

		if ( $current && $current['signature'] === $signature ) {
			$current['end'] = $key;
		} else {
			if ( $current ) $groups[] = $current;
			$current = [ 'signature' => $signature, 'start' => $key, 'end' => $key, 'day' => $day ];
		}
	}
	if ( $current ) $groups[] = $current;

	$parts = [];
	foreach ( $groups as $g ) {
		if ( $g['signature'] === 'closed' ) continue; // on n'affiche pas les jours fermés
		$range = $g['start'] === $g['end']
			? $short[ $g['start'] ]
			: $short[ $g['start'] ] . '–' . $short[ $g['end'] ];
		$parts[] = $range . ' ' . $g['day']['open'] . '–' . $g['day']['close'];
	}

	return implode( ' · ', $parts );
}

/**
 * Indique si la boutique est ouverte maintenant (fuseau du site) et,
 * si elle est fermée, quand elle rouvre.
 */
function fastfix_open_status() {
	$hours = fastfix_get_setting( 'hours', [] );
	$keys  = [ 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday' ];
	$short = [ 'monday' => 'lun', 'tuesday' => 'mar', 'wednesday' => 'mer', 'thursday' => 'jeu', 'friday' => 'ven', 'saturday' => 'sam', 'sunday' => 'dim' ];

	$now      = current_time( 'timestamp' );
	$today    = $keys[ (int) wp_date( 'w', $now ) ];
	$hhmm     = wp_date( 'H:i', $now );
	$today_hr = $hours[ $today ] ?? [ 'closed' => true ];

	if ( empty( $today_hr['closed'] ) && $hhmm >= $today_hr['open'] && $hhmm < $today_hr['close'] ) {
		return [ 'open' => true, 'label' => 'Ouvert · ferme à ' . $today_hr['close'] ];
	}

	// Cherche la prochaine ouverture dans les 7 prochains jours.
	for ( $i = 0; $i < 8; $i++ ) {
		$ts  = $now + $i * DAY_IN_SECONDS;
		$key = $keys[ (int) wp_date( 'w', $ts ) ];
		$h   = $hours[ $key ] ?? [ 'closed' => true ];
		if ( ! empty( $h['closed'] ) ) continue;
		if ( $i === 0 && $hhmm >= $h['open'] ) continue; // déjà passé aujourd'hui
		$when = $i === 0 ? "aujourd'hui" : ( $i === 1 ? 'demain' : $short[ $key ] );
		return [ 'open' => false, 'label' => 'Fermé · ' . $when . ' ' . $h['open'] ];
	}

	return [ 'open' => false, 'label' => 'Fermé' ];
}

/* ── Page de réglages ── */
function fastfix_render_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) return;

	if ( isset( $_POST['fastfix_settings_nonce'] ) && wp_verify_nonce( $_POST['fastfix_settings_nonce'], 'fastfix_save_settings' ) ) {
		$settings = fastfix_get_settings();

		$text_fields = [
			'business_name', 'tagline', 'legal_name', 'intro',
			'phone', 'phone_link', 'whatsapp',
			'address', 'postal_code', 'city',
			'google_rating', 'google_reviews', 'repairs_count', 'since_year',
			'promise_1', 'promise_2', 'promise_3',
			'cors_origins',
		];
		foreach ( $text_fields as $f ) {
			if ( isset( $_POST[ $f ] ) ) $settings[ $f ] = sanitize_text_field( wp_unslash( $_POST[ $f ] ) );
		}

		foreach ( [ 'email', 'notify_email' ] as $f ) {
			if ( isset( $_POST[ $f ] ) ) $settings[ $f ] = sanitize_email( wp_unslash( $_POST[ $f ] ) );
		}

		foreach ( [ 'maps_url', 'facebook', 'instagram', 'linkedin', 'tiktok' ] as $f ) {
			if ( isset( $_POST[ $f ] ) ) $settings[ $f ] = esc_url_raw( wp_unslash( $_POST[ $f ] ) );
		}

		foreach ( array_keys( fastfix_day_labels() ) as $day ) {
			$settings['hours'][ $day ] = [
				'closed' => ! empty( $_POST[ "closed_$day" ] ),
				'open'   => sanitize_text_field( wp_unslash( $_POST[ "open_$day" ] ?? '' ) ),
				'close'  => sanitize_text_field( wp_unslash( $_POST[ "close_$day" ] ?? '' ) ),
			];
		}

		update_option( 'fastfix_settings', $settings );
		echo '<div class="notice notice-success is-dismissible"><p><strong>Réglages enregistrés.</strong> Les changements sont visibles immédiatement sur le site.</p></div>';
	}

	$s = fastfix_get_settings();
	?>
	<div class="wrap">
		<h1>FastFix — Réglages du site</h1>
		<p class="description" style="font-size:14px;max-width:800px;">
			Ces informations alimentent l'ensemble du site : en-tête, pied de page, page contact, page boutique.
			Une modification ici est visible immédiatement, sans intervention technique.
		</p>

		<form method="post">
			<?php wp_nonce_field( 'fastfix_save_settings', 'fastfix_settings_nonce' ); ?>

			<h2 class="title">Identité</h2>
			<table class="form-table">
				<tr>
					<th><label for="business_name">Nom de l'enseigne</label></th>
					<td><input type="text" id="business_name" name="business_name" value="<?php echo esc_attr( $s['business_name'] ); ?>" class="regular-text" /></td>
				</tr>
				<tr>
					<th><label for="tagline">Slogan</label></th>
					<td><input type="text" id="tagline" name="tagline" value="<?php echo esc_attr( $s['tagline'] ); ?>" class="regular-text" /></td>
				</tr>
				<tr>
					<th><label for="intro">Phrase de présentation</label></th>
					<td>
						<input type="text" id="intro" name="intro" value="<?php echo esc_attr( $s['intro'] ); ?>" class="large-text" />
						<p class="description">Affichée dans le pied de page, sous le logo.</p>
					</td>
				</tr>
				<tr>
					<th><label for="legal_name">Raison sociale (mentions légales)</label></th>
					<td><input type="text" id="legal_name" name="legal_name" value="<?php echo esc_attr( $s['legal_name'] ); ?>" class="regular-text" /></td>
				</tr>
			</table>

			<h2 class="title">Coordonnées</h2>
			<table class="form-table">
				<tr>
					<th><label for="phone">Téléphone (affiché)</label></th>
					<td><input type="text" id="phone" name="phone" value="<?php echo esc_attr( $s['phone'] ); ?>" class="regular-text" placeholder="02 219 49 16" /></td>
				</tr>
				<tr>
					<th><label for="phone_link">Téléphone (format appel)</label></th>
					<td>
						<input type="text" id="phone_link" name="phone_link" value="<?php echo esc_attr( $s['phone_link'] ); ?>" class="regular-text" placeholder="+3222194916" />
						<p class="description">Format international, sans espaces — utilisé quand on clique sur le numéro.</p>
					</td>
				</tr>
				<tr>
					<th><label for="whatsapp">WhatsApp</label></th>
					<td>
						<input type="text" id="whatsapp" name="whatsapp" value="<?php echo esc_attr( $s['whatsapp'] ); ?>" class="regular-text" placeholder="3222194916" />
						<p class="description">Numéro sans "+" ni espaces. Laissez vide pour masquer le bouton WhatsApp du site.</p>
					</td>
				</tr>
				<tr>
					<th><label for="email">E-mail public</label></th>
					<td><input type="email" id="email" name="email" value="<?php echo esc_attr( $s['email'] ); ?>" class="regular-text" /></td>
				</tr>
			</table>

			<h2 class="title">Adresse</h2>
			<table class="form-table">
				<tr>
					<th><label for="address">Rue et numéro</label></th>
					<td><input type="text" id="address" name="address" value="<?php echo esc_attr( $s['address'] ); ?>" class="regular-text" /></td>
				</tr>
				<tr>
					<th><label for="postal_code">Code postal</label></th>
					<td><input type="text" id="postal_code" name="postal_code" value="<?php echo esc_attr( $s['postal_code'] ); ?>" class="small-text" /></td>
				</tr>
				<tr>
					<th><label for="city">Ville</label></th>
					<td><input type="text" id="city" name="city" value="<?php echo esc_attr( $s['city'] ); ?>" class="regular-text" /></td>
				</tr>
				<tr>
					<th><label for="maps_url">Lien Google Maps</label></th>
					<td><input type="url" id="maps_url" name="maps_url" value="<?php echo esc_attr( $s['maps_url'] ); ?>" class="large-text" /></td>
				</tr>
			</table>

			<h2 class="title">Horaires d'ouverture</h2>
			<table class="widefat striped" style="max-width:620px;">
				<thead><tr><th>Jour</th><th>Fermé</th><th>Ouverture</th><th>Fermeture</th></tr></thead>
				<tbody>
					<?php foreach ( fastfix_day_labels() as $key => $label ) :
						$d = $s['hours'][ $key ] ?? [ 'closed' => true, 'open' => '', 'close' => '' ];
					?>
						<tr>
							<td><strong><?php echo esc_html( $label ); ?></strong></td>
							<td><input type="checkbox" name="closed_<?php echo esc_attr( $key ); ?>" value="1" <?php checked( ! empty( $d['closed'] ) ); ?> /></td>
							<td><input type="time" name="open_<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $d['open'] ?? '' ); ?>" /></td>
							<td><input type="time" name="close_<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $d['close'] ?? '' ); ?>" /></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<p class="description">Résumé actuellement affiché sur le site : <strong><?php echo esc_html( fastfix_hours_summary() ); ?></strong></p>

			<h2 class="title">Réseaux sociaux</h2>
			<table class="form-table">
				<?php foreach ( [ 'facebook' => 'Facebook', 'instagram' => 'Instagram', 'linkedin' => 'LinkedIn', 'tiktok' => 'TikTok' ] as $key => $label ) : ?>
					<tr>
						<th><label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label></th>
						<td><input type="url" id="<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $s[ $key ] ); ?>" class="large-text" placeholder="Laissez vide pour masquer" /></td>
					</tr>
				<?php endforeach; ?>
			</table>

			<h2 class="title">Chiffres mis en avant</h2>
			<table class="form-table">
				<tr>
					<th><label for="google_rating">Note Google</label></th>
					<td><input type="text" id="google_rating" name="google_rating" value="<?php echo esc_attr( $s['google_rating'] ); ?>" class="small-text" /></td>
				</tr>
				<tr>
					<th><label for="google_reviews">Nombre d'avis</label></th>
					<td><input type="text" id="google_reviews" name="google_reviews" value="<?php echo esc_attr( $s['google_reviews'] ); ?>" class="regular-text" /></td>
				</tr>
				<tr>
					<th><label for="repairs_count">Nombre de réparations</label></th>
					<td><input type="text" id="repairs_count" name="repairs_count" value="<?php echo esc_attr( $s['repairs_count'] ); ?>" class="regular-text" /></td>
				</tr>
				<tr>
					<th><label for="since_year">Année de création</label></th>
					<td><input type="text" id="since_year" name="since_year" value="<?php echo esc_attr( $s['since_year'] ); ?>" class="small-text" /></td>
				</tr>
			</table>

			<h2 class="title">Promesses (barre du haut)</h2>
			<table class="form-table">
				<?php foreach ( [ 'promise_1', 'promise_2', 'promise_3' ] as $i => $key ) : ?>
					<tr>
						<th><label for="<?php echo esc_attr( $key ); ?>">Promesse <?php echo $i + 1; ?></label></th>
						<td><input type="text" id="<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $s[ $key ] ); ?>" class="regular-text" /></td>
					</tr>
				<?php endforeach; ?>
			</table>

			<h2 class="title">Technique</h2>
			<table class="form-table">
				<tr>
					<th><label for="notify_email">E-mail de notification des RDV</label></th>
					<td>
						<input type="email" id="notify_email" name="notify_email" value="<?php echo esc_attr( $s['notify_email'] ); ?>" class="regular-text" />
						<p class="description">Adresse qui reçoit un e-mail à chaque nouvelle demande de rendez-vous.</p>
					</td>
				</tr>
				<tr>
					<th><label for="cors_origins">Domaines autorisés (CORS)</label></th>
					<td>
						<input type="text" id="cors_origins" name="cors_origins" value="<?php echo esc_attr( $s['cors_origins'] ); ?>" class="large-text" />
						<p class="description"><code>*</code> autorise tous les domaines. Pour restreindre : <code>https://fastfix.be,https://fatfix.pages.dev</code></p>
					</td>
				</tr>
			</table>

			<p class="submit"><button type="submit" class="button button-primary button-hero">Enregistrer les réglages</button></p>
		</form>
	</div>
	<?php
}
