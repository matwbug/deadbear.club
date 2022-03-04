<?php
//use Twilio\Rest\Client;

class Site{
        public static function getCurrentUrl(){
            return $_SERVER['REQUEST_URI']; 
        }
        public static function checkManutencao(){
            $json_object = file_get_contents('config.json');
            $data = json_decode($json_object, true);
            if($data['manutencao'] == true){
                if(Admin::logado()){
                    return false;
                }else{
                    return true;
                }
            }else{
                return false;
            }
            
        }
        public static function gerar_token(){
            if(!isset($_SESSION['token'])){
    	        $_SESSION["token"] = bin2hex(random_bytes(32)); //sessão token igual ao token gerado
            }   
            return $_SESSION['token'];
        }
        public static function validar_token($token){
            if(!isset($_SESSION["token"])){
                return false; //se não existir a session token
            }

            if($_SESSION["token"] != $token){
                return false; //se a session for diferente de $token
            }
            return true;
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
                    return 'Indefinido'; //TIRAR ISSO DPS DE FAZER O SITE
                }
            }
        }
        public static function loadJs($vers,$path,$page,$arq){
            if(is_array($page)){$pages = $page;}
            
            if($path == 'admin'){$path = ADMIN;}else{$path = BASE;}
            $url = explode('/',strtok(self::getCurrentUrl(),'?')); 
            $allPages = $page == 'all' ? $allPages = true : $allPages = false; 
            $base = $url[1] == 'db' ? 'local' : 'online';
            if($base == 'local'){$key = $vers == 'admin' ? 3 : 2;}
            else{$key = $vers == 'admin' ? 2 : 1;}
            if( $allPages || $url[$key] == $page || in_array($url[$key], $pages)){
                echo '<script type="text/javascript" src='.$path.'JS/'.$arq.'.js></script>';
            }
        }
        public static function redirecionar($url){
            echo '<script>location.href="'.$url.'"</script>';
        }
        public static function logado(){
            if(isset($_COOKIE['loginToken'])){
                $sql = MySql::conectar()->prepare("SELECT * FROM `sessionlogin.token` WHERE `token` = ? AND `ip.user` = ?");
                $sql->execute(array($_COOKIE['loginToken'], self::getIpUser()));
                if($sql->rowCount() == 1){
                    return true;
                }else{
                    setcookie('loginToken', false, time() + (-1),'/');
                    return false;
                }
            }else{
                return false;
            }
        }
        public static function getUserInfo($token=null){
            if(!isset($_COOKIE['loginToken'])){return false;}
            $token = $_COOKIE['loginToken'];
            $ipuser = self::getIpUser();

            if(Site::getRowCountDB('sessionlogin.token',"`token` = '$token' AND `ip.user` = '$ipuser'") >= 1){
                $sessionInfo = self::getInfoDB('sessionlogin.token',"`token` = '$token' AND `ip.user` = '$ipuser'");
                $infoUser = self::getInfoDB('usuarios','`id`="'.$sessionInfo['id.account'].'"');
                return $infoUser;
            }
            return false;
        }
        public static function getUserInfoByID($id){
            $sql = MySql::conectar()->prepare("SELECT * FROM `usuarios` WHERE `id` = ?");
            $sql->execute(array($id));
            $sql = $sql->fetch();
            return $sql;
        }
        public static function scriptJS($script){
            echo '<script>'.$script.'</script>';
        }
        public static function getImageUser($id){
            $sql = MySql::conectar()->prepare("SELECT * FROM `usuarios.img` WHERE `id.usuario` = ?");
            $sql->execute(array($id));
            if($sql->rowCount() >= 1){
                $img = $sql->fetch()['name'];
                return $img;
            }else{
                $img = 'default.webp';
                return $img;
            }

            
        }
        public static function getImageAnuncio($idanuncio){
            $sql = MySql::conectar()->prepare("SELECT * FROM `produtos.imagens` WHERE `id.produto` = ?");
            $sql->execute(array($idanuncio));
            if($sql->rowCount() >= 1){
                $img = $sql->fetch()['name'];
                return $img;
            }else{
                $img = 'default.webp';
                return $img;
            }
            
        }
        
        public static function logout(){
            setcookie('loginToken', '', time() + (-1),'/');
            Site::redirecionar(BASE.'login');
        }
        public static function gerarSlug($str){
			$str = mb_strtolower($str);
			$str = preg_replace('/(â|á|ã)/', 'a', $str);
			$str = preg_replace('/(ê|é)/', 'e', $str);
			$str = preg_replace('/(í|Í)/', 'i', $str);
			$str = preg_replace('/(ú)/', 'u', $str);
			$str = preg_replace('/(ó|ô|õ|Ô)/', 'o',$str);
			$str = preg_replace('/(_|\/|!|\?|#)/', '',$str);
			$str = preg_replace('/( )/', '-',$str);
			$str = preg_replace('/ç/','c',$str);
			$str = preg_replace('/(-[-]{1,})/','-',$str);
			$str = preg_replace('/(,)/','-',$str);
			$str=strtolower($str);
			return $str;
		}
        public static function generateRandomString($length){
            $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $charactersLength = strlen($characters);
            $randomString = '';
            for ($i = 0; $i < $length; $i++) {
                $randomString .= $characters[rand(0, $charactersLength - 1)];
            }
            return $randomString;
        }
        public static function getDateTime(){
            $date = date('Y-m-d H:i:s');
            return $date;
        }
        public static function createTicket($userid,$pedidoid){
            $sql = MySql::conectar()->prepare("INSERT INTO `tickets` VALUES(NULL, 0, ?, 0, 'aberto',?,?,0)");
            $date = date('Y-m-d H:i:s');
            $sql->execute(array($userid,$pedidoid,$date));
        } 
        /*
        public static function sendSMS($number,$text){

            $account_sid = 'AC4e162068c2c01c6c924adceda9f3805a';
            $auth_token = '4ffd2bfd4227b2f63d8bbf1ca393762d';

            $client = new Client($account_sid, $auth_token);
            $client->messages->create(
                $number,
                array(
                    'from' => '+13235534402',
                    'body' => $text
                )
            );
get
        }
        */
        public static function generateLogintoken(){
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ~!@#$%^&*(){}[],./?';
            $charactersLength = strlen($characters);
            $loginToken = '';
            for ($i = 0; $i < 60; $i++) {
                $loginToken .= $characters[rand(0, $charactersLength - 1)];
            }
            setcookie('loginToken', $loginToken, time() + (60*60*24*7),'/');
            return $loginToken;
        }
        public static function getRowCountDB($db,$query = null){
            $query  = $query != null ? "SELECT * FROM `$db` WHERE $query" : "SELECT * FROM `$db`";
            $sql = MySql::conectar()->prepare($query);
            $sql->execute();
            return $sql->rowCount();
        }

        public static function getInfoDB($db,$query = null){
            $query  = $query != null ? "SELECT * FROM `$db` WHERE $query" : "SELECT * FROM `$db`";
            $sql = MySql::conectar()->prepare($query);
            $sql->execute();
            if($sql->rowCount() >= 1){
                return $sql->fetch();
            }
            return false;
        }

        public static function getInfoDBAll($db,$query = null){
            $query  = $query != null ? "SELECT * FROM `$db` WHERE $query" : "SELECT * FROM `$db`";
            $sql = MySql::conectar()->prepare($query);
            $sql->execute();
            if($sql->rowCount() >= 1){
                return $sql->fetchAll();
            }
            return false;
        }
        public static function insertDB($db,$query){
            /*
            $query = "INSERT INTO `$db` VALUES(";
            for($i=0;$i<count(explode(',',$query));$i++){
                if($i == (count(explode(',', $query)))){
                    $query .= '?';
                }else{
                    $query .= '?,';
                }
            }
            $query .= ")";
            //echo $query;
            $sql = MySql::conectar()->prepare("INSERT INTO `$db` VALUES($query)");
            $sql->execute();
            */
        }
        public static function updateDB($db,$query = null){
            $query  = $query != null ? "UPDATE * FROM `$db` $query" : "SELECT * FROM `$db`";
            $sql = MySql::conectar()->prepare($query);
            $sql->execute();
            
        }
        public static function deleteFromDB($db,$query){
            $query  = $query != null ?: "DELETE FROM `$db` WHERE $query";
            $sql = MySql::conectar()->prepare($query);
            $sql->execute();
            if($sql->rowCount() >= 1){
                return $sql->fetch();
            }
            return false;
        }
        public static function retornarValorEmBR($valor){
            $valor = number_format($valor,2);
            return $valor;
        }

    }
    
?>