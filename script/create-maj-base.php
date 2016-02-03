<?php
/*
 * Script créant et vérifiant que les champs requis s'ajoutent bien
 */

if(!defined('INC_FROM_DOLIBARR')) {
	define('INC_FROM_CRON_SCRIPT', true);

	require('../config.php');

}



dol_include_once('/of/class/ordre_fabrication_asset.class.php');

$PDOdb=new TPDOdb;

$o=new TAssetOF;
$o->init_db_by_vars($PDOdb);

$o=new TAssetOFLine;
$o->init_db_by_vars($PDOdb);

if (class_exists('TWorkstation'))
{
	$o=new TAssetWorkstation;
	$o->init_db_by_vars($PDOdb);	
}
else 
{
	echo 'Workstation module is missing';
	exit;
}


$o=new TAssetWorkstationOF;
$o->init_db_by_vars($PDOdb);

$o=new TAssetControl;
$o->init_db_by_vars($PDOdb);

$o=new TAssetControlMultiple;
$o->init_db_by_vars($PDOdb);

$o=new TAssetOFControl;
$o->init_db_by_vars($PDOdb);

$o=new TAssetWorkstationTask;
$o->init_db_by_vars($PDOdb);

$o=new TAssetWorkstationProduct;
	$o->init_db_by_vars($PDOdb);