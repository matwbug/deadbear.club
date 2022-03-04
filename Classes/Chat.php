<?php 
    class Chat{
       
        public static function insertMessage($ticketid,$type,$content,$userid){
            $date = date('Y-m-d H:i:s');
            $sql = MySql::conectar()->prepare("INSERT INTO `tickets.msg` VALUES(null,$ticketid,'$userid','$type','$content',1,?)");
            if($sql->execute(array($date))){
                return MySql::conectar()->lastInsertId();
            }
            return false;
        }
        public static function messageDeadbear($identification=null,$msg){
            $msg = '<div title="Mensagem enviada por um robô" class="ticket-message bot flex-center-notresize '.$identification.' direction-row w100 direction-row" style="justify-content:flex-start; flex-wrap:nowrap; align-items:flex-start;">
                        <div class="flex-center direction-column"> 
                            <img class="avatarPhoto" src="'.BASE.'data/images/logo.png">
                        </div>
                        <div class="flex-center-notresize" style="justify-content:flex-start; margin-left:10px;">
                            <div class="w100 flex-center-notresize" style="justify-content: flex-start;">
                            <p>Bot Deadbear <i class="material-icons" style="font-size:10px!important">warning</i></p>
                                <span class="time flex-center-notresize">'.self::returnFormatedDate().'</span>
                            </div>
                            <div class="flex-center">
                                <span class="content">'.$msg.'</span>
                            </div>
                        </div>
                    </div>';
            return $msg;
        }

        public static function returnFormatedDate($date = null){
            $date = isset($date) ? $date : date('Y-m-d H:i:s');
            if(date('Y-m-d',strtotime($date)) == date('Y-m-d',strtotime("now"))){
               $returned = 'Hoje às '.date('H:i', strtotime($date));
            }else if($date == date('Y-m-d H:i:s',strtotime("yesterday"))){
                $returned = 'Ontem '.date('H:i', strtotime($date));
            }else{
                $returned = date('d/m/Y', strtotime($date)).'<b class="escondido"> '.date('H:i', strtotime($date)).'</b>';
            }
            return $returned;
        }
    }   

?>