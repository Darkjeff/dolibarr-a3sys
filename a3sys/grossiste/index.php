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


require_once '../lib/PHPExcel/Classes/PHPExcel/IOFactory.php';
require_once '../class/sys3a.class.php';

$action=GETPOST('action', 'alpha');
$newFile=GETPOST('newFile', 'int');

$obj = new Sys3a($db);



/*
 * Actions
 */

  if ($action == 'uploadxls')
    {
		$file = $_FILES['fxls']['tmp_name'];
		$filename = $_FILES['fxls']['name'];
		// var_dump($filename);
		$ext = pathinfo($filename, PATHINFO_EXTENSION);
		
		if($ext != 'xls'){
			$error = "le type du fichier est incompatible";
			die($error);
		}
		
		$inputFileType = PHPExcel_IOFactory::identify($file);
		$objReader = PHPExcel_IOFactory::createReader($inputFileType);
		$objPHPExcel = $objReader->load($file);
		
		$file_array = $objPHPExcel->getSheet()->toArray(null,true,true,true);
		
		// var_dump($file_array);
		// die();
		
		$listerror = array();
		$autres = array();
		$data = array();
		$dataFac = array();
		$error = false;
		$code_company_error = array();
		$code_prd_error = array();
		$code_inv_error = array();
		$success = null;
		$i=1;
		
		foreach($file_array as $key => $value){
			// var_dump($value['H']);continue;
			if(!is_numeric($value['B'])){
				continue;
			}
			// die($value['H']);
			// var_dump($line);
			$societe_id = $obj->check_code_client($value['B'], 1);
			
			if(empty($societe_id)){
				$code_company_error[$value['B']] = $value['B'];
				$error = true;
				continue;
			}
			
			$product_id = $obj->check_code_product($value['L'], 1);
			
			if(empty($product_id)){
				$code_prd_error[$value['L']] = $value['L'];
				$error = true;
				continue;
			}
			
			if(empty($value['H'])){
				$autres[$key] = $value['H'];
				$error = true;
				continue;
			}
			
			$inv = $obj->check_code_invoice($value['H'], 1);
			
			if(!empty($inv)){
				$code_inv_error[$value['H']] = $value['H'];
				$error = true;
				continue;
			}
			$value['H'] = (int)$value['H'];
			$ttc = $value['Y']*$value['O'];
        
        	if ($value['AD'] == 1) {
            $txtva = 3.8;
            }else{	
            $txtva = 8;
            }
            
			if(array_key_exists($value['H'], $data)){
				$data[$value['H']]['products'][] = array(
												'prod_id' => $product_id,
												'desc' => utf8_encode ($value['N']),
												'qte' => $value['O'],
												'ttc' => $value['Y'],
												't_ttc' => $ttc,
												'txtva' => $txtva
											);
				
			}else{				
				$data[$value['H']] = array( 
									'societe_id' => $societe_id,
									'n_fact' => $value['H'],
									'd_fact' => $value['I']
									);
									
				$data[$value['H']]['products'][] = array(
												'prod_id' => $product_id,
												'desc' => utf8_encode ($value['N']),
												'qte' => $value['O'],
												'ttc' => $value['Y'],
												't_ttc' => $ttc,
												'txtva' => $txtva
											);
			}
			
			
			
		}
		// var_dump($code_company_error);
		// var_dump($code_prd_error);
		// var_dump($code_inv_error);
		
		if(!empty($data)){ // j'enregistre les factures
			
			// var_dump($data);
			$obj->save_invoice($data, $user, 1);
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
	$title = $langs->trans('Import Grossiste');
	llxHeader('',$title,'','','','',$morejs,'',0,0);

	print '<form method="POST" action="" enctype="multipart/form-data">';
	print '<input type="hidden" name="action" value="uploadxls">';
	print '<input type="hidden" name="newFile" value="1">';
		dol_fiche_head();
		print load_fiche_titre($langs->trans("Import fichier Grossiste"));
		
			print ' <table style="text-align: center;" id="tb1" class="liste" width="100%">
				  <tr>
					<td style="text-align:right;">Fichier à importer</td>
					<td><input class="flat" type="file" size="33" name="fxls"/></td>
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
		
		if(!empty($autres)){
			echo '<pre><ul>';
			foreach($code_inv_error as $field=>$err){
				echo '<li style="color:red;"> Autre erreur à la ligne '.$err.' !</li>';
			}
			echo '</ul></pre>';
		}
		
	}

llxFooter();
$db->close();