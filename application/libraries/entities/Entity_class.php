<?php

/**
 * @author 娓氼垱灏熺�锟�* @date 2012-6-28
 * @encode UTF-8
 */
class Entity_class{

    public function __construct(){
        
    }

}

/**
 *  
 */
class Entity{

    private $attributes = array( );
    private $is_deleted = false;
    private $identifier = false;
    private $db_handler = null;
    private $identity = null;
    private $auto_load = false;
    private $with_related_entities = array( );

    public static $related_entities = array();

    public function __construct( $identity_or_data = array( ), $params = array( ) ){

        foreach( $params as $param_name => $param ){
            $this -> $param_name = $param;
        }

        if( $this -> is_identity( $identity_or_data ) ){
            $this -> identity = $identity_or_data;
        }else{
            $this -> set( $identity_or_data );
        }
    }

    public function is_identity( $identity_or_data ){
        return !is_array( $identity_or_data ) && !is_object( $identity_or_data );
    }

    public function load( $identity ){
        if( !$this -> db_handler ){
            return false;
        }

        $loaded_data = $this -> db_handler -> load( $identity, $this -> with_related_entities );
        if( !$loaded_data ){
            return false;
        }else{
            return $this -> set( $loaded_data );
        }
    }

    public function reload( $identity ){
        $this -> chunk();
        $this -> load( $identity );
    }

    public function set_identifier( $identifier ){
        $this -> identifier = $identifier;
    }

    public function get_identifier(){
        return $this -> identifier;
    }

    public function get_identity(){
        return $this -> identity;
    }

    public function reset( $data ){
        $this -> chunk();
        return $this -> set( $data );
    }

    public function get( $attr_name ){
        $data = isset( $this -> attributes[$attr_name] ) ? $this -> attributes[$attr_name] -> value : false;
        if( in_array( $attr_name, $this -> with_related_entities ) 
                && in_array( $attr_name, self::$related_entities) ){
            
            $class_name = self::$related_entities[$attr_name ];
            
            if( class_exists( $class_name ) ){
                return new $class_name( $data );
            }else{
                return new Entity( $data );
            }
        }
        
        return $data;
    }

    public function get_attr_detail( $attr_name ){
        return isset( $this -> attributes[$attr_name] ) ? $this -> attributes[$attr_name] : false;
    }

    public function set(){
        if( $this -> is_deleted ){
            return false;
        }

        $args = func_get_args();
        if( count( $args ) == 1 ){
            $attr_array = (array) $args[0];
        }else if( count( $args ) == 2 ){
            $attr_array = array( $args[0] => $args[1] );
        }

        $this -> _set_to_standard_attr( $attr_array );

        return $this;
    }

    private function _set_to_standard_attr( $attr_array ){
        foreach( $attr_array as $key => $value ){
            $value_obj = new stdClass();
            $value_obj -> changed = false;
            $value_obj -> new = true;

            if( isset( $this -> attributes[$key] ) ){
                $value_obj -> new = false;
                if( $this -> attributes[$key] -> value !== $value ){
                    $value_obj -> changed = true;
                    $value_obj -> last = $this -> attributes[$key] -> value;
                }
            }
            $value_obj -> value = $value;

            $this -> attributes[$key] = $value_obj;
        }
    }

    public function get_changed_attr(){
        $changed_attr_obj = new stdClass();
        foreach( $this -> attributes as $key => $attribute ){
            if( $attribute -> changed ){
                $changed_attr_obj -> $key = $attribute;
            }
        }
        return $changed_attr_obj;
    }

    public function chunk(){
        $this -> attributes = array( );
        return $this;
    }

    public function save(){
        if( !$this -> db_handler ){
//            echo "return false!";
//            print_r( $this -> to_object( ) );
            return false;
        }

        if( !$this -> get_identity() ){
//            echo "no identity";
            $data_to_save = $this -> to_object();
        }else{
            $data_to_save = new stdClass();
            foreach( $this -> attributes as $key => $attribute ){
                if( $attribute -> changed || $attribute -> new ){
                    $data_to_save -> $key = $attribute -> value;
                }
            }
            if( $this -> get( $this -> identifier ) ){
                $data_to_save -> {$this -> identifier} = $this -> get( $this -> identifier );
            }
        }
        
//        print_r( $data_to_save );
        $result = $this -> db_handler -> save( $data_to_save );
        return $this -> refresh_saved_entity( $result );
    }

    private function refresh_saved_entity( $save_result_or_identity ){
        if( $save_result_or_identity == false ){
            return false;
        }

        if( !$this -> get_identity() ){
            $this -> reload( $save_result_or_identity );
        }
        return $this;
    }

    public function delete(){
        if( !$this -> db_handler ){
            return false;
        }
        $this -> db_handler -> delete( $this );
        $this -> deleted = true;
    }

    public function to_object(){
        $object = new stdClass();
        foreach( $this -> attributes as $key => $value_obj ){
            $object -> $key = $value_obj -> value;
        }
        return $object;
    }

