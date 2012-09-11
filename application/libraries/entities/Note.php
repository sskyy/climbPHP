<?php

/**
 * Description of Category
 *
 * @author jason
 */
class Note extends Entity{

    public static $related_entities = array(
        'last_param' => 'Param',
        'user' => 'User',
        'book' => 'Book'
    );

    public function __construct( $data_or_identifier, $params = array() ){
        parent::__construct( $data_or_identifier, $params );
    }
    
}

?>
