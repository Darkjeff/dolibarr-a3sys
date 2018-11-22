<?php
/* <one line to give the program's name and a brief idea of what it does.>
 * Copyright (C) <2017> SaaSprov.ma <saasprov@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/** Includes */
require_once DOL_DOCUMENT_ROOT."/core/class/commonobject.class.php";

/**
 * Put your class' description here
 */
class Sys3a  extends CommonObject
{

    /** @var string Error code or message */
	public $error;
    /** @var array Several error codes or messages */
	public $errors = array();
    /** @var int An example ID */
	public $id;

	/**
	 * Constructor
	 *
	 * @param DoliDb $db Database handler
	 */
	public function __construct(DoliDB $db)
	{
		global $langs;

        $this->db = $db;
	}

	/*
	* check company by codeclient
	*******************/
	
	public function check_code_client($code = null, $grossiste = null) 
	{
		global $langs;
		$sql = "SELECT";
		$sql.= " rowid ";
		$sql.= " FROM ".MAIN_DB_PREFIX."societe  ";
		
		if(empty($grossiste)){			
			$sql.= " where code_client = " . $code; 
		}else{
			$sql.= " where barcode = " . $code; 
		}
		
		dol_syslog(get_class($this) . "::fetch ", LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql) {
			if ($this->db->num_rows($resql)) {
				$obj = $this->db->fetch_object($resql);
				return $obj->rowid;
			}else{
				return false;
			}
		} else {
			$this->error = "Error " . $this->db->lasterror();
			dol_syslog(__METHOD__ . " " . $this->error, LOG_ERR);

			return false;
		}
	}
	
	/*
	* check product by code
	*******************/
	
	public function check_code_product($code = null, $grossiste = null) 
	{
		global $langs;
		$sql = "SELECT";
		$sql.= " rowid ";
		$sql.= " FROM ".MAIN_DB_PREFIX."product  ";
		
		if(empty($grossiste)){			
			$sql.= " where ref = " . $code;
		}else{
			$sql.= " where barcode = '" . $code . "'"; 
		}
		
		dol_syslog(get_class($this) . "::fetch ", LOG_DEBUG);
		// echo $sql."<br>";
		$resql = $this->db->query($sql);
		if ($resql) {
			if ($this->db->num_rows($resql)) {
				$obj = $this->db->fetch_object($resql);
				return $obj->rowid;
			}else{
				return false;
			}
		} else {
			$this->error = "Error " . $this->db->lasterror();
			dol_syslog(__METHOD__ . " " . $this->error, LOG_ERR);

			return false;
		}
	}
	
	/*
	* check invoice by ref
	*******************/
	
	public function check_code_invoice($ref = null) 
	{
		global $langs;
		$sql = "SELECT";
		$sql.= " rowid ";
		$sql.= " FROM ".MAIN_DB_PREFIX."facture  ";
		$sql.= " where facnumber = " . $ref; 
		dol_syslog(get_class($this) . "::fetch ", LOG_DEBUG);
		// echo $sql."<br>";
		$resql = $this->db->query($sql);
		if ($resql) {
			if ($this->db->num_rows($resql)) {
				$obj = $this->db->fetch_object($resql);
				return $obj->rowid;
			}else{
				return false;
			}
		} else {
			$this->error = "Error " . $this->db->lasterror();
			dol_syslog(__METHOD__ . " " . $this->error, LOG_ERR);

			return false;
		}
	}
	
	/*
	* Save INVOICE
	*******************/
	
