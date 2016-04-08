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
		$id_transaction = $flux['data']['_id_transaction'];

		// Les infos supplémentaires de la transaction
		$transaction = sql_fetsel('id_reservation,montant,auteur', 'spip_transactions', 'id_transaction=' . $id_transaction);
		$id_reservation = $flux['id_reservation'] = $transaction['id_reservation'];
		$montant_transaction = $flux['montant'] = $transaction['montant'];


		//Cas spécial pour les crédits
		if ($flux['data']['_mode'] == 'credit' AND $credit = credit_client('',$transaction['auteur'])) {
				$flux['data']['credit'] = '';
				$flux['data']['email_client'] = $email_client = $transaction['auteur'];
				$flux['_hidden'] .= '<input name="email_client" value="' .$email_client. '" type="hidden"/>';
		}

		// Définir les champs pour les détails de réservation.
		$sql = sql_select('id_reservations_detail,prix,prix_ht,quantite,devise,taxe,descriptif,montant_paye', 'spip_reservations_details', 'id_reservation=' . $id_reservation);

		$montant_detail = array();
		$montant_reservations_detail_defaut = array();
		$montant_reservations_detail_total = array();
		$count = sql_count($sql);
		$montant_transaction_detail = '';
		if ($count > 0) {
			$montant_transaction_detail = $montant_transaction / $count;
		}
		$montant_ouvert = '';
		$montant_defaut = '';
		while ($data = sql_fetch($sql)) {
			$id_reservations_detail = $data['id_reservations_detail'];
			$devise = $data['devise'];
			$montant_paye[$id_reservations_detail] = $data['montant_paye'];

			if ($montant = $data['prix'] <= 0) {
				$montant = $data['prix_ht'] + $data['taxe'];
			}

			$montant_reservations_detail_total[$id_reservations_detail] = $montant;


			$montant_ouvert = $montant_defaut = $montant- $data['montant_paye'];

			if ($montant_ouvert < $montant_transaction_detail AND $montant_ouvert >= 0) {
					if (!$montant_defaut = _request('montant_reservations_detail_' . $id_reservations_detail)) {
				$montant_defaut = $montant_ouvert;
				}
			}

			if ($credit[$devise] > 0 AND ($credit[$devise]/ $count ) <= $montant_defaut) {
				$montant_defaut = $credit[$devise] / $count ;
			}

			if ($montant_defaut > 0) {

				$montant_detail[] = array(
					'saisie' => 'input',
					'options' => array(
						'nom' => 'montant_reservations_detail_' . $id_reservations_detail,
						'label' => $data['descriptif'],
						'defaut' => $montant_defaut,
						'size' => 20,
					)
				);
			}

				$flux['data']['montant_reservations_detail_' . $id_reservations_detail] = '';
				$montant_reservations_detail_defaut[$id_reservations_detail] = $montant_ouvert;
		}

		if($credit) {
			$flux['credit'] = '';
			$flux['_hidden'] .= '<input name="credit" value="' .$credit[$devise]. '" type="hidden"/>';
		}

		$flux['_mes_saisies'] =  array(
			array(
			'saisie' => 'fieldset',
			'options' => array(
				'nom' => 'specifier',
				'label' => _T('reservation_bank:label_fieldset_specifier'),
				),
					'saisies' => array(
						array(
							'saisie' => 'oui_non',
							'options' => array(
							'nom' => 'specifier_montant',
							'label' => _T('reservation_bank:label_specifier_montant'),
							'defaut' => _request('specifier_montant')
						)
					),
				),
			),
			array(
			'saisie' => 'fieldset',
			'options' => array(
				'nom' => 'montant',
				'label' => _T('reservation_bank:label_fieldset_montant_detail',array('devise' => $devise)),
				'afficher_si' => '@specifier_montant@ == "on"',
				),
				'saisies' => $montant_detail,
			)
		);
		$flux['data']['specifier_montant'] = _request('specifier_montant');
		$flux['data']['montant_reservations_detail_defaut'] = '';
		$flux['data']['montant_paye'] = $montant_paye;
		$flux['_hidden'] .= '<input name="id_reservation" value="' .$id_reservation. '" type="hidden"/>';
		$flux['_hidden'] .= '<input name="id_transaction" value="' .$id_transaction. '" type="hidden"/>';

		$montant_reservations_detail_defaut = serialize($montant_reservations_detail_defaut);
		$montant_reservations_detail_total = serialize($montant_reservations_detail_total);
		$flux['_hidden'] .= "<input name='montant_reservations_detail_defaut' value='$montant_reservations_detail_defaut' type='hidden'/>";
		$flux['_hidden'] .= "<input name='montant_reservations_detail_total' value='$montant_reservations_detail_total' type='hidden'/>";
	}
	return $flux;
}

