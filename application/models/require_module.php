<?php
/**
 * only this module is dirty!
 *
 * @author sskyy
 */
class require_module extends Module{
    
    private $CI;
    private $allowed_method = array("library", "helper", "model", "module");
    public function __construct() {
        parent::__construct();
        $this -> CI = &get_instance();
    }
    
    public function __call( $method_name, $arguments ) {
        $source_name = $arguments[0];
        if( in_array( $method_name, $this -> allowed_method ) ){
            call_user_func_array( array( $this -> CI -> load, $method_name ) , $arguments );
            $ins_name = strtolower( $source_name );
            return $this -> CI -> $ins_name;
        }else{
            return false;
        }
    }
    
    public function utility( $util_name ){
        if( isset( $this ->CI -> $util_name)){
            return $this -> CI -> $util_name;
        }
        return false;
    }
    
    public function class_def( $class_name ){
        require_once APPPATH."libraries/{$class_name}.php";
    }
}

?>
