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
 * @pipeline post_edition
 * @param  array $flux Données du pipeline
 * @return array       Données du pipeline
 */
  function reservation_bank_formulaire_traiter($flux){
    $form = $flux['args']['form'];
    // si creation d'un nouvel article lui attribuer la licence par defaut de la config
    if ($form == 'reservation') {
      $flux['data']['message_ok'] .= recuperer_fond('inclure/paiement',
        array('id_reservation' => session_get('id_reservation')));
    }
    return $flux;
  }

/*
 * Un fichier de pipelines permet de regrouper
 * les fonctions de branchement de votre plugin
 * sur des pipelines existants.
 */
?>