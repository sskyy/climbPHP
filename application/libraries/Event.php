<?php

/**
 * @author 侯振宇
 * @date 2012-6-25
 * @encode UTF-8
 */
class Event{
    /*
    used to store binded event;
    example event string : event_name1.namespace2.namespace1;
    example structure:
    array(
    );
    */
    private $namespaceSpliter = ".";
    private $variableSign = ":";
    private $eventNameParamSpliter = "/";
    private $eventArray = array();
    private $muteExpression = "(!{.*?})";
    private $targetExpression = "(@{.*?})";

    /*
    pregEventArray structure:
    array(
        ".*?" => array(
            ".*?" => array(
                "user" => array(
                    "#" => array( $this, $method, $namespace)
                )
            ),
            "id" => array(
                "#" => array(
                    array( $this, $method, $namespace ),
                    array( $this, $method, $namespace ),
                    array( $this, $method, $namespace ),
                )
            )
        ),
        "user" => array(
            ".*?" => array(
                "#" => array(
                    array($this, $method, $namespace),
                    array($this, $method, $namespace),
                    array($this, $method, $namespace),
                )
            )
        )
    )
    */
    private $pregEventArray = array();

    public function __construct(){
        
    }

    //callback stucture: array(module_name, callback_method)
    public function bind( $eventNameInput, $callback, $actionOrder = false ){
        $eventNameDetail = $this -> standardize_event_name( $eventNameInput );
        $callbackDetail = $this -> standardize_callback( $eventNameDetail, $callback, $actionOrder );
        $this -> persist( $eventNameDetail, $callbackDetail );
    }

    public function bind_multi( &$bindings ){
        foreach( $bindings as $event_name => $module_callback ){
            $this -> bind( $event_name, $module_callback );
        }
    }

    private function standardize_event_name( $eventNameInput ){
        $output = array(
            'eventName' => '',
            'namespace' => '',
            "isExpression" => false,
            "target" => array(),
            "mute" => array()
        );

        $targetMatches = array();
        if( preg_match_all( "/{$this -> targetExpression}/", $eventNameInput, $targetMatches) != 0){
            // unset( $targetMatches[0]);
            foreach( $targetMatches[0] as $match ){
                $match = preg_replace("/[@{}]/", "", $match);
                $output['target'][] = $match;
            }
            $eventNameInput = preg_replace( "/{$this -> targetExpression}/","", $eventNameInput);
        }
        $muteMatches = array();
        if( preg_match_all( "/{$this -> muteExpression}/", $eventNameInput, $muteMatches) != 0){
            unset( $muteMatches[0]);
            foreach( $muteMatches as $match ){
                $match = preg_replace("/[!{}]/", "", $match);
                $output['mute'][] = $match;
            }
        }

        $splitIndex = strpos( $eventNameInput, $this -> namespaceSpliter );
        if( $splitIndex === false ){
            $output['eventName'] =  $eventNameInput ;
        }else{
            $output['eventName'] =  substr( $eventNameInput, 0 , $splitIndex );
            $output['namespace'] = substr( $eventNameInput, $splitIndex );
        } 

        if( $this -> is_expression( $output['eventName']) ){
            $output['isExpression'] = true;
        }

        return $output;
    }

    /*
     * input callback structure:
        type1 :
            array(  
                "object" => $object, 
                "method"=> $method ,
                "namespace" => $namespace,
                "order" => $order
            )
    */
    public function standardize_callback( $eventNameDetail, $callback, $actionOrder = false ){
        $callbackDetail = array();

        $callbackDetail['object'] = $callback[0];
        $callbackDetail['method'] = $callback[1];
        $callbackDetail['name'] = isset( $callback[2])? $callback[2] : get_class($callback[0]);
        if( isset( $eventNameDetail['namespace'] ) ){
            $callbackDetail['namespace'] = $eventNameDetail['namespace'];
        }
        $standardOrder = array(
            'first' => false,
            'last' => false,
            'before' => array(),
            'after' => array()
        );

        if( $actionOrder =='first'){
            $standardOrder['first'] = true;
        }elseif( $actionOrder =='last'){
            $standardOrder['last'] = true;
        }elseif( isset($actionOrder['before'])){
            $standardOrder['before'] = !is_array( $actionOrder['before']) ? array( $actionOrder['before']) : $actionOrder['before'];
        }elseif( isset($actionOrder['after'])){
            $standardOrder['after'] = !is_array( $actionOrder['after']) ? array( $actionOrder['after']) : $actionOrder['after'];
        }
        
        $callbackDetail['order'] = $standardOrder;

        return $callbackDetail;
    }

