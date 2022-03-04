<?php 
    namespace Controllers;

    class CategoryController{
        public function __construct()
        {
            $this->view = new \Views\MainView('categoria',array(
                'titulo'=>'Deadbear | ${categoria}',
                'desc'=>'${desc.categoria}',
                'tags'=>'steam, steam market, steam accounts, steam accounts for sale, contas steam, contas steam comprar, comprar contas steam, contas steam, comprar'
            ));
        }
        public function index() {
            $this->view->renderTemplate();
        }
    }

?>