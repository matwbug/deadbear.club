<?php 
    namespace Controllers;
    class RegistrarController extends Controller{
        public function __construct()
        {
            $url = explode('/',$_GET['url']);
            if(@$url[1] == 'verificar-email'){
                $this->view = new \Views\MainView('confirmar-email',array(
                    'titulo'=>'Confirmação de e-mail',
                    'desc'=>'Nosso site oferece uma variedade de contas steam por um preço que você não verá em outro lugar, venha e converse com um de nossos atendentes!',
                    'tags'=>'steam, steam market, steam accounts, steam accounts for sale, contas steam, contas steam comprar, comprar contas steam, contas steam, comprar'
                ));
            }else{
                $this->view = new \Views\MainView('registro',array(
                    'titulo'=>'Registro em Deadbear',
                    'desc'=>'Nosso site oferece uma variedade de contas steam por um preço que você não verá em outro lugar, venha e converse com um de nossos atendentes!',
                    'tags'=>'steam, steam market, steam accounts, steam accounts for sale, contas steam, contas steam comprar, comprar contas steam, contas steam, comprar'
                ));
            }
        }
        public function index(){
            $this->view->renderTemplate();
        }
    }
?>