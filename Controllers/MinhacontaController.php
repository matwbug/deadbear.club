<?php 
    namespace Controllers;
    use Site;
    class MinhacontaController{
        public function __construct()
        {
            if(Site::logado()){
                $this->view = new \Views\MainView('minhaconta',array(
                    'titulo'=>'Minha conta | Deadbear',
                    'desc'=>'Nosso site oferece uma variedade de contas steam por um preço que você não verá em outro lugar, venha e converse com um de nossos atendentes!',
                    'tags'=>'steam, steam market, steam accounts, steam accounts for sale, contas steam, contas steam comprar, comprar contas steam, contas steam, comprar'
                ));
            }else{
                Site::redirecionar(BASE.'login?ref=minhaconta/');                
            }
            
        }
        public function index(){
            $this->view->renderTemplate ();
        }
    }

?>