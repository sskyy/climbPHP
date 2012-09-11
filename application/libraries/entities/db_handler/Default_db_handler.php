<?php

/**
 * Description of Default_db_handler
 *
 * @author jason
 */
require_once "Db_handler_helpers.php";
require_once APPPATH . "libraries/Table.php";

class Default_db_handler implements Entity_db_handler_interface {

    private $event;
    private $entity_name;
    private $db_handler_helper;
    private $child_handler_class = null;

    public function __construct( $entity_name, &$event_handler ) {
        $this -> entity_name = $entity_name;
        $this -> event = $event_handler;
        $this -> db_handler_helper = new Db_handler_helpers( $event_handler );
        $this -> child_handler_class = get_class( $this );
    }

    public function load( $identity, $with_related_entities = array( ) ) {
        if( empty( $with_related_entities ) || !$this -> child_handler_class ) {
            return $this -> event -> trigger_async( "db request", "get entity by identity", $this -> entity_name, $identity );
        }

        $entity = $this -> event -> trigger_async( "db request", "get entity by identity", $this -> entity_name, $identity );
        $children_class = $this -> child_handler_class;
        $entity = $this -> db_handler_helper -> load_related_entities( $this -> entity_name, $entity, $with_related_entities, $children_class::$related_entities_map );
        return $entity;
    }

    public function save( $entity ) {
        if( !$this -> child_handler_class ) {
            return $this -> event -> trigger_async( "db request", "save entity", $this -> entity_name, $entity );
        }

        $children_class = $this -> child_handler_class;
        $entity = $this -> db_handler_helper -> map_entity_to_meta( $entity, $this -> entity_name, $children_class );
        return $this -> event -> trigger_async( "db request", "save entity", $this -> entity_name, $entity );
    }

    public function delete( $identity ) {
        return $this -> event -> trigger_async( "db request", "delete entity by identity", $this -> entity_name, $identity );
    }

    public function get_identifier() {
        return $this -> event -> trigger_async( "db request", "get entity primary key", $this -> entity_name );
    }

}

class Default_collection_db_handler implements Entity_db_handler_interface {

    private $event;
    private $entity_name;
    private $child_entity_class = null;

    public function __construct( $entity_name, &$event_handler, $child_entity_class = null ) {
        $this -> entity_name = $entity_name;
        $this -> event = $event_handler;
        $this -> child_entity_class = $child_entity_class;
        $this -> db_handler_helper = new Db_handler_helpers( $event_handler );
    }

    public function load( $meta, $with_related_entities = array(), $params = false ) {
        if( $this -> child_entity_class ) {
            $child_entity_class = $this -> child_entity_class;
            $meta = $this -> db_handler_helper -> map_entity_to_meta( $meta, $this -> entity_name, $child_entity_class );
        }

        if( empty( $with_related_entities ) || !$this -> child_entity_class ) {
            return $this -> event -> trigger_async( "db request", "get entity by meta", $this -> entity_name, $meta, $params );
        }


        $entities = $this -> event -> trigger_async( "db request", "get entity by meta", $this -> entity_name, $meta, $params );
        if( !is_array( $entities ) ) {
            $entities = array( $entities );
        }

        foreach( $entities as &$entity ) {
            $entity = $this -> db_handler_helper -> load_related_entities( $this -> entity_name, $entity, $with_related_entities, $child_entity_class::$related_entities_map );
        }


        return $entities;
    }

    public function save( $collection ) {
        
    }

    public function delete( $meta ) {
        
    }

    public function get_identifier() {
        return $this -> event -> trigger_async( "db request", "get entity primary key", $this -> entity_name );
    }

}

?>
