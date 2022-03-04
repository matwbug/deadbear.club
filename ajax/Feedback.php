<?php 
    error_reporting(E_ALL ^ E_NOTICE); 
    @session_start();

    include('../Classes/Site.php');
    $token = @$_POST['token'];

    if(!\Site::validar_token($token)){
        $data['msg'] = 'Aconteceu algum erro, contate administração';
        $data['sucesso'] = false;
        die(json_encode($data));
    }

    $data['sucesso'] = true;
    $acao = $_POST['acao'];
    if(isset($acao)){
        require('../vendor/autoload.php');
        include('../Classes/MySql.php');
        include('../config.php');
        include('../Models/ProductsDefault.php');
        include('../Models/FeedbackModels.php');
        if($acao == 'tab-writeFeedback'){
            $id = $_POST['ticketid'];
            if(Site::getRowCountDB('feedbacks','`ticket.id`='.$id) <= 0){
                $infoTicket = Site::getInfoDB('tickets',`id=`.$id);
                $userInfo = Site::getUserInfo($_COOKIE['loginToken']);
                $data['response'] = '<div style="margin: 20px 0; border: 3px solid var(--border-color); padding:20px 0; border-radius:3px; min-height:200px; flex-direction:column;" class="flex-center tab-opened in-left w100">
                                        <h4>Olá, <b style="color:var(--border-color); margin: 0 2px;">'.$userInfo['username'].'</b> obrigado pela sua compra 💜<br> Aqui você pode dar uma avaliação sobre o que achou do atendimento e do produto entregue a você.</h4>
                                        <label for="image" class="flex-center w100" style="flex-direction:column; max-width:200px;">
                                            <div class="img"> 
                                                <img src="" style="display:none;">
                                                <div class="svg-addphoto position-center" style="width:40px; height:40px;"></div>
                                            </div>
                                            <input id="image" onchange="addImage()" type="file" accept="image/jpg,image/png,image/webp" style="display:none;">
                                        </label>
                                        <div class="stars" style="font-size:25px;">
                                            <div class="fas fa-star" data-rating="1" ></div>
                                            <div class="fas fa-star" data-rating="2" ></div>
                                            <div class="fas fa-star" data-rating="3" ></div>
                                            <div class="fas fa-star" data-rating="4" ></div>
                                            <div class="fas fa-star selected" data-rating="5"></div>
                                        </div>
                                        <textarea class="w100"></textarea>
                                        <button js="submitFeedback">Enviar</button>
                                    </div>';
            }else{
                $info = Site::getInfoDB('feedbacks','`ticket.id`='.$id);
                $rating = intval($info['stars']);
                $rate='<div class="stars" style="font-size:25px;">';
                $rest = (5 - $rating);
                for($i=1; $i<=$rating; $i++){
                    $rate .= '<div class="fas fa-star" data-rating="'.$i.'" ></div>';
                }
                if($rest != 0){
                    for($i=0; $i<$rest; $i++){
                        $rate .= '<div class="fas fa-star die" data-rating="'.$i.'" ></div>'; 
                    }
                }
                $rate .= '</div>';
                $data['response'] = '<div style="margin: 20px 0; border: 3px solid var(--border-color); padding-bottom:20px; border-radius:3px; min-height:200px; flex-direction:column;" class="flex-center tab-opened in-left w100">
                                        <div class="w100 flex-center" style="padding:10px;margin-bottom:10px;background: var(--border-color);"><p>Sua avaliação já foi enviada e está sendo analisada!</p></div>
                                        <div class="img"> 
                                            <img src="'.BASE.'data/images/upload/'.$info['img'].'">
                                        </div>
                                        '.$rate.'
                                        <div class="text"><span>'.$info['text'].'</span></div>
                                    </div>';
            }
        }else if($acao == 'addImage'){
            $img = $_FILES['file'];
            if(\Models\FeedbackModels::validarImagem($img)){
                if($img = \Models\FeedbackModels::uploadImagem($img)){
                    $data['image'] = $img;
                }else{
                    $data['msg'] = 'Aconteceu algum erro ao enviar a imagem.';
                    $data['sucesso'] = false;
                    die(json_encode($data));  
                }
            }else{
                $data['msg'] = 'Imagem inválida tente usar uma com dimensões maiores que 300 ou tente outro formato de imagem.';
                $data['sucesso'] = false;
                die(json_encode($data));  
            }

        }else if($acao == 'insertFeedback'){
            $ticketid = $_POST['ticketid'];
            if(Site::getRowCountDB('feedbacks','`ticket.id`='.$ticketid) <= 0){
                foreach($_POST as $key => $value){
                    if($value == '' || $value == null){
                        $data['msg'] = 'Você precisa preencher tudo para enviar seu feedback.';
                        $data['sucesso'] = false;
                        die(json_encode($data));
                    }
                }
                $img = $_POST['img'];
                $texto = strip_tags($_POST['texto']);
                $rating = isset($_POST['rating']) && is_numeric($_POST['rating']) ? $_POST['rating'] : 5;
                $date = date('Y-m-d H:i:s');
                $userInfo = Site::getUserInfo($_COOKIE['loginToken']);
                $sql = MySql::conectar()->prepare("INSERT INTO `feedbacks` VALUES(null,?,?,?,?,?,0,'$date',null,0)");
                if($sql->execute(array($userInfo['id'],$ticketid,$texto,$img,$rating))){
                     $data['msg'] = 'Seu feedback foi enviado com sucesso, agradecemos por sua avaliação! 🙏';
                }else{
                    $data['msg'] = 'Aconteceu algum erro enviar seu feedback, contate administração!';
                    $data['sucesso'] = false;
                }
            }else{
                $data['msg'] = 'Sua avaliação para esse ticket já foi enviada! 🤔';
                $data['sucesso'] = true;
            }
            
        }
        die(json_encode($data));

    }else{
        $data['msg'] = 'Aconteceu algum erro.';
        $data['sucesso'] = false;
        die(json_encode($data));  
    }


    

?>