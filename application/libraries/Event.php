<?php

/**
 * @author 侯振宇
 * @date 2012-6-25
 * @encode UTF-8
 */
class Event{

    private $event_array = array( );

    public function __construct(){
        
    }

    //$module_callback : array(module_name, callback_method)
    public function bind( $event_name, $module_callback ){
        if( !isset( $this -> event_array[$event_name] ) ){
            $this -> event_array[$event_name] = array( );
        }
        array_push( $this -> event_array[$event_name], $module_callback );
    }

    public function bind_multi( &$bindings ){
        foreach( $bindings as $event_name => $module_callback ){
            $this -> bind( $event_name, $module_callback );
        }
    }

    public function trigger(){
        $args = func_get_args();
        $event_name = $args[0];
        if( !isset( $this -> event_array[$event_name] ) ){
            return false;
        }
        
        $results = array();
        foreach( $this -> event_array[$event_name] as $module_callback ){
            $callback_args = array_slice( $args, 1 );

            $results[] = call_user_func_array(  array($module_callback[0] ,$module_callback[1]), $callback_args );
        }
        return count( $results ) > 1 ? $results : $results[0]; 
    }

    public function trigger_async(){
        $args = func_get_args();
        return call_user_func_array(  array($this ,'trigger'), $args );
    }

}

?>
