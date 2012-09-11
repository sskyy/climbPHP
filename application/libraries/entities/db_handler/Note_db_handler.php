<?php

/**
 * Description of Default_db_handler
 *
 * @author jason
 */
require_once "Default_db_handler.php";
require_once "Db_handler_helpers.php";
class Note_db_handler extends Default_db_handler{
    private $event;
    private $entity_name = "Note";
    public static $related_entities_map = array(
        'book' => "book_id",
        'user' => "user_id",
        'last_param' => 'preview'
    );
    public function __construct(  &$event_handler ){
        parent::__construct($this -> entity_name, $event_handler);
        $this -> event = $event_handler;
        $this -> db_handler_helper = new Db_handler_helpers($event_handler);
    }
    
//    public function load( $identity, $with_related_entities = array() ){
//        $entity = $this -> event -> trigger_async( "db request", "get entity by identity", $this -> entity_name, $identity );
//        $entity = $this -> db_handler_helper -> load_related_entities( $this -> entity_name, $entity, $with_related_entities, self::$related_entities_map);
//        return $entity;
//    }
    
    public function save( $entity ){
        $entity = $this -> db_handler_helper -> map_entity_to_meta( $entity, $this -> entity_name, "Note_db_handler" );
        return $this -> event -> trigger_async( "db request", "save entity", $this -> entity_name, $entity );
    }
}

class Note_collection_db_handler extends Default_collection_db_handler{
    private $event;
    private $entity_name = "Note";
    public function __construct( &$event_handler){
        $this -> event = $event_handler;
        parent::__construct($this -> entity_name, $event_handler, "Note_db_handler"  );
        $this -> db_handler_helper = new Db_handler_helpers($event_handler);
    }
//    
//    public function load( $meta, $with_related_entities = array() ){
//        $meta = $this -> db_handler_helper -> map_entity_to_meta( $meta, $this -> entity_name, "Note_db_handler" );
//        $entities = $this -> event -> trigger_async( "db request", "get entity by meta", $this -> entity_name, $meta );
//        if( !is_array( $entities ) ){
//            $entities = array($entities);
//        }
//        foreach( $entities as &$entity ){
//            $entity = $this -> db_handler_helper -> load_related_entities( $this -> entity_name, $entity, $with_related_entities, Note_db_handler::$related_entities_map);
//        }
//        return $entities;
//    }
    
}


?>
