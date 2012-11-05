<?php
	class persistenceModule{
		private $modelTypeMap = array();
		private $evt;
		private $manager;

		public function __construct(&$manager){
			$this -> evt =& $manager -> mod("event");
			$this -> manager =& $manager;
		}



		public function extend_upper_level_module( &$moduleIns ){
			if( isset( $moduleIns -> modelNs) ){
				// print_r( $moduleIns -> modelNs);
				$modelNs = is_array( $moduleIns -> modelNs )? $moduleIns -> modelNs : array( $moduleIns -> modelNs );
				foreach( $modelNs as $modelType ){
					$this -> modelTypeMap[$modelType] = $moduleIns;
				}
			} 

		}

		public function model_res_op( $modelName, $id, $action, $param ){
			$result = NULL;
			
			if( isset( $this -> modelTypeMap[$modelName] ) ){
				$moduleIns = $this -> modelTypeMap[$modelName];
				if( empty( $param ) || !isset($param["id"])){
					$param["id"] = $id;
				}

				if( method_exists( $moduleIns , $action )){
					$result = call_user_func_array( array($moduleIns, $action), $param );
				}else{
					$result = $this -> $action( $param );
					$result = res_link( $result, $this -> evt -> trigger("model/{$modelName}/{$action}", $param ) );
				}
			}

			return $result;
		}

		public function model_action( $type, $action, $param ){
			$result = NULL;
			if( isset( $this -> $action ) ){
				$result = call_user_func_array( array( $this, $action), $param );
			}
			return $result;
		}

		public function get( $id ){

		}

		public function post( $model ){

		}

		public function put( $model ){
			print_r( $model );
		}

		public function delete( $id ){

		}

		public function query( $meta ){

		}

		public function persist( $model ){
			return $this -> post( $model );
		}

		private function is_restful_model_entry( $class ){
			return in_array( $class, array_keys($this -> modelTypeMap )) 
				&& in_array( $this -> get_action(), array("put","get","post","delete"));
		}

		private function get_action(){
			return $this -> manager -> util("input") -> get_post("action");
		}

		private function get_param(){
			$params = $this -> manager -> util("input") -> get();
			if( empty( $params)){
				$params = $this -> manager -> util("input") -> post();
			}

			return $params;
		}

		public function setup_restful_entry( $class, $method ){
			if( $this -> is_restful_model_entry( $class ) ){
				$model = $class;
				$id = $method == "index" ? ""  : $mothod;
				$action = $this -> get_action();
				$param = $this -> get_param();
				// print_r( $this -> evt -> list_binds() );
				$this -> evt -> trigger("@{$class}/{$id}/{$action}", $param );
			}
		}

		public function extend_module_info(){
			return "modelNs";
		}
	}
?>