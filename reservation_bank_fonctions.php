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
	 * @param integer $id_reservation
	 *        	id_reservation
	 * @return $id_transaction Id de la transaction crée
	 */
	function rb_inserer_transaction($id_reservation) {
		
		// Voir si on peut récupérer une transaction, sino on crée une.
		if (!$id_transaction = sql_getfetsel('id_transaction', 'spip_transactions', 'id_reservation=' . $id_reservation . ' AND statut LIKE ("commande")')) {
			$inserer_transaction = charger_fonction("inserer_transaction", "bank");
			$donnees = unserialize(recuperer_fond('inclure/paiement_reservation', array(
				'id_reservation' => $id_reservation,
				'cacher_paiement_public' => TRUE
			)));
			$id_transaction = $inserer_transaction($donnees['montant'], $donnees['options']);
		}
		
		return $id_transaction;
	}
	
	/**
	 * Retourne les prestataires simple (pas besoin d'une application externe à spip).
	 *
	 * @param integer $id_reservation
	 *        	L'id de la réservation.
	 *
	 * @return array
	 */
	function rb_prestataires_simples_actives($id_reservation) {
		// Les prestas coonfigurés.
		include_spip('inc/bank');
		
		$prestas_actifs = bank_lister_configs();
		
		$fonction_prix = charger_fonction('prix', 'inc/');
		$sql = sql_select('id_reservations_detail', 'spip_reservations_details', 'id_reservation=' . $id_reservation);
		$prix_details = array();
		while ($data = sql_fetch($sql)) {
			$prix_details[] = $fonction_prix('reservations_detail', $data['id_reservations_detail']);
		}
		$prix = array_sum($prix_details);
		
		// Si montant supérieur 'a 0, les types de prestas nécessitant pas de callback du fournisseur.
		if ($prix > 0) {
			
			$prestas_simple = array(
				'cheque',
				'virement'
			);
			$prestataires_simples_actives = array();
			foreach ($prestas_simple as $presta) {
				if (isset($prestas_actifs[$presta]) and $prestas_actifs[$presta]['actif']) {
					$prestataires_simples_actives[$presta] = _T('bank:label_presta_' . $presta);
				}
			}
		}
		// Sinon presta = gratuit.
		else {
			$prestataires_simples_actives = array(
				'gratuit' => _T('bank:titre_bouton_payer_gratuit')
			);
		}
		
		return $prestataires_simples_actives;
	}