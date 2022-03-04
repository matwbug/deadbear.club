<?php 
    namespace Controllers;

    class PerfilController{
        public function __construct()
        {
            $this->view = new \Views\MainView('perfil',array(
                'titulo'=>'deadbear | editando meu perfil',
                'desc'=>'Painel Admin',
                'tags'=>'deadbear'
            ));
        }
        public function index(){
            $this->view->renderTemplate();
        }
    }
?>