<?php
/**
 * 所有public操作都必须有返回值，它表示整个操作链中的一环。
 * 返回NULL时，表示操作没有执行。
 * 返回evtRes对象时，表示有相关的链式操作。
 * 返回其他对象时，表示只有一个单一操作。
 *
 * 一个模块中的函数可能有三种含义：
 * 1. 响应外界的操作，例如login。一般会返回SysRes对象。
 * 2. 内部操作，如_login，一般只返回成功或者失败。
 * 3. 资源操作，如get。是RestFul接口的响应。
 */

class userModule{
	/**
	 * 如果模块实现了 get_resource 方法，模块加载器就通过该方法将资源传递给该类。
	 * 如果没有实现，就默认占用以下成员名：
	 *   req - 资源加载工具，方法：
	 *     mod - 获取已加载模块实例
	 *     req - 动态加载文件，不返回实例
	 *     reqMod - 动态加载模块，并返回实例 		 
	 *      
	*/

	private $evt;
	public function __construct(){

	}

	public function get_resource( &$manager ){
		$this -> evt = $manager -> mod("event");
	}


	public function user_action( $action, $param ){
		$result = NULL;
		if( isset( $this -> $action )){
			$result = call_user_func( array( $this, $action ), $param );
		}
		return $result;
	}

	public function login( $name, $password ){
		$result = NULL;

		if( $this -> _is_user_exist( $name, $password) ){
			$user = $this -> _get_user_by_meta( array("name"=>$name, "password"=>$password));
			
			if( $this -> _login( $user ) ){
				$ripple = $this -> r -> mod("evt") -> trigger("user/logged_in");
				$result = res_mashup( $user, $ripple );
			}
		}
		return $result;
	}



	private function _is_user_exist( $name, $password ){
		return $this -> get_user_by_meta( array("name"=>$name, "password"=>$password) ) ? true : false;
	}

	private function _get_user_by_meta( $meta ){
		return res_target( "user", $this -> evt -> trigger("entity/user/query", $meta ) );
	}

	private function _login( $user ){
		return res_bool( $this -> evt -> trigger("entity/user/persist", $user, "session") );
	}



}

?>