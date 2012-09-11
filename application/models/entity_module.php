<?php

/**
 *
 * @author jason
 */
class entity_module extends Module{

    private $event;
    private $library_path;
    private $entity_class = 'Entity_class';
    private $entity_db_handler_path;
    private $default_entity_name = 'Default';
    private $entity_db_handler_name_map = array(
        "/(.*)/", '$1_db'
    );

    //if you moved this file , you need to check the path below.
    public function __construct(){
        parent::__construct();
        $this -> library_path = APPPATH . "libraries/";
        $this -> entity_path = APPPATH . "libraries/entities/";
        $this -> entity_db_handler_path = APPPATH . "libraries/entities/db_handler/";
        require_once $this -> entity_path . $this -> entity_class . ".php";
    }

    public function declare_dependence(){
        return array( 'event_module', 'database_module' );
    }

    public function set_event_handler( &$event ){
        $this -> event = $event;
    }

    private function invoke_entity_class( $entity_name ){
        if( !class_exists( $entity_name ) ){
            $file_path = $this -> entity_path . $entity_name . ".php";
            if( !file_exists( $file_path ) ){
                return false;
            }

            require_once $file_path;

            if( !class_exists( $entity_name ) ){
                return false;
            }
        }
        return true;
    }

    public function create_entity( $entity_name, $identity_or_data = array( ), $params = array( ) ){
        $default_params = array( "auto_load" => TRUE, "with_related_entities" => array( ) );
        $params = array_merge( $default_params, $params );
        $invoke_result = $this -> invoke_entity_class( $entity_name );
        if( $invoke_result ){
            if( !empty( $params['with_related_entities'] ) ){
                $this -> invoke_related_entity_classes( $entity_name, $params['with_related_entities'] );
            }
            $entity = new $entity_name( $identity_or_data, $params );
        }else{
            $entity = new Entity( $identity_or_data, $params );
        }
        $entity -> set_db_handler( $this -> get_entity_db_handler( $entity_name ) );
        return $entity;
    }
    
    public function search_entity( $entity_name, $meta ){
        $search_result_collection = $this -> create_collection($entity_name, array("meta"=>$meta));
        if( $search_result_collection -> length > 0 ){
            $entity = $search_result_collection -> pop();
            return $entity;
        }
    }

    public function invoke_related_entity_classes( $entity_name, $with_related_entities ){
        $related_entities = $entity_name::$related_entities;
        foreach( $related_entities as $entity_name => $entity_type ){
            if( is_array( $with_related_entities ) && !in_array( $entity_name, $with_related_entities ) ){
                continue;
            }
            $this -> invoke_entity_class( $entity_type );
        }
    }

    public function create_collection( $entity_name, $params = array() ){
        $this -> invoke_entity_class( $entity_name );
        $collection_name = $this -> map_entity_name_to_collection( $entity_name );
        
        $default_params = array("auto_load" => TRUE);
        $params = array_merge( $default_params, $params);
        if( class_exists( $collection_name ) ){
            $collection = new $collection_name( $entity_name, $params );
        }else{
            $collection = new Entity_collection( $entity_name, $params );
        }

        $collection_db_handler = $this -> get_collection_db_handler( $entity_name );
        $entity_db_handler = $this -> get_entity_db_handler( $entity_name );
        $collection -> set_db_handler( $collection_db_handler, $entity_db_handler );
        return $collection;
    }

    private function map_entity_name_to_collection( $entity_name ){
        return $entity_name . "_collection";
    }

    private function translate_entity_name_to_db_handler( $entity_name ){
//        return preg_replace( 
//            $this -> entity_db_handler_name_map[0],
//            $this -> entity_db_handler_name_map[1], 
//            $entity_name
//        );
        return $entity_name . "_db_handler";
    }

    private function translate_collection_name_to_db_handler( $entity_name ){
//        return preg_replace( 
//            $this -> entity_db_handler_name_map[0],
//            $this -> entity_db_handler_name_map[1], 
//            $entity_name
//        );
        return $entity_name . "_collection_db_handler";
    }

    private function get_entity_db_handler( $entity_name ){
        $db_handler_name = $this -> translate_entity_name_to_db_handler( $entity_name );
        $db_handler_file = $this -> entity_db_handler_path . $db_handler_name . ".php";
        if( !class_exists( $db_handler_name ) ){
            if( file_exists( $db_handler_file ) ){
                require_once $db_handler_file;
                return new $db_handler_name( $this -> event );
            }else{
                $default_db_handler_name = $this -> translate_entity_name_to_db_handler( $this -> default_entity_name );
                require_once $this -> entity_db_handler_path . $default_db_handler_name . ".php";
                return new $default_db_handler_name( $entity_name, $this -> event );
            }
        }else{
            return new $db_handler_name( $this -> event );
        }
        return false;
    }

    private function get_collection_db_handler( $entity_name ){
        $entity_handler_name = $this -> translate_entity_name_to_db_handler( $entity_name );
        $collection_handler_name = $this -> translate_collection_name_to_db_handler( $entity_name );
        $db_handler_file = $this -> entity_db_handler_path . $entity_handler_name . ".php";
        if( !class_exists( $collection_handler_name ) ){
            if( file_exists( $db_handler_file ) ){
                require_once $db_handler_file;
            }

            if( !class_exists( $collection_handler_name ) ){

                $default_entity_handler_name = $this -> translate_entity_name_to_db_handler( $this -> default_entity_name );
                $default_collection_handler_name = $this -> translate_collection_name_to_db_handler( $this -> default_entity_name );
                require_once $this -> entity_db_handler_path . $default_entity_handler_name . ".php";
                return new $default_collection_handler_name($entity_name, $this -> event);
            }
        }

        return new $collection_handler_name( $this -> event );
    }

}

interface Entity_db_handler_interface{

    public function load( $identity );

    public function save( $entity );

    public function delete( $identity );
}

?>
