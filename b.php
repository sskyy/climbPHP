<?php
$test = fopen('php://stdin', 'r');
main( $test );
function main( $user_input ){
	$lines = explode("\r", $user_input );
	if(count($lines) == 1 ){
		$lines = explde( "\n", $lines[0]);
	}
	
	$knowladge = knowladge($lines[0]);

	$tests = explode(" ", $lines[1]);
	foreach( $tests as $test ){
		echo single_test($knowladge, $test) ? "true<br>" : "false<br>";
	}
	
}

function knowladge( $input){
	$knowladge = array();
	$formulas = explode( " ", $input );

	foreach( $formulas as $formula ){
		$base_and_ans = explode( ">", $formula );
		$base_arr = explode( ",", $base_and_ans[0] );
		foreach( $base_arr as $base ){
			if( !isset( $knowladge[$base]) ){
				$knowladge[$base] = array( $base_and_ans[1] );
			}else{
				$knowladge[$base][] = $base_and_ans[1];
			}
		}
	}
	return $knowladge;
}

function single_test( $knowladge, $test ){
	$test_knowladges = knowladge( $test );
	
	foreach( $test_knowladges as $key => $test_knowladge ){
		foreach( $test_knowladge as $test_knowladge_item ){
			if( !single_knowladge_test( $knowladge, $key, $test_knowladge_item ) ){
				return false;
			}else{
				return true;
			}
		}
	}
}

function single_knowladge_test( $knowladge, $key, $test_knowladge ){
	
	if( !isset( $knowladge[$key])){
		return false;
	}
	if( in_array( $test_knowladge, $knowladge[$key] ) ){
		return true;
	}else{
		foreach( $knowladge[$key] as $new_key ){
			if( single_knowladge_test( $knowladge, $new_key, $test_knowladge ) ){
				return true;
			}
		}
	}

	return false;
}

?>