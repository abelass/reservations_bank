<?php
// This is a SPIP language file  --  Ceci est un fichier langue de SPIP

if (!defined('_ECRIRE_INC_VERSION')) return;

$GLOBALS[$GLOBALS['idx_lang']] = array(


	// L
	'label_cacher_paiement_public' => 'ne pas proposer de formulaire de paiement sur le site public',
	'label_fieldset_montant_detail' => 'Spécifiez le montant (en @devise@) pour chaque détail de réservation',
	'label_fieldset_specifier' => 'Spécifier',
	'label_specifier_montant' => 'Spécifier le montant',

	// M
	'message_erreur_montant_credit' => 'Vous avez depassé la limite de votre crédit qui es de @credit@ !',
	'message_erreur_montant_reservations_detail' => 'Le montant ne doit pas être supérieure à @montant_ouvert@ (montant encore à payer)',
	'message_paiement_vendeur' => 'Mode de paiement : "@mode@",  voir <a href="@url@">détail</a>',
	'montant_paye' => 'Payé :',


	// P
	'paiement_commande' => 'Paiement de la commande #@id_commande@',
	'paiement_reservation' => 'Paiement de la réservation #@id_reservation@',
	// R
	'reservation_bank_titre' => 'Réservations Bank',

	// T
	'titre_page_configurer_reservation_bank' => 'Configuration Réservations Bank',
	'titre_paiement_reservation' => 'Paiement de la réservation',
	'titre_paiement_vendeur' => 'Paiement :',
	'titre_payer_reservation' => 'Payez la réservation',
);

?>