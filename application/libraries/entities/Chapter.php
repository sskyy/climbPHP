<?php

class Chapter extends Entity{

    public static $related_entities = array(
        'book' => 'Book',
        'parent_chapter' => 'Chapter',
        'user' => 'User',
    );

    public function __construct( $data_or_identifier, $params = array() ){
        parent::__construct( $data_or_identifier, $params );
    }
    
}
?>

