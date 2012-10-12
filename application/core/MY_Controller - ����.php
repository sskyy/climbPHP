<?php

class MY_Controller extends CI_Controller{
    /*
     * expect 
     * array(
     *  'module_A_rule' => array(
     *      validate => array(
     *          'function_a',
     *          'function_b
     *      ),
     *      ignore => array(
     *          'module_B_rule'
     *      ),
     *      module => 'module_A';
     *  ),
     *  'module_B_rule' => array(
     *      validate => array(
     *          'function_c',
     *          'function_d',
     *      ),
     *      module => 'module_B',
     *      ignored_by => array(
     *          array('module_A_rule', 'module_A')
     *      ),
     *  )
     * )
     */

    private $module_info = array();
    private $unimplemented_access_rules = array( );
    protected $access_rules = array();
    private $access_validate_info = array();

    public function __construct(){
        parent::__construct();

        $this -> load -> library( "Event" );
        $this -> event -> bind( "module loaded", array( $this, "_register_module" ) );

        $modules = array( 'log_model','test_model' );
        $this -> _load_modules( $modules );

        $this -> event -> trigger( "system_init" );
    }

    private function _load_modules( $modules ){
        foreach( $modules as $module_name ){
            $this -> load -> model( $module_name );
            $this -> _init_module( $module_name );
        }
    }
    
    public function _register_module( $module_name, $module_info ){
        $this -> module_info[strtolower( $module_name )] = $module_info;
    }

    public function _init_module( $module_name ){
        if( !isset( $this -> module_info[$module_name] ) ){
            return;
        }
        $module_info = $this -> module_info[$module_name];
        $this -> _set_module_event_handler( $module_name );
        $this -> _bind_module_listen( $module_name, $module_info['listen_events'] );
        $this -> _merge_module_access_rules( $module_name, $module_info['access_rules'] );
    }

    private function _set_module_event_handler( $module_name ){
        if( method_exists( $this -> $module_name, 'set_event_handler' ) ){
            $this -> $module_name -> set_event_handler( $this -> event );
        }
    }

    private function _bind_module_listen( $module_name, $listen_events ){
        $bindings = array( );
        foreach( $listen_events as $event_name => $callback ){
            $bindings[$event_name] = array( $this -> $module_name, $callback );
        }
        $this -> event -> bind_multi( $bindings );
    }

    private function _merge_module_access_rules( $module_name, $access_rules ){
        $current_rules = array();
        $RTR =& load_class('Router', 'core');
        $current_route = $RTR->fetch_class()."/".$RTR->fetch_method();
        if( isset($access_rules[$current_route])){
            $current_rules = $access_rules[$current_route];
        }
        
        foreach( $current_rules as $rule_name => $access_rule ){
            $rule = $access_rule;
            $rule['module'] = $module_name;

            if( isset( $rule['ignore'] ) ){
                $this -> _ignore_rules( $rule['ignore'], $rule_name, $module_name );
            }

            $rule = $this -> _set_rule_ignorence( $rule_name, $rule );

            $this -> access_rules[$rule_name] = $rule;
        }
    }

    private function _ignore_rules( $rules_to_be_ignore, $rule_name, $module_name ){
        if( !is_array( $rules_to_be_ignore ) ){
            $rules_to_be_ignore = array( $rules_to_be_ignore );
        }

        foreach( $rules_to_be_ignore as $rule_to_be_ignore ){
            if( isset( $this -> access_rules[$rule_to_be_ignore] ) ){
                if( !isset( $this -> access_rules[$rule_to_be_ignore]['ignored_by'] ) ){
                    $this ->  access_rules[$rule_to_be_ignore]['ignored_by'] = array( );
                }
                $this ->  access_rules[$rule_to_be_ignore]['ignored_by'][] = array( $rule_name, $module_name );
            }else{
                if( !isset( $this -> unimplemented_access_rules[$rule_to_be_ignore] ) ){
                    $this -> unimplemented_access_rules[$rule_to_be_ignore] = array( );
                }
                $this -> unimplemented_access_rules[$rule_to_be_ignore][] = array( $rule_name, $module_name );
            }
        }
    }

    private function _set_rule_ignorence( $rule_name, $rule ){
        if( isset( $this -> unimplemented_access_rules[$rule_name] ) ){
            if( !isset( $rule['ignored_by'] ) ){
                $rule['ignored_by'] = array( );
            }
            $rule['ignored_by'] = $this -> unimplemented_access_rules[$rule_name];
            unset( $this -> unimplemented_access_rules[$rule_name] );
        }

        return $rule;
    }

    public function access_validate(){
        foreach( $this -> access_rules as $rule_name => $rule ){
            if( isset( $rule['ignored_by'])){
                foreach( $rule['ignored_by'] as $ignored_info ){
                    $this -> access_validate_info[] = $rule_name . " ignored by " . $ignored_info[0];
                }
                continue;
            }
            
            if( !is_array( $rule['validate'] ) ){
                $rule['validate'] = array( $rule['validate'] );
            }
            foreach( $rule['validate'] as $validate_funs ){
                if( method_exists( $this -> $rule['module'], $validate_funs ) ){
                    $this -> access_validate_info[] = $rule_name . " validating ";
                    $result = $this -> $rule['module'] -> $validate_funs();
                    if( !$result ){
                        $this -> access_validate_info[] = $rule_name . " validate failed ";
                        return $result;
                    }
                }else{
                    $this -> access_validate_info[] = "{$rule_name} {$validate_funs} not exist";
                }
            }
            return true;
        }
    }
    
    public function get_access_validate_info(){
        return $this -> access_validate_info;
    }

}

?>
