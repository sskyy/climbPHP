<?php
/**
 * Description of Book
 *
 * @author sskyy
 */
class Book extends Entity{
    public static $related_entities = array(
        'user' => 'User',
    );

    public function __construct( $data_or_identifier, $params = array() ){
        parent::__construct( $data_or_identifier, $params );
    }
}

?>
