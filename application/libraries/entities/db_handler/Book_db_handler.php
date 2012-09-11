<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Book_db_handler
 *
 * @author sskyy
 */
require_once "Default_db_handler.php";
class Book_db_handler extends Default_db_handler{
    private $event;
    private $entity_name = "Book";
    public static $related_entities_map = array(
        'user' => 'user_id'
    );
    public function __construct(  &$event_handler ){
        parent::__construct($this -> entity_name, $event_handler);
        $this -> event = $event_handler;
    }
}

class Book_collection_db_handler extends Default_collection_db_handler{
    private $event;
    private $entity_name = "Book";
    public function __construct( &$event_handler){
        $this -> event = $event_handler;
        parent::__construct($this -> entity_name, $event_handler, "Book_db_handler"  );
        $this -> db_handler_helper = new Db_handler_helpers($event_handler);
    }
}

?>
