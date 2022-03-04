<?php 
    namespace Models;

    use MySql;
    use Site;
    use WideImage;
    use Admin;

    class ContaModels{
        public static function getTickets(){
            $userid = Site::getUserInfo($_COOKIE['loginToken'])['id'];
            $sql = MySql::conectar()->prepare("SELECT * FROM `pedido.cart.users` WHERE `creator_id` = ?");
            $sql->execute(array($userid));
            if($sql->rowCount() >= 1){

                $pedidos = $sql->fetchAll();
                echo '<div class="head">
                        <div>Pedido <i class="fas fa-store"></i></div>
                        <div>Status</div>
                        <div></div>
                        <div>Atendido por</div>
                    </div>';
                foreach($pedidos as $key => $value){
                    $feedback = MySql::conectar()->prepare("SELECT * FROM `feedbacks` WHERE `ticket.id` = ?");
                    $feedback->execute(array($value['id']));
                    if($feedback->rowCount() >= 1){
                        $feedback = $feedback->fetch()['stars'];
                        if($feedback == 1){
                            $feedback =  '<div class="stars" style="width: unset;height: unset;border: none;"><i class="fas fa-star"></i><i class="fas fa-star die"></i><i class="fas fa-star die"></i><i class="fas fa-star die"></i><i class="fas fa-star die"></i></div>';
                        }else if($feedback == 2){
                            $feedback =  '<div class="stars" style="width: unset;height: unset;border: none;"><i class="fas fa-star"></i><i class="fas fa-star"></i></i><i class="fas fa-star die"></i><i class="fas fa-star die"></i><i class="fas fa-star die"></i></div>';
                        }else if($feedback == 3){
                            $feedback =  '<div class="stars" style="width: unset;height: unset;border: none;"><i class="fas fa-star"></i><i class="fas fa-star"></i></i><i class="fas fa-star"></i><i class="fas fa-star die"></i><i class="fas fa-star die"></i></div>';
                        }else if($feedback == 4){
                            $feedback =  '<div class="stars" style="width: unset;height: unset;border: none;"><i class="fas fa-star"></i><i class="fas fa-star"></i></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star die"></i></div>';
                        }else if($feedback == 5){
                            $feedback =  '<div class="stars" style="width: unset;height: unset;border: none;"><i class="fas fa-star"></i><i class="fas fa-star"></i></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>';
                        }
                    }else{
                        $feedback = '<span>Ainda não avaliado</span>';
                    }

                    if($value['status'] == 'aberto'){
                        $status = '<span class="aberto">Aberto</span>';
                    }else if($value['status'] == 'pausado'){
                        $status = '<span class="pausado">Pausado</span>';
                    }else if($value['status'] == 'fechado'){
                        $status = '<span class="finalizado">Finalizado</span>';
                    }
                    if($value['reivindicado']){
                        $reivindicado = '<img src="'.Admin::getProfilePhotoId($value['reivindicado_id']).'"> <span>'.Admin::getUserInfoId($value['reivindicado_id'])['username'].'</span>';
                    }else{
                        $reivindicado = '<span>Ninguém atendeu seu pedido ainda.</span>';
                    }
                   
                    echo '<div class="body">
                            <div class="single">
                                <div class="id" dataid="'.$value['id'].'"><button class="slideInfo"><i class="fas fa-plus-circle"></i></button><span>Pedido #'.$value['id'].'</span></div>
                                <div class="status">'.$status.'</div>
                                <div class="name"><button><i class="fas fa-external-link-alt"></i> Ver pedido</button></div>
                                <div class="avatar">'.$reivindicado.'</div>
                            </div>
                        </div>';
                }
            }else{
                echo '<div class="w100 center" style="background:#2596be; margin: 20px 0; border-radius:3px; text-align:center;"><i class="fas fa-times"></i> Você não fez nenhum pedido ainda.</div>';
            }
        }
        public static function validarProfileImage($img){
            if(preg_match('/^image\/(pjpeg|jpeg|png|gif|bmp|jpg)$/', $img['type'])){
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
            
            \WideImage\WideImage::load($imga)->crop($width,$height)->saveToFile('../data/images/upload/'.$nameFile.'.webp');
            
             
            $userid = Site::getUserInfo($_COOKIE['loginToken'])['id'];
            $sql = MySql::conectar()->prepare("SELECT * FROM `usuarios.img` WHERE `id.usuario` = ?");
            $sql->execute(array($userid));
            if($sql->rowCount() >= 1){
                $imgNameAnt = $sql->fetch()['name'];
                unlink('../data/images/upload/'.$imgNameAnt);
            }
            
            $sql = MySql::conectar()->prepare("DELETE FROM `usuarios.img` WHERE `id.usuario` = ?");
            if($sql->execute(array($userid))){
                $sql = MySql::conectar()->prepare("INSERT INTO `usuarios.img` VALUES(null,?,?)");
                if($sql->execute(array($nameFile.'.webp',$userid))){
                    return $nameFile;
                }else{return false;}
            }else{return false;}
            
        }
    }

?>