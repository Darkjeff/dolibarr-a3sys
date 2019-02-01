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


// Load Dolibarr environment
$res = @include ("../../main.inc.php"); // For root directory
if (! $res)
	$res = @include ("../../../main.inc.php"); // For "custom" directory
if (! $res)
	die("Include of main fails");


require_once '../class/sys3a.class.php';

$action=GETPOST('action', 'alpha');
$newFile=GETPOST('newFile', 'int');

$obj = new Sys3a($db);



/*
 * Actions
 */

  if ($action == 'uploadtxt')
    {
		$ext = pathinfo($filename, PATHINFO_EXTENSION);
		
		/// try something else sometime don t work 
		/*
		if($ext != 'txt'){
			$error = "le type du fichier est incompatible";
			die($error);
		}
		*/
		
		$lines = file($_FILES['ftxt']['tmp_name'], FILE_IGNORE_NEW_LINES);
		$listerror = array();
		$data = array();
		$dataFac = array();
		$error = false;
		$code_company_error = array();
		$code_prd_error = array();
		$code_inv_error = array();
		$success = null;
		$i=1;
		$head = 
			array(
				"Code client",
				"N° fact",
				"Date fact",
				"Montant fact",
				"Réf article",
				"Libellé article",
				"Q",
				"PU TTC",
				"Montant ligne",
				"TVA"
			)
		;
		
		foreach($lines as $key => $value){
			// var_dump($line);
			
			$line = explode("\t",$value);
			// var_dump($line);die;
			if(!is_numeric($line[0])){
				continue;
			}
			// var_dump($line);
			$societe_id = $obj->check_code_client($line[0]);
			
			if(empty($societe_id)){
				$code_company_error[$line[0]] = $line[0];
				$error = true;
				continue;
			}
			
			$product_id = $obj->check_code_product($line[4]);
			
			if(empty($product_id)){
				$code_prd_error[$line[4]] = $line[4];
				$error = true;
				continue;
			}
			
			$inv = $obj->check_code_invoice($line[1]);
			
			if(!empty($inv)){
				$code_inv_error[$line[1]] = $line[1];
				$error = true;
				continue;
			}
			
			$line[8] = (int)$line[8];
			
			var_dump($data) ;
			
			
			if(array_key_exists($line[1], $data)){
				$data[$line[1]]['products'][] = array(
												'prod_id' => $product_id,
												'desc' => utf8_encode ($line[5]),
												'qte' => $line[6],
												'ttc' => $line[7],
												't_ttc' => $line[8],
												'txtva' => $line[9]
											);
				
			}else{				
				$data[$line[1]] = array( 
									'societe_id' => $societe_id,
									'n_fact' => $line[1],
									'd_fact' => $line[2]
									);
									
				$data[$line[1]]['products'][] = array(
												'prod_id' => $product_id,
												'desc' => utf8_encode ($line[5]),
												'qte' => $line[6],
												'ttc' => $line[7],
												't_ttc' => $line[8],
												'txtva' => $line[9]
											);
			}
			
			
			
		}
		// var_dump($code_company_error);
		// var_dump($code_prd_error);
		// var_dump($code_inv_error);
		
		if(!empty($data)){ // j'enregistre les factures
		
			$obj->save_invoice($data, $user);
			$success = "Factures enregistrées";
		}
		
		if($error){
			$errcomp = implode(", ", $code_company_error);
			$errprd = implode(", ", $code_prd_error);
			$errinv = implode(", ", $code_inv_error);

			if($errcomp) setEventMessages($langs->trans("Sociétés avec codes ($errcomp)  indisponibles!"), null, 'errors');
			if($errprd) setEventMessages($langs->trans("Produits avec codes ($errprd)  indisponibles!"), null, 'errors');
			if($errinv) setEventMessages($langs->trans("Numéros de factures ($errinv) existent déjà!"), null, 'errors');
			
		}
		
    }

	$form = new Form($db);

	$morejs=array();
	$title = $langs->trans('Import Cresus');
	llxHeader('',$title,'','','','',$morejs,'',0,0);

	print '<form method="POST" action="" enctype="multipart/form-data">';
	print '<input type="hidden" name="action" value="uploadtxt">';
	print '<input type="hidden" name="newFile" value="1">';
		dol_fiche_head();
		print load_fiche_titre($langs->trans("Import fichier Cresus"));
		
			print ' <table style="text-align: center;" id="tb1" class="liste" width="100%">
				  <tr>
					<td style="text-align:right;">Fichier à importer</td>
					<td><input class="flat" type="file" size="33" name="ftxt"/></td>
				  </tr>
				  <tr>
					<td colspan="2"><input type="submit" class="button" name="add" value="Import "/></td>
				  </tr>
			</table>';
		dol_fiche_end();
	print '</form>';
	
	if(!empty($data)){
		echo '<pre><ul>';
		foreach($data as $field=>$success){
			echo '<li style="color:green;"> Facture enregistrée : '.$success['n_fact'].'</li>';
		}
		echo '</ul></pre>';
	}
	
	if(!empty($error)){
		if(!empty($code_company_error)){
			echo '<pre><ul>';
			foreach($code_company_error as $field=>$err){
				echo '<li style="color:red;"> Société avec code : '.$err.' indisponible!</li>';
			}
			echo '</ul></pre>';
		}
		
		
		if(!empty($code_prd_error)){
			echo '<pre><ul>';
			foreach($code_prd_error as $field=>$err){
				echo '<li style="color:red;"> Produits avec code : '.$err.' indisponible!</li>';
			}
			echo '</ul></pre>';
		}
		
		
		if(!empty($code_inv_error)){
			echo '<pre><ul>';
			foreach($code_inv_error as $field=>$err){
				echo '<li style="color:red;"> Numéros de factures '.$err.' existe déjà dans la base de données!</li>';
			}
			echo '</ul></pre>';
		}
		
	}
		
	
		
	

llxFooter();
$db->close();