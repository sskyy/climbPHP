<?php

/**
 * Description of Default_db_handler
 *
 * @author jason
 */
require_once "Default_db_handler.php";
class Param_db_handler extends Default_db_handler{
    private $event;
    private $entity_name = "Param";
    public static $related_entities_map = array(
        'book' => "book_id",
        'user' => "user_id",
        'note' => 'note_id',
        'chapter' => 'chapter_id'
    );
    public function __construct(  &$event_handler ){
        parent::__construct($this -> entity_name, $event_handler);
        $this -> event = $event_handler;
    }
}

class Param_collection_db_handler extends Default_collection_db_handler{
    private $event;
    private $entity_name = "Param";
    public function __construct( &$event_handler){
        $this -> event = $event_handler;
        parent::__construct($this -> entity_name, $event_handler, "Param_db_handler"  );
        $this -> db_handler_helper = new Db_handler_helpers($event_handler);
    }
}


?>
