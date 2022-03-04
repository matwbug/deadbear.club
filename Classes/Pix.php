<?php 

    class Pix{
        public function __construct($apiKey,$token){
            $this->apiKey = $apiKey;
            $this->Token = $token;
        }
        static function converterCents($value)
        {
            return intval(
                strval(floatval(
                    preg_replace("/[^0-9.]/", "", $value)
                ) * 100)
            );
        }
        public function criarCobPix($idpedido){
            $carts = explode(',',Site::getInfoDB('pedido.cart.users','`id`='.$idpedido)['carts.id']);
            foreach($carts as $key => $value){
                $infoCart = Site::getInfoDB('cart.users', '`id`='.$value);
                $info = Site::getInfoDB('produtos.default','`id`='.$infoCart['id.item']);
                $valoritem = $infoCart['cupom.id'] == 0 ? $infoCart['valor.un'] : $infoCart['cupom.preco.desconto'];
                $itens = new stdClass;
                $itens->item_id = $info['id'];
                $itens->description = $info['desc'];
                $itens->quantity = $infoCart['quantidade'];
                $itens->price_cents = self::converterCents(number_format($valoritem),2);
            };
            $reffId = 'DEADBEAR'.Site::generateRandomString(6);
            $userInfo = Site::getUserInfo($_COOKIE['loginToken']);
            $data = array(
                'apiKey' => $this->apiKey,
                'order_id' => $reffId,
                'payer_email' => $userInfo['email'],
                'payer_name' => $userInfo['nome'].' '.$userInfo['sobrenome'], // nome completo ou razao social
                'payer_cpf_cnpj' => $userInfo['cpf'], // cpf ou cnpj
                'payer_phone' => $userInfo['telefone'], // fixou ou móvel
                'days_due_date' => '7', // dias para vencimento do Pix
                'items' => array($itens) 
            );
            $data_post = json_encode($data);
            $url = "https://pix.paghiper.com/invoice/create/";
            $mediaType = "application/json"; // formato da requisição
            $charSet = "UTF-8";
            $headers = array();
            $headers[] = "Accept: ".$mediaType;
            $headers[] = "Accept-Charset: ".$charSet;
            $headers[] = "Accept-Encoding: ".$mediaType;
            $headers[] = "Content-Type: ".$mediaType.";charset=".$charSet;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_post);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $response = curl_exec($ch);
            $response = json_decode($response, true);
            if($response['pix_create_request']['result'] == 'success'){
                try{
                    $valortotal = Site::getInfoDB('pedido.cart.users','`id`='.$idpedido)['valortotal'];
                    if(Site::getRowCountDB('pedido.txid', '`id.pedido` ='.$idpedido) == 0){
                        $sql = MySql::conectar()->prepare("INSERT INTO `pedido.txid` VALUES(null,?,null,'PIX','pendente',?,$valortotal,?,?,null,'carrinho',?)");
                        $sql->execute(array($idpedido,$response['pix_create_request']['transaction_id'],$response['pix_create_request']['pix_code']['emv'],$response['pix_create_request']['pix_code']['qrcode_image_url'],date('Y-m-d H:i:s')));
                        return $response;
                    }
                    return false;                    
                }catch(Exception $e){
                    return $e;
                }
            }else{
                return false;
            }
        }
        public function PagamentoStatus($txid){
            $data = array(
                'apiKey' => $this->apiKey,
                'token' => $this->Token,
                'transaction_id' => $txid,
            );
            $curl = curl_init();
            curl_setopt_array($curl, [
            CURLOPT_URL => "https://pix.paghiper.com/invoice/status/",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_HTTPHEADER => [
                "Accept: application/json",
                "Content-Type: application/json"
            ],
            ]);

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            $json = json_decode($response, true);
            return $json;
        }
        public function criarCobSemPedido($ticketid,$nome,$valor){
            $itens = new stdClass;
            $itens->item_id = '10001';
            $itens->description = $nome;
            $itens->quantity = '1';
            $itens->price_cents = self::converterCents($valor);;
            
            $reffId = 'DEABEAR_PEDIDO'.$ticketid.'_'.Site::generateRandomString(6);
            $sql = MySql::conectar()->prepare("SELECT * FROM `tickets` WHERE `id` = ?"); $sql->execute(array($ticketid));
            $ticketInfo = $sql->fetch();
            $userInfo = Site::getUserInfoByID($ticketInfo['creator_id']);
            $data = array(
                'apiKey' => $this->apiKey,
                'order_id' => $reffId,
                'payer_email' => $userInfo['email'],
                'payer_name' => $userInfo['nome'].' '.$userInfo['sobrenome'], // nome completo ou razao social
                'payer_cpf_cnpj' => $userInfo['cpf'], // cpf ou cnpj
                'payer_phone' => $userInfo['telefone'], // fixou ou móvel
                'days_due_date' => '7', // dias para vencimento do Pix
                'items' => array($itens) 
            );
            $data_post = json_encode( $data );
            $url = "https://pix.paghiper.com/invoice/create/";
            $mediaType = "application/json"; // formato da requisição
            $charSet = "UTF-8";
            $headers = array();
            $headers[] = "Accept: ".$mediaType;
            $headers[] = "Accept-Charset: ".$charSet;
            $headers[] = "Accept-Encoding: ".$mediaType;
            $headers[] = "Content-Type: ".$mediaType.";charset=".$charSet;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_post);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $response = curl_exec($ch);
            $response = json_decode($response, true);
            if($response['pix_create_request']['result'] == 'success'){
                try{
                    $sql = MySql::conectar()->prepare("INSERT INTO `pedido.txid` VALUES(null,null,?,'PIX','pendente',?,$valor,?,?,null,'gerado',?)");
                    if($sql->execute(array($ticketid,$response['pix_create_request']['transaction_id'],$response['pix_create_request']['pix_code']['emv'],$response['pix_create_request']['pix_code']['qrcode_image_url'],date('Y-m-d H:i:s')))){return $response;}
                }catch(Exception $e){
                    return false;
                }
            }else{
                return false;
            }
        }

        public function estornarTransacao($transationId){
            $transationInfo = Site::getInfoDB('pedido.txid','`id`='.$transationId);
            $data = array(
                'token' => $this->Token,
                'apiKey' => $this->apiKey,
                'status' => 'canceled',
                'transaction_id' => $transationInfo['txid']
            );
            $curl = curl_init();
            curl_setopt_array($curl, [
            CURLOPT_URL => "https://pix.paghiper.com/invoice/cancel/",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_HTTPHEADER => [
                "Accept: application/json",
                "Content-Type: application/json"
            ],
            ]);

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            $json = json_decode($response, true);
            //\Models\Pagamento::insertDbPaymentRefund($transationInfo['id.pedido'],$transationInfo['id.ticket'],$cancelRequest->cancellationId);
            return $json;
        }
    }

?>