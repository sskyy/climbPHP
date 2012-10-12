<?php

/**
 *
 * @author jason
 */
class event_module extends Module{
    private $manager;
    private $events;
    
    public function __construct(){
        parent::__construct();
    }
    
    public function on_module_register( $manager ){
        $this -> manager = $manager;
        $this -> _build_event_center();
    }
     
    public function on_upper_module_load( &$upper_module ){
        if( method_exists(  $upper_module, "set_event_handler" )){
            return $upper_module -> set_event_handler( $this -> events );
        }
        return false;
    }
    
    private function _build_event_center(){
        $this -> manager -> get("require") -> library("Event");
        $this -> events = new Event();
    }
    
    public function trigger(){
        $args = func_get_args();
        return call_user_func_array(  array($this -> events ,'trigger'), $args );
    }
    
    public function trigger_async(){
        $args = func_get_args();
        return call_user_func_array(  array($this ,'trigger'), $args );
    }

    public function bind( $event_name, $callback ){
        $this -> events -> bind( $event_name, $callback );
    }

}

?>
