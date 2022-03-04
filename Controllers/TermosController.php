<?php 
    namespace Controllers;
    class TermosController extends Controller{
        public function __construct(){
            $this->view = new \Views\MainView('termos',array(
                'titulo'=>'Deadbear | Market',
                'desc'=>'Termos que você precisa saber antes de fazer uma compra.',
                'tags'=>'steam, steam market, steam accounts, steam accounts for sale, contas steam, contas steam comprar, comprar contas steam, contas steam, comprar'
            ));
        }
        public function index(){
            $this->view->renderTemplate();
        }
    }
?>