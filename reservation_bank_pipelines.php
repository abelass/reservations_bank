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
 * Intervient au traitement du formulaire
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
		if (!$cacher_paiement_public = lire_config('reservation_bank/cacher_paiement_public')) {
			$flux['data']['message_ok'] .= recuperer_fond('inclure/paiement', array('id_reservation' => session_get('id_reservation')));
		}
	}
	return $flux;
}