    public function to_array(){
        return (array) $this -> to_object();
    }

    public function is_empty(){
        return empty( $this -> attributes );
    }

    public function is_delete(){
        return $this -> deleted;
    }

    public function set_db_handler( $db_handler ){
        $this -> db_handler = $db_handler;
        if( !$this -> identifier && method_exists( $this -> db_handler, "get_identifier" ) ){
            $this -> set_identifier( $this -> db_handler -> get_identifier() );
        }
        
        if( isset( $this -> attributes[$this -> identifier])){
            $this -> identity = $this -> attributes[$this -> identifier];
        }

        if( $this -> auto_load ){
            if( $this -> identity !== null && empty( $this -> attributes ) ){
                $this -> load( $this -> identity );
            }
        }
    }

}

/**
 * TODO " add method 'filter', 'find', 'set', 'save', 'delete', 'add'
 */
class Entity_collection implements IteratorAggregate{

    public $length = 0;
    public $entity_name = 'Entity';
    public $children = array( );
    public $params = array(
        'auto_load' => false,
        'children' => array( ),
        'meta' => array( ),
        'with_related_entities' => array(),
        'params' => false,
    );
    private $db_handler = false;
    private $entity_db_handler = false;

    public function __construct( $entity_name, $params ){

        $this -> entity_name = $entity_name;
        $this -> params = array_merge( $this -> params, $params );
        $this -> children = $this -> params['children'];
    }

//    public function filter( $filters, $reference = false ){
//        $filtered_data = array( );
//        if( $reference ){
//            foreach( $this -> children as &$child ){
//                if( $this -> _is_model_filtered( $filters, $children ) ){
//                    $filtered_data[] = $child;
//                }
//            }
//        }else{
//            foreach( $this -> children as $child ){
//                if( $this -> _is_model_filtered( $filters, $children ) ){
//                    $filtered_data[] = $child;
//                }
//            }
//        }
//
//        return $filtered_data;
//    }
//    private function _is_model_filtered( $filters, $model ){
//        foreach( $filters as $filter_key => $filter_value ){
//            if( $model -> get( $filter_key ) != $filter_value ){
//                return false;
//                break;
//            }
//        }
//        return true;
//    }

    public function to_array(){
        $output = $this -> children;

        foreach( $output as &$child ){
            if( is_a( $child, "Entity" ) ){
                $child = $child -> to_object();
            }
        }
        return $output;
    }

    public function reset( &$children ){
        $this -> children = $children;
        $this -> length = count( $children );
    }

//    public function reset_from_data( $children_data ){
//        foreach( $children_data as $child_data ){
//            $class_name = $this -> entity_name;
//            $children_obj = new $class_name( $child_data );
//            if( !$children_obj -> is_empty() ){
//                $this -> children[] = $children_obj;
//            }
//        }
//
//        $this -> length = count( $this -> children );
//    }

    public function get( $id ){
        if( isset( $this -> children[$id] ) && is_a( $this -> children[$id], $this -> entity_name ) ){
            return $this -> children[$id];
        }else{
            if( class_exists( $this -> entity_name ) ){
                $entity_name = $this -> entity_name;
                $entity = new $entity_name( $id );
            }else{
                $entity = new Entity( $id, array( "auto_load" => TRUE ) );
            }
            $entity -> set_db_handler( $this -> entity_db_handler );
            return $entity;
        }

        return false;
    }

    public function set_db_handler( $collection_db_handler, $entity_db_handler ){
        $this -> db_handler = $collection_db_handler;
        $this -> entity_db_handler = $entity_db_handler;
        if( $this -> params['auto_load'] !== false ){
            $this -> load( $this -> params['meta'], $this ->params['with_related_entities']);
        }
    }

    public function load(){
        $data = $this -> db_handler -> load( $this -> params['meta'], $this -> params['with_related_entities'], $this -> params['params'] );
        if( $data ){
            $data = is_array( $data ) ? $data : array( $data );
            $this -> reset( $data );
            return true;
        }
        return false;
    }

    public function save(){
                if( isset( $this -> children[$id] ) && is_a( $this -> children[$id], $this -> entity_name ) ){
            return $this -> children[$id];
        }else{
            if( class_exists( $this -> entity_name ) ){
                $entity_name = $this -> entity_name;
                $entity = new $entity_name( $id );
            }else{
                $entity = new Entity( $id, array( "auto_load" => TRUE ) );
            }
            $entity -> set_db_handler( $this -> entity_db_handler );
            return $entity;
        }
    }

    public function delete(){
        
    }
    
    public function pop(){
        $children = $this -> children;
        $data = array_pop( $children );
        if( class_exists( $this -> entity_name ) ){
            $entity_name = $this -> entity_name;
            $entity = new $entity_name( $data );
        }else{
            $entity = new Entity( $data );
        }
        $entity -> set_db_handler( $this -> entity_db_handler );
        return $entity;
    }

    public function getIterator(){
        return new ArrayIterator( $this -> children );
    }

}

?>
