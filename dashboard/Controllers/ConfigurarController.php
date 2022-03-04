<?php 
    namespace Controllers;
    class ConfigurarController{
        public function __construct()
        {
            $this->view = new \Views\MainView('configuracoes',array(
                'titulo'=>'deadbear | configuracoes',
                'desc'=>'deadbear dashboard',
                'tags'=>'deadbear dashboard'
            ));
        }
        public function index(){
            $this->view->renderTemplate();
        }
    } 
?>