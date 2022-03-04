<div class="container-main">
    <div class="center">
        <div class="container-flex">
            <?php \Models\FeedbackModels::getFeedback() ?>
        </div>  
    </div>
</div>
<?php 
    if(isset($_GET['ticketid']) && isset($_GET['action']) || @$_GET['action'] == 'wfeedback'){
?>
    <script>
        var id = <?php echo $_GET['ticketid'] ?>;
        $.ajax({
            url:BASE+'ajax/Feedback.php',
            method:'post',
            data:{'acao':'tab-writeFeedback','ticketid':id,'token':sessionStorage.getItem('token')}
        }).done(function(data){
            data = JSON.parse(data)
            $('.container-flex').prepend(data.response)
        })
    </script>
<?php }?>