<?php
/* <one line to give the program's name and a brief idea of what it does.>
 * Copyright (C) 2015 ATM Consulting <support@atm-consulting.fr>
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

/**
 * \file    class/actions_of.class.php
 * \ingroup of
 * \brief   This file is an example hook overload class file
 *          Put some comments here
 */

/**
 * Class Actionsof
 */
class Actionsof
{
	/**
	 * @var array Hook results. Propagated to $hookmanager->resArray for later reuse
	 */
	public $results = array();

	/**
	 * @var string String displayed by executeHook() immediately after return
	 */
	public $resprints;

	/**
	 * @var array Errors
	 */
	public $errors = array();

	/**
	 * Constructor
	 */
	public function __construct()
	{
	}

	/**
	 * Overloading the doActions function : replacing the parent's function with the one below
	 *
	 * @param   array()         $parameters     Hook metadatas (context, etc...)
	 * @param   CommonObject    &$object        The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          &$action        Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	function doActions($parameters, &$object, &$action, $hookmanager)
	{
		
        if($parameters['currentcontext'] === 'ordersuppliercard') {
           
            if(GETPOST('action') === 'confirm_commande' && GETPOST('confirm') === 'yes') {
                
                $time_livraison = $object->date_livraison; 
                
                $res = $db->query("SELECT fk_source as 'fk_of' 
                            FROM ".MAIN_DB_PREFIX."element_element 
                            WHERE sourcetype='ordre_fabrication' AND fk_target=".$object->id." AND targettype='order_supplier' ");
                
                define('INC_FROM_DOLIBARR',true);
                
                dol_include_once("/asset/config.php");
                dol_include_once("/asset/class/asset.class.php");   
                dol_include_once("/asset/class/ordre_fabrication_asset.class.php");   
                            
                if($obj = $db->fetch_object($res)) {
                    // of lié à la commande
                    $PDOdb=new TPDOdb;
                    
                    $OF = new TAssetOF;
                    $OF->load($PDOdb, $obj->fk_of);
                    
                    $OF->date_lancement =  $time_livraison;
                    $OF->save($PDOdb); 
                    
                }
                else {
                   // pas d'of liés directement         
                   $TProduct = $TProd =  array();     
                   foreach($object->lines as &$l) {
                        if($l->product_type == 0){ 
                            $TProduct[] = $l->fk_product;
                            
                            if(!isset($TProd[$l->fk_product])) $TProd[$l->fk_product] = 0;
                            $TProd[$l->fk_product]+=$l->qty;
                        }    
                   } 
                  
                        
                   $res = $db->query("SELECT DISTINCT of.rowid as 'fk_of' 
                            FROM ".MAIN_DB_PREFIX."assetOf_line ofl
                            LEFT JOIN ".MAIN_DB_PREFIX."assetOf of ON (of.rowid = ofl.fk_assetOf)
                            WHERE ofl.fk_product IN (".implode(',',$TProduct).")
                            AND of.status='ONORDER'
                            ORDER BY of.date_besoin ASC");    
                   $PDOdb=new TPDOdb;
                   
                   while($obj = $db->fetch_object($res)) {
                       
                       $OF = new TAssetOF;
                       $OF->load($PDOdb, $obj->fk_of);
                       $to_save = false;
                       foreach($OF->TAssetOFLine as &$line) {
                           
                           if(isset($TProd[$line->fk_product]) && $TProd[$line->fk_product]>0) {
                               $TProd[$line->fk_product]-= ($line->qty_needed>0 ?  $line->qty_needed : $line->qty );
                               
                               if($OF->date_lancement<$time_livraison){
                                   $OF->date_lancement =  $time_livraison;
                                   $to_save = true;
                               }
                               
                               
                           }
                           
                       }
                       
                       if($to_save) {
                          // print 'OF '.$OF->getId().'$time_livraison'.$time_livraison;
                           $OF->save($PDOdb);
                       }
                      
                   }
                    //exit;     
                    
                }
                
            }
            
            
        }

	}



}