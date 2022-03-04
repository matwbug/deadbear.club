<?php 
    namespace Models;
    use MySql;
    use Site;
    use WideImage;
class ChatModels{

        public static function userHaveasTicketOn(){
            if(!isset($_COOKIE['loginToken'])){return false;}
            $userid = Site::getUserInfo($_COOKIE['loginToken'])['id'];
            
            if(Site::getRowCountDB('tickets','`creator_id`="'.$userid.'" AND `closed`=0') >= 1){
                return true;
            }
            return false;
        }
        public static function ImagemValida($img){
            if(preg_match('/^image\/(pjpeg|jpeg|png|gif|bmp|jpg)$/', $img['type'])){
               return true;
            }
            return false;
        }
        public static function uploadImage($img){
            $nameFile = md5(uniqid());
            if($img['type'] == 'image/jpeg'){
                $imga = imagecreatefromjpeg($img['tmp_name']); 
                imagepalettetotruecolor($imga);
            }else if($img['type'] = 'image/png'){
                $imga = imagecreatefrompng($img['tmp_name']); 
                imagepalettetotruecolor($imga);
            }else if($img['type'] == 'image/gif'){
                $imga = imagecreatefromgif($img['tmp_name']);
                imagepalettetotruecolor($imga);
            }else{
                return false;
            }
            \WideImage\WideImage::load($imga)->saveToFile('../data/users/msg/'.$nameFile.'.webp');
            return $nameFile.'.webp';
        }
        public static function mensagensNaolidas(){
            $lastId = isset($_SESSION['lastId']) ? $_SESSION['lastId'] : false;
            if(!$lastId){return false;}
            $sql = MySql::conectar()->prepare("SELECT * FROM `tickets` WHERE `creator_id` = ? AND `closed` = 0");
            $sql->execute(array(Site::getUserInfo($_COOKIE['loginToken'])['id']));
            $ticketid = $sql->fetch()['id'];
            $penis = MySql::conectar()->prepare("SELECT * FROM `tickets.msg` WHERE `ticket_id` = ? AND `id` > ?");
            $penis->execute(array($ticketid,$lastId));
            $Nmensagens = $penis->rowCount();
            if($Nmensagens >= 1){
                return '('.$Nmensagens.')';
            }
        }
        
    }
?>