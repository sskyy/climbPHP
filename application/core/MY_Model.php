<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of MY_Model
 *
 * @author jason
 */
class MY_Model extends CI_Model{
    public function __construct(){
        parent::__construct();

        $module_info = array(
            'listen_events' => array(),
            'access_rules' => array()
        );
        if( method_exists( $this, 'listen_events')){
            $module_listen = $this -> listen_events();
            $module_info['listen_events'] = $module_listen;
        }
        
        if( method_exists( $this, 'access_rules')){
            $module_listen = $this -> access_rules();
            $module_info['access_rules'] = $module_listen;
        }
        
        //! CI mechanics trick
        if( get_class($this) != "MY_Model"){
            get_instance() -> event -> trigger("module loaded",  get_class($this), $module_info);
        }
    }
}

?>
