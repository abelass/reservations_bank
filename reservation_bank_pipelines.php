<?php
/**
 * Utilisations de pipelines par Réservations Bank
 *
 * @plugin     Réservations Bank
 * @copyright  2015
 * @author     Rainer Müller
 * @licence    GNU/GPL
 * @package    SPIP\Reservation_bank\Pipelines
 */

if (!defined('_ECRIRE_INC_VERSION'))
	return;

/**
 * permet de modifier le tableau de valeurs envoyé par la fonction charger d’un formulaire CVT
 *
 * @pipeline formulaire_charger
 * @param  array $flux Données du pipeline
 * @return array       Données du pipeline
 */
function reservation_bank_formulaire_charger($flux){
	$form = $flux['args']['form'];
	if ($form == 'encaisser_reglement'){
		$flux['data']['_mes_saisies'] =  array(
			array( // le fieldset
			'saisie' => 'fieldset',
			'options' => array(
				'nom' => 'montant',
				'label' => _T('reservation_bank:fieldset_label_montant'),
				),
					'saisies' => array( // les champs dans le fieldset
						array( // champ titre : ligne de texte
							'saisie' => 'oui_non',
							'options' => array(
							'nom' => 'specifier_montant',
							'label' => _T('reservation_bank:label_specifier_montant'),
						)
					),
				)
			),
		);
	}
	return $flux;
}
		/**
 * Intervient au traitement d'unf formulaire CVT
 *
 * @pipeline formulaire_traiter
 * @param  array $flux Données du pipeline
 * @return array       Données du pipeline
 */
function reservation_bank_formulaire_traiter($flux) {
	$form = $flux['args']['form'];

	// Affiche le formulaire de paiment au retour du formulaire réservation
	if ($form == 'reservation') {
		include_spip('inc/config');
		$id_reservation = session_get('id_reservation');

		if (!$cacher_paiement_public = lire_config('reservation_bank/cacher_paiement_public')) {
			$flux['data']['message_ok'] .= recuperer_fond('inclure/paiement', array(
					'id_reservation' => session_get('id_reservation'),
					'cacher_paiement_public' => FALSE,
				)
			);
		}
		else {
			$inserer_transaction = charger_fonction("inserer_transaction", "bank");
			$donnees = unserialize(recuperer_fond(
				'inclure/paiement',
					array('id_reservation' => session_get('id_reservation'),
						'cacher_paiement_public' => TRUE,
					)
				)
			);
			$id_transaction = $inserer_transaction($donnees['montant'], $donnees['options']);
		}
	}
	return $flux;
}

/**
 * Intervient avant l'enregistrement d'un objet
 *
 * @pipeline pre_insertion
 * @param  array $flux Données du pipeline
 * @return array       Données du pipeline
 */
function reservation_bank_pre_insertion($flux) {
	$table = $flux['args']['table'];

	// Enregistre l'id_reservation dans la transaction.
	if ($table = 'spip_transactions') {
		$flux['data']['id_reservation'] = session_get('id_reservation');
	}
	return $flux;
}

/**
 * Permet de compléter ou modifier le résultat de la compilation d’un squelette donné.
 *
 * @pipeline recuperer_fond
 * @param  array $flux Données du pipeline
 * @return array       Données du pipeline
 */
function reservation_bank_recuperer_fond($flux){
	$fond = $flux['args']['fond'];

	// Ajoute des champs supplùementaires pour le paiment des réservations dans l'espace privé.
	if ($fond == 'formulaires/encaisser_reglement' AND _request('exec') == 'payer_reservation'){
		$reservation_bank = recuperer_fond('formulaires/inc-encaisser_reglement_reservation',$flux['data']['contexte']);
		$flux['data']['texte'] = str_replace('<ul class="editer-groupe">', $reservation_bank . '<ul class="editer-groupe">', $flux['data']['texte']);
	}
	return $flux;
}
