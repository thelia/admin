var Thelia = {
    generateForm : function(param){
        
        var opt = {
             action : "",
             parent : "0",
             object_id : "0",
             value : "",
             target : "parcourir.php"
        }
        
        jQuery.extend(opt, param);
        
        var form = $('<form />')
                    .attr("action",opt.target)
                    .attr("method","post");
        form.prepend('<input type="hidden" name="action" value="'+opt.action+'">')
            .prepend('<input type="hidden" name="parent" value="'+opt.parent+'">')
            .prepend('<input type="hidden" name="'+opt.object_name+'" value="'+opt.object_id+'">')
            .prepend('<input type="hidden" name="newClassement" value="'+opt.value+'">')
        ;
        
        return form;
    }
}
