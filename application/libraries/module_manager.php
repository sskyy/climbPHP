<?php
/**
 * if you want to 
 *
 * @author jason
 */
class module_manager extends Module{
    //you can add modules need to be load automatically here.
    protected $auto_load = array();
    
    protected $loaded_modules = array( );
    
    public function __construct( &$params ){
        parent::__construct();
        $this -> loaded_modules['require'] = $params['require'];
        $this -> _auto_load();
    }

    private function _auto_load(){
        foreach( $this -> auto_load as $module_name ){
            $this -> load( $module_name );
        }
    }

    //for dynamicaly load use.
    public function &load( $module_name, $auto_register = TRUE ){
        if( ! $this -> get("require") ){
            return false;
        }

        if( $this -> is_loaded( $module_name ) ){
            $module = $this -> get($module_name);
        }else{
            $module = $this -> get("require") -> module( $module_name );
        }
        
        if( method_exists( $module, "declare_dependence" ) ){
            $this -> _load_dependence( $module -> declare_dependence(), $module );
        }
        
        
        if( $auto_register ){
            $this -> register( $module_name, $module );
        }

        return $module;
    }
    
    public function is_loaded( $module_name ){
        return $this -> get( $module_name );
    }

    private function _load_dependence( $depend_module_names, $current_module ){
        foreach( $depend_module_names as $depend_module_name ){
            $depend_module = &$this -> load( $depend_module_name );

            if( method_exists( $depend_module,"on_upper_module_load" )){
                $depend_module ->  on_upper_module_load( $current_module );
            }
        }
    }

    public function register( $module_name, $module_ins ){
        if( $this -> get($module_name ) ){
            return $this -> get($module_name) ;
        }
        $this -> loaded_modules[$module_name] = $module_ins;
        if( method_exists( $module_ins,"on_module_register" )){
            $module_ins ->  on_module_register( $this );
        }

        return $module_ins;
    }
    
    public function get( $module_name ){
        if( isset( $this -> loaded_modules[$module_name] ) ){
            return $this -> loaded_modules[$module_name];
        }
            return false;
    }
    
}

?>