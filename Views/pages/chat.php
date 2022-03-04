<?php 
    if(isset($_GET['paymentConfirmed'])){
        echo '<script src="'.BASE.'JS/pagamento.js"></script>';
        Site::scriptJS('checkPaymentChat('.$_GET['paymentConfirmed'].')');
    }
?>
<div class="center">
    <div class="controladorPage">
        <a href="<?php echo BASE?>"><span>Ínicio</span></a>
            <span>/</span>
        <a><span>Chat</span> </a>
    </div>
    <div class="ticket-box">
        <div class="ticket-head">
            <h2><img src="<?php echo BASE?>data/images/loading.gif"></h2>
        </div><!--ticket-head-->
        <div class="ticket-body">
                <?php 
                
                ?>
        </div><!--ticket-body-->
        <div class="ticket-text-message">
            <div class="warn"></div>
            <!--<img width="200" id="sendImageTicket" />-->
            <form method="POST" class="chat-form">
                <input type="hidden" name="perm" value="user">
                <input type="hidden" name="acao" value="enviarMensagem">
                <label class="sendImage flex-center direction-column">
                    <i class="material-icons">attach_file</i>
                    <input onchange="sendImg();" type="file" name="img" style="display:none;" accept="image/png, image/jpeg">
                </label>
                <textarea placeholder="Diga alguma coisa"></textarea>
                <button type="submit"><span class="svg-Send"></span></button>             
            </form>
        </div>
    </div><!--ticket-box-->
</div><!--center-->
