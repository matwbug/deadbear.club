<?php 
    class Application
    {
        public function run(){
            if(isset($_GET['logout'])){
                Site::logout();
            }
            $url = explode('/',Site::getCurrentUrl()); $base = $url[1] == 'db' ? 'local' : 'online'; $key = $base == 'local' ? 2 : 1 ;
            $url = strtok($url[$key],'?');
            if($url != ''){
                $url = ucfirst(strtolower($url));
                $url.="Controller";
            }else{
                $url = 'Home';
                $url.="Controller";
            }
            if(Site::checkManutencao()){ // manutenção
                $className = 'Controllers\\ManutencaoController';
                $controller = new $className();
                $controller->index();
            }else if(Usuario::checkHasUserBanned()){
                $className = 'Controllers\\BannedController';
                $controller = new $className();
                $controller->index();
            }else if(file_exists('Controllers/'.$url.'.php')){
                $className = 'Controllers\\'.$url;
                $controller = new $className();
                $controller->index();
            }else if(\Models\ProductsDefault::checkUrlIsCategory()){
                $className = 'Controllers\\CategoryController';
                $controller = new $className();
                $controller->index();
            }else{
                $className = 'Controllers\\ErroController';
                $controller = new $className();
                $controller->index();
            }

        }
    }

?>