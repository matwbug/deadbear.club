<?php 

    use Picpay\Payment;
    use Picpay\Buyer;
    use Picpay\Seller;
    use Picpay\Request\PaymentRequest;
    use Picpay\Exception\RequestException;
    use Picpay\Request\CancelRequest;
    use Picpay\Request\StatusRequest;

    class PicPaya{
        public function __construct($picpaytoken,$sellertoken){
            $this->picpayToken = $picpaytoken;
            $this->sellerToken = $sellertoken;
        }
        public function criarCobPix($idpedido,$valor){
            $valor = number_format($valor, 2,'.','.');
            $reffId = 'DEADBEAR_PEDIDO'.$idpedido.'_'.Site::generateRandomString(6);
            $userInfo = Site::getUserInfo($_COOKIE['loginToken']);
            $seller = new Seller($this->picpayToken,$this->sellerToken); //instanciando classe picpay
            $buyer = new Buyer($userInfo['nome'],$userInfo['sobrenome'],$userInfo['cpf'],$userInfo['email'],$userInfo['telefone']);
            $payment = new Payment($reffId, BASE.'chat', $valor, $buyer, BASE.'chat?paymentConfirmed='.$idpedido);

            try{
                $pedidoPagamento = new PaymentRequest($seller, $payment);
                $pedidoResponse = $pedidoPagamento->execute();
                $sql = MySql::conectar()->prepare("SELECT * FROM `pedido.txid` WHERE `id.pedido` = ?"); $sql->execute(array($idpedido));
                try{
                    if($sql->rowCount() <= 0){
                        $sql = MySql::conectar()->prepare("INSERT INTO `pedido.txid` VALUES(null,?,null,'PICPAY','pendente',?,$valor,?,?,null,'carrinho',?)");
                        if($sql->execute(array($idpedido,$reffId,$pedidoResponse->qrcode->content,$pedidoResponse->qrcode->base64,date('Y-m-d H:i:s')))){return $pedidoResponse;}
                        
                    }
                }catch(Exception $e){
                    return $e;
                }
                
                
            }catch(RequestException $e){
                $errorMessage = $e->getMessage();
                $statusCode = $e->getCode();
                $errors = $e->getErrors();
            }
        }
        public function PagamentoStatus($reffId){
            // STATUS
            $seller = new Seller($this->picpayToken,$this->sellerToken); //instanciando classe picpay 
            try {
                // Cria uma nova requisição de status do pagamento com os dados da loja e id do pedido
                $statusRequest = new StatusRequest($seller, $reffId);
                // Faze a requisição. O retorno contém o status do pagamento, seu id do pedido e numero de autorizaçao caso esteja pago
                $statusResponse = $statusRequest->execute();
                return $statusResponse;
            } catch (RequestException $e) {
                // Tratar os erros da requisição aqui
                $errorMessage = $e->getMessage();
                $statusCode = $e->getCode();
                $errors = $e->getErrors();
}
        }
        public function criarCobPixSemPedido($ticketid,$valor){
            $valor = @number_format($valor, 2); $valor = str_replace('.','',$valor); $valor = str_replace(',','.',$valor);
            $reffId = 'DEADBEAR_PEDIDO'.$ticketid.'_'.Site::generateRandomString(6);
            $sql = MySql::conectar()->prepare("SELECT * FROM `tickets` WHERE `id` = ?"); $sql->execute(array($ticketid));$ticketInfo = $sql->fetch();
            $userInfo = Site::getUserInfoByID($ticketInfo['creator_id']);
            $seller = new Seller($this->picpayToken,$this->sellerToken); //instanciando classe picpay 
            $buyer = new Buyer($userInfo['nome'],$userInfo['sobrenome'],$userInfo['cpf'],$userInfo['email'],$userInfo['telefone']);
            $payment = new Payment($reffId, BASE.'chat', $valor, $buyer, BASE.'chat');

            try{
                $pedidoPagamento = new PaymentRequest($seller, $payment);
                $pedidoResponse = $pedidoPagamento->execute();
                try{
                    $sql = MySql::conectar()->prepare("INSERT INTO `pedido.txid` VALUES(null,null,?,'PICPAY','pendente',?,$valor,?,?,null,'gerado',?)");
                    if($sql->execute(array($ticketid,$reffId,$pedidoResponse->qrcode->content,$pedidoResponse->qrcode->base64,date('Y-m-d H:i:s')))){return $pedidoResponse;}
                }catch(Exception $e){
                    return $e;
                }
                
                
            }catch(RequestException $e){
                $errorMessage = $e->getMessage();
                $statusCode = $e->getCode();
                $errors = $e->getErrors();
            }
        }

        public function estornarTransacao($transationid){
            // Dados da loja (PicPay Token e Seller Token)
            $seller = new Seller($this->picpayToken,$this->sellerToken); //instanciando classe picpay 
            $transationInfo = Site::getInfoDB('pedido.txid','`id`='.$transationid);
            // CANCELAMENTO
            try {
                // Cria uma nova requisição de cancelamento do pagamento com os dados da loja, id do pedido e codigo de autorização
                $cancelRequest = new CancelRequest($seller, $transationInfo['txid'], $transationInfo['picpayAutorizationID']); 

                // Faze a requisição. O retorno contém o id do cancelamento e seu id do pedido
                $cancelResponse = $cancelRequest->execute();
                \Models\Pagamento::insertDbPaymentRefund($transationInfo['id.pedido'],$transationInfo['id.ticket'],$cancelRequest->cancellationId);
                return $cancelResponse;
            } catch (RequestException $e) {
                return false;
            }
        }
                    
                
            
    }
            


?>