	public function save_invoice($data = null, $user, $grossiste = null) 
	{
		// print "<pre>";
		// print_r($data);
		// print "</pre>";
		// die;
		
		foreach($data as $num_f => $v){
			$inv_id = 0;
			// var_dump($num_f);
			
			if(empty($grossiste)){			
				$d = explode(".", $v['d_fact']);
				$dateinvoice = '20'.$d[2].'-'.$d[1].'-'.$d[0];
			}else{
				// echo $v['d_fact'];
				$date = DateTime::createFromFormat('Ymj', $v['d_fact']);
				if(empty($date)){die("Erreur date :".$v['d_fact']);}
				$dateinvoice = $date->format('Y-m-d');
			}
			
			
			
			$sql = "INSERT INTO ".MAIN_DB_PREFIX."facture (";
			$sql.= " facnumber";
			$sql.= ", entity";
			$sql.= ", type";
			$sql.= ", fk_soc";
			$sql.= ", datec";
			$sql.= ", date_valid";
			$sql.= ", datef";
			$sql.= ", fk_statut";
        	$sql.= ", paye";
			$sql.= ", fk_user_author";
			$sql.= ")";
			$sql.= " VALUES (";
			$sql.= "'$num_f'";
			$sql.= ", 1";
			$sql.= ", '0'";
			$sql.= ", '".$v['societe_id']."'";
			$sql.= ", '" . date('Y-m-d') . "'";
			$sql.= ", '" . date('Y-m-d') . "'";
			$sql.= ", '".$dateinvoice."'";
			$sql.= ", 1";
        	$sql.= ", 1";
			$sql.= ", ".($user->id > 0 ? "'".$user->id."'":"null");
			$sql.=")";
			// echo $sql."<br>";
			dol_syslog(get_class($this)."::create", LOG_DEBUG);
			$resql=$this->db->query($sql);
			
			if ($resql){
				$inv_id = $this->db->last_insert_id(MAIN_DB_PREFIX.'facture');
				// echo $inv_id."<br>";
				$TTC = 0;
				$THT = 0;
				$TTVA = 0;
				foreach($v['products'] as $k => $v2){
					
					// $object2->fk_product = $prod_id;
					// $object2->desc = $v2['desc'];
					// $object2->qty = $v2['qte'];
					// $object->lines[$k] = $object2;
					
					$ttc = $v2['ttc'];
					$t_ttc = $v2['t_ttc'];
					$ht = ($ttc * 100/(100+$v2['txtva']));
					$tht = $ht * $v2['qte'];
					$ttva = $tht * $v2['txtva']/100;
					
					$ht = round($ht, 2);
					$tht = round($tht, 2);
					$ttva = round($ttva, 2);
					$t_ttc = round($t_ttc, 2);
					
					$TTC += $t_ttc;
					$THT += $tht;
					$TTVA += $ttva;
					
					// $object->lines[$k]->tva_tx = 20;
					
					$sql = "INSERT INTO ".MAIN_DB_PREFIX."facturedet (";
					$sql.= " fk_facture";
					$sql.= ", fk_product";
					$sql.= ", label";
					$sql.= ", tva_tx";
					$sql.= ", qty";
					$sql.= ", subprice";
					$sql.= ", total_ht";
					$sql.= ", total_tva";
					$sql.= ", total_ttc";
					$sql.= ")";
					$sql.= " VALUES (";
					$sql.= "'$inv_id'";
					$sql.= ", '".$v2['prod_id']."'";
					$sql.= ", '".$v2['desc']."'";
					$sql.= ", ".$v2['txtva'];
					$sql.= ", ".$v2['qte'];
					$sql.= ", ".$ht;
					$sql.= ", ".$tht;
					$sql.= ", ".$ttva;
					$sql.= ", ".$t_ttc;
					$sql.=")";
					// echo $sql."<br>";
					$resqldet = $this->db->query($sql);
				}
				
				/** update prices **/
				$sqlupdate = "UPDATE ".MAIN_DB_PREFIX."facture SET total = $THT, total_ttc = $TTC, tva = $TTVA where rowid = $inv_id";
				$resqldet = $this->db->query($sqlupdate);
				
				
				
				
			}else {
				$error ++;
				$errors[] = "Error " . $this->db->lasterror();
			}
			
			if ($error) {
				foreach ($this->errors as $errmsg) {
					dol_syslog(__METHOD__ . " " . $errmsg, LOG_ERR);
					$this->error.=($this->error ? ', ' . $errmsg : $errmsg);
				}
				$this->db->rollback();

			} else {
				$this->db->commit();
			}
			
		}
		
	}
}
