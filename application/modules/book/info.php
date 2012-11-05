<?php

$require = array("persistence");

$listen = array(
	"model/user/put" => "add_note_book_for_new_user",
	"model/user/delete" => array( "check_book_info" , "first" ),
	//"model/user/post" => array("any_action", array("before"=>array("module2", "any_action")));
	//"model/user/post" => array("any_action", array("after"=>array("module2", "any_action")));
);

$modelNs = array("book");

?>