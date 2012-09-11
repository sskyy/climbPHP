<?php

/**
 * Description of Db_handler_helpers
 *
 * @author jason
 */
class Db_handler_helpers{

    private $event;

    public function __construct( &$event_handler ){
        $this -> event = $event_handler;
    }

    public function load_related_entities( $entity_class, $entity, $with_related_entities, $related_entities_map ){
        foreach( $with_related_entities as $related_entity_name ){
            $related_entity_class = $entity_class::$related_entities[$related_entity_name];
            $related_entity_identity = $entity -> {$related_entities_map[$related_entity_name]};
            $related_entity = $this -> event -> trigger_async( "db request", "get entity by identity", $related_entity_class, $related_entity_identity );
            $entity -> $related_entity_name = $related_entity;
        }
        return $entity;
    }

    public function map_entity_to_meta( $meta, $entity_class, $entity_db_handler ){
        if( !isset($entity_db_handler::$related_entities_map)){
            return $meta;
        }
        
        $output = array( );
        foreach( $meta as $meta_key => $meta_value ){
            
            if( in_array( $meta_key, array_keys( $entity_db_handler::$related_entities_map ) ) ){
                if( !isset($entity_class::$related_entities[$meta_key] )){
                    continue;
                }
                $meta_value = (array)$meta_value;
                $related_entity_class = $entity_class::$related_entities[$meta_key];
                $related_entity_db_handler = new Default_db_handler( $related_entity_class, $this -> event );
                $related_entity_identifier = $related_entity_db_handler -> get_identifier();
                $output[$entity_db_handler::$related_entities_map[$meta_key]] = $meta_value[$related_entity_identifier];
            }else{
                $output[$meta_key] = $meta_value;
            }
        }

        return $output;
    }

}

?>
