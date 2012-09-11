<?php

define( "TABLE_DATA_FILLED", 1 );

class Table{

    private $db;
    private $status = array( );
    private $data;
    private $meta;
    private $condition_keys = array( "where", "or_where", "where_in", "like" );
    private $params = array();


    public function __construct( $table_name, $conditions_or_primary_key = array(), $params = array() ){
        $this -> _set_db_handler();
        $this -> _init_table( $table_name, $conditions_or_primary_key, $params );
    }

    private function _set_db_handler(){
        $this -> db = &get_instance() -> db;
    }

    private function _set_meta( $meta_name, $meta_value ){
        return $this -> meta[$meta_name] = $meta_value;
    }

    private function _get_meta( $meta_name ){
        if( !isset( $this -> meta[$meta_name] ) ){
            return false;
        }
        return $this -> meta[$meta_name];
    }

    private function _set_status( $status, $new_status = false ){
        if( $new_status == false && !in_array( $status, $this -> status ) ){
            return array_push( $this -> status, $status );
        }else if( $new_status != false && in_array( $this -> status, $status ) ){
            $key = array_search( $status, $this -> status );
            $this -> status[$key] = $new_status;
        }
        return true;
    }

    private function _has_status( $status ){
        $key = array_search( $status, $this -> status );
        return $key == false ? false : true;
    }

    private function _init_table( $table_name, $conditions_or_primary_key, $params = false ){
        $this -> _set_table( $table_name );
        $this -> _set_table_meta();

        if( is_array( $conditions_or_primary_key ) ){
            $this -> _set_conditions( $conditions_or_primary_key );
        }else{
            $condition = array( $this -> _get_meta( 'primary_key' ) => $conditions_or_primary_key );
            $this -> _set_conditions( $condition );
        }
        
        if( $params && is_array( $params )){
            $this -> _set_params( $params );
        }
    }
    
    private function _set_params( $params ){
        return $this -> params = $params;
    }

    private function _set_table( $table_name = '' ){
        return $this -> _set_meta( 'table', $table_name );
    }

    private function _set_conditions( $conditions = array( ) ){
        $conditions = $this -> _standardize_condition_arguments( $conditions );
        $this -> _set_meta( 'conditions', $conditions );
    }

    private function _standardize_condition_arguments( $arguments = false ){
        $output = array( );
        if( $arguments != false && !$this -> is_batch( $arguments ) ){
            $output['where'] = $arguments;
        }else{
            $output = $arguments;
        }
        return $output;
    }

    private function _set_table_meta(){
        $columns = $this -> _db_get_table_columns();
        $this -> _set_meta( 'columns', $columns );

        $this -> _set_meta( 'primary_key', $this -> _db_get_primary_column_name( $columns ) );
    }

    private function _get_data(){
        $this -> data = $this -> _db_get_data();
    }

    private function _db_set_conditions(){
        $conditions = $this -> _get_meta( "conditions" );
        foreach( $this -> condition_keys as $condition_key ){
            if( isset( $conditions[$condition_key] ) ){
                $this -> db -> $condition_key( $conditions[$condition_key] );
            }
        }
    }

    private function _db_set_filters( $filters ){
        $this -> db -> where( $filters );
    }

    private function _db_get_data(){
        $this -> _db_set_table();
        $this -> _db_set_conditions();
        $this -> _db_set_params();
        $result =  $this -> db -> get() -> result();
        return $result;
    }

    private function _db_set_table( $table_name = '' ){
        if( $table_name == '' ){
            $table_name = $this -> _get_meta( 'table' );
        }
        return $this -> db -> from( $table_name );
    }
    
    private function _db_set_params(){
        if( !empty( $this -> params) ){
            if( isset( $this -> params["limit"])){
                $offset = isset( $this -> params["limit"]["offset"]) ? $this -> params["limit"]["offset"] : 0;
                $this -> db -> limit( $this -> params["limit"]['limit'], $offset);
            }
        }
    }

    private function _db_get_table_columns(){
        return $this -> db -> field_data( $this -> _get_meta( 'table' ) );
    }

    private function _db_get_primary_column_name( $columns ){
        foreach( $columns as $column ){
            if( $column -> primary_key ){
                return $column -> name;
            }
        }
    }

    private function _db_insert( $data ){
        if( $this -> is_batch( $data ) ){
            return $this -> db -> insert_batch( $this -> _get_meta( 'table' ), $data );
        }else{
            if( $this -> db -> insert( $this -> _get_meta( 'table' ), $data ) ){
                return $this -> db -> insert_id();
            }
            return false;
        }
    }

    private function _db_update( $data, $filters = array( ) ){
        if( $this -> is_batch( $data ) ){
            return $this -> db -> update_batch( $this -> _get_meta( 'table' ), $data, $this -> get_primary_key );
        }else{
            $this -> _db_set_conditions();
            $this -> _db_set_filters( $filters );
            return $this -> db -> update( $this -> _get_meta( 'table' ), $data );
        }
    }
    
    private function _db_delete(){
        $this -> _db_set_table();
        $this -> _db_set_conditions();
        return $this -> db -> delete();
    }

    public function get_primary_key(){
        return $this -> _get_meta( 'primary_key' );
    }

    public function write( $data ){
        $data = (array)$data;
        if( $this -> is_primary_key_in_data( $data ) ){
            if( $this -> is_batch( $data ) ){
                $this -> update( $data );
            }else{
                $filter = array( $this -> get_primary_key() => $data[$this -> get_primary_key()] );
                unset( $data[$this -> get_primary_key()] );
                return $this -> update( $data, $filter );
            }
        }else{
            return $this -> insert( $data );
        }
        
        return false;
    }

    private function is_primary_key_in_data( $data ){
        $data = (array) $data;
        if( isset( $data[$this -> get_primary_key()] ) ){
            return true;
        }
        if( $this -> is_batch( $data ) && isset( $data[0][$this -> get_primary_key()] ) ){
            return true;
        }
        return false;
    }

    private function is_batch( $data ){
        $data = (array) $data;
        foreach( $data as $k => $v){
            if( is_array( $v)){
                return true;
            }
        }
        return false;
    }

    public function insert( $data ){
        return $this -> _db_insert( $data );
    }

    public function update( $data, $filters = array( ) ){
        $result = $this -> _db_update( $data, $filters );
        return $result;
    }

    public function get( $from_cache = false ){

        if( !$from_cache || !$this -> _has_status( TABLE_DATA_FILLED ) ){
            $this -> _get_data();
        }

        $this -> _set_status( TABLE_DATA_FILLED );
        return $this -> data;
    }

    public function filter( $filters ){
        if( !$this -> _has_status( TABLE_DATA_FILLED ) ){
            $this -> get();
        }
        return $this -> filter( $filters );
    }

    public function delete(){
        return $this -> _db_delete();
    }

    public function set_table( $table_name ){
        $this -> _set_table( $table_name );
        return $this;
    }

    public function set_conditions( $conditions = array( ) ){
        $this -> _set_conditions( $conditions );
        return $this;
    }

    public function reset_conditions( $conditions = array( ) ){
        return $this -> set_conditions( $conditions );
    }

}
