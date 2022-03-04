<?php 
    namespace Controllers;
    use Models\CarrinhoModels;
    class CarrinhoController{
        public function __construct()
        {
            if(CarrinhoModels::verifyUserHaveCart()){
                $this->view = new \Views\MainView('carrinho',array(
                    'titulo'=>'Deadbear | Carrinho',
                    'desc'=>'Nosso site oferece uma variedade de contas steam por um preço que você não verá em outro lugar, venha e converse com um de nossos atendentes!',
                    'tags'=>'steam, steam market, steam accounts, steam accounts for sale, contas steam, contas steam comprar, comprar contas steam, contas steam, comprar'
                ));
            }else{
                $this->view = new \Views\MainView('carrinho-sem-carrinho',array(
                    'titulo'=>'Deadbear | Carrinho',
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