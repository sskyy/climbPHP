<?php
/**
 * 模块管理器三大职责
 * 1. 构造时自动加载系统模块，加载模块时自动加载依赖
 * 2. 传递模块所需的资源
 * 3. 注册模块监听的事件
 * @author jason
 */
require_once APPPATH."libraries/require.php";
class Manager{
    
    private $req;
    private $event;
    protected $modules = array( );
    
    public function __construct( ){
        $this -> req = new requireModule();
        $this -> _build_event_center();
        $this -> _module_autoinit();
        // print_r( $this -> modules );
    }

    private function _build_event_center(){
        $this -> req -> lib("event");
        $this -> event = new Event();
        $this -> mod("event", $this -> event );
    }

    private function _module_autoinit(){
        $moduleAutoload = $this -> req -> conf("moduleAutoload");
        foreach( $moduleAutoload as $moduleName ){
            $this -> modules[$moduleName] =& $this -> _init_module( $moduleName );
        }

    }


    private function &_init_module( $moduleName ){
        static $infoExtends = array();

        if( $this -> is_loaded( $moduleName) ){
            return $this -> modules[$moduleName];
        }

        include APPPATH."modules/{$moduleName}/info.php";
        require_once APPPATH."modules/{$moduleName}/{$moduleName}.php";

        if( isset( $require ) ){
            if( !array_intersect(array_keys( $require), array("libraries","modules"))){
                $require = array("modules" => $require);
            }

            if( isset( $require["libraries"] ) ){
                foreach( $require['libraries'] as $library ){
                    $this -> req -> lib( $library );
                }
            }

        }

        $moduleClassName = $this -> _trans_module_name( $moduleName );
        $moduleIns = new $moduleClassName( $this );
        $moduleIns -> require = isset( $require ) ? $require : array();
        $moduleIns -> listen = isset( $listen ) ? $listen : array();

        if( isset( $listen ) ){
            foreach( $listen as $eventName => $actionDetail ){
                $actionOrder = false;
                if( is_array( $actionDetail)){
                    $actionName = $actionDetail[0];
                    $actionOrder = $actionDetail[1];
                }else{
                    $actionName = $actionDetail;
                }
                $this -> event -> bind( $eventName, array( $moduleIns, $actionName, $moduleName ), $actionOrder );
            }
        }


        $requireModules = array();
        if( isset($require) && isset( $require['modules'])){
            foreach( $require['modules'] as $requireModuleName ){
                $requireModules[] =& $this -> _init_module( $requireModuleName );
            }
        }

        if( isset( $moduleIns -> listen['system/module_info_extend'])){
            $moduleInfoExtendAction = $moduleIns -> listen['system/module_info_extend'];
            if( method_exists( $moduleIns, $moduleInfoExtendAction )){
                $extends = $moduleIns -> $moduleInfoExtendAction();
                if( !is_array( $extends )){
                    $extends = array( $extends );
                }
                $infoExtends = array_merge( $infoExtends, $extends );
            }
        }

        foreach( $infoExtends as $infoName ){
            if( isset( $$infoName )){
                $moduleIns -> $infoName = $$infoName;
            }
        }


        if( !empty( $requireModules )){
            foreach( $requireModules as &$requireModuleIns ){
                if( isset( $requireModuleIns -> listen["system/upper_module_init"])){
                    $upperModuleInitAction = $requireModuleIns -> listen["system/upper_module_init"];
                    $requireModuleIns -> $upperModuleInitAction( $moduleIns );
                }
            }
        }

        $this -> modules[$moduleName] =& $moduleIns; 
        return $moduleIns;
    }

    private function _trans_module_name( $moduleName ){
        return $moduleName."Module";
    }

    
    public function is_loaded( $moduleName ){
        return $this -> mod( $moduleName ) ? true : false;
    }





    public function mod( $moduleName, &$moduleIns = null ){
        if( isset( $this -> modules[$moduleName] ) && $moduleIns !== null ){
            return false;
        }
        if( !isset($this -> modules[$moduleName]) && $moduleIns ===null ){
            return false;
        }

        if( isset( $this -> modules[$moduleName] ) ){
            return $this -> modules[$moduleName];
        }else{
            $this -> modules[$moduleName] = $moduleIns;
            return $moduleIns;
        }
        return false;
    }

    public function util( $utility ){
        return $this -> req -> util( $utility );
    }

    public function library( $library ){
        return $this -> req -> lib( $library );
    }

    public function model( $model ){
        return $this -> req -> model( $model );
    }

    public function module_get_name( $moduleIns ){
        $moduleName = str_replace( "Module", "", get_class($moduleIns));
        if( $this -> is_loaded($moduleName)){
            return $moduleName;
        }
        return false;
    }
}

?>