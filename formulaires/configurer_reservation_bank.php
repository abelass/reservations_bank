<?php

// Sécurité
if (!defined('_ECRIRE_INC_VERSION'))
	return;

function formulaires_configurer_reservation_bank_saisies_dist() {
	include_spip('inc/config');
	$config = lire_config('reservation_bank');
	// Les prestas coonfigurés.
	include_spip('inc/bank');
	$prestas_actifs = bank_lister_configs();
	print_r($prestas_actifs);

	// Les types de prestas nécessitanpt pas de callbacj du fournisseur.
	$prestas_simple = array('cheque', 'gratuit', 'virement');
	$prestas_defauts = array();

	foreach ($prestas_simple AS $presta) {
		if (isset($prestas_actifs[$presta]) and $prestas_actifs[$presta]['actif']) {
			$prestas_defauts[$presta] = _T('bank:label_presta_' . $presta);
		}
	}

	return array(
		array(
			'saisie' => 'fieldset',
			'options' => array(
				'nom' => 'fieldset_parametres',
				'label' => _T('reservation_evenement:cfg_titre_parametrages')
			),
			'saisies' => array(
				array(
					'saisie' => 'oui_non',
					'options' => array(
						'nom' => 'cacher_paiement_public',
						'label' => _T('reservation_bank:label_cacher_paiement_public'),
						'defaut' => $config['cacher_paiement_public']
					)
				),
				array(
					'saisie' => 'oui_non',
					'options' => array(
						'nom' => 'preceder_formulaire',
						'label' => _T('reservation_bank:label_preceder_formulaire'),
						'defaut' => $config['preceder_formulaire'],
						'afficher_si' => '@cacher_paiement_public@ == ""',
					)
				),
			),
		),
		array(
			'saisie' => 'fieldset',
			'options' => array(
				'nom' => 'fieldset_espace_prive',
				'label' => _T('reservation_bank:cfg_titre_espace_prive')
			),
			'saisies' => array(
				array(
					'saisie' => 'oui_non',
					'options' => array(
						'nom' => 'definir_presta_defaut',
						'label' => _T('reservation_bank:label_definir_presta_defaut'),
						'defaut' => $config['definir_presta_defaut']
					)
				),
				array(
					'saisie' => 'selection',
					'options' => array(
						'nom' => 'presta_defaut',
						'datas' => $prestas_defauts,
						'defaut' => 'valide',
						'cacher_option_intro' => 'on',
						'label' => _T('reservation_bank:label_presta_defaut'),
						'defaut' => $config['presta_defaut'],
						'afficher_si' => '@definir_presta_defaut@ == "on"',
					),
				),
			),
		),
	);
}
