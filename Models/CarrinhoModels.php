<?php 
    namespace Models;

    use MySql;
    use Site;
    use Models\ProductsDefault;

class CarrinhoModels{
        public static function verifyUserHaveCart(){
            $userid = Site::getUserInfo($_COOKIE['loginToken'])['id'];
            $sql = MySql::conectar()->prepare("SELECT * FROM `cart.users` WHERE `user_id` = ? AND `ativo` = 1");
            $sql->execute(array($userid));
            if($sql->rowCount() >= 1){
                return true;
            }else{
                return false;
            }
        }
        public static function getCarrinho(){
                if(Site::logado()){
                    $userid = Site::getUserInfo($_COOKIE['loginToken'])['id'];
                    $sql = MySql::conectar()->prepare("SELECT * FROM `cart.users` WHERE `user_id` = ? AND `ativo` = 1");
                    $sql->execute(array($userid));
                    if($sql->rowCount() >= 1){
                        $total = 0.00;
                        $carrinho = $sql->fetchAll();
                        foreach($carrinho as $key => $value){
                            $info = MySql::conectar()->prepare("SELECT * FROM `produtos.default` WHERE `id` = ?");$info->execute(array($value['id.item']));
                            $info = $info->fetch();
                            $cupomInfo = MySql::conectar()->prepare("SELECT * FROM `produtos.default.cupons` WHERE `id` = ?");
                            $cupomInfo->execute(array($value['cupom.id']));
                            $cupomInfo = $cupomInfo->fetch();
                            if($value['cupom.id'] == 0){
                                $valor = number_format($value['valor.un'] * $value['quantidade'],2);
                                echo '<div class="body" cid='.$value['id'].'>
                                        <button class="remove"><span class="material-icons">close</span></button>
                                        <div class="name"><img src="'.BASE.'data/images/upload/'.\Models\ProductsDefault::getImageProductFromID($info['id'])[0]['name'].'"><span style="word-break:break-word;">'.$info['nome'].'</span></div>
                                        <div><input type="text" onkeypress="validateNumb()" class="quantCart" name="quantCart" value='.$value['quantidade'].'></input></div>
                                        <div class="price">R$'.$valor.'</div>
                                    </div>';
                                    @$total = str_replace('.',',',$total + $valor);
                            }else{
                                $valor = number_format($value['cupom.preco.desconto'] * $value['quantidade'],2);
                                echo '<div class="body" cid='.$value['id'].'>
                                        <button class="remove"><span class="material-icons">close</span></button>
                                        <div class="name"><a href="'.BASE.'item/'.$info['slug'].'"><img src="'.BASE.'data/images/upload/'.\Models\ProductsDefault::getImageProductFromID($info['id'])[0]['name'].'"><span style="word-break:break-word;">'.$info['nome'].'</span></a></div>
                                        <div><input type="text" onkeypress="validateNumb()" class="quantCart" name="quantCart" value='.$value['quantidade'].'></input></div>
                                        <div class="price"><span>R$'.number_format($value['valor.un']).'<b>(R$'.number_format($value['cupom.preco.desconto'],2).')</b><br><b>Você usou o cupom: '.strtoupper($cupomInfo['code']).'<br>e ganhou '.$cupomInfo['porcentdesconto'].'% de desconto.</b></span></div>
                                    </div>';
                                    $total = str_replace('.',',',$total + $valor);
                            }
                        }
                        echo '<div class="info"><div class="total">Total: <b js="totalCart">R$'.$total.'</b></div></div>';
                    }else{
                        echo '<div class="flex-center" style="flex-direction:column"><h3>Parece que você não fez login ainda.</h3><img style="max-width:100%; width:200px; margin:10px 0;" src="'.BASE.'data/images/404.webp"</div>';
                    }
                }else{
                    echo '<div class="flex-center" style="flex-direction:column"><h3>Parece que você não fez login ainda.</h3><img style="max-width:100%; width:200px; margin:10px 0;" src="'.BASE.'data/images/404.webp"</div>';
                }

        }
    }
?>