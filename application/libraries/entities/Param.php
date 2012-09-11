<?php

/**
 * Description of Param
 *
 * @author jason
 */
class Param extends Entity{

    public static $related_entities = array(
        'note' => 'Note',
        'user' => 'User',
        'book' => 'Book',
        'chapter' => 'Chapter',
    );

    public function __construct( $data_or_identifier, $params = array() ){
        parent::__construct( $data_or_identifier, $params );
    }
    
}

?>
