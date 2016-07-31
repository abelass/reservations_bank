<?php
function montant_reservations_detail_total($id_reservation) {
	spip_log('bank_traiter_reglement', 'teste');
	$sql = sql_select('id_reservations_detail,prix,prix_ht,quantite,devise,taxe,descriptif,montant_paye',
		'spip_reservations_details', 'id_reservation=' . $id_reservation);
		
	$montant_reservations_detail_total = array ();
	$montant_paye = array ();
	while ( $data = sql_fetch($sql) ) {
		$id_reservations_detail = $data['id_reservations_detail'];
		$montant_paye[$id_reservations_detail] = $data['montant_paye'];
	
		if ($montant = $data['prix'] <= 0) {
			$montant = $data['prix_ht'] + $data['taxe'];
		}
		$montant_reservations_detail_total[$id_reservations_detail] = $montant;
		set_request('montant_reservations_detail_' . $id_reservation_detail,$montant);
	}
	set_request('montant_paye',$montant_paye);
	set_request('montant_reservations_detail_total', $montant_reservations_detail_total);
	
	return $montant_reservations_detail_total;
}