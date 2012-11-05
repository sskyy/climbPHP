<?php
/**
 * only this module is dirty!
 *
 * @author sskyy
 */
class requireModule{
    
    private $CI;
    public function __construct() {
        $this -> CI = &get_instance();
    }
    
    public function util( $utilName ){
        if( isset( $this -> CI -> $utilName)){
            return $this -> CI -> $utilName;
        }else{
            if( @$this -> lib( $utilName ) ){
                return $this -> CI -> $utilName;
            }
            if( @$this -> helper( $utilName ) ){
                return $this -> CI -> $utilName;
            }
        }
        return false;
    }

    public function lib( $library ){
        require_once APPPATH."libraries/{$library}.php";
    }

    public function model( $model ){
        return $this -> CI -> load -> model( $model );
    }

    public function conf( $config_item ){
        return $this -> CI -> config -> item( $config_item);
    }
    
}

?>
