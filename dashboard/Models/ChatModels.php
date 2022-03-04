<?php 
    namespace Models;

use MySql;
use Site;

class ChatModels{
        public static function verifyExistsChat(){
            $url = explode('/',Site::getCurrentUrl()); $base = $url[1] == 'db' ? 'local' : 'online'; $key = $base == 'local' ? 4 : 3 ;
            if(isset($url[$key]) && is_numeric($url[$key])){
                $sql = MySql::conectar()->prepare("SELECT * FROM `tickets` WHERE `id` = ?");
                $sql->execute(array($url[$key]));
                if($sql->rowCount() >= 1){
                    return true;
                }else{Site::redirecionar(BASE.'dashboard/chat/?ticketnaoencontrado');}

            }
        }
        public static function getChats(){
            $sql = MySql::conectar()->prepare("SELECT * FROM `tickets` WHERE `closed` = 0");
            $sql->execute();
            if($sql->rowCount() >= 1){
                $tickets = $sql->fetchAll();
                foreach($tickets as $key => $value){
                    if($value['reivindicado'] == false){
                        echo '<div class="box-chatSingleWrapper" >
                                <div class="box-chatSingle" id="'.$value['id'].'">
                                    <div class="head">
                                        <h2><i class="fas fa-comments"></i> Ticket #'.$value['id'].'</h2>
                                        <img src="'.BASE.'data/images/upload/'.Site::getImageUser(Site::getUserInfo($value['creator_id'])['id']).'">
                                    </div>
                                    <div class="body">
                                        <button class="reivindicarTicket"> <i class="far fa-hand-paper"></i> Reivindicar</button>
                                    </div>
                                </div>
                            </div>';
                    }else{
                        echo '<div class="box-chatSingleWrapper" >
                                <div class="box-chatSingle" id="'.$value['id'].'">
                                    <div class="head">
                                        <h2><i class="fas fa-comments"></i> Ticket #'.$value['id'].'</h2>
                                        <img src="'.BASE.'data/images/upload/'.Site::getImageUser(Site::getUserInfo($value['creator_id'])['id']).'">
                                    </div>
                                    <div class="body">
                                        <button class="chat-vis-btn"><i class="fas fa-eye"></i> Visualizar</button>
                                    </div>
                                </div>
                            </div>';
                    }
                    $_SESSION['lastIdTickets'] = $value['id'];  
                    
                } 
            }else{
                echo '<div style="margin-top: 20px; text-align:center" class="alert-div-login w100">Não foi encontrado nenhum chat :/</div>';
            }
        }
        public static function notificarEmail($email,$rem,$assunto,$texto){
            $mail = new \Email('deadbear.club','admin@deadbear.club','+na2GlEm1T*V','Deadbear');
            $mail->enviarPara($email,$rem);
            $mail->formatarEmail(array('Assunto'=>$assunto,'Corpo'=>
            '
            <html>
            <head>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
            <title>Deadbear</title>
            <link rel="preconnect" href="https://fonts.googleapis.com">
            <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
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
                }
            </style>
            </head>
            <body>
                <div class="email-wrap">
                    <div style="background: #4717f6; width:100%; min-height:300px; margin:10px 0; padding: 10px; " >
                        <img style="width: 90px; height:90px; border-radius:50%;" src="'."https://deadbear.club/".'data/images/logo.webp">
                        <div style="font-weight:500; margin:10px 0;">
                        <p>Ei você recebeu uma mensagem dos administradores de Deadbear, venha ver o que eles estão dizendo</p>
                        <h4>'.$assunto.'</h4>
                        <p>'.$texto.'</p>
                        </div>
                    </div>
                </div>
            </body>
            </html>'));
            if($mail->enviarEmail()){
                return true;
            }else{
                return false;
            }
        }
        
    }
?>