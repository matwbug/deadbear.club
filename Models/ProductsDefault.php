<?php 
namespace Models;

use MySql;
use Site;

class ProductsDefault{
        public static function checkUrlIsCategory(){
            $url = explode('/',Site::getCurrentUrl()); $base = $url[1] == 'db' ? 'local' : 'online'; $key = $base == 'local' ? 2 : 1 ;
            $sql = MySql::conectar()->prepare("SELECT * FROM `produtos.default.categorias` WHERE `slug` = ? AND `status` = 1");
            $sql->execute(array($url[$key]));
            if($sql->rowCount() >= 1){
                return true;
            }else{
                return false;
            }
        }
        public static function checkItemExists(){
            $url = explode('/',Site::getCurrentUrl()); $base = $url[1] == 'db' ? 'local' : 'online'; $key = $base == 'local' ? 3 : 2 ;
            if(isset($url[$key]) && $url[$key] != ''){
                $sql = MySql::conectar()->prepare("SELECT * FROM `produtos.default` WHERE `slug` = ?");
                $sql->execute(array(@$url[$key]));
                if($sql->rowCount() >= 1){
                    return true;
                }else{
                    return false;
                }
            }else{
                return false;
            }
        }
        public static function getImageProductFromID($id){
            $sql = MySql::conectar()->prepare("SELECT * FROM `produtos.imagens` WHERE `id.produto` = ?");$sql->execute(array($id));
            if($sql->rowCount() >= 1){
                $imagens = $sql->fetchAll();
                return $imagens;
            }else{
                return false;
            }
        }
        public static function getProducts(){
            $sql = MySql::conectar()->prepare("SELECT * FROM `produtos.default` WHERE `status` = 1");
            $sql->execute();
            if($sql->rowCount() >= 1){
                $itens = $sql->fetchAll();
                foreach($itens as $key => $value){
                    $imagem = self::getImageProductFromID($value['id']) != '' ? 'upload/'.self::getImageProductFromID($value['id'])[0]['name'] : 'nada.webp';
                    echo '<div class="single-div" slug="'.$value['slug'].'">
                            <p>'.strtoupper(substr($value['nome'],0,30)).'</p>
                            <img src="'.BASE.'data/images/'.$imagem.'">
                            <button link="item/'.$value['slug'].'"><span class="material-icons">shopping_cart</span> <span>Comprar</span> </button>
                        </div>';
                }
            }else{
                echo '<div class="w100" style="background: var(--border-color); padding: 10px;border-radius: 3px;margin: 10px 0;v"><span>Não foi encontrado nada.</span></div>';
            }

        }
        
