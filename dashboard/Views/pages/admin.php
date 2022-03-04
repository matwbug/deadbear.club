<script src="https://cdn.ckeditor.com/ckeditor5/29.0.0/classic/ckeditor.js"></script>
<div class="container-contentWrapper">
    <div class="container-content" style="justify-content: center;">
        <div class="info" style='flex-direction:row'>
            <button class="manage-an"><p>Anúncios</p></button>
            <button class="manage-users"><p>Usuários</p></button>
            <button class="manage-email"><p>Emails</p></button>
            <button class="manage-feedback"><p>Feedbacks</p></button>
            <button class="manage-transacoes"><p>Transações</p></button>
        </div>
    </div>
    
    <div class="container-content info-content" style="justify-content: center; display:none;">
        
    </div>
</div>
<?php //Models\AdminModels::adminFunctionModal()?>







<script>
    $(document).on('click','.copyCode', function(){
        copyToClipboard(document.getElementById("copyTarget"));
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