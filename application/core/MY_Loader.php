<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of MY_Loader
 *
 * @author jason
 */
class MY_Loader extends CI_Loader{

    public function __construct(){
        $CI = &get_instance();
        parent::__construct();
        parent::library("Module");
        parent::model( "require_module" );
        parent::library( "module_manager", array( "require" => $CI -> require_module) );
        
    }

    
    public function module( $module_name ){
        $CI = & get_instance();
        if( !isset( $CI -> $module_name) ){
            parent::model( $module_name );
            return $CI -> module_manager -> load($module_name);
        }else{
            return $CI -> $module_name;
        }
        
    }

}

?>
