<?php 
    namespace Models;

    use MySql;
    use Admin;
    use WideImage;
    use Email;
    use Site;

class AdminModels{
        public static function validarImage($img){
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
        public static function uploadImage($img){
            $nameFile = md5(uniqid());
            if($img['type'] == 'image/jpeg'){
                $imga = imagecreatefromjpeg($img['tmp_name']); 
                imagepalettetotruecolor($imga);
            }else if($img['type'] = 'image/png'){
                $imga = imagecreatefrompng($img['tmp_name']); 
                imagepalettetotruecolor($imga);
            }else{
                return false;
            }
            \WideImage\WideImage::load($imga)->saveToFile('../data/images/upload/'.$nameFile.'.webp');
            
            return $nameFile;
        }

        public static function adicionarAdministrador($email,$username){
            $code = Site::generateRandomString(6);
            $mail = new \Email('deadbear.club','admin@deadbear.club','+na2GlEm1T*V','Deadbear');
            $mail->enviarPara($email,'Olá '.$username);
            $mail->formatarEmail(array('Assunto'=>'Ative sua conta administradora em deadbear','Corpo'=>
            '
            <html>
            <head>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                <title>Deadbear</title>
            <style>
                *{color:black;}
                .email-wrap{
                    text-align:center;
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
                }
            </style>
            </head>
            <body>
                <div class="email-wrap">
                
                    <h1>Olá seja bem vindo a administração Deadbear, a loja mais confiável do brasil para compra e venda de contas steam!</h1>
                    <span>Para concluir seu cadastro no dashboard acesse este link</span>
                    <br><br>
                    <button style="width:100%;"><a style="text-decoration:none; color:white;" href="'.ADMIN.'login?registrar-admin=1&code='.$code.'">Ativar</a></button>
                </div>
            </body>
            </html>'));
            $mail->enviarEmail();
        }
        public static function enviarEmail($text,$remetenteid){
            $mail = new \Email('deadbear.club','admin@deadbear.club','+na2GlEm1T*V','Deadbear');
            if($remetenteid == 'all'){
                $users = Site::getInfoDBAll('usuarios');
                foreach($users as $key => $value){
                    $mail->enviarPara($value['email'],'Olá '.$value['username']);
                    $mail->formatarEmail(array('Assunto'=>'Ative sua conta administradora em deadbear','Corpo'=>
                    '
                    <html>
                    <head>
                    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                        <title>Deadbear</title>
                    </head>
                    <body>
                        <div class="email-wrap">
                            '.$text.'
                        </div>
                    </body>
                    </html>'));
                    $mail->enviarEmail();
                }
            }else{
                $userInfo = Site::getInfoDB('usuarios','`id`="'.$remetenteid.'"');
                $mail->enviarPara($userInfo['email'],'Olá '.$userInfo['username']);
                $mail->formatarEmail(array('Assunto'=>'Ative sua conta administradora em deadbear','Corpo'=>
                '
                <html>
                <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                    <title>Deadbear</title>
                </head>
                <body>
                    <div class="email-wrap">
                        '.$text.'
                    </div>
                </body>
                </html>'));
                $mail->enviarEmail();
            }
            
        }

    }

?>