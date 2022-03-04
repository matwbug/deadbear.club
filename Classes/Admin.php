<?php 
    class Admin{
        public static function logado(){
            if(isset($_COOKIE['admin_loginToken'])){
                $sql = MySql::conectar()->prepare("SELECT * FROM `sessionadminlogin.token` WHERE `token` = ? AND `ip.user` = ?");
                $sql->execute(array($_COOKIE['admin_loginToken'], self::getIpUser()));
                if($sql->rowCount() == 1){
                    return true;
                }else{
                    return false;
                }
            }else{
                return false;
            }
        }
        public static function getIpUser(){
            foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key) {
                if (array_key_exists($key, $_SERVER) === true) {
                    foreach (array_map('trim', explode(',', $_SERVER[$key])) as $ip) {
                        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                            return $ip;
                        }
                    }
                }else{
                    return 'A'; //TIRAR ISSO DPS DE FAZER O SITE
                }
            }
        }
        public static function gerar_token(){
            if(!isset($_SESSION['tokenAdmin'])){
                $token = bin2hex(random_bytes(32));
    	        $_SESSION["tokenAdmin"] = $token; //sessão token igual ao token gerado
                return $token;
            }
        }
        public static function validar_token($token){
            if($_SESSION["tokenAdmin"] != $token || !isset($_SESSION["tokenAdmin"])){
                return false; //se a session for diferente de $token
            }
            return true;
        }
        public static function menuActive($page){
            $url = explode('/',Site::getCurrentUrl())[1] == 'db' ? explode('/',Site::getCurrentUrl())[3] : explode('/',Site::getCurrentUrl())[2];
            //print_r(explode('/',Site::getCurrentUrl()));
            if($url == $page){
                echo 'active';
            }
        }
        public static function getUserInfo($token){
            $sql = MySql::conectar()->prepare("SELECT `id.account` FROM `sessionadminlogin.token` WHERE `token` = ?");
            $sql->execute(array($token));
            $userInfo = $sql->fetch();
            $sql = MySql::conectar()->prepare("SELECT * FROM `admin` WHERE `id` = ?");
            $sql->execute(array($userInfo['id.account']));
            $sql = $sql->fetch();
            return $sql;
        }
	    public static function getUserInfoId($id){
            $sql = MySql::conectar()->prepare("SELECT * FROM `admin` WHERE `id` = ?");
            $sql->execute(array($id));
            $sql = $sql->fetch();
            return $sql;
        }
	
        public static function getProfilePhoto(){
            $imgName = Admin::getUserInfo($_COOKIE['admin_loginToken'])['img.perfil'];
            if($imgName == null){
                $path = BASE.'data/images/upload/default.webp';

            }else{
                $path = BASE.'data/images/upload/'.$imgName;

            }
            return $path;
        }
        public static function getProfilePhotoId($id){
            $imgName = self::getUserInfoId($id)['img.perfil'];
            if($imgName == null){
                $path = BASE.'data/images/upload/default.webp';

            }else{
                $path = BASE.'data/images/upload/'.$imgName;

            }
            return $path;
        }
        public static function getTotalUsers(){
            $sql = MySql::conectar()->prepare("SELECT * FROM `usuarios`");
            $sql->execute();
            return $sql->rowCount();
        }
        public static function getTotalTickets(){
            $sql = MySql::conectar()->prepare("SELECT * FROM `tickets`");
            $sql->execute();
            return $sql->rowCount();
        }
        public static function isOnline($id,$time=null){
            $time = isset($time) ? $time : 15;
            $lastactivity = @Site::getInfoDB('admin.online','`id.admin`='.$id)['lastactivity'];
            if(isset($lastactivity) && (strtotime($lastactivity) + $time) > time()){
                return true;
            }else{
                return false;
            }
        }
        public static function checkCodeMaster($code){
            $json = json_decode(file_get_contents('../config.json'),true);
            if(md5(trim($code)) == $json['chaveMestra']){ 
                return true;
            }
            return false;
        }
    }

?>