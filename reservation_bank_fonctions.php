<?php
/**
 * Fonctions utiles au plugin Réservations Bank
 *
 * @plugin     Réservations Bank
 * @copyright  2015
 * @author     Rainer Müller
 * @licence    GNU/GPL
 * @package    SPIP\Reservation_bank\Fonctions
 */

if (!defined('_ECRIRE_INC_VERSION'))
	return;

/**
 * Crée une transaction
 *
 * @param  integer $id_reservation id_reservation
 * @return $id_transaction  Id de la transaction crée
 */
function rb_inserer_transaction($id_reservation) {
	session_set('id_reservation',$id_reservation);
	$inserer_transaction = charger_fonction("inserer_transaction", "bank");
	$donnees = unserialize(recuperer_fond(
		'inclure/paiement',
			array('id_reservation' => $id_reservation,
				'cacher_paiement_public' => TRUE,
			)
		)
	);
	$id_transaction = $inserer_transaction($donnees['montant'], $donnees['options']);
	return $id_transaction;
}