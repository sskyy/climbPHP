<?php

/**
 * Description of User
 *
 * @author sskyy
 */
class User extends Entity{
    public function __construct( $data_or_identifier, $params = array()  ) {
        parent::__construct( $data_or_identifier, $params );
    }
}

?>
