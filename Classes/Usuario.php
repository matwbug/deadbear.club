<?php 
    class Usuario{ // classe pra colocar funções repetitivas de usuario
        public static function isOnline($id){
            $lastactivity = Site::getInfoDB('usuarios.online','`id.usuario`="'.$id.'"')['lastactivity'];
            if((strtotime($lastactivity) + 15) > time()){
                return true;
            }else{
                return false;
            }
        }
        public static function banFromId($id,$motivo){
            $sql = MySql::conectar()->prepare("SELECT * FROM `usuarios.banidos` WHERE `id.account` = $id");
            $sql->execute();
            if($sql->rowCount() >= 1){
                return false;
            }else{
                $sql = MySql::conectar()->prepare("INSERT INTO `usuarios.banidos` VALUES(null,?,?)");
                $sql->execute(array($id,$motivo));
                return true;
            }

        }
        public static function checkHasUserBannedByID($iduser){
            if(Site::getRowCountDB('usuarios.banidos','`id.account`="'.$iduser.'"') >= 1){
                return true;
            }
            return false;
        }
        public static function UnbanFromId($id){
            $sql = MySql::conectar()->prepare("DELETE FROM `usuarios.banidos` WHERE `id.account` = '$id'");
            $sql->execute();
        }
        public static function getNumbersOfOrderByID($iduser){
            return Site::getRowCountDB('pedido.cart.users','`user.id`="'.$iduser.'"');
        }
        public static function getNumbersOfRefsByID($iduser){
            return Site::getRowCountDB('reffers','`refferencer.id`="'.$iduser.'"');
        }
        public static function checkHasUserBanned(){
            if(!isset($_COOKIE['loginToken'])){return false;}
            $iduser = Site::getUserInfo($_COOKIE['loginToken'])['id'];
            if(Site::getRowCountDB('usuarios.banidos','`id.account`="'.$iduser.'"') >= 1){
                return true;
            }
            return false;
        }
    }
?>