        public static function getInfoProduct(){
            $slug = explode('/',$_GET['url'])[1];
            $sql = MySql::conectar()->prepare("SELECT * FROM `produtos.default` WHERE `slug` = ? AND `status` = 1");
            $sql->execute(array($slug));
            if($sql->rowCount() >= 1){
                $sql = $sql->fetch();
                return $sql;
            }else{
                return false;
            }
        }
        public static function getInfoProductByID($id){
            $sql = MySql::conectar()->prepare("SELECT * FROM `produtos.default` WHERE `id` = ? AND `status` = 1");
            $sql->execute(array($id));
            if($sql->rowCount() >= 1){
                $sql = $sql->fetch();
                return $sql;
            }else{
                return false;
            }
        }
        public static function getCartsFromPedidoId($id){
            $carts = explode(',',Site::getInfoDB('pedido.cart.users', '`id`='.$id)['carts.id']);
            $cartarray = array();
            foreach($carts as $key => $value){
                $cartInfo = Site::getInfoDB('cart.users', '`id`='.$value);
                $cartarray[] = $cartInfo;
            }
            return $cartarray;
                    
        }
        public static function getProductsCategory(){
            $url = explode('/',Site::getCurrentUrl()); $base = $url[1] == 'db' ? 'local' : 'online'; $key = $base == 'local' ? 2 : 1 ;
			$slug = explode('/',Site::getCurrentUrl())[$key];
            $sql = MySql::conectar()->prepare("SELECT * FROM `produtos.default.categorias` WHERE `slug` = ?");
			$sql->execute(array($slug));
			$id = $sql->fetch()['id'];
            $sql = MySql::conectar()->prepare("SELECT * FROM `produtos.default` WHERE `categoria_id` = ? AND `status` = 1");
            $sql->execute(array($id));
            if($sql->rowCount() > 0){
                $anuncios = $sql->fetchAll();
                foreach($anuncios as $key => $value){
                    $imagem = self::getImageProductFromID($value['id']) ? 'upload/'.self::getImageProductFromID($value['id'])[0]['name'] : 'nada.webp';
                    echo '<div class="single-div" js="clickProduto" slug="'.$value['slug'].'">
                            <p>'.substr($value['nome'],0,30).'</p>
                            <img src="'.BASE.'data/images/'.$imagem.'">
                            <span>'.substr($value['desc'],0,100).'</span>
                            <button><span class="material-icons">shopping_cart</span> <span>Comprar</span></button>
                        </div>';
                }
            }else{
                echo '<div style="margin:40px 0; display:flex; justify-content:center; align-content:center; flex-direction:column;">
                        <h2>Ainda não há nada nessa categoria 😯</h2>
                        <img style="max-width:100%; margin:0 auto;width:200px;" src="'.BASE.'data/images/logo.webp">
                        <div class="errorPage"><a href="'.BASE.'"><button style="width: 100%; max-width: 200px;">Voltar</button></div>
                    </div>';
            }

		}
        public static function getImageCategoriaFromID($id){
            $sql = MySql::conectar()->prepare("SELECT * FROM `produtos.default.categorias` WHERE `id` = ? LIMIT 3"); $sql->execute(array($id));
            return $sql->fetch()['img'];
        }
        public static function getCategoriasHome(){
            $sql = MySql::conectar()->prepare("SELECT * FROM `produtos.default.categorias` LIMIT 3"); $sql->execute();
            $catProdutos = $sql->fetchAll();
            foreach($catProdutos as $key => $value){
                $cats[] = '<div class="box-produtos-single flex-center" slug="'.$value['slug'].'" title="'.$value['nome'].'">
                            <p>'.substr($value['nome'],0,30).'</p>
                            <img src="'.BASE.'data/images/upload/'.self::getImageCategoriaFromID($value['id']).'">
                            <a href="'.BASE.$value['slug'].'"><button class="btn-hover">Acessar</button></a>
                    </div><!--box-produtos-single-->';
            }
            return $cats;
        }
        public static function moreProducts(){
            $slug = explode('/',$_GET['url'])[1];
            $randomNumber = rand(1,intval(Site::getRowCountDB('produtos.default','status = 1') - 2));
            $sql = MySql::conectar()->prepare("SELECT * FROM `produtos.default` WHERE `slug` != ? AND `status` = 1 LIMIT 2 OFFSET $randomNumber");
            $sql->execute(array($slug));
            if($sql->rowCount() >= 1){
                
                $products = $sql->fetchAll();
                echo '<div class="more-products in-left">
                        <h3>Você também pode se interessar</h3>
                        <div class="inside flex-center">';
                foreach($products as $key => $value){
                    $imagem = self::getImageProductFromID($value['id']) ? 'upload/'.self::getImageProductFromID($value['id'])[0]['name'] : 'nada.webp';
                    echo '
                                <div class="single" slug="'.$value['slug'].'">
                                    <div class="head">
                                        <h4>'.substr($value['nome'],0,30).'</h4>
                                    </div>
                                    <div class="body">
                                        <img src="'.BASE.'data/images/'.$imagem.'">
                                        <div class="info">
                                            <span style="font-size:20px; font-weight:bold;">R$'.Site::retornarValorEmBR($value['preco']).'</span>
                                        </div>
                                    </div>
                                    <button link="item/'.$value['slug'].'"><span class="material-icons">shopping_cart</span>Acessar</button>
                                </div>
                            ';
                }
                echo '</div>
                </div>';  
            }else{
            }
        }
        public static function getCategorias(){
            
            $sql = MySql::conectar()->prepare("SELECT * FROM `produtos.default.categorias`");
            $sql->execute(array());
            if($sql->rowCount() > 0){
                $categorias = $sql->fetchAll();
                foreach($categorias as $key => $value){
                    echo '<div class="box-produtos-single" slug="'.$value['slug'].'" style="max-width:300px;" title="'.$value['nome'].'">
                                <p>'.substr($value['nome'],0,30).'</p>
                                <img alt="'.$value['nome'].'" src="'.BASE.'data/images/upload/'.self::getImageCategoriaFromID($value['id']).'">
                                <a><button link="'.$value['slug'].'" class="btn-hover"><span>Acessar<span></button></a>
                        </div><!--box-produtos-single-->';
                }
            }else{
                echo '<div style="display:flex; justify-content:center; align-content:center; flex-direction:column;">
                        <h2>Não há categorias 😯</h2>
                        <img style="max-width:100%; margin:0 auto;width:200px;" src="'.BASE.'data/images/logo.webp">
                        <div class="errorPage"><a href="'.BASE.'"><button style="width: 100%; max-width: 200px;">Voltar</button></div>
                    </div>';
            }
        }
    }

?>