/**
 * Intervientpendant la vérification d'un formulaire CVT
 *
 * @pipeline formulaire_verifier
 * @param  array $flux Données du pipeline
 * @return array       Données du pipeline
 */
function reservation_bank_formulaire_verifier($flux) {
	$form = $flux['args']['form'];
	if ($form == 'encaisser_reglement'){
		$id_reservation = _request('id_reservation');

		$montant_reservations_detail_defaut = _request('montant_reservations_detail_defaut') ? unserialize(_request('montant_reservations_detail_defaut')) : array();
		$montant_reservations_detail_total = _request('montant_reservations_detail_total') ? unserialize(_request('montant_reservations_detail_total')) : array();

		set_request('montant_reservations_detail_defaut',$montant_reservations_detail_defaut);
		set_request('montant_reservations_detail_total',$montant_reservations_detail_total);

		$sql = sql_select('id_reservations_detail,montant_paye', 'spip_reservations_details', 'id_reservation=' . $id_reservation);
		$montant_ouvert = array();
		$montant_paye = array();
		$montants = array();
		while ($data = sql_fetch($sql)) {
			$id_reservations_detail = $data['id_reservations_detail'];
			$montant = _request('montant_reservations_detail_' .$id_reservations_detail);
			$montant_defaut = $montant_reservations_detail_defaut[$id_reservations_detail];

			$montant_paye[$id_reservations_detail] = $paye = $data['montant_paye'];
			$montants[] = $montant;
			set_request('montant_paye',$montant_paye);
			if (_request('specifier_montant') AND $montant > $montant_defaut) {
				$flux['data']['montant_reservations_detail_' .$id_reservations_detail]= _T('reservation_bank:message_erreur_montant_reservations_detail',array('montant_ouvert' => $montant_defaut));
			}
		}

		if ($credit = _request('credit') AND $credit < array_sum($montants)){
			$flux['data']['specifier_montant']= _T('reservation_bank:message_erreur_montant_credit',array('credit' => $credit));
		}
		session_set('encaisser_montant_regle',array_sum($montants));
	}
	return $flux;
}

/**
 * Intervient au traitement d'un formulaire CVT
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
			$id_transaction =rb_inserer_transaction(session_get('id_reservation'));
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
	if ($table == 'spip_transactions') {
		$flux['data']['id_reservation'] = session_get('id_reservation' );
		}
		return $flux;
		}

/**
 * Intervient lors de l’édition d’un élément éditorial.
 *
 * @pipeline pre_edition
 * @param  array $flux Données du pipeline
 * @return array       Données du pipeline
 */
