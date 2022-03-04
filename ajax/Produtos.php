<?php 
    error_reporting(E_ALL ^ E_NOTICE); 
    @session_start();

    include('../Classes/Site.php');

    $token = @$_POST['token'];
    if(!\Site::validar_token($token)){
        $data['msg'] = 'Aconteceu algum erro, contate administração';
        $data['sucesso'] = false;
        die(json_encode($data));
    }

    $data['sucesso'] = true;
    $acao = $_POST['acao'];
    if(isset($acao)){
        require('../vendor/autoload.php');
        include('../Classes/MySql.php');
        include('../config.php');
        include('../Models/ProductsDefault.php');

        if($acao == 'getProduto'){
            $slug = $_POST['slug'];

            $infoProduto = Site::getInfoDB('produtos.default',"slug = '$slug'");
            if($infoProduto){
                if(\Models\ProductsDefault::getInfoProductByID($infoProduto['id'])){
                    $imageinfo = @count(\Models\ProductsDefault::getImageProductFromID($infoProduto['id'])) > 1 ? \Models\ProductsDefault::getImageProductFromID($infoProduto['id']) : 'nada.webp';
                    if($imageinfo){
                        if (is_array($imageinfo) || is_object($imageinfo))
                        {
                            $imagens = '<div class="smallimages flex-center direction-column">';
                            foreach($imageinfo as $key => $value){
                                $active = $key == 0 ? 'active' : '';
                                $imagens .= '<img class=" '.$active.' smallimage" src="'.BASE.'data/images/upload/'.$value['name'].'">';
                                if($key == 3){break;}
                            }
                            $imagens .= '</div>';
                        }
                    }
                    $imagebig = \Models\ProductsDefault::getImageProductFromID($infoProduto['id']) != '' ? 'upload/'.\Models\ProductsDefault::getImageProductFromID($infoProduto['id'])[0]['name'] : 'nada.webp';
                    
                    
                    $data['response'] = ' <h3 class="flow-text in-left" style="text-align:left;">'.ucfirst($infoProduto['nome']).'</h3>
                                        <div class="produto-box w100 inleft flex-center">
                                            <div class="img">
                                                <div class="images w100 direction-row" style="display:flex; justify-content: space-evenly; align-items:center;">
                                                    '.$imagens.'
                                                    <div class="bigimage flex-center" style="min-height:250px;">
                                                        <img class="parentimage" src="'.BASE.'data/images/'.$imagebig.'">
                                                    </div>
                                                </div>
                                                
                                                <div class="stars" title="Um dos pedidos mais escolhidos na loja."><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                                            </div>
                                            <div class="body">
                                                <p class="w100" style="text-align:left;margin: 5px 0;">Vendido por <b>deadbear</b> | <b style="color:#66bb6a"> Em estoque</b></p>
                                                <div class="desc">
                                                    <span>'.$infoProduto['desc'].'</span>
                                                </div>
                                                <div class="flex-center direction-row w100" style="justify-content: left;">
                                                    <p class="priceitem">R$'.Site::retornarValorEmBR($infoProduto['preco']).'</p>
                                                    <button class="buy-action flex-center"><span class="material-icons" style="margin: 0 5px;"> shopping_cart </span> Comprar</button>
                                                </div>
                                                <div class="info">
                                                    <span>'.$infoProduto['complement'].'</span>
                                                </div>
                                            </div>
                                        </div>';
                }else{
                    $data['response'] = '<div class="center">
                                            <div class="container-main flex-center" style="min-height: 300px;border: 3px solid var(--border-color);border-radius: 3px;">
                                                <div class="flex-center" style="flex-direction:column;">
                                                    <div class="w100 flex-center" style="margin:10px 0;"><i class="fas fa-ban" style="margin: 0 auto;font-size:100px;"></i></div>
                                                    <p> Este anúncio não está mais disponível.</p>
                                                </div>
                                            </div>
                                        </div>';
                }
            }else{
                $data['msg'] = 'Aconteceu algum erro ao obter as informações do anúncio';
                $data['sucesso'] = false;
            }
        }
        die(json_encode($data));

    }else{
        $data['msg'] = 'Aconteceu algum erro.';
        $data['sucesso'] = false;
        die(json_encode($data));  
    }


    

?>