    private function persist( $eventNameDetail, $callbackDetail ){
        $eventName = $eventNameDetail['eventName'];

        if( !$eventNameDetail['isExpression'] ){
            if( !isset( $this -> eventArray[$eventName])){
                $this -> eventArray[$eventName] = array();
            }

            $this -> eventArray[$eventName][] = $callbackDetail;
        }else{
            $eventParam = explode( $this -> eventNameParamSpliter, $eventName );
            for( $level = 0; $level < count( $eventParam ); $level++ ){
                $currentLevel =& $this -> get_preg_event_array_ref( $level, $eventParam[$level] ); 

                if( $level == count( $eventParam) - 1 ){
                    if( !isset( $arrayRef["#"])){
                        $currentLevel["#"] = array();
                    }
                    $currentLevel["#"][] = $callbackDetail;
                }

            }
        }
    }

    private function eveluate_expression( $expression ){
        if( !$this -> is_expression($expression)){
            return $expression;
        }

        $eveluated = preg_replace("/{.*?}/", "(.*?)", $expression);
        $eveluated = preg_replace( "/:.*?$/", "(.*?)", $expression);
        return $eveluated;
    }

    private $lastRef;

    private function &get_preg_event_array_ref( $level, $expression ){
        //函数内部静态成员不能保存对外部的引用；所以需要借助一个类成员
        
        if( $level == 0){
            $this -> lastRef =& $this -> pregEventArray;
        }

        $currentParam = $this -> eveluate_expression( $expression);
        if( !isset( $this -> lastRef[$currentParam])){
            $this -> lastRef[$currentParam] = array();
            if( $this -> is_expression( $expression )){
                $this -> lastRef[$currentParam]['#is_expression'] = true;
            } 
        }

        $this -> lastRef =& $this -> lastRef[$currentParam];
        
        return $this -> lastRef;
    }

    //use !{event_name} to mute specified event
    //user @{bind_name, method_name} to specify certain handler
    //event name have 3 types: 
    //   1.  trigger without namespace
    //   2.  trigger only namespace
    //   3.  tirgger with namespace
    public function trigger(){
        static $muteArray =array();

        $args = func_get_args();
        $eventNameInput = $args[0];
        $eventNameDetail = $this -> standardize_event_name( $eventNameInput );

        if( in_array( $eventNameDetail['eventName'], $muteArray)){
            return new eventRes( false, "{$eventNameDetail['eventName']} is muted");
        }else{
            foreach( $muteArray as $muteEvent ){
                if( $this -> is_namespace( $muteEvent) && $this -> namespace_match( $eventNameDetail['namespace'], $muteEvent)){
                    return new eventRes( false, "{$eventNameDetail['eventName']} is muted");
                }
            }
        }


        if( !empty( $eventNameDetail['mute'])){
            $muteArray = array_merge( $muteArray, $eventNameDetail["mute"]);
            $originalMuteArray = $eventNameDetail['mute'];
            $eventNameDetail['mute'] = $muteArray;
        }

        $callbackArgs = array_slice( $args, 1 );

        $callbacks = array_merge( $this -> get_callback_from_event_array( $eventNameDetail ),
            $this -> get_callback_from_preg_event_array( $eventNameDetail )
        );

        if( !empty( $eventNameDetail['target'] )){
            foreach( $callbacks as $index => $callback ){
                $callbackId = $this -> _gen_callback_id( $callback );
                if( !in_array($callbackId, $eventNameDetail['target'])){
                    unset($callbacks[$index]);
                }
            }
        }

        $callbacks = $this -> make_order( $callbacks );

        $results = array();
        foreach( $callbacks as $callback ){
            $mergedArgs = $callbackArgs;

            if( !empty( $callback["paramMatches"])){
                $mergedArgs = array_merge( $callback["paramMatches"], $callbackArgs );
            }
            $results[] = res_wrap( call_user_func_array( array($callback["object"] ,$callback["method"]), $mergedArgs ),
                array("event" => $eventNameDetail, "arguments" => $callbackArgs, "operator" => array( $callback["name"], $callback["method"]))
            );
        }

        if( isset($originalMuteArray)){
            $muteArray = array_diff( $muteArray, array_intersect( $muteArray, $originalMuteArray ) );
        }

        return $results;
    }

