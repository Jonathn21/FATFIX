<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Contenus des pages — titres de sections, textes et images de chaque bloc
 * du site, éditables depuis wp-admin → FastFix → Contenus des pages.
 *
 * Principe : chaque élément éditable porte une clé (ex. "home.hero.title").
 * Le frontend applique la valeur si elle est renseignée ; sinon il conserve
 * le texte livré dans le code. Un champ vidé ne casse donc jamais la page.
 *
 * Tout est stocké dans une seule option : fastfix_content.
 */

/**
 * Description de toutes les pages, sections et champs éditables.
 * Ajouter un contenu = ajouter une ligne ici (l'interface se génère seule).
 *
 * Types de champ : text | textarea | image | list
 */
function fastfix_content_registry() {
	return [

		/* ═══════════ ACCUEIL ═══════════ */
		'home' => [
			'label'    => 'Accueil',
			'sections' => [
				'hero' => [
					'label'  => 'Bandeau principal',
					'fields' => [
						'title'         => [ 'label' => 'Titre', 'type' => 'text', 'default' => 'Réparez votre téléphone & ordinateur à Bruxelles' ],
						'subtitle'      => [ 'label' => 'Sous-titre', 'type' => 'textarea', 'default' => 'Des solutions fiables et rapides pour vos appareils électroniques' ],
						'cta_primary'   => [ 'label' => 'Bouton principal', 'type' => 'text', 'default' => 'Prendre rendez-vous' ],
						'cta_secondary' => [ 'label' => 'Bouton secondaire', 'type' => 'text', 'default' => 'Voir les prix' ],
						'link_postal'   => [ 'label' => 'Lien envoi postal', 'type' => 'text', 'default' => 'Envoyer par la poste →' ],
						'image'         => [ 'label' => 'Image des appareils', 'type' => 'image' ],
					],
				],
				'popular_devices' => [
					'label'  => 'Section « Appareils populaires »',
					'fields' => [
						'title'      => [ 'label' => 'Titre', 'type' => 'text', 'default' => 'Appareils populaires' ],
						'link_label' => [ 'label' => 'Lien à droite', 'type' => 'text', 'default' => 'Tous les modèles →' ],
					],
				],
				'popular_repairs' => [
					'label'  => 'Section « Réparations populaires »',
					'fields' => [
						'title'      => [ 'label' => 'Titre', 'type' => 'text', 'default' => 'Réparations populaires' ],
						'link_label' => [ 'label' => 'Lien à droite', 'type' => 'text', 'default' => 'Prendre rendez-vous →' ],
					],
				],
				'what_to_repair' => [
					'label'  => 'Section « Que faut-il réparer ? »',
					'fields' => [
						'title' => [ 'label' => 'Titre', 'type' => 'text', 'default' => 'Que faut-il réparer ?' ],
						'pills' => [
							'label'   => 'Types de réparation (une par ligne)',
							'type'    => 'list',
							'columns' => [ 'Libellé' ],
							'default' => "Réparation d'écran
Remplacement de la batterie
Remplacement de la face arrière
Réparation dégât des eaux
Polissage de l'écran
Diagnostic",
						],
					],
				],
				'real_stores' => [
					'label'  => 'Section « De vrais magasins »',
					'fields' => [
						'title'      => [ 'label' => 'Titre', 'type' => 'text', 'default' => 'De vrais magasins, de vrais spécialistes' ],
						'text'       => [ 'label' => 'Texte', 'type' => 'textarea' ],
						'bullet_1'   => [ 'label' => 'Point 1', 'type' => 'text' ],
						'bullet_2'   => [ 'label' => 'Point 2', 'type' => 'text' ],
						'bullet_3'   => [ 'label' => 'Point 3', 'type' => 'text' ],
						'bullet_4'   => [ 'label' => 'Point 4', 'type' => 'text' ],
						'link_label' => [ 'label' => 'Lien', 'type' => 'text', 'default' => 'En savoir plus sur nous →' ],
						'image'      => [ 'label' => 'Photo', 'type' => 'image' ],
					],
				],
				'store_or_post' => [
					'label'  => 'Section « En magasin ou par la poste »',
					'fields' => [
						'title'       => [ 'label' => 'Titre', 'type' => 'text', 'default' => 'Passer en magasin ou envoyer par la poste ? Les deux sont possibles.' ],
						'store_title' => [ 'label' => 'Carte 1 — titre', 'type' => 'text', 'default' => 'En magasin' ],
						'store_text'  => [ 'label' => 'Carte 1 — texte', 'type' => 'textarea' ],
						'store_cta'   => [ 'label' => 'Carte 1 — bouton', 'type' => 'text', 'default' => 'Prendre rendez-vous' ],
						'store_image' => [ 'label' => 'Carte 1 — photo', 'type' => 'image' ],
						'post_title'  => [ 'label' => 'Carte 2 — titre', 'type' => 'text', 'default' => 'Envoi par la poste' ],
						'post_text'   => [ 'label' => 'Carte 2 — texte', 'type' => 'textarea' ],
						'post_cta'    => [ 'label' => 'Carte 2 — bouton', 'type' => 'text', 'default' => 'Comment ça marche →' ],
						'post_image'  => [ 'label' => 'Carte 2 — photo', 'type' => 'image' ],
					],
				],
				'sell' => [
					'label'  => 'Encart « Plutôt vendre que réparer ? »',
					'fields' => [
						'title' => [ 'label' => 'Titre', 'type' => 'text', 'default' => 'Plutôt vendre que réparer ?' ],
						'text'  => [ 'label' => 'Texte', 'type' => 'textarea' ],
						'cta'   => [ 'label' => 'Bouton', 'type' => 'text', 'default' => 'Calculer mon offre' ],
					],
				],
				'our_store' => [
					'label'  => 'Section « Notre magasin »',
					'fields' => [
						'title' => [ 'label' => 'Titre', 'type' => 'text', 'default' => 'Notre magasin' ],
					],
				],
				'reviews' => [
					'label'  => 'Section « Avis clients »',
					'fields' => [
						'title'    => [ 'label' => 'Titre', 'type' => 'text' ],
						'subtitle' => [ 'label' => 'Sous-titre', 'type' => 'text' ],
					],
				],
				'refurb_teaser' => [
					'label'  => 'Encart « Reconditionnés »',
					'fields' => [
						'badge' => [ 'label' => 'Badge', 'type' => 'text' ],
						'title' => [ 'label' => 'Titre', 'type' => 'text', 'default' => 'Smartphones reconditionnés, garantis 12 mois' ],
						'text'  => [ 'label' => 'Texte', 'type' => 'textarea' ],
						'cta'   => [ 'label' => 'Bouton', 'type' => 'text' ],
					],
				],
				'faq' => [
					'label'  => 'Section « Questions fréquentes »',
					'fields' => [
						'title' => [ 'label' => 'Titre', 'type' => 'text', 'default' => 'Questions fréquentes' ],
					],
				],
				'cta_band' => [
					'label'  => 'Bandeau noir « Comment déposer »',
					'fields' => [
						'title' => [ 'label' => 'Titre', 'type' => 'text', 'default' => 'Comment souhaitez-vous déposer votre appareil ?' ],
						'cta'   => [ 'label' => 'Bouton', 'type' => 'text', 'default' => 'Prendre rendez-vous' ],
					],
				],
				'final_cta' => [
					'label'  => 'Bandeau final',
					'fields' => [
						'title'         => [ 'label' => 'Titre', 'type' => 'text' ],
						'text'          => [ 'label' => 'Texte', 'type' => 'textarea' ],
						'cta_primary'   => [ 'label' => 'Bouton principal', 'type' => 'text' ],
						'cta_secondary' => [ 'label' => 'Bouton secondaire', 'type' => 'text' ],
					],
				],
			],
		],

		/* ═══════════ PRIX ═══════════ */
		'prix' => [
			'label'    => 'Page Prix',
			'sections' => [
				'hero' => [
					'label'  => 'Bandeau',
					'fields' => [
						'title'         => [ 'label' => 'Titre', 'type' => 'text', 'default' => 'Nos tarifs' ],
						'text'          => [ 'label' => 'Texte', 'type' => 'textarea' ],
						'cta_primary'   => [ 'label' => 'Bouton principal', 'type' => 'text', 'default' => 'Prendre rendez-vous' ],
						'cta_secondary' => [ 'label' => 'Bouton secondaire', 'type' => 'text', 'default' => 'Devis personnalisé' ],
					],
				],
				'sections' => [
					'label'  => 'Titres de sections',
					'fields' => [
						'popular_title'    => [ 'label' => 'Titre « Réparations populaires »', 'type' => 'text', 'default' => 'Réparations populaires' ],
						'all_title'        => [ 'label' => 'Titre « Tous les tarifs »', 'type' => 'text', 'default' => 'Tous les tarifs par catégorie' ],
						'other_title'      => [ 'label' => 'Titre « Autres réparations »', 'type' => 'text', 'default' => 'Autres réparations' ],
						'not_listed_title' => [ 'label' => 'Titre « Modèle non listé »', 'type' => 'text', 'default' => "Votre modèle n'est pas listé ?" ],
						'not_listed_text'  => [ 'label' => 'Texte « Modèle non listé »', 'type' => 'textarea' ],
					],
				],
				'popular' => [
					'label'  => 'Réparations populaires (cartes du haut)',
					'fields' => [
						'items' => [
							'label'   => 'Cartes',
							'type'    => 'list',
							'columns' => [ 'Réparation', 'Durée', 'Garantie', 'Prix €' ],
							'default' => "Écran iPhone 16 | ± 1h | 12 mois | 179
Batterie iPhone 17 Pro | ± 1h | 6 mois | 109
Écran Galaxy S26 Ultra | ± 1.5h | 12 mois | 289
Batterie iPhone 16 Pro Max | ± 1h | 6 mois | 99
Écran iPad Pro M5 | ± 2h | 12 mois | 349
Vitre arrière iPhone 17 | ± 1h | 12 mois | 79
Batterie Galaxy S24 | ± 1h | 6 mois | 89
Écran MacBook Air M4 | ± 3h | 12 mois | 399",
						],
					],
				],
				'grid' => [
					'label'  => 'Grille tarifaire par catégorie',
					'fields' => [
						'cat1_name' => [ 'label' => 'Catégorie 1 — nom', 'type' => 'text', 'default' => 'iPhone' ],
						'cat1_rows' => [
							'label'   => 'Catégorie 1 — modèles et prix',
							'type'    => 'list',
							'columns' => [ 'Modèle', 'Écran', 'Batterie', 'Vitre arrière', 'Caméra' ],
							'default' => "iPhone 17 Pro Max | 249 | 109 | 89 | 149
iPhone 17 Pro | 229 | 109 | 89 | 139
iPhone 17 | 199 | 99 | 79 | 119
iPhone 16 Pro Max | 219 | 99 | 79 | 129
iPhone 16 | 179 | 89 | 69 | 109",
						],
						'cat2_name' => [ 'label' => 'Catégorie 2 — nom', 'type' => 'text', 'default' => 'Samsung Galaxy' ],
						'cat2_rows' => [
							'label'   => 'Catégorie 2 — modèles et prix',
							'type'    => 'list',
							'columns' => [ 'Modèle', 'Écran', 'Batterie', 'Vitre arrière', 'Caméra' ],
							'default' => "Galaxy S26 Ultra | 289 | 119 | 89 | 159
Galaxy S26+ | 249 | 109 | 79 | 139
Galaxy S26 | 229 | 99 | 79 | 129
Galaxy S24 | 199 | 89 | 69 | 109
Galaxy S23 Ultra | 219 | 99 | 69 | 129",
						],
						'cat3_name' => [ 'label' => 'Catégorie 3 — nom', 'type' => 'text', 'default' => 'iPad & MacBook' ],
						'cat3_rows' => [
							'label'   => 'Catégorie 3 — modèles et prix',
							'type'    => 'list',
							'columns' => [ 'Modèle', 'Écran', 'Batterie', 'Vitre arrière', 'Caméra' ],
							'default' => "iPad Pro 13\" M5 | 349 | 149 | | 129
iPad Pro 13\" M4 | 329 | 139 | | 119
MacBook Air M4 | 399 | 199 | |",
						],
						'cat4_name' => [ 'label' => 'Catégorie 4 — nom', 'type' => 'text', 'default' => 'Autres appareils' ],
						'cat4_rows' => [
							'label'   => 'Catégorie 4 — modèles et prix',
							'type'    => 'list',
							'columns' => [ 'Modèle', 'Écran', 'Batterie', 'Vitre arrière', 'Caméra' ],
							'default' => "Apple Watch | 149 | 89 | |
Galaxy Tab S10+ | 249 | 119 | |
PS5 | | | |
Xbox Series X | | | |",
						],
					],
				],
				'other' => [
					'label'  => 'Autres réparations',
					'fields' => [
						'subtitle' => [ 'label' => 'Sous-titre', 'type' => 'text', 'default' => 'Tarifs indicatifs pour les interventions spécifiques.' ],
						'items'    => [
							'label'   => 'Liste des interventions',
							'type'    => 'list',
							'columns' => [ 'Intervention', 'Prix', 'Durée' ],
							'default' => "Connecteur de charge | 49 – 89 € | ± 1h
Haut-parleur | 39 – 69 € | ± 45 min
Micro | 39 – 69 € | ± 45 min
Face ID (iPhone) | 129 – 179 € | ± 1.5h
Bouton power / volume | 39 – 59 € | ± 1h
Nettoyage oxydation | 59 – 99 € | ± 2h
Antenne / WiFi | 49 – 89 € | ± 1h
Carte mère (diagnostic) | à partir de 99 € | ± 2-5 jours
Micro-soudure | à partir de 79 € | ± 1-3 jours",
						],
					],
				],
			],
		],

		/* ═══════════ RÉPARATIONS ═══════════ */
		'reparations' => [
			'label'    => 'Page Réparations',
			'sections' => [
				'hero' => [
					'label'  => 'Bandeau',
					'fields' => [
						'title' => [ 'label' => 'Titre', 'type' => 'text' ],
						'text'  => [ 'label' => 'Texte', 'type' => 'textarea' ],
					],
				],
			],
		],

		/* ═══════════ RECONDITIONNÉS ═══════════ */
		'reconditionnes' => [
			'label'    => 'Page Reconditionnés',
			'sections' => [
				'hero' => [
					'label'  => 'Bandeau',
					'fields' => [
						'badge' => [ 'label' => 'Badge', 'type' => 'text', 'default' => 'Reconditionnés par FastFix' ],
						'title' => [ 'label' => 'Titre', 'type' => 'text', 'default' => 'Smartphones reconditionnés, garantis 12 mois' ],
						'text'  => [ 'label' => 'Texte', 'type' => 'textarea', 'default' => 'Reconditionnés par nos techniciens, testés sur 30 points de contrôle. Comme neufs, sans le prix du neuf.' ],
					],
				],
				'sections' => [
					'label'  => 'Titres de sections',
					'fields' => [
						'process_title' => [ 'label' => 'Titre « Processus »', 'type' => 'text', 'default' => 'Notre processus de reconditionnement' ],
						'compare_title' => [ 'label' => 'Titre « Comparatif »', 'type' => 'text', 'default' => 'Reconditionné FastFix vs neuf' ],
						'cta_title'     => [ 'label' => 'Titre « Modèle introuvable »', 'type' => 'text', 'default' => 'Vous ne trouvez pas votre modèle ?' ],
						'cta_text'      => [ 'label' => 'Texte « Modèle introuvable »', 'type' => 'textarea' ],
					],
				],
				'lists' => [
					'label'  => 'Listes',
					'fields' => [
						'grades' => [
							'label'   => 'Grades expliqués',
							'type'    => 'list',
							'columns' => [ 'Grade', 'Description' ],
							'default' => "A+ | Excellent état — aucune trace visible, batterie > 95%
A | Très bon état — micro-rayures invisibles en usage, batterie > 90%
B+ | Bon état — légères traces d'usure, batterie > 85%",
						],
						'process' => [
							'label'   => 'Étapes du reconditionnement',
							'type'    => 'list',
							'columns' => [ 'Titre', 'Description' ],
							'default' => "Réception | L'appareil est reçu et inspecté visuellement
Diagnostic | Test complet de tous les composants (30 points)
Réparation | Remplacement des pièces usées ou défectueuses
Nettoyage | Nettoyage professionnel intérieur et extérieur
Certification | Tests finaux, grade attribué, garantie activée",
						],
						'compare' => [
							'label'   => 'Tableau comparatif',
							'type'    => 'list',
							'columns' => [ 'Critère', 'FastFix', 'Neuf' ],
							'default' => "Prix | Jusqu'à -50% | Prix plein
Garantie | 12 mois FastFix | 24 mois constructeur
Batterie | > 85% de capacité | 100%
Pièces | D'origine ou certifiées | D'origine
Tests qualité | 30 points de contrôle | Tests usine
Impact écologique | Réduit de 80% | Empreinte complète
Retour | 30 jours | 14 jours (légal)",
						],
					],
				],
			],
		],

		/* ═══════════ BOUTIQUE ═══════════ */
		'boutiques' => [
			'label'    => 'Page Boutique',
			'sections' => [
				'hero' => [
					'label'  => 'Bandeau',
					'fields' => [
						'title' => [ 'label' => 'Titre', 'type' => 'text', 'default' => 'Notre boutique' ],
						'text'  => [ 'label' => 'Texte', 'type' => 'textarea' ],
					],
				],
				'store' => [
					'label'  => 'Fiche boutique',
					'fields' => [
						'image' => [ 'label' => 'Photo de la boutique', 'type' => 'image' ],
					],
				],
				'lists' => [
					'label'  => 'Listes',
					'fields' => [
						'features' => [
							'label'   => 'Services proposés (badges)',
							'type'    => 'list',
							'columns' => [ 'Service' ],
							'default' => "Micro-soudure
Réparation PS5/Xbox
Reconditionnés en stock
Accessoires en vente
Protection d'écran",
						],
						'practical' => [
							'label'   => 'Infos pratiques (3 cartes)',
							'type'    => 'list',
							'columns' => [ 'Titre', 'Description' ],
							'default' => "Quoi apporter ? | Juste votre appareil. Pas besoin de chargeur, câble ou boîte. Nous avons tout le nécessaire.
Combien de temps ? | La plupart des réparations sont terminées en 60 minutes. Vous pouvez attendre sur place ou revenir.
Moyens de paiement | Cash, Bancontact, carte de crédit et paiement mobile (Apple Pay, Google Pay).",
						],
					],
				],
				'mail' => [
					'label'  => 'Encart envoi postal',
					'fields' => [
						'title' => [ 'label' => 'Titre', 'type' => 'text', 'default' => 'Trop loin pour vous déplacer ?' ],
						'text'  => [ 'label' => 'Texte', 'type' => 'textarea' ],
						'cta'   => [ 'label' => 'Bouton', 'type' => 'text', 'default' => "Découvrir l'envoi postal" ],
					],
				],
			],
		],

		/* ═══════════ À PROPOS ═══════════ */
		'a-propos' => [
			'label'    => 'Page À propos',
			'sections' => [
				'hero' => [
					'label'  => 'Bandeau',
					'fields' => [
						'title' => [ 'label' => 'Titre', 'type' => 'text' ],
						'text'  => [ 'label' => 'Texte', 'type' => 'textarea' ],
					],
				],
				'stats' => [
					'label'  => 'Chiffres clés',
					'fields' => [
						'value_1' => [ 'label' => 'Chiffre 1', 'type' => 'text', 'default' => '30 000+' ],
						'label_1' => [ 'label' => 'Libellé 1', 'type' => 'text', 'default' => 'Appareils réparés' ],
						'value_2' => [ 'label' => 'Chiffre 2', 'type' => 'text', 'default' => '98%' ],
						'label_2' => [ 'label' => 'Libellé 2', 'type' => 'text', 'default' => 'Clients satisfaits' ],
						'value_3' => [ 'label' => 'Chiffre 3', 'type' => 'text', 'default' => '20+' ],
						'label_3' => [ 'label' => 'Libellé 3', 'type' => 'text', 'default' => "Années d'expérience" ],
						'value_4' => [ 'label' => 'Chiffre 4', 'type' => 'text', 'default' => '1' ],
						'label_4' => [ 'label' => 'Libellé 4', 'type' => 'text', 'default' => 'Boutique à Bruxelles' ],
					],
				],
				'story' => [
					'label'  => 'Notre histoire',
					'fields' => [
						'badge'  => [ 'label' => 'Badge', 'type' => 'text', 'default' => 'Notre histoire' ],
						'title'  => [ 'label' => 'Titre', 'type' => 'text', 'default' => "De la passion à l'expertise" ],
						'text_1' => [ 'label' => 'Paragraphe 1', 'type' => 'textarea' ],
						'text_2' => [ 'label' => 'Paragraphe 2', 'type' => 'textarea' ],
						'text_3' => [ 'label' => 'Paragraphe 3', 'type' => 'textarea' ],
					],
				],
				'sections' => [
					'label'  => 'Autres sections',
					'fields' => [
						'values_title' => [ 'label' => 'Titre « Ce qui nous guide »', 'type' => 'text', 'default' => 'Ce qui nous guide' ],
						'certs_title'  => [ 'label' => 'Titre « Certifications »', 'type' => 'text', 'default' => 'Certifications & partenaires' ],
						'certs_text'   => [ 'label' => 'Texte « Certifications »', 'type' => 'text' ],
						'join_title'   => [ 'label' => 'Titre « Rejoindre »', 'type' => 'text', 'default' => "Envie de rejoindre l'équipe ?" ],
						'join_text'    => [ 'label' => 'Texte « Rejoindre »', 'type' => 'textarea' ],
						'join_cta'     => [ 'label' => 'Bouton « Rejoindre »', 'type' => 'text', 'default' => 'Postuler' ],
					],
				],
				'lists' => [
					'label'  => 'Listes',
					'fields' => [
						'values' => [
							'label'   => 'Nos valeurs',
							'type'    => 'list',
							'columns' => [ 'Titre', 'Description' ],
							'default' => "Transparence totale | Prix affichés, devis gratuit, pas de frais cachés.
Rapidité | 90% de nos réparations sont terminées en 60 minutes.
Excellence | Techniciens formés, pièces certifiées, tests avant et après.
Proximité | Nous sommes accessibles, réactifs et à l'écoute.
Responsabilité | Réparer plutôt que jeter, pour moins de déchets électroniques.
Formation continue | Nos techniciens se forment chaque trimestre.",
						],
						'certifications' => [
							'label'   => 'Certifications & partenaires',
							'type'    => 'list',
							'columns' => [ 'Titre', 'Description' ],
							'default' => "Réparateur Agréé Apple | Pièces d'origine Apple, outils certifiés, procédures constructeur.
Samsung Partenaire | Accès direct aux pièces Samsung d'origine pour Galaxy S et A.
ISO 9001 | Système de management qualité certifié pour nos processus.
Eco-Réparateur | Label de réparation durable pour la réduction des déchets.",
						],
					],
				],
			],
		],

		/* ═══════════ CONTACT ═══════════ */
		'contact' => [
			'label'    => 'Page Contact',
			'sections' => [
				'hero' => [
					'label'  => 'Bandeau',
					'fields' => [
						'title' => [ 'label' => 'Titre', 'type' => 'text', 'default' => 'Contactez-nous' ],
						'text'  => [ 'label' => 'Texte', 'type' => 'textarea' ],
					],
				],
			],
		],

		/* ═══════════ GARANTIE ═══════════ */
		'garantie' => [
			'label'    => 'Page Garantie',
			'sections' => [
				'hero' => [
					'label'  => 'Bandeau',
					'fields' => [
						'title' => [ 'label' => 'Titre', 'type' => 'text', 'default' => 'Notre garantie' ],
						'text'  => [ 'label' => 'Texte', 'type' => 'textarea' ],
					],
				],
				'sections' => [
					'label'  => 'Titres de sections',
					'fields' => [
						'how_title'     => [ 'label' => 'Titre « Faire jouer la garantie »', 'type' => 'text', 'default' => 'Comment faire jouer la garantie ?' ],
						'faq_title'     => [ 'label' => 'Titre « Questions »', 'type' => 'text', 'default' => 'Questions sur la garantie' ],
						'problem_title' => [ 'label' => 'Titre « Un problème ? »', 'type' => 'text', 'default' => 'Un problème après réparation ?' ],
						'problem_text'  => [ 'label' => 'Texte « Un problème ? »', 'type' => 'textarea' ],
					],
				],
			],
		],

		/* ═══════════ ENVOI POSTAL ═══════════ */
		'envoi' => [
			'label'    => 'Page Envoi postal',
			'sections' => [
				'hero' => [
					'label'  => 'Bandeau',
					'fields' => [
						'title' => [ 'label' => 'Titre', 'type' => 'text', 'default' => 'Envoyez votre appareil par la poste' ],
						'text'  => [ 'label' => 'Texte', 'type' => 'textarea' ],
					],
				],
				'info' => [
					'label'  => 'Section « Ce qu\'il faut savoir »',
					'fields' => [
						'title' => [ 'label' => 'Titre', 'type' => 'text', 'default' => "Ce qu'il faut savoir" ],
					],
				],
			],
		],

		/* ═══════════ RENDEZ-VOUS ═══════════ */
		'rdv' => [
			'label'    => 'Page Rendez-vous',
			'sections' => [
				'hero' => [
					'label'  => 'Bandeau',
					'fields' => [
						'badge' => [ 'label' => 'Badge', 'type' => 'text', 'default' => 'FastFix · Bruxelles' ],
						'title' => [ 'label' => 'Titre', 'type' => 'text', 'default' => 'Planifiez votre réparation' ],
						'text'  => [ 'label' => 'Texte', 'type' => 'textarea' ],
					],
				],
			],
		],
	];
}

