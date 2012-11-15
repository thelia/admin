<?php

function lister_contenuassoc($type, $objet) {
    foreach(AssociatedContentAdmin::getInstance()->getList($type, $objet) as $content){
    ?>
        <tr class="content_liste">
            <td><?php echo $content["folder"]; ?></td>
            <td><?php echo $content["content"]; ?></td>
            <td><a href="#" class="content-delete" data-content="<?php echo $content["id"]; ?>"><i class="icon-trash"></i></a></td>
        </tr>
    <?php
    }
}
?>