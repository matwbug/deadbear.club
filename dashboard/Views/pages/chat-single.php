<script>
    $(document).on('click','.copyCode', function(){
        copyToClipboard($(this).parent().find('#copyTarget')[0]);
    })
    function copyToClipboard(elem) {
	  // create hidden text element, if it doesn't already exist
    var targetId = "_hiddenCopyText_";
    var isInput = elem.tagName === "INPUT" || elem.tagName === "TEXTAREA";
    var origSelectionStart, origSelectionEnd;
    if (isInput) {
        // can just use the original source element for the selection and copy
        target = elem;
        origSelectionStart = elem.selectionStart;
        origSelectionEnd = elem.selectionEnd;
    } else {
        // must use a temporary form element for the selection and copy
        target = document.getElementById(targetId);
        if (!target) {
            var target = document.createElement("textarea");
            target.style.position = "absolute";
            target.style.left = "-9999px";
            target.style.top = "0";
            target.id = targetId;
            document.body.appendChild(target);
        }
        target.textContent = elem.textContent;
    }
    // select the content
    var currentFocus = document.activeElement;
    target.focus();
    target.setSelectionRange(0, target.value.length);
    
    // copy the selection
    var succeed;
    try {
    	  succeed = document.execCommand("copy");
    } catch(e) {
        succeed = false;
    }
    // restore original focus
    if (currentFocus && typeof currentFocus.focus === "function") {
        currentFocus.focus();
    }
    
    if (isInput) {
        // restore prior selection
        elem.setSelectionRange(origSelectionStart, origSelectionEnd);
    } else {
        // clear temporary content
        target.textContent = "";
    }
    alertar('Código copiado com sucesso.','')
    return succeed;
}
</script>
<div class="container-contentWrapper">
    <div class="container-content flex-center" style="flex-direction:column; text-align:unset;">
        <div class="chat-box" id="<?php $url = explode('/',$_GET['url']); $url = $url[1]; echo $url?>">
            <div class="head flex-center">
                <div class="ajax-loading"><p><i class="fa-spin fas fa-spinner" aria-hidden="true"></i>Aguarde</p></div>
            </div>
            <div class="w100" style="background:white; padding:1px;"></div>
            <div class="body">
                <div class="ajax-loading"><i class="fa-spin fas fa-spinner" aria-hidden="true"></i></div>
            </div>
            <div class="sendMessage w100 flex-center">
                <form method="post" class="w100 flex-center">
                    <input type="hidden" name="perm" value="user">
                    <input type="hidden" name="acao" value="enviarMensagem">
                    <label class="sendImage">
                        <i class="fas fa-paperclip"></i>
                        <input onchange="sendImg();" type="file" name="img" style="display:none;" accept="image/png, image/gif, image/jpeg">
                    </label>
                    <textarea placeholder="Diga alguma coisa"></textarea>
                    <button class="sendChat"><span class="svg-Send"></span></button>
                </form>
            </div>
        </div>
        <div class="chat-box" style="min-height: 200px;">
            <div class="ajax-load" style="margin:0 auto; text-align:center; font-size:20px;"><i class="fa-spin fas fa-spinner" aria-hidden="true"></i></div>
            <div class="orders-box w100">
                <div class="info w100">
                    <h2>Informações sobre o pedido:</h2>
                    <ul class="insert w100">
                    </ul>
                </div>
            </div>
            <div class="controls">
                <div class="ajax-loading" style="display: none;"><i class="fa-spin fas fa-spinner" aria-hidden="true"></i></div>
            </div>
            <div class="open-tab">
                
            </div>
        </div>
             
    </div>
</div>