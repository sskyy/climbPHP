<?php

class xn_output_module extends Module{
    public function __construct(){
        parent::__construct();
    }
    
    public function output( ){
        $args= func_get_args();
        $type = $args[0];
        if( method_exists( $this , "output_".$type ) ){
            $method_name = "output_".$type;
        }else{
            $method_name = "output_default";
        }
        call_user_func_array( array($this, $method_name), $args );
    }
    
    public function output_default(){
        $args = func_get_args();
        $type = $args[0];
        $output_args = array_pop( array_slice( $args , 1) );
        $output = new stdClass();
        $output -> $type = $output_args;
        echo json_encode( $output );
    }
}
?>
