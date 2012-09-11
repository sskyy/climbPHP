<?php
class database_module extends Module{

    private $event;
    private $event_name_to_method_map = array( '/\s*/','/_/');
    private $entity_name_to_table_name_map = array('/(.*?)/','/$1/');
    
    public function __construct(){
        parent::__construct();
    }

    public function declare_dependence(){
        return array( "event_module" );
    }

    public function set_event_handler( &$event ){
        $this -> event = $event;
    }

    public function on_module_register( $manager ){
        $this -> bind_events();
        $manager -> get("require") -> class_def("Table");
    }

    function bind_events(){
        $listen_events = array(
            'db request' => 'db_request'
        );

        foreach( $listen_events as $event_name => $callback ){
            $this -> event -> bind( $event_name, array( $this, $callback ) );
        }
    }

    public function db_request(){
        $args = func_get_args();
        $event_name = $args[0];
        $method_name = $this -> translate_action_to_method( $event_name );
        if( method_exists( $this, $method_name ) ){
            $arguments = array_slice( $args, 1 );
            return call_user_func_array( array( $this, $method_name ), $arguments );
        }
        return false;
    }
    
    function translate_action_to_method( $event_name ){
//        return str_replace(
//                $this -> event_name_to_method_map[0], 
//                $this -> event_name_to_method_map[1], 
//                $event_name
//        );
        return str_replace(" ", "_", $event_name);
    }
    
    function map_entity_name_to_table_name( $entity_name ){
        return strtolower (str_replace( 
                $this -> entity_name_to_table_name_map[0],
                $this -> entity_name_to_table_name_map[1], 
                $entity_name
        ));
    }
    
    function get_entity_by_identity( $entity_name, $identity ){
        $table_name = $this -> map_entity_name_to_table_name( $entity_name );
        $table = new Table( $table_name, $identity );
        $data = $table -> get();
        if( count($data) == 1){
            return $data[0];
        }else{
            return $data;
        }
    }
    
    function get_entity_by_meta( $entity_name, $meta, $params = false ){
        $table_name = $this -> map_entity_name_to_table_name( $entity_name );
        $table = new Table( $table_name, $meta, $params );
        $data = $table -> get();
        if( count($data) == 1){
            return $data[0];
        }else{
            return $data;
        }
    }
    
    function save_entity( $entity_name, $entity ){
        $table_name = $this -> map_entity_name_to_table_name( $entity_name );
        $table = new Table( $table_name );
        return $table -> write( $entity );
    }
    
    function delete_entity_by_identity( $entity_name, $identity){
        $table_name = $this -> map_entity_name_to_table_name( $entity_name );
        $table = new Table( $table_name, $identity );
        return $table -> delete();
    }
    
    function get_entity_primary_key( $entity_name ){
        $table_name = $this -> map_entity_name_to_table_name( $entity_name );
        $table = new Table( $table_name );
        return $table -> get_primary_key();
    }
    
    function search_record( $table_name, $data ){
        $table = new Table( $table_name, $data );
        $record = $table -> get();
        if( count($record) == 1){
            return $record[0];
        }else{
            return $record;
        }
    }
    
    function delete_record( $table_name, $data ){
        print_r( $data );
        $table = new Table( $table_name, $data );
        
        return $table -> delete();
    }
    
    function write_record( $table_name, $data ){
        $table = new Table( $table_name );
        return $table -> write( $data );
    }

}

?>
