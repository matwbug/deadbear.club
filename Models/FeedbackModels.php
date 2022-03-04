<?php 
    namespace Models;

use MySql;
use Site;

class FeedbackModels{
        public static function getFeedbackHome(){
            $sql = MySql::conectar()->prepare("SELECT * FROM `feedbacks` WHERE `aprovado` = 1 ORDER BY `id` DESC limit 3");
            $sql->execute();
            if($sql->rowCount() >= 1){
                $feedbacks = $sql->fetchAll();
                echo '<section class="reputation w100 flex-center" style="flex-direction:column;">
                        <h2>Feedbacks</h2>
                        <div class="body flex-center">';
                foreach($feedbacks as $key => $value){
                    $feedback = $value['stars'];
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
                    $sql = MySql::conectar()->prepare("SELECT * FROM `usuarios` WHERE `id` = ?");
                    $sql->execute(array($value['id.usuario']));
                    $userInfo = $sql->fetch();
                    echo '<div class="rep-single-wrapper" id="'.$value['id'].'">
                            <div class="rep-single">
                                <img src="'.BASE.'data/images/upload/'.Site::getImageUser($userInfo['id']).'">
                                <p>'.$userInfo['username'].'</p>
                                <div class="text">
                                    <span>'.substr($value['text'],0,0).'</span>
                                </div>
                                '.$feedback.'
                            </div>  
                        </div>';
                }
                echo '</div>
                    </section>';
            }
            
        }
        public static function getFeedback(){

            $sql = MySql::conectar()->prepare("SELECT * FROM `feedbacks` WHERE `aprovado` = 1 ORDER BY `id` DESC");
            $sql->execute();
            $feedbacks = $sql->fetchAll();
            foreach($feedbacks as $key => $value){
                $imagem = $value['img'] != '' ? '<div class="text"><div><img class="clicktosee" src="'.BASE.'data/images/upload/'.$value['img'].'"></div></div>' : '';
                $feedback = $value['stars'];
                if($feedback == 1){
                    $feedback =  '<div class="stars" style="width: 100%;height: unset;border: none;"><i class="fas fa-star"></i><i class="fas fa-star die"></i><i class="fas fa-star die"></i><i class="fas fa-star die"></i><i class="fas fa-star die"></i></div>';
                }else if($feedback == 2){
                    $feedback =  '<div class="stars" style="width: 100%;height: unset;border: none;"><i class="fas fa-star"></i><i class="fas fa-star"></i></i><i class="fas fa-star die"></i><i class="fas fa-star die"></i><i class="fas fa-star die"></i></div>';
                }else if($feedback == 3){
                    $feedback =  '<div class="stars" style="width: 100%;height: unset;border: none;"><i class="fas fa-star"></i><i class="fas fa-star"></i></i><i class="fas fa-star"></i><i class="fas fa-star die"></i><i class="fas fa-star die"></i></div>';
                }else if($feedback == 4){
                    $feedback =  '<div class="stars" style="width: 100%;height: unset;border: none;"><i class="fas fa-star"></i><i class="fas fa-star"></i></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star die"></i></div>';
                }else if($feedback == 5){
                    $feedback =  '<div class="stars" style="margin:10px 0;width: 100%;height: unset;border: none;"><i class="fas fa-star"></i><i class="fas fa-star"></i></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>';
                }
                $sql = MySql::conectar()->prepare("SELECT * FROM `usuarios` WHERE `id` = ?");
                $sql->execute(array($value['id.usuario']));
                $userInfo = $sql->fetch();
                echo '<div class="single-feedback in-left">
                        <div class="img w100 flex-center"><img style="margin:0 auto;" src="'.BASE.'data/images/upload/'.Site::getImageUser($userInfo['id']).'"></div>
                        <div class="text">
                            <p><b>'.$userInfo['username'].'</b></p>
                            <span>'.substr($value['text'],0,80).'</span>
                            '.$imagem.'
                            '.$feedback.'
                        </div>                
                    </div>';
            }
        }
        public static function validarImagem($img){
            if(preg_match('/^image\/(pjpeg|jpeg|png|gif|bmp|jpg)$/', $img['type'])){
                //formato valido
                $imginfo = getimagesize($img['tmp_name']);
                if($imginfo[0] < 300 || $imginfo[1] < 300){
                    return false;
                }else{
                    return true;
                }
            }else{
                return false;
            }
        }
        public static function uploadImagem($img){
            $imginfo = getimagesize($img['tmp_name']);
            if($img['type'] == 'image/jpeg'){
                $imga = imagecreatefromjpeg($img['tmp_name']); 
                imagepalettetotruecolor($imga);
            }else if($img['type'] = 'image/png'){
                $imga = imagecreatefrompng($img['tmp_name']); 
                imagepalettetotruecolor($imga);
            }else{
                return false;
            }

            $nameFile = md5(uniqid());
            \WideImage\WideImage::load($imga)->saveToFile('../data/images/upload/'.$nameFile.'.webp');
            return BASE.'data/images/upload/'.$nameFile.'.webp';
            
        }
    }

?>