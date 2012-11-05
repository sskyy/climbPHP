<?php

class MY_Controller extends CI_Controller{

    public function __construct(){
        parent::__construct();
        $this -> module_system_init();
        $moduleResponce = $this -> module_execute();
        $this -> output( $moduleResponce );
    }

    public function module_system_init(){
        $this -> load -> library("Manager");
    }

    private function module_execute(){
        $RTR =& load_class('Router', 'core');
        $class = $RTR -> fetch_class();
        $method = $RTR -> fetch_method();
    	return $this -> manager -> mod("event") -> trigger("system/init", $class, $method );
    }

    private function output( $moduleResponce ){
    	echo json_encode( $moduleResponce);
    }
}

?>
