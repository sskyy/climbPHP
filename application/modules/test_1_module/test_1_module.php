<?php
	class test_1_module{
		public function __construct(){
			echo "construct";
		}

		public function who_am_i(){
			return get_class( $this );
		}
	}
?>