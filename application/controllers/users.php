<?php
class Users extends MY_Controller {
    
    public function __construct(){
        parent::__construct();
        $this -> xn_output = $this -> load -> module("xn_output_module");
        $this -> xn_input = $this -> load -> module("xn_input_module");
        $this -> xn_user = $this -> load -> module("xn_user_module");
    }
    
    public function index(){
        $this -> load -> view("index.php");
    }
    
    // public function register(){
    //     $data = $this -> xn_input -> fetch_data(array("email","password"));
        
    //     $register_user = $this -> xn_user -> register( $data );
        
    //     if( is_numeric( $register_user ) && $register_user == ERROR_USER_EXIST ){
    //         $this -> xn_output -> output("error", "email {$data['email']} already exist" );
    //     }else{
    //         if( $register_user ){
    //             $this -> xn_output -> output("data", $register_user -> to_object());
    //         }else{
    //             $this -> xn_output -> output("error", $data );
    //         }
    //     }
    // }

    public function register(){
        $data = $this -> xn_input -> fetch_data(array("email","password"));
        $register_result = $this -> xn_user -> register( $data );
        $this -> output -> output( $register_result );
    }

    public function login(){
        $data = $this -> xn_input -> fetch_data(array("email","password"));
        
        $logged_in_user = $this -> xn_user -> login( $data );
        if( $logged_in_user ){
            $this -> xn_output -> output("data", $logged_in_user -> to_object());
        }else{
            $this -> xn_output -> output("error", $data );
        }
    }
    
    public function logout(){
        $logout = $this -> xn_user -> logout();
        if( $logout ){
            $this -> xn_output -> output("data", $logout);
        }else{
            $this -> xn_output -> output("error", $logout );
        }
    }
    
    public function who_am_i(){
        $me = $this -> xn_user -> who_am_i();
        $this -> xn_output -> output("data", $me );
    }
    
    public function leave_message(){
        $data = $this -> xn_input -> fetch_data( array("message"));
        if( $this -> xn_user -> who_am_i () ){
            $data['user_id'] = $this -> xn_user -> who_am_i () -> id;
        }
        
        if( $this -> db -> insert("message", $data) ){
            $this -> xn_output -> output("data", $data );
        }
    }
}

?>
