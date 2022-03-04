<?php 
    namespace Controllers;
    class BannedController{
        public function __construct()
        {
        $this->view = new \Views\MainView('banido',array(
            'titulo'=>'Sua conta foi banida!',
            'desc'=>'Sua conta não tem mais acesso ao site.',
            'tags'=>'steam, steam market, steam accounts, steam accounts for sale, contas steam, contas steam comprar, comprar contas steam, contas steam, comprar'
        ));
        }   
        public function index(){
            $this->view->renderNoHeader();
        }
    }
?>