<?php
class Test extends CI_Controller {
    public function __construct(){
        parent::__construct();
// 
    }

    public function index(){
    	$this -> listen_and_bind();
        $this -> event_namespace();
        $this -> module_info();
    }

    /*
     * part 1: 事件的测试，包括基本的监听触发，命名空间。
    */

    //target:事件的触发和监听
    public function listen_and_bind(){
    	//#event
    	$this -> event -> bind("test_listen_and_bind", array( $this, "trigger_listen_and_bind" ));
    	$this -> event -> trigger( "test_listen_and_bind");
    }

    public function trigger_listen_and_bind(){
    	$this -> echo_format( "event triggered" );
    }

    //target:命名空间
    public function event_namespace(){
    	$this -> event -> bind("event_name1.namespace1", array($this, "func_event_name1") );
    	$this -> event -> trigger("event_name1");
    	$this -> event -> trigger(".namespace1");
    }

    public function func_event_name1(){
    	$this -> echo_format( "this is test for event_namespace, should be output twice." );
    }

    public function echo_format( $content ){
        echo "<br>===========<br>";
        echo $content;
        echo "<br>===========<br>";

    }

    //target: 模块的声明文件识别
    public function module_info(){
        $this -> echo_format( print_r( $this -> test_1_module -> info , true) ) ;
    }


    public function test_event_array(){
        echo "<pre>";
        $this -> load -> library("event");
        $this -> event -> bind("user/login.np1.np2", array($this, "echo_format"));
        // print_r( $this -> event -> list_binds());

        echo "</pre>";
    }

    public function test_preg_event_array(){
        echo "<pre>";
        $this -> load -> library("event");
        $this -> event -> bind(":user/login.np1.np2", array($this, "echo_format"));
        // print_r( $this -> event -> list_binds());

        echo "</pre>";
    }

    public function test_static_array_ref(){
        $i = 0;
        $ref = array(
            'a' => array(
                "a" => array(
                    "a" => "b")
                )
            );

        while( $i  <2){
            $i++;
            $this -> test_array_ref($ref);
            echo "<pre>";
            print_r( $ref );
            echo "</pre>";
        }
    }

    public function test_array_ref( &$arr ){
        static $ref;
        
        print_r( $ref);

        if( !$ref ){
            echo "mmmmmmmmmmsdfsdm"; 
            echo count( $ref);
            $ref = $arr;
        }

        
        $ref["c"] = "c";
        // $ref['b'] = "b";
        
        return $ref;
    }

    public function test_trigger_event_array(){
        $this -> test_event_array();
        $this -> echo_format("trigger with name");
        $this -> event -> trigger("user/login", "hahaha");//suss

        $this -> echo_format("trigger with name");
        $this -> event -> trigger("man/login", "hahaha");//fail

        $this -> echo_format("trigger with namespace");
        $this -> event -> trigger("user/login.np1", "hahaha");//fail

        $this -> echo_format("trigger with namespace");
        $this -> event -> trigger("user/login.np2", "hahaha");//suss

        $this -> echo_format("trigger with namespace");
        $this -> event -> trigger("user/login.np1.np2", "hahaha");//suss

        $this -> echo_format("trigger with only namespace");
        $this -> event -> trigger(".np2", "hahaha");//suss

        $this -> echo_format("trigger with namespace");
        $this -> event -> trigger(".np1", "hahaha");//fail
    }

    public function test_trigger_preg_event_array(){
        $this -> test_preg_event_array();
        $this -> echo_format("trigger with name");
        $this -> event -> trigger("user/login");//suss

        $this -> echo_format("trigger with name");
        $this -> event -> trigger("duck/login" );//suss

        $this -> echo_format("trigger with namespace");
        $this -> event -> trigger("user/login.np1");//fail

        $this -> echo_format("trigger with namespace");
        $this -> event -> trigger("son/login.np2");//suss

        $this -> echo_format("trigger with namespace");
        $this -> event -> trigger("girl/login.np1.np2");//suss

        $this -> echo_format("trigger with only namespace");
        $this -> event -> trigger(".np2");//suss

        $this -> echo_format("trigger with namespace");
        $this -> event -> trigger(".np1");//fail
    }

    public function action_1(){
        $this -> echo_format( __FUNCTION__ );
    }    
    
    public function action_2(){
        $this -> echo_format( __FUNCTION__ );
    }    
    
    public function action_3(){
        $this -> echo_format( __FUNCTION__ );
    }    
    
    public function action_4(){
        $this -> echo_format( __FUNCTION__ );
    }    
    
    public function action_5(){
        $this -> echo_format( __FUNCTION__ );
    }    

    public function test_event_order(){
        $this -> load -> library("event");
        $this -> event -> bind('test/test', array($this, "action_1"));
        $this -> event -> bind('test/test', array($this, "action_4"), array("after"=>"Test->action_3"));
        $this -> event -> bind('test/test', array($this, "action_3"), array("before" => "Test->action_4", "after"=>"Test->action_2"));
        $this -> event -> bind('test/test', array($this, "action_2"), array("before" => "Test->action_1"));
        $this -> event -> bind('test/test', array($this, "action_5"), "last");
        $this -> event -> trigger("test/test");
    }

    public function test_event_target(){
        $this -> load -> library("event");
        $this -> event -> bind('test/test', array($this, "action_1"));
        $this -> event -> bind('test/test', array($this, "action_4"), array("after"=>array("Test", "action_3")));
        $this -> event -> bind('test/test', array($this, "action_3"), array("before" => "Test->action_4", "after"=>"Test->action_2"));
        $this -> event -> bind('test/test', array($this, "action_2"));
        $this -> event -> bind('test/test', array($this, "action_5"), "last");
        $this -> event -> trigger("test/test@{Test->action_2}");
    }

    public function test_event_mute(){
        $this -> load -> library("event");
        $this -> event -> bind('test/test', array($this, "action_trigger_another_event"));
        // $this -> event -> trigger("test/test");
        $this -> event -> trigger("test/test!{test/event_in_event}");
    }

    public function action_trigger_another_event(){
        $this -> event -> bind( "test/event_in_event", array($this, "action_event_in_event"));
        $this -> event -> trigger("test/event_in_event");
    }

    public function action_event_in_event(){
        $this -> echo_format( __FUNCTION__ );
    }
}
?>