<?php 
    namespace Models;

    use MySql;
    use Site;
    class RegisterModels{
       public static function sessionToken($token,$email,$usuario,$senha){
            $code = self::generateRandomString();
            if(Site::getRowCountDB('usuarios','`username`="'.$usuario.'" OR `email`="'.$email.'"') >= 1){
                return false;
            }
            $sql = MySql::conectar()->prepare("INSERT INTO `sessionregister.token` VALUES(null,?,?,?,?,?,?,?,null,1)");
            if($sql->execute(array($token,$email,$usuario,$senha,$code,date('Y-m-d H:i:s'),Site::getIpUser()))){
                return true;
            }
            return false;
       }
       public static function generateRandomString(){
        $characters = '0123456789';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < 6; $i++){
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;}

        public static function confirmationCode($token){
            if(!$info = Site::getInfoDB('sessionregister.token','`token`="'.$token.'"')){return false;}
            $mail = new \Email('deadbear.club','admin@deadbear.club','w?y*RW9pwnI@','deadbear');
            $mail->enviarPara($info['email'],$info['username']);
            $mail->formatarEmail(array('Assunto'=>'Código de confirmação para ativar sua conta.','Corpo'=>
            '
            <html>
            <head>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                <title>Deadbear</title>
            <style>
                .code{
                    background:#ccc;
                    width:fit-content;
                    padding: 5px;
                    font-size:25px;
                }
                button{
                    padding: 10px;
                    border:none;
                    background-color:#512da8;
                    color:white;
                }
            </style>
            </head>
            <body>
                    <div style="background: #4717f6; width:100%; min-height:300px; margin:10px 0; padding: 10px;">
                        <img style="width: 90px; height:90px; border-radius:50%;" src="'.BASE.'data/images/logo.webp">
                        <h4>Olá seja bem vindo a Deadbear, a loja mais confiável do brasil para compra e venda de contas steam!</h4>
                        <p>Para ativar sua conta use o seguinte código, ou clique no link!</p>
                        <div><span class="code">'.$info['code'].'</span></div>
                        <a href="'.BASE.'registrar/verificar-email?code='.$info['code'].'"><button>Ativar minha conta</button></a>
                    </div>
            </body>
            </html>'));
            $mail->enviarEmail();
            return true;
        }
        public static function reenviarEmail($token){
            $date = Site::getInfoDB('sessionregister.token','`token`="'.$token.'"')['date-att'];
            if($date == null || strtotime(date('Y-m-d H:i:s')) < strtotime(date($date, strtotime('+5 minutes')))){
                $sql = MySql::conectar()->prepare("UPDATE `sessionregister.token` SET `date-att` = ? WHERE `token` = ?");
                if($sql->execute(array(date('Y-m-d H:i:s'),$token))){
                    self::confirmationCode($token);
                    return true;
                }
                return false;
            }
            return 'colldown';
            
        }
        public static function registerUser($user,$pass,$email){
            $sql = MySql::conectar()->prepare("UPDATE `sessionregister.token` SET `status` = 0 WHERE `username` = ? AND `email` = ?");
            $sql->execute(array($user,$email));
            $date = date('Y-m-d H:i:s');
            $id = uniqid();
            $sql = MySql::conectar()->prepare("INSERT INTO `usuarios` VALUES(?,?,?,?,?,null,true,false)");
            $sql->execute(array($id,$user,$pass,$email,$date));
            self::successLogin(MySql::conectar()->lastInsertId());
        }
        public static function resetPassword($remetente,$code){
            $mail = new \Email('deadbear.club','admin@deadbear.club','+na2GlEm1T*V','Deadbear');
            $mail->enviarPara($remetente,'Caro usuário');
            $mail->formatarEmail(array('Assunto'=>'Código de confirmação para alterar sua conta.','Corpo'=>
            '
                <html>
                <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                <title>Deadbear</title>
                <link rel="preconnect" href="https://fonts.googleapis.com/%22%3E
                <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin>
                <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,300;0,500;1,700&display=swap" rel="stylesheet">
                <style>
                    *{
                        font-family: "Roboto", sans-serif;
                        color:white;
                    }
                    .email-wrap{
                        text-align:center;
                        margin: 20px 0;
                        padding:10px;
                    }
                    .code{
                        background:#ccc;
                        width:fit-content;
                        padding: 5px;
                        font-size:25px;
                    }
                    button{
                        padding: 10px;
                        border:none;
                        background-color:#512da8;
                        color:white;
                        cursor:pointer;
                        margin-top:10px; 
                        border-radius:3px;
                    }
                </style>
                </head>
                <body>
                <div class="email-wrap">
                    <div style="background: #4717f6; width:100%; min-height:300px; margin:10px 0; padding: 10px;">
                        <img style="width: 90px; height:90px; border-radius:50%;" src="'.BASE.'data/images/logo.webp">
                        <h4>Olá seja bem vindo a Deadbear, a loja mais confiável do brasil para compra e venda de contas steam!</h4>
                        <p>Para redefinir sua senha clique no seguinte botão.</p>
                        <a href="'.BASE.'login/resetar-senha?code='.$code.'"><button>Redefinir minha senha</button></a>
                    </div>
                </div>
                </body>
                </html>'));
            $mail->enviarEmail();
        }
        public static function insertDBResetPass($code,$email,$data){
            $iduser = Site::getInfoDB('usuarios','`email`="'.$email.'"')['id'];
            $sql = MySql::conectar()->prepare("INSERT INTO `resetpassword.token` VALUES(null,?,?,?,1,?)");
            if($sql->execute(array($code,$iduser,$data,Site::getIpUser()))){
                return true;
            }
            return false;
        }
        public static function verifyHasRequestResetPass($email){
            $iduser = Site::getInfoDB('usuarios','`email`="'.$email.'"')['id'];
            $info = Site::getInfoDB('resetpassword.token','`id.usuario`="'.$iduser.'" AND `ip`="'.Site::getIpUser().'"');
            $currentdate = date('Y-m-d H:i:s');
            if(Site::getRowCountDB('resetpassword.token','`id.usuario`="'.$iduser.'" AND `ip`="'.Site::getIpUser().'"')){
                $datacriacao = $info['data-criacao'];
                if(strtotime($currentdate) > strtotime(date($datacriacao, strtotime('+10 minutes')))){
                    return false;
                }
                return true;
            }
            return true;
        }
        public static function generateTokenResetPass($email){
            $token = md5(uniqid());
            $sql = MySql::conectar()->prepare("INSERT INTO `resetpassword.cookietoken` VALUES(?,?)");
            if($sql->execute(array($token,$email))){
                setcookie('tokenIDReset',$token, time() + (60*60*24), '/');
                return true;
            }
            return false;
        }
        public static function checkTokenResetPass(){
            if(isset($_COOKIE['tokenIDReset'])){
                if(Site::getRowCountDB('resetpassword.cookietoken','tokenID="'.$_COOKIE['tokenIDReset'].'"')){
                    return true;
                }
            }
            return false;
        }
        public static function changePassword($email,$senha){
            $sql = MySql::conectar()->prepare('UPDATE `usuarios` SET `password` = ? WHERE `email` = ?');
            if($sql->execute(array($senha,$email))){
                setCookie('tokenIDReset',true,time() -1, '/');
                $id = Site::getInfoDB('usuarios','`email`="'.$email.'"')['id'];
                self::notificarSenhaAlterada($id);
                return true;
            }else{
                return false;
            }
        }
        
        public static function notificarLogin(){
                $userInfo = Site::getUserInfo($_COOKIE['loginToken']);
                $mail = new \Email('deadbear.club','admin@deadbear.club','+na2GlEm1T*V','Deadbear');
                $mail->enviarPara($userInfo['email'],'Caro usuário '.ucfirst($userInfo['username']));
                $mail->formatarEmail(array('Assunto'=>'Nós detectamos uma atividade em comum na sua conta.','Corpo'=>
                '
                <html>
                <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                <title>Deadbear</title>
                <link rel="preconnect" href="https://fonts.googleapis.com/%22%3E
                <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin>
                <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,300;0,500;1,700&display=swap" rel="stylesheet">
                <style>
                    *{
                        font-family: "Roboto", sans-serif;
                        color:white;
                    }
                    .email-wrap{
                        text-align:center;
                        margin: 20px 0;
                        padding:10px;
                    }
                    .code{
                        background:#ccc;
                        width:fit-content;
                        padding: 5px;
                        font-size:25px;
                    }
                    button{
                        padding: 10px;
                        border:none;
                        background-color:#512da8;
                        color:white;
                        cursor:pointer;
                        margin-top:10px; 
                        border-radius:3px;
                    }
                </style>
                </head>
                <body>
                <div class="email-wrap">
                    <div style="background: #4717f6; width:100%; min-height:300px; margin:10px 0; padding: 10px;">
                        <img style="width: 90px; height:90px; border-radius:50%;" src="'.BASE.'data/images/logo.webp">
                        <h4>Nossos robôs detectaram que sua conta foi acessada de um local diferente do habitual!</h4>
                        <p>Confira se o login foi feito por você mesmo, caso não tenha sido recomendamos que altere sua senha imediatamente clicando no botão abaixo.</p>
                        <a href="'.BASE.'login/resetar-senha"><button>Redefinir minha senha</button></a>
                    </div>
                </div>
                </body>
                </html>'));
                $mail->enviarEmail();
        }
            public static function successLogin($id = null){
                $info = Site::getUserInfo($_COOKIE['loginToken']) != '' ? Site::getUserInfo($_COOKIE['loginToken']) : Site::getUserInfoByID($id); 
                $token = \Site::generateLogintoken();
                $sql = MySql::conectar()->prepare("INSERT INTO `sessionlogin.token` VALUES(?,?,?)");
                if($sql->execute(array($token,\Site::getIpUser(),$info['id']))){
                    setCookie('tokenSessionReg', '', time() + -1, '/');
                    $_SESSION['login'] = true;
                    return true;
                }
                return false;
                
            }
            public static function notificarSenhaAlterada($id){
                $userInfo = Site::getUserInfoByID($id);
                $mail = new \Email('deadbear.club','admin@deadbear.club','+na2GlEm1T*V','Deadbear');
                $mail->enviarPara($userInfo['email'],'Caro usuário '.ucfirst($userInfo['username']));
                $mail->formatarEmail(array('Assunto'=>'Sua senha foi alterada com sucesso.','Corpo'=>
                '
                <html>
                <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                <title>Deadbear</title>
                <link rel="preconnect" href="https://fonts.googleapis.com/%22%3E
                <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin>
                <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,300;0,500;1,700&display=swap" rel="stylesheet">
                <style>
                    *{
                        font-family: "Roboto", sans-serif;
                        color:white;
                    }
                    .email-wrap{
                        text-align:center;
                        margin: 20px 0;
                        padding:10px;
                    }
                    .code{
                        background:#ccc;
                        width:fit-content;
                        padding: 5px;
                        font-size:25px;
                    }
                    button{
                        padding: 10px;
                        border:none;
                        background-color:#512da8;
                        color:white;
                        cursor:pointer;
                        margin-top:10px; 
                        border-radius:3px;
                    }
                </style>
                </head>
                <body>
                <div class="email-wrap">
                    <div style="background: #4717f6; width:100%; min-height:300px; margin:10px 0; padding: 10px;">
                        <img style="width: 90px; height:90px; border-radius:50%;" src="'.BASE.'data/images/logo.webp">
                        <h4>Sua senha foi alterada com sucesso!</h4>
                        <p>Sua senha foi alterada em <b>deadbear.club</b> as  <b>'.date('H:i:s d/m/Y').'</b> com o IP <b>'.\Site::getIpUser().'</b>, caso não tenha sido você e sua tenha sido invadida recomendamos que entre em contato com a adminstração.</p>
                        <a href="'.BASE.'Discord"><button>Entre no nosso discord</button></a>
                    </div>
                </div>
                </body>
                </html>'));
                $mail->enviarEmail();
            }
        }   
?>