    public function &make_order( &$callbacks ){
        $callbacks = $this -> _add_callbacks_index( $callbacks );

        $output = array();
        $output_first = array();
        $output_last = array();
        $output_middle = array();

        foreach( $callbacks as $thisCallbackId => $thisCallback ){
            /*
            处理所有的last和first
            */
            if( $thisCallback['order']["first"] ){
                $output_first[] = $thisCallback;
                unset( $callbacks[$thisCallbackId ]);
                continue;
            }elseif( $thisCallback['order']["last"]){
                $output_last[] = $thisCallback;
                unset( $callbacks[$thisCallbackId ]);
                continue;
            }else{
                foreach( $thisCallback['order']["before"] as $beforeId ){
                    if( isset( $callbacks[$beforeId])){
                        $callbacks[$beforeId]['order']['after'][] = $thisCallbackId;
                    }
                }
            }
        }

        $working_middle_stack = array();
        while( $callback = array_shift( $callbacks )){
            $working_middle_stack[] = array( $callback, false );
            while( !empty($working_middle_stack) ){
                list($last, $isHandled) = array_pop( $working_middle_stack );
                if( !$isHandled ){
                    array_push( $working_middle_stack, array( $last, true ) );
                    if( !empty( $last['order']['after'])){
                        foreach( $last['order']['after'] as $afterId ){
                            if( isset( $callbacks[$afterId])){
                                $working_middle_stack[] = array( $callbacks[$afterId], false );
                                unset( $callbacks[$afterId]);
                            }
                        }
                    }
                }else{
                    array_push( $output_middle, $last );
                }

            }
        }

        $output = array_merge( $output_first, $output_middle, $output_last );

        return $output;
    }

    private function _caculate_order( $order, $stack ){

    }

    private function _add_callbacks_index( $callbacks ){
        $output = array();
        foreach( $callbacks as $callback ){
            $output[$this -> _gen_callback_id( $callback)] = $callback;
        }
        return $output;
    }

    private function _gen_callback_id( $callback ){
        return $callback['name']."->".$callback['method'];
    }

    private function _gen_callback_id_with_raw( $array ){
        return $array[0]."->".$array[1];
    }


    public function get_callback_from_event_array( $eventNameDetail ){
        $callbacks = array();
        $eventName = $eventNameDetail['eventName'];
        $namespace = $eventNameDetail['namespace'];
        if( $eventName !== '' ){
            if( !isset( $this -> eventArray[$eventName] ) ){
                return array();
            }

            if( $namespace == '' ){
                //condition 1: no namespace
                $callbacks = $this -> eventArray[$eventName];
            }else{
                //condition 2
                foreach( $this -> eventArray[$eventName] as $item ){
                    if( $this -> callback_namespace_match( $namespace, $item) ){
                        $callbacks[] = $item;
                    }
                }
            }
        }else{
            //condition 3
            foreach( $this -> eventArray as $event ){
                foreach( $event as $item ){
                    if( $this -> callback_namespace_match( $namespace, $item) ){
                        $callbacks[] = $item;
                    }
                }
            }
        }

        return $callbacks;
    }

    public function callback_namespace_match( $namespace, $callback ){
        return $this -> namespace_match( $namespace, $callback["namespace"] ) == 0 ? false : true;
    }

    public function namespace_match( $text, $namespace){
        if( $namespace == "" ){
            return false;
        }

        return preg_match( "/{$text}$/", $namespace ) == 0 ? false : true;
    }

    public function preg_event_name_param_match( $param, $expression, &$matches = false ){
        $result = preg_match("/^{$expression}$/", $param, $matches) == 0 ? false : true;
        if($matches !== false ){
            unset( $matches[0] );
        }
        return $result;
    }

