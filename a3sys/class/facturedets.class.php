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
require_once DOL_DOCUMENT_ROOT.'/multicurrency/class/multicurrency.class.php';

/**
 * Put your class' description here
 */
class Facturedets  extends CommonObject
{

    /** @var string Error code or message */
	public $error;
    /** @var array Several error codes or messages */
	public $errors = array();
    public $element='a3sys_facturedets';
    public $table_element = 'a3sys_facturedets';
    /** @var int An example ID */
	public $id;
    /** @var mixed An example property */
	public $ref_article;
	/** @var mixed An example property */
	public $libelle_article;
    /** @var mixed An example property */
	public $qty;
	/** @var mixed An example property */
	public $pu_ttc;
    /** @var mixed An example property */
	public $montant_ligne;
	/** @var mixed An example property */
	public $tva;
	/** @var mixed An example property */
	public $N_fact;

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

	/**
	 * Create object into database
	 *
	 * @param User $user User that create
	 * @param int $notrigger 0=launch triggers after, 1=disable triggers
	 * @return int <0 if KO, Id of created object if OK
	 */
	public function create($user)
	{
		global $conf, $langs;
		$error = 0;

		// Clean parameters
		if (isset($this->ref_article)) {
			$this->ref_article = trim($this->ref_article);
		}
		if (isset($this->libelle_article)) {
			$this->libelle_article = trim($this->libelle_article);
		}
		if (isset($this->qty)) {
			$this->qty = trim($this->qty);
		}
		if (isset($this->pu_ttc)) {
			$this->pu_ttc = trim($this->pu_ttc);
		}
		if (isset($this->montant_ligne)) {
			$this->montant_ligne = trim($this->montant_ligne);
		}
		if (isset($this->tva)) {
			$this->tva = trim($this->tva);
		}
		if (isset($this->N_fact)) {
			$this->N_fact = trim($this->N_fact);
		}

		// Check parameters
		// Put here code to add control on parameters values
		// Insert request
		$sql = "INSERT INTO " . MAIN_DB_PREFIX . "a3sys_facturedets(";
		$sql.= " ref_article,";
		$sql.= " libelle_article,";
		$sql.= " qty,";
		$sql.= " pu_ttc,";
		$sql.= " montant_ligne,";
		$sql.= " tva,";
		$sql.= " N_fact";
		$sql.= ") VALUES (";
		$sql.= " " . $this->db->escape($this->ref_article) . ",";
		$sql.= " '" . $this->db->escape($this->libelle_article) . "',";
		$sql.= " " . $this->db->escape($this->qty) . ",";
		$sql.= " " . $this->db->escape($this->pu_ttc) . ",";
		$sql.= " " . $this->db->escape($this->montant_ligne) . ",";
		$sql.= " " . $this->db->escape($this->tva) . ",";
		$sql.= " " . $this->db->escape($this->N_fact) . "";
		$sql.= ")";
		$this->db->begin();

		dol_syslog(get_class($this) . "::create ", LOG_DEBUG);
		$resql = $this->db->query($sql);
		if (! $resql) {
			$error ++;
			$this->errors[] = "Error " . $this->db->lasterror();
		}

		// Commit or rollback
		if ($error) {
			foreach ($this->errors as $errmsg) {
				dol_syslog(__METHOD__ . " " . $errmsg, LOG_ERR);
				$this->error.=($this->error ? ', ' . $errmsg : $errmsg);
			}
			$this->db->rollback();

			return -1 * $error;
		} else {
			$this->db->commit();

			return $this->id;
		}
	}

	/**
	 * Load object in memory from database
	 *
	 * @param int $id Id object
	 * @return int <0 if KO, >0 if OK
	 */
	public function fetch($id = null, $N_fact = null, $montant = null)
	{
		global $langs;
		$num = -1;
		$sql = "SELECT";
		$sql.= " t.rowid,";
		$sql.= " t.ref_article,";
		$sql.= " t.libelle_article,";
		$sql.= " t.qty,";
		$sql.= " t.pu_ttc,";
		$sql.= " t.montant_ligne,";
		$sql.= " t.tva,";
		$sql.= " t.N_fact ";
		$sql.= " FROM " . MAIN_DB_PREFIX . "a3sys_facturedets as t";
		if(!empty($id))$sql.= " WHERE t.rowid = " . $id;
		if(!empty($N_fact) && !empty($montant)){
			$sql.= ','.MAIN_DB_PREFIX . "a3sys_factures as f";
			$sql.= " WHERE t.N_fact = " . $N_fact;
			$sql.= " and f.montant_fact >= " . $montant;
		}
		dol_syslog(get_class($this) . "::fetch ", LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql) {
			$num = $this->db->num_rows($resql);
			if ($this->db->num_rows($resql)) {
				$obj = $this->db->fetch_object($resql);

				$this->id = $obj->rowid;
				$this->entity = $obj->entity;
				$this->ref_article = $obj->ref_article;
				$this->libelle_article = $obj->libelle_article;
				$this->qty = $obj->qty;
				$this->pu_ttc = $obj->pu_ttc;
				$this->montant_ligne = $obj->montant_ligne;
				$this->tva = $obj->tva;
				$this->N_fact = $obj->N_fact;
			}
			$this->db->free($resql);
			return $num;
		} else {
			$this->error = "Error " . $this->db->lasterror();
			dol_syslog(__METHOD__ . " " . $this->error, LOG_ERR);

			return -1;
		}
	}

