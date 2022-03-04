<?php 
    namespace Controllers;
    use Site;
    class ChatController{
        public function __construct()
        {
            $url = explode('/', Site::getCurrentUrl());
            if(\Models\ChatModels::verifyExistsChat()){
                $this->view = new \Views\MainView('chat-single',array(
                    'titulo'=>'deadbear | dashboard',
                    'desc'=>'deadbear dashboard',
                    'tags'=>'deadbear dashboard'
                ));
            }else{
                $this->view = new \Views\MainView('chats',array(
                    'titulo'=>'deadbear | dashboard',
                    'desc'=>'deadbear dashboard',
                    'tags'=>'deadbear dashboard'
                ));
            }
            
        }
        public function index(){
            $this->view->renderTemplate();
        }
    } 
?>