/** Valeurs par défaut, aplaties en clés « page.section.champ ». */
function fastfix_content_defaults() {
	$defaults = [];
	foreach ( fastfix_content_registry() as $page_key => $page ) {
		foreach ( $page['sections'] as $section_key => $section ) {
			foreach ( $section['fields'] as $field_key => $field ) {
				if ( ! empty( $field['default'] ) ) {
					$defaults[ "$page_key.$section_key.$field_key" ] = $field['default'];
				}
			}
		}
	}
	return $defaults;
}

/** Contenus enregistrés, par-dessus les valeurs par défaut. */
function fastfix_get_content() {
	$saved = get_option( 'fastfix_content', [] );
	if ( ! is_array( $saved ) ) $saved = [];
	$saved = array_filter( $saved, function( $v ) { return $v !== '' && $v !== null; } );
	return array_merge( fastfix_content_defaults(), $saved );
}

function fastfix_content( $key, $default = '' ) {
	$content = fastfix_get_content();
	return $content[ $key ] ?? $default;
}

/**
 * Contenus prêts pour le frontend : les images (stockées comme identifiant de
 * média) sont converties en URL directement utilisables.
 */
function fastfix_get_content_for_api() {
	$content  = fastfix_get_content();
	$registry = fastfix_content_registry();

	foreach ( $registry as $page_key => $page ) {
		foreach ( $page['sections'] as $section_key => $section ) {
			foreach ( $section['fields'] as $field_key => $field ) {
				$type = $field['type'] ?? 'text';
				$key  = "$page_key.$section_key.$field_key";
				if ( empty( $content[ $key ] ) ) continue;

				if ( $type === 'image' ) {
					$url = wp_get_attachment_image_url( (int) $content[ $key ], 'full' );
					if ( $url ) $content[ $key ] = $url;

				} elseif ( $type === 'list' ) {
					// Une ligne = un élément ; les colonnes sont séparées par « | »
					$rows = [];
					foreach ( preg_split( '/

|
|
/', $content[ $key ] ) as $line ) {
						$line = trim( $line );
						if ( $line === '' ) continue;
						$rows[] = array_map( 'trim', explode( '|', $line ) );
					}
					$content[ $key ] = $rows;
				}
			}
		}
	}
	return $content;
}

/* ── Interface d'administration ── */
add_action( 'admin_enqueue_scripts', function( $hook ) {
	if ( isset( $_GET['page'] ) && $_GET['page'] === 'fastfix-contenus' ) {
		wp_enqueue_media();
	}
} );

function fastfix_render_content_page() {
	if ( ! current_user_can( 'manage_options' ) ) return;

	$registry     = fastfix_content_registry();
	$current_page = isset( $_GET['ff_page'] ) ? sanitize_key( $_GET['ff_page'] ) : 'home';
	if ( ! isset( $registry[ $current_page ] ) ) $current_page = 'home';

	if ( isset( $_POST['fastfix_content_nonce'] ) && wp_verify_nonce( $_POST['fastfix_content_nonce'], 'fastfix_save_content' ) ) {
		$saved = get_option( 'fastfix_content', [] );
		if ( ! is_array( $saved ) ) $saved = [];

		foreach ( $registry[ $current_page ]['sections'] as $section_key => $section ) {
			foreach ( $section['fields'] as $field_key => $field ) {
				$key   = "$current_page.$section_key.$field_key";
				$input = 'ff_' . str_replace( '.', '__', $key );
				if ( ! isset( $_POST[ $input ] ) ) continue;

				$value = wp_unslash( $_POST[ $input ] );
				$type  = $field['type'] ?? 'text';
				$saved[ $key ] = in_array( $type, [ 'textarea', 'list' ], true )
					? sanitize_textarea_field( $value )
					: sanitize_text_field( $value );
			}
		}

		update_option( 'fastfix_content', $saved );
		echo '<div class="notice notice-success is-dismissible"><p><strong>Contenus enregistrés.</strong> Les changements sont visibles immédiatement sur le site.</p></div>';
	}

	$content = fastfix_get_content();
	?>
	<div class="wrap">
		<h1>FastFix — Contenus des pages</h1>
		<p class="description" style="font-size:14px;max-width:860px;">
			Modifiez les titres, textes et images de chaque section du site.
			<strong>Un champ laissé vide conserve le contenu d'origine</strong> livré avec le site :
			vous ne risquez donc jamais de vider une section par erreur.
		</p>

		<h2 class="nav-tab-wrapper" style="margin-top:18px;">
			<?php foreach ( $registry as $page_key => $page ) : ?>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=fastfix-contenus&ff_page=' . $page_key ) ); ?>"
				   class="nav-tab <?php echo $page_key === $current_page ? 'nav-tab-active' : ''; ?>">
					<?php echo esc_html( $page['label'] ); ?>
				</a>
			<?php endforeach; ?>
		</h2>

		<form method="post" style="margin-top:20px;">
			<?php wp_nonce_field( 'fastfix_save_content', 'fastfix_content_nonce' ); ?>

			<?php foreach ( $registry[ $current_page ]['sections'] as $section_key => $section ) : ?>
				<h2 class="title"><?php echo esc_html( $section['label'] ); ?></h2>
				<table class="form-table">
					<?php foreach ( $section['fields'] as $field_key => $field ) :
						$key   = "$current_page.$section_key.$field_key";
						$input = 'ff_' . str_replace( '.', '__', $key );
						$value = $content[ $key ] ?? '';
						$type  = $field['type'] ?? 'text';
					?>
						<tr>
							<th><label for="<?php echo esc_attr( $input ); ?>"><?php echo esc_html( $field['label'] ); ?></label></th>
							<td>
								<?php if ( $type === 'list' ) :
									$cols = $field['columns'] ?? [ 'Valeur' ];
								?>
									<textarea id="<?php echo esc_attr( $input ); ?>" name="<?php echo esc_attr( $input ); ?>" rows="7" class="large-text code" placeholder="Vide = liste actuelle du site conservée"><?php echo esc_textarea( $value ); ?></textarea>
									<p class="description">
										Une ligne par élément.
										<?php if ( count( $cols ) > 1 ) : ?>
											Séparez les colonnes par <code>|</code> —
											format : <code><?php echo esc_html( implode( ' | ', $cols ) ); ?></code>
										<?php endif; ?>
										Ajoutez ou supprimez des lignes pour changer le nombre d'éléments affichés.
									</p>

								<?php elseif ( $type === 'textarea' ) : ?>
									<textarea id="<?php echo esc_attr( $input ); ?>" name="<?php echo esc_attr( $input ); ?>" rows="3" class="large-text" placeholder="Vide = contenu actuel du site conservé"><?php echo esc_textarea( $value ); ?></textarea>

								<?php elseif ( $type === 'image' ) :
									$img_url = $value ? wp_get_attachment_image_url( (int) $value, 'medium' ) : '';
								?>
									<div class="ff-media">
										<input type="hidden" id="<?php echo esc_attr( $input ); ?>" name="<?php echo esc_attr( $input ); ?>" value="<?php echo esc_attr( $value ); ?>" />
										<div class="ff-media-preview" style="margin-bottom:8px;">
											<?php if ( $img_url ) : ?>
												<img src="<?php echo esc_url( $img_url ); ?>" style="max-height:90px;border:1px solid #dcdcde;border-radius:4px;padding:4px;background:#fff;" />
											<?php else : ?>
												<span class="description">Image d'origine du site conservée</span>
											<?php endif; ?>
										</div>
										<button type="button" class="button ff-media-pick">Choisir une image</button>
										<button type="button" class="button ff-media-clear">Retirer</button>
									</div>

								<?php else : ?>
									<input type="text" id="<?php echo esc_attr( $input ); ?>" name="<?php echo esc_attr( $input ); ?>" value="<?php echo esc_attr( $value ); ?>" class="large-text" placeholder="Vide = contenu actuel du site conservé" />
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</table>
			<?php endforeach; ?>

			<p class="submit"><button type="submit" class="button button-primary button-hero">Enregistrer cette page</button></p>
		</form>
	</div>

	<script>
	jQuery(function ($) {
		var emptyLabel = '<span class="description">Image d\'origine du site conservée</span>';

		$('.ff-media-pick').on('click', function () {
			var wrap  = $(this).closest('.ff-media');
			var frame = wp.media({ title: 'Choisir une image', multiple: false, library: { type: 'image' } });

			frame.on('select', function () {
				var att = frame.state().get('selection').first().toJSON();
				var url = (att.sizes && att.sizes.medium) ? att.sizes.medium.url : att.url;
				wrap.find('input[type=hidden]').val(att.id);
				wrap.find('.ff-media-preview').html(
					'<img src="' + url + '" style="max-height:90px;border:1px solid #dcdcde;border-radius:4px;padding:4px;background:#fff;" />'
				);
			});
			frame.open();
		});

		$('.ff-media-clear').on('click', function () {
			var wrap = $(this).closest('.ff-media');
			wrap.find('input[type=hidden]').val('');
			wrap.find('.ff-media-preview').html(emptyLabel);
		});
	});
	</script>
	<?php
}