	/**
	 * Update object into database
	 *
	 * @param User $user User that modify
	 * @param int $notrigger 0=launch triggers after, 1=disable triggers
	 * @return int <0 if KO, >0 if OK
	 */
	public function update($user = 0, $notrigger = 0)
	{
		global $conf, $langs;
		$error = 0;

		// Clean parameters
		if (isset($this->ref_article)) {
			$this->ref_article = trim($this->ref_article);
		}
		if (isset($this->libelle_article)) {
			$this->libelle_article = trim($this->libelle_article);
		}
		if (isset($this->qty)) {
			$this->qty = trim($this->qty);
		}
		if (isset($this->pu_ttc)) {
			$this->pu_ttc = trim($this->pu_ttc);
		}
		if (isset($this->montant_ligne)) {
			$this->montant_ligne = trim($this->montant_ligne);
		}
		if (isset($this->tva)) {
			$this->tva = trim($this->tva);
		}
		if (isset($this->N_fact)) {
			$this->N_fact = trim($this->N_fact);
		}

		// Check parameters
		// Put here code to add control on parameters values
		// Update request

		$sql = "UPDATE " . MAIN_DB_PREFIX . "a3sys_facturedets SET";
		$sql.= " ref_article=" . (strval($this->ref_article)!=''?"'".$this->ref_article."'":"null") . ",";
		$sql.= " libelle_article=" . (strval($this->libelle_article)!=''?"'".$this->libelle_article."'":"null") . ",";
		$sql.= " qty=" . (strval($this->qty)!=''?"'".$this->qty."'":"null") . ",";
		$sql.= " pu_ttc=" . (strval($this->pu_ttc)!=''?"'".$this->pu_ttc."'":"null") . ",";
		$sql.= " montant_ligne=" . (strval($this->montant_ligne)!=''?"'".$this->montant_ligne."'":"null") . ",";
		$sql.= " tva=" .(strval($this->tva)!=''?"'".$this->db->idate($this->tva)."'":"null"). ",";
		$sql.= " N_fact=" . (strval($this->N_fact)!=''?"'".$this->N_fact."'":"null") . "";
		$sql.= " WHERE rowid=" . $this->id;

		$this->db->begin();

		dol_syslog(__METHOD__ . " sql=" . $sql, LOG_DEBUG);
		$resql = $this->db->query($sql);
		if (! $resql) {
			$error ++;
			$this->errors[] = "Error " . $this->db->lasterror();
		}

		// Commit or rollback
		if ($error) {
			foreach ($this->errors as $errmsg) {
				dol_syslog(__METHOD__ . " " . $errmsg, LOG_ERR);
				$this->error.=($this->error ? ', ' . $errmsg : $errmsg);
			}
			$this->db->rollback();

			return -1 * $error;
		} else {
			$this->db->commit();

			return $this->id;
		}
	}

	/**
	 * Delete object in database
	 *
	 * @param User $user User that delete
	 * @param int $notrigger 0=launch triggers after, 1=disable triggers
	 * @return int <0 if KO, >0 if OK
	 */
	public function delete($user)
	{
		global $conf;
		
		if (! $error)
        {
			$sql = "DELETE FROM " . MAIN_DB_PREFIX . "a3sys_facturedets";
			$sql .= " WHERE rowid = " . $this->id;
			dol_syslog(get_class($this) . "::delete ", LOG_DEBUG);
			$resql = $this->db->query($sql);
		}
		
		if (! $resql) {
			$error ++;
			$this->errors[] = "Error " . $this->db->lasterror();
		}
		
		if (! $error)
		{
			$this->db->commit();

			return 1;
		}
		else
		{
			dol_syslog($this->error, LOG_ERR);
			$this->db->rollback();
			return -1;
		}
		
		if (! $error) {
			return 1;
		} else {
			$this->error = $this->db->lasterror();
			return - 1;
		}
	}
	
	
	public function fetchALl()
	{
		global $langs;
		$tab = array();
		$sql = "SELECT *";
		$sql.= " FROM " . MAIN_DB_PREFIX . "a3sys_facturedets ";
		dol_syslog(__METHOD__ . " sql=" . $sql, LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql) {
			for($cmp=0;$cmp<$this->db->num_rows($resql);$cmp++){
				$obj = $this->db->fetch_object($resql);
				$tab [$obj->rowid] = array('ref_article'=> $obj->ref_article, 'libelle_article'=> $obj->libelle_article, 'qty'=> $obj->qty, 'pu_ttc'=> $obj->pu_ttc, 'montant_ligne'=> $obj->montant_ligne, 'tva'=> $obj->tva, 'N_fact'=> $obj->N_fact);
			}
			$this->db->free($resql);

		} else {
			$this->error = "Error " . $this->db->lasterror();
			dol_syslog(__METHOD__ . " " . $this->error, LOG_ERR);

			return -1;
		}
		return $tab;
	}

}
