<?php 
    namespace Controllers;
    class ItemController extends Controller{
        public function __construct()
        {
            if(\Models\ProductsDefault::checkItemExists()){
                $this->view = new \Views\MainView('item',array(
                    'titulo'=>'Deadbear | Produtos ',
                    'desc'=>'Nosso site oferece uma variedade de contas steam por um preço que você não verá em outro lugar, venha e converse com um de nossos atendentes!',
                    'tags'=>'steam, steam market, steam accounts, steam accounts for sale, contas steam, contas steam comprar, comprar contas steam, contas steam, comprar'
                ));
            }else{
                $this->view = new \Views\MainView('itens',array(
                    'titulo'=>'Deadbear | Produtos ',
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