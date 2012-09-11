<?php
define("ERROR_USER_EXIST", 1);
define("ERROR_USER_NOT_LOGIN", 2);

class xn_user_module{
    //用于保存 模块管理器 的引用
    private $manager;
    //用于保存 事件模块 提供的 全局事件 的引用
    private $event;
    //用于保存 实体模块 的引用
    private $entity_handler;
    //用于保存 CI 的 session对象
    private $session;
    public function __construct(){
        
    }
    
    //使用 事件模块提供的钩子
    public function set_event_handler( &$event ){
        $this -> event = $event;
    }
    
    //声明依赖的模块
    public function declare_dependence(){
        return array( "event_module", "entity_module" );
    }

    //使用模块管理器提供的钩子
    public function on_module_register(  &$manager ){
        $this -> manager = $manager;
        $this -> entity_handler = $manager -> get("entity_module");
        $this -> session = $manager -> get("require") -> library("session");
    }
    
    
    public function login( $data ){
        $user_exist = $this -> entity_handler -> search_entity( "user", $data );
        
        if( $user_exist ){
            $this -> session -> set_userdata(array("user" => $user_exist -> to_object()));
            $user_exist -> set ("last_login", time()) ->save();
        }
        
        return $user_exist;
    }
    
    public function register( $data ){
        $user_exist_data = array("email"=>$data['email']); 
        $user_exist = $this -> entity_handler -> search_entity("User", $user_exist_data);
        if( $user_exist ){
            return ERROR_USER_EXIST;
        }
        
        $user = $this -> entity_handler -> create_entity("User", $data);
        $user -> set("created", time());
        if( $user -> save() ){
            $this -> login( $user -> to_array() );
        }
        return $user;
    }
    
    public function logout(){
        $user_data = $this -> session -> userdata("user");
        if( $user_data ){
            $this -> session -> sess_destroy();
        }
        
        return true;
    }
    
    public function is_user_logged_in(){
        return $this -> session -> userdata("user");
    }
    
    public function get_current_user(){
        return $this -> session -> userdata("user");
    }
    
    public function who_am_i(){
        $user_data = $this -> session -> userdata("user");
        return  $user_data ? $user_data : "i don't know";
    }
    

}
?>