function reservation_bank_pre_edition($flux) {
	$table = $flux['args']['table'];


	if ($table == 'spip_reservations_details' AND $flux['data']['statut'] == 'accepte') {
		// Si le montant payé est inférieur au montant dû on change les statuts.
		$id_reservation_detail = $flux['args']['id_reservation_detail'];
		$montant_reservations_detail_total = _request('montant_reservations_detail_total');
		$montant_total = $montant_reservations_detail_total[$id_reservation_detail];
		$montant_reservations_detail = _request('montant_reservations_detail_' . $id_reservation_detail);
		$montant_paye = _request('montant_paye');
		$montant_paye = $montant_paye[$id_reservation_detail] + $montant_reservations_detail;

		if ($montant_paye < $montant_total) {
			$flux['data']['statut'] = 'accepte_part';
		}
		// Enregistre le montant payé
		$flux['data']['montant_paye'] = $montant_paye;

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
	$contexte = $flux['data']['contexte'];

	// Ajoute des champs supplémentaires pour le paiment des réservations dans l'espace privé.
	if ($fond == 'formulaires/encaisser_reglement' AND _request('exec') == 'payer_reservation'){
		$reservation_bank = recuperer_fond('formulaires/inc-encaisser_reglement_reservation', $contexte);
		$flux['data']['texte'] = str_replace('<ul class="editer-groupe">', $reservation_bank . '<ul class="editer-groupe">', $flux['data']['texte']);
	}

	//Ajoute un colonne en plus à la liste des réservations
	if ($fond =='prive/objets/liste/inc-reservations_reservations') {
		$row = recuperer_fond('prive/objets/liste/inc-reservations_thead', $contexte);
		$flux['data']['texte'] = str_replace("<th class='client' scope='col'>", $row. "<th class='client' scope='col'>", $flux['data']['texte']);
	}

	//Ajoute un colonne en plus à la liste des réservations
	if ($fond =='prive/objets/liste/inc-reservations_row') {
		$row = recuperer_fond('prive/objets/liste/inc-reservations_row_paiement', $contexte);
		$flux['data']['texte'] = str_replace("<td class='client'>", $row. "<td class='client'>", $flux['data']['texte']);
	}

	//Ajoute le lien de paiement à la page réservation
	if ($fond =='prive/objets/contenu/inc-reservation_montant') {
		$id_reservation = $contexte['id_reservation'];
		$sql = sql_select('montant_paye', 'spip_reservations_details', 'id_reservation=' . $id_reservation );

		$montant_paye = array();
		while ($data = sql_fetch($sql)) {
			$montant_paye[] = $data['montant_paye'];
		}
		$contexte['montant_paye'] = array_sum($montant_paye);
		$row = recuperer_fond('prive/objets/contenu/inc-reservation_montant_paiement', $contexte);
		$flux['data']['texte'] = str_replace('</div>','</div>' . $row, $flux['data']['texte']);
	}

	return $flux;
}

/**
 * Enregistrer le bon reglement d'une commande liee a une transaction du plugin bank
 *
 * @pipeline bank_traiter_reglement
 * @param array $flux
 * @return array mixed
 */
function reservation_bank_bank_traiter_reglement($flux){
	// Si on est dans le bon cas d'un paiement de reservation et qu'il y a un id_reservation et que la reservation existe toujours
	if (
		$id_transaction = $flux['args']['id_transaction']
		and $transaction = sql_fetsel("*","spip_transactions","id_transaction=".intval($id_transaction))
		and $id_reservation = $transaction['id_reservation']
	){
		$montant_reservations_detail_total = _request('montant_reservations_detail_total') ? _request('montant_reservations_detail_total') : array();

		$paiement_detail = array();
		foreach(array_keys($montant_reservations_detail_total) AS $id_reservation_detail ) {
			$paiement_detail[$id_reservation_detail] = _request('montant_reservations_detail_' . $id_reservation_detail);
		}

		if (!$montant_regle = array_sum($paiement_detail)){
			$montant_regle = $transaction['montant_regle'];
		}
		elseif (is_array($montant_regle)) {
			$montant_regle = array_sum($montant_regle);
		}


		set_request('montant_regle',$montant_regle);

		$set = array(
			'montant_regle' => $montant_regle,
			'paiement_detail' => serialize($paiement_detail)
			);

		sql_updateq('spip_transactions',$set,'id_transaction=' . $id_transaction);
		spip_log('bank_traiter_reglement', 'teste');
		include_spip('action/editer_objet');
		objet_instituer('reservation',$id_reservation,array(
			'statut' => 'accepte',
			'date_paiement' => $transaction['date_transaction'],
			)
		);
	}

	return $flux;
}
