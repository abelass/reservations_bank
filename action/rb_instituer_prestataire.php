<?php

if (!defined("_ECRIRE_INC_VERSION"))
	return;

	function action_rb_insituer_prestataire_dist(){
		$securiser_action = charger_fonction('securiser_action', 'inc');
		$arg = $securiser_action();

		list($id_reservation, $prestataire) = explode($arg);



	}
