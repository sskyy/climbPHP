<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>climbPHP</title>
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.1/jquery.min.js"></script>
        <style>
            .form-line{
                margin-bottom: 10px;
            }
        </style>
    </head>
    <body>
        <div class="form-line">
            请先导入database下的sql文件。<br>
            默认用户:any@any.com  密码:any
        </div>
        <div class="form-line">
            <div>email</div>
            <input type="text" id="email">
        </div>
        <div class="form-line">
            <div>密码</div>
            <input type="password" id="password">
        </div>
        
        <div class="form-line">
            <input type="button" value="提交" id="submit">
        </div>

        
        <script>
            $("#submit").click(function(){
                var data = {
                    email : $("#email").val(),
                    password : $("#password").val()
                }
                $.ajax({
                    url:"users/login",
                    data:data,
                    dataType :'json',
                    success : function( res ){
                        if( "data" in res ){
                            alert("登陆成功");
                        }else{
                            alert("登陆失败");
                        }
                    }
                });
            })
        </script>
    </body>
</html>
