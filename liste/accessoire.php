<?php

function lister_accessoires($refproduit) {

	foreach(ProductAdmin::getInstanceByRef($refproduit)->getAccessoryList() as $accesory){ ?>
            <tr class="accessory_liste">
                <td><?php echo $accesory["rubrique"]; ?></td>
                <td><?php echo $accesory["produit"]; ?></td>
                <td><a href="#" class="accessory-delete" data-accessory="<?php echo $accesory["id"]; ?>"><i class="icon-trash"></i></a></td>
            </tr>
        <?php }
}
?>