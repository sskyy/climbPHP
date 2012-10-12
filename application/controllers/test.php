<?php
class Test extends MY_Controller {
    public function __construct(){
        parent::__construct();
        $this -> xn_output = $this -> load -> module("xn_output_module");
        $this -> xn_input = $this -> load -> module("xn_input_module");
        
        $this -> event = $this -> load -> module("event_module");
    }

    public function index(){
    	echo "index page";
    }

    /*
     * part 1: 事件的测试，包括基本的监听触发，命名空间。
    */

    //target:事件的触发和监听
    public function listen_and_bind(){
    	//#event
    	$this -> event -> bind("test_listen_and_bind", array( $this, "trigger_listen_and_bind" ));
    	$this -> event -> trigger( "trigger_listen_and_bind");

    }

    public function trigger_listen_and_bind(){
    	echo "event triggered";
    }

    //target:命名空间
    public function event_namespace(){
    	$this -> event -> bind("event_name1.namespace1", array($this, "func_event_name1") );
    	$this -> event -> trigger("event_name1");
    	$this -> event -> trigger(".namespace1");
    }

    public function func_event_name1(){
    	echo "this is test for event_namespace, should be output twice.";
    }
}
?>