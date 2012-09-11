<?php

class xn_input_module extends Module{
    private $input;
    public function __construct(){
        parent::__construct();
    }
    
    public function on_module_register( &$manager ){
        $this -> input = $manager -> get("require") -> utility("input");
    }
    
    public function fetch_data( $keys_to_be_fetch ){
        $output = array();
        $keys = is_array( $keys_to_be_fetch )? $keys_to_be_fetch :array($keys_to_be_fetch);
        foreach( $keys as $key ){
            if( $this -> input -> get_post($key) ){
                $output[$key] = $this -> input -> get_post($key);
            }
        }
        return  $output ;
    }
    
}
?>
