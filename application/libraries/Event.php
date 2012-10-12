<?php

/**
 * @author 侯振宇
 * @date 2012-6-25
 * @encode UTF-8
 */
class Event{
    private $no_namespace_sign = "#";

    /*
    used to store binded event;
    example event string : event_name1.namespace2.namespace1;
    example structure:
    array(
        'event_name1' => array(
            '#' => array(
                callback_array1,
                callback_array2
            ),
            'namespace1' => array(
                '#' => array(
                    callback_array3,
                    callback_array4
                ),
                'namespace2' => array(
                    callback_array5
                )
            )
        )
    );
    */
    private $event_array = array();



    public function __construct(){
        
    }

    //$module_callback : array(module_name, callback_method)
    public function bind( $event_name_str, $module_callback ){
        $event_name_arr = $this -> standardize_event_name( $event_name_str );
        $event_name = $event_name_arr['event_name'];
        $namespaces = $event_name_arr['namespaces'];

        if( !isset( $this -> event_array[$event_name_arr['event_name']] )){
            $this -> event_array[$event_name] = array();
        }

        $current_namespace = &$this -> event_array[$event_name];
        while( $namespace = array_pop( $namespaces) ){
            if( !isset( $current_namespace[$namespace] ) ){
                $current_namespace[$namespace] = array();
            }
            $current_namespace = &$current_namespace[$namespace];
        }
        array_push( $current_namespace, $module_callback );
    }

    public function bind_multi( &$bindings ){
        foreach( $bindings as $event_name => $module_callback ){
            $this -> bind( $event_name, $module_callback );
        }
    }

    private function standardize_event_name( $event_name ){
        $output = array(
            'event_name' => '',
            'namespaces' => array()
        );

        $event_name_arr = explode('.', $event_name );
        $output['event_name'] = $event_name_arr[0];

        $output['namespaces'] = array_slice( $event_name_arr, 1);
        $output['namespaces'] = empty( $output['namespaces'] ) ? array("#") : $output['namespaces'];

        return $output;
    }

    public function trigger(){
        $args = func_get_args();
        $event_name_str = $args[0];
        $event_name_arr = $this -> standardize_event_name( $event_name_str );
        $event_name = $event_name_arr['event_name'];
        $namespaces = $event_name_arr['namespaces'];

        if( !isset( $this -> event_array[$event_name] ) ){
            return false;
        }
        
        $results = array();
        $current_namespace = &$this -> event_array[$event_name];
        while( $namespace = array_pop( $namespaces ) ){
            if( isset( $current_namespace[$namespace] ) ){
                $current_namespace = &$current_namespace[$namespace];
            }else{
                return false;
            }
        }

        foreach( $current_namespace as $module_callback ){
            $callback_args = array_slice( $args, 1 );
            $results[] = call_user_func_array(  array($module_callback[0] ,$module_callback[1]), $callback_args );
        }
        return $results; 
    }

    public function trigger_async(){
        $args = func_get_args();
        return call_user_func_array(  array($this ,'trigger'), $args );
    }

}

?>
