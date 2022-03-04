<?php 
    namespace Controllers;
    class LoginController extends Controller{
        public function __construct(){
            $url = explode('/',$_GET['url']);
            if(@$url[1] == 'resetar-senha'){
                if(isset($_GET['code'])){
                    $this->view = new \Views\MainView('resetar-senha-code',array(
                        'titulo'=>'Recuperar conta | Deadbear',
                        'desc'=>'Nosso site oferece uma variedade de contas steam por um preço que você não verá em outro lugar, venha e converse com um de nossos atendentes!',
                        'tags'=>'steam, steam market, steam accounts, steam accounts for sale, contas steam, contas steam comprar, comprar contas steam, contas steam, comprar'
                    ));   
                }else if(isset($_GET['certifiedToken'])){
                    $this->view = new \Views\MainView('resetar-senha-token',array(
                        'titulo'=>'Recuperar conta | Deadbear',
                        'desc'=>'Nosso site oferece uma variedade de contas steam por um preço que você não verá em outro lugar, venha e converse com um de nossos atendentes!',
                        'tags'=>'steam, steam market, steam accounts, steam accounts for sale, contas steam, contas steam comprar, comprar contas steam, contas steam, comprar'
                    ));   
                }else{
                    $this->view = new \Views\MainView('resetar-senha',array(
                        'titulo'=>'Recuperar conta | Deadbear',
                        'desc'=>'Nosso site oferece uma variedade de contas steam por um preço que você não verá em outro lugar, venha e converse com um de nossos atendentes!',
                        'tags'=>'steam, steam market, steam accounts, steam accounts for sale, contas steam, contas steam comprar, comprar contas steam, contas steam, comprar'
                    ));
                }
            }else{
                $this->view = new \Views\MainView('login',array(
                    'titulo'=>'Log in Deadbear',
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