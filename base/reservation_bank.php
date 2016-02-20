<?php
/**
 * Déclarations relatives à la base de données
 *
 * @plugin     Réservations Bank
 * @copyright  2015
 * @author     Rainer
 * @licence    GNU/GPL
 * @package    SPIP\Reservations_credits\Pipelines
 */

if (!defined('_ECRIRE_INC_VERSION'))
  return;


/**
 * Déclaration des objets éditoriaux
 *
 * @pipeline declarer_tables_objets_sql
 * @param array $tables
 *     Description des tables
 * @return array
 *     Description complétée des tables
 */
function reservation_bank_declarer_tables_objets_sql($tables) {
  //Ajouter un champ id_reservation à la table transaction
  $tables['spip_transactions']['field']['id_reservation'] = "bigint(21) NOT NULL DEFAULT 0";
  $tables['spip_transactions']['field']['paiement_detail'] = "varchar(255)  DEFAULT '0' NOT NULL";
  return $tables;
}
