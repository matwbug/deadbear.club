<?php 
    class Application{
        public function run(){
            $url = explode('/',Site::getCurrentUrl()); $base = $url[1] == 'db' ? 'local' : 'online'; $key = $base == 'local' ? 3 : 2 ;
            $url = isset($_GET['url']) ? explode('/', Site::getCurrentUrl())[$key] : 'Home';
            $url = strtok(ucfirst($url), '?');
            $url.='Controller';
            if(!Admin::logado()){
                $className = 'Controllers\\LoginController';
                $controller = new $className();
                $controller->index();  
            }
            elseif(file_exists('Controllers/'.$url.'.php')){
                $className = 'Controllers\\'.$url;
                $controller = new $className();
                $controller->index();
            }else{
                $className = 'Controllers\\HomeController';
                $controller = new $className();
                $controller->index();
            }
        }
    }

?>