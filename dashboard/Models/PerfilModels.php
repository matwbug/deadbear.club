<?php 
    namespace Models;
    
    use Admin;
    use Site;
    use MySql;

    class PerfilModels{
        public static function validarProfileImage($img){
            if(preg_match('/^image\/(pjpeg|jpeg|png|jpg)$/', $img['type'])){
                //formato valido
                $imginfo = getimagesize($img['tmp_name']);
                if($imginfo[0] < 500 || $imginfo[1] < 500){
                    return false;
                }else{
                    return true;
                }
            }else{
                return false;
            }
        }
        public static function uploadProfileImage($img){
            $nameFile = md5(uniqid());
            $imginfo = getimagesize($img['tmp_name']);
            $width = 500 - $imginfo[0];
            $height = 500 - $imginfo[1];
            if($img['type'] == 'image/jpeg'){
                $imga = imagecreatefromjpeg($img['tmp_name']); 
                imagepalettetotruecolor($imga);
            }else if($img['type'] = 'image/png'){
                $imga = imagecreatefrompng($img['tmp_name']); 
                imagepalettetotruecolor($imga);
            }else{
                return false;
            }
            
            \WideImage\WideImage::load($imga)->crop($width,$height)->saveToFile('../Data/Images/upload/'.$nameFile.'.webp');
            
             
            $adminid = Admin::getUserInfo($_COOKIE['admin_loginToken'])['id'];
            $sql = MySql::conectar()->prepare("SELECT * FROM `admin` WHERE `id` = ?");
            $sql->execute(array($adminid));
            $info = $sql->fetch();
            if($info['img.perfil'] != null){
                $imgNameAnt = $info['img.perfil'];
                unlink('../Data/Images/upload/'.$imgNameAnt);
            }
            
            $sql = MySql::conectar()->prepare("UPDATE `admin` SET `img.perfil` = ? WHERE `id` = ?");
            if($sql->execute(array($nameFile.'.webp',$adminid))){
                return $nameFile;
            }else{return false;}
            
        }
    }

?>