    public function get_callback_from_preg_event_array( $eventNameDetail ){

        $callbacks = array();
        $eventName = $eventNameDetail['eventName'];
        $namespace = $eventNameDetail['namespace'];
        $eventNameParam = $this -> explode_event_name($eventName);

        if( !empty( $eventNameParam) ){
            $stack = array( array( "array" => $this -> pregEventArray, "param" => $eventNameParam[0], "paramMatches" => array()));
            while( !empty( $stack)) {
                $current = array_pop( $stack );
                if( $current["param"] == "#" && isset( $current["array"]["#"])){
                    foreach( $current["array"]["#"] as $callback ){
                        if( $this -> callback_namespace_match($namespace, $callback) ){
                            $callback["paramMatches"] = $current["paramMatches"];
                            $callbacks[] = $callback;
                        }
                    }
                }else{
                    foreach( $current["array"] as $expression => $next_level_array ){
                        if( !is_array( $next_level_array )){
                            continue;
                        }

                        $matches = array();
                        if( $this -> preg_event_name_param_match( $current["param"], $expression, $matches)){
                            $next_level_param = isset( $eventNameParam[ array_search($current["param"], $eventNameParam) + 1 ] ) ?
                                $eventNameParam[ array_search($current["param"], $eventNameParam) + 1 ] : "#";

                            if( !empty( $next_level_array['#is_expression']) ){
                                $matches = array_merge( $current['paramMatches'], $matches);
                            }
                            array_push($stack, array( "array" => $next_level_array, "param" => $next_level_param , "paramMatches" => $matches));
                        }
                    }
                }

            }
        }else{
            //condition 3
            $stack = array( array( "array" => $this -> pregEventArray, "paramMatches" => array()));
            while( !empty( $stack ) ){
                $current = array_pop( $stack );
                if( isset( $current["array"]['#'] ) ){
                    foreach( $current["array"]['#'] as $callback ){
                        if( $this -> callback_namespace_match( $namespace, $callback )){
                            $callback[] = $current["paramMatches"];
                            $callbacks[] = $callback;
                        }
                    }
                    unset( $current['#']);
                }
                foreach( $current["array"] as $next_level_array ){
                    if( !is_array( $next_level_array)){
                        continue;
                    }

                    $match = $current["paramMatches"];
                    if( isset( $next_level_array['#is_expression']) && $next_level_array['#is_expression']){
                        $match[] = null;
                    }

                    array_push( $stack, array("array"=> $next_level_array, "paramMatches" => $match ));
                }
            }
        }

        return $callbacks;
    }

    public function trigger_async(){
        $args = func_get_args();
        return call_user_func_array(  array($this ,'trigger'), $args );
    }

    public function list_binds(){
        return array( $this -> eventArray, $this -> pregEventArray );
    }

    public function is_expression( $eventName ){
        $signIndex = strpos( $eventName, $this -> variableSign );
        if( $signIndex === false ){
            return false;
        }

        return true;
    }

    public function is_namespace( $str ){
        return $str[0] == "." ? true : false;
    }

    public function explode_event_name( $eventName ){
        $result = explode( $this -> eventNameParamSpliter, $eventName);
        if( count( $result) == 1 && empty( $result[0] )){
            $result = array();
        }
        return $result;
    }

}


class eventRes{
    private $result;
    private $data;
    private $link;
    private $info;
    public function __construct( $arg1, $arg2 = true){
        $this -> initialize( $arg1, $arg2);
    }

    public function initialize( $arg1, $arg2 ){
        if( is_bool( $arg1 )){
            $this -> result = $arg1;
        }else{
            $this -> data = $arg1;
            $this -> result = $arg2;
        }
    }

    public function set_data( $data ){
        $this -> data = $data;
    }

    public function get_data(){
        return $this -> data;
    }

    public function get( $target ){
        if( isset( $this -> $target )){
            return $target;
        }
        return false;
    }

    public function set_link( $link ){
        $this -> link = $link;
    }

    public function set_info( $info ){
        $this -> info = $info;
    }

}

//functions

function res_wrap( $result, $info = false ){
    $result = false;
    if( res_check_type( $result)){
        return $result;
    }

    $result = new eventRes( $result );

    if( $info !==false ){
        $result -> set_info( $info );
    }

    return $result;
}

function res_check_type( $data ){
    if( is_array( $data)){
        foreach( $data as $item ){
            if( !is_a( $item, "eventRes")){
                return false;
            }
        }
        return true;
    }

    if( is_a( $data, "eventRes")){
        return true;
    }

    return false;
}


function res_target( $eventRes, $targetName){
    return $eventRes -> get($targetName);
}

function res_link( $mainRes, $responseRes ){
    $mainRes = res_wrap( $mainRes );
    $responseRes =  res_wrap( $responseRes );

    $mainRes -> set_link( $responseRes );
    return $mainRes;
}

function res_bool( $eventRes){
    if( !res_check_type( $eventRes)){
        return false;
    }

    if( is_array( $eventRes)){
        foreach( $eventRes as $item ){
            if( !$item -> get("result")){
                return false;
            }
        }
        return true;
    }


    if( is_a( $eventRes, "eventRes")){
        return $eventRes -> get("result");
    }

    return false;

}


























?>
