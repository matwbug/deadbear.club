<?php 
    namespace Controllers;

    class FeedbacksController{
        public function __construct(){
            $this->view = new \Views\MainView('feedbacks',array(
                'titulo'=>'Deadbear | Feedbacks',
                'desc'=>'Veja o que cada cliente disse sobre nossos produtos :)',
                'tags'=>'steam, steam market, steam accounts, steam accounts for sale, contas steam, contas steam comprar, comprar contas steam, contas steam, comprar'
            ));
        }
        public function index(){
            $this->view->renderTemplate();
        }
    }
?>