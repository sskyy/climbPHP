<?php
class bookModule{
	private $evt;
	public function __construct(&$manager){
		$this -> evt = $manager -> mod("event");
	}


	public function get_resource(  ){
		
	}

	public function is_operater_book_owner( $bookId){
		$current_user = res_target("user", $this -> evt -> trigger("user/get_current_user"));
		$book = $this -> evt -> trigger("entity/book/get", $bookId );

		if( entity_equal( $current_user, $book -> owner ) ){
			return true;
		}

		$stopRes = new sysRes("err","operater is not book owner");
		return $stopRes;
	}


	

	public function add_note_book_for_new_user( $user ){
		print_r( $user );
		echo "meee";
	}
}
?>