var Thelia = {
    generateForm : function(param){
        var form = $('<form />')
                    .attr("action","parcourir.php")
                    .attr("method","post");
        form.prepend('<input type="hidden" name="action" value="'+param.action+'">')
            .prepend('<input type="hidden" name="parent" value="'+param.parent+'">')
            .prepend('<input type="hidden" name="'+param.object_name+'" value="'+param.object_id+'">')
            .prepend('<input type="hidden" name="newClassement" value="'+param.value+'">')
        ;
        
        return form;
    }
}
