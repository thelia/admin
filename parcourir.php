<?php
require_once("auth.php");
        
if(! est_autorise("acces_catalogue")) exit; 

if(!isset($parent)) $parent="";
if(!isset($id)) $id="";
if(!isset($classement)) $classement="";
if(!isset($action)) $action = "";

$addProductError = false;
$addCategoryError = false;

switch($action){
    
    //category
    case 'deleteCategory':
        CategoryAdmin::getInstance($category_id)->delete($parent);
        break;
    case 'addCategory':
        $addCategoryError = CategoryAdmin::getInstance()->add($title, $parent);
        break;
    case 'modClassementCategory':
        CategoryAdmin::getInstance($category_id)->modifyOrder($type, $parent);
        break;
    case 'changeClassementCategory':
        CategoryAdmin::getInstance($category_id)->changeOrder($newClassement, $parent);
        break;
    
    //Product
    case 'deleteProduct':
        ProductAdmin::getInstance($product_id)->delete($parent);
        break;
    case 'modClassementProduct':
        ProductAdmin::getInstance($product_id)->modifyOrder($type, $parent);
        break;
    case 'changeClassementProduct':
        ProductAdmin::getInstance($product_id)->changeOrder($newClassement, $parent);
        break;
    case 'addProduct':
        $addProductError = ProductAdmin::getInstance()->add($ref, $title, $parent);
        break;
}


?>
<!DOCTYPE html>
<html lang="fr">
<head>
<?php require_once("title.php");?>
</head>

<body>
    
    <?php
	ActionsAdminModules::instance()->inclure_module_admin("parcourir_top");
        $menu = "catalogue";
        $breadcrumbs = Breadcrumb::getInstance()->getCategoryList($parent);
        require_once("entete.php");
    ?>
    
        <div class="row-fluid">
            <div class="span12">
                <div class="bigtable">
                <table class="table table-striped">
                    <caption>
                        <h3>
                            <?php echo trad('LISTE_RUBRIQUES', 'admin'); ?>
                        
                            <div class="btn-group">
                                <a class="btn btn-large" title="<?php echo trad('ajouter', 'admin'); ?>" href="#categoryAddModal" data-toggle="modal">
                                    <i class="icon-plus-sign icon-white"></i>
                                </a>
                            </div>
                        </h3>
<?php
	ActionsAdminModules::instance()->inclure_module_admin("parcourir");
?>                        
                    </caption>
                    <thead>
                        <tr>
                            <th><?php echo trad('Titre_rubrique', 'admin'); ?></th>
                            <th><?php echo trad('En_ligne', 'admin'); ?></th>
                            <th><?php echo trad('Classement', 'admin'); ?></th>
                            <th></th>
                            <th><?php echo trad('Suppr', 'admin'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach(CategoryAdmin::getInstance()->getList($parent, 'classement', 'ASC', '') as $rubrique): ?>
                            <tr>
                                <td><?php echo $rubrique["titre"]; ?></td>
                                <td><input type="checkbox" category-id="<?php echo $rubrique["id"]; ?>" class="categoryDisplay" <?php if($rubrique["ligne"]) echo 'checked="checked"' ?>></td>
                                <td>
                                    <a href="parcourir.php?parent=<?php echo $parent; ?>&category_id=<?php echo $rubrique["id"]; ?>&type=M&action=modClassementCategory"><i class="icon-arrow-up"></i></a>
                                    <span class="object_classement_editable" object-action="changeClassementCategory" object-name="category_id" object-id="<?php echo $rubrique["id"]; ?>"><?php echo $rubrique["classement"]; ?></span>
                                    <a href="parcourir.php?parent=<?php echo $parent; ?>&category_id=<?php echo $rubrique["id"]; ?>&type=D&action=modClassementCategory"><i class="icon-arrow-down"></i></a>
                                </td>
                                <td>
                                    <a href="parcourir.php?parent=<?php echo $rubrique["id"] ?>"><?php echo trad('parcourir','admin'); ?></a>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a class="btn btn-mini" title="<?php echo trad('editer', 'admin'); ?>" href="rubrique_modifier.php?id=<?php echo($rubrique["id"]); ?>"><i class="icon-edit"></i></a>
                                        <a class="btn btn-mini js-category-delete" title="<?php echo trad('supprimer', 'admin'); ?>" data-toggle="modal" href="#delObject" category-id="<?php echo $rubrique["id"]; ?>" ><i class="icon-trash"></i></a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            </div>
        </div>
        
        <div class="row-fluid">
            <div class="span12">
                <div class="bigtable">
                <table class="table table-striped">
                    <caption>
                        <h3><?php echo trad('LISTE_PRODUITS', 'admin'); ?>
                            <div class="btn-group">
                                <a class="btn btn-large" title="<?php echo trad('ajouter', 'admin'); ?>" href="#productAddModal" data-toggle="modal">
                                    <i class="icon-plus-sign icon-white"></i>
                                </a>
                            </div>
                        </h3> 
                    </caption>
                    <thead>
                        <tr>
                            <th></th>
                            <th><?php echo trad('ref','admin'); ?></th>
                            <th><?php echo trad('Titre_produit', 'admin'); ?></th>
                            <th><?php echo trad('Stock', 'admin'); ?></th>
                            <th><?php echo trad('Prix', 'admin'); ?></th>
                            <th><?php echo trad('Prix_promo', 'admin'); ?></th>
                            <th><?php echo trad('Promotion', 'admin'); ?></th>
                            <th><?php echo trad('Nouveaute', 'admin'); ?></th>
                            <th><?php echo trad('En_ligne', 'admin'); ?></th>
                            <th><?php echo trad('Classement', 'admin'); ?></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach(ProductAdmin::getInstance()->getList($parent, 'classement', 'ASC', '') as $produit): ?>
                            <tr>
                                <td><?php if($produit["image"]["fichier"]): ?>
                                        <img src="../fonctions/redimlive.php?nomorig=<?php echo $produit["image"]["fichier"];?>&type=produit&width=51&height=51&exact=1" title="<?php echo $produit["ref"]; ?>">
                                    <?php endif; ?>
                                </td>
                                <td><?php echo ProductAdmin::truncate($produit["ref"], 20); ?></td>
                                <td><?php echo $produit["titre"]; ?></td>
                                <td><?php echo $produit["stock"]; ?></td>
                                <td><?php echo $produit["prix"]; ?></td>
                                <td><?php echo $produit["prix2"]; ?></td>
                                <td><input type="checkbox" product-id="<?php echo $produit["id"]; ?>" product-action="changePromo" class="productCheckbox" <?php if($produit["promo"]) echo 'checked="checked"' ?>></td>
                                <td><input type="checkbox" product-id="<?php echo $produit["id"]; ?>" product-action="changeNew" class="productCheckbox" <?php if($produit["nouveaute"]) echo 'checked="checked"' ?>></td>
                                <td><input type="checkbox" product-id="<?php echo $produit["id"]; ?>" product-action="changeDisplay" class="productCheckbox" <?php if($produit["ligne"]) echo 'checked="checked"' ?>></td>
                                <td>
                                    <a href="parcourir.php?parent=<?php echo $parent; ?>&product_id=<?php echo $produit["id"]; ?>&type=M&action=modClassementProduct"><i class="icon-arrow-up"></i></a>
                                    <span class="object_classement_editable" object-action="changeClassementProduct" object-name="product_id" object-id="<?php echo $produit["id"]; ?>"><?php echo $produit["classement"]; ?></span>
                                    <a href="parcourir.php?parent=<?php echo $parent; ?>&product_id=<?php echo $produit["id"]; ?>&type=D&action=modClassementProduct"><i class="icon-arrow-down"></i></a>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a class="btn btn-mini" title="<?php echo trad('editer', 'admin'); ?>" href="produit_modifier.php?ref=<?php echo($produit["ref"]); ?>&rubrique=<?php echo $produit["rubrique"]; ?>"><i class="icon-edit"></i></a>
                                        <a class="btn btn-mini js-product-delete" title="<?php echo trad('supprimer', 'admin'); ?>" data-toggle="modal" href="#delObject" product-id="<?php echo $produit["id"]; ?>" ><i class="icon-trash"></i></a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            </div>
        </div>
        
        <div class="modal hide fade" id="delObject">
            <div class="modal-header"> <a class="close" data-dismiss="modal">x</a>
                <h3>Plus d'informations</h3>
            </div>
            <div class="modal-body">
                <p id="explainText"></p>
            </div>
            <div class="modal-footer">
                <a class="btn" data-dismiss="modal" aria-hidden="true"><?php echo trad('non', 'admin'); ?></a>
                <a class="btn btn-primary" id="deleteLink"><?php echo trad('Oui', 'admin'); ?></a>
            </div>
        </div>
        
        <!-- category add -->
        <div class="modal hide fade" id="categoryAddModal" tabindex="-1" role="dialog" aria-hidden="true">
        <form method="POST" action="parcourir.php">
            <input type="hidden" name="action" value="addCategory" />
            <input type="hidden" name="parent" value="<?php echo $parent; ?>" />
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h3 ><?php echo trad('AJOUTER_RUBRIQUE', 'admin'); ?></h3>
            </div>
            <div class="modal-body">

<?php if($addCategoryError){ ?>
                <div class="alert alert-block alert-error fade in" id="categoryError">
                    <h4 class="alert-heading"><?php echo trad('Cautious', 'admin'); ?></h4>
                <p><?php echo trad('check_information', 'admin'); ?></p>
                </div>
<?php } ?>

                <table class="table table-striped" id="categoryCreation">
                    <tbody>
                        <tr class="<?php if($addCategoryError && empty($title)){ ?>error<?php } ?>">
                            <td><?php echo trad('Titre', 'admin'); ?> *</td>
                            <td>
                                <input type="text" value="<?php echo $title ?>" name="title"  />
                            </td>
                        </tr>
                    </tbody>
                </table>

            </div>
            <div class="modal-footer">
                <a class="btn" data-dismiss="modal" aria-hidden="true"><?php echo trad('Cancel', 'admin'); ?></a>
                <button type="submit" class="btn btn-primary"><?php echo trad('Ajouter', 'admin'); ?></button>
            </div>
        </form>
        </div>
        
        <!-- product add -->
        <div class="modal hide fade" id="productAddModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h3 id="myModalLabel"><?php echo trad('AJOUTER_PRODUIT', 'admin'); ?></h3>
            </div>
            <div class="modal-body">
            <?php if($addProductError){ ?>
                <div class="alert alert-block alert-error fade in" id="productError">
                    <h4 class="alert-heading"><?php echo trad('Cautious', 'admin'); ?></h4>
                <p><?php echo trad('check_information', 'admin'); ?></p>
                </div>
            <?php } ?>
            <form method="POST" action="parcourir.php">
            <input type="hidden" name="action" value="addProduct" />
            <input type="hidden" name="parent" value="<?php echo $parent; ?>" />
                <table class="table table-striped" id="productCreation">
                    <tbody>
                        <tr class="<?php if($addProductError["ref"]){ ?>error<?php } ?>">
                            <td><?php echo trad('Reference', 'admin'); ?> *</td>
                            <td>
                                <input type="text" value="<?php echo $ref ?>" name="ref"  />
                            </td>
                        </tr>
                        <tr class="<?php if($addProductError["title"]){ ?>error<?php } ?>">
                            <td><?php echo trad('Titre', 'admin'); ?> *</td>
                            <td>
                                <input type="text" value="<?php echo $title ?>" name="title"  />
                            </td>
                        </tr>
                    </tbody>
                </table>

            </div>
            <div class="modal-footer">
                <a class="btn" data-dismiss="modal" aria-hidden="true"><?php echo trad('Cancel', 'admin'); ?></a>
                <button type="submit" class="btn btn-primary"><?php echo trad('Ajouter', 'admin'); ?></button>
            </div>
            </form>
        </div>
<?php
	ActionsAdminModules::instance()->inclure_module_admin("parcourir_bottom");
?>           
<?php require_once("pied.php");?> 
<script type="text/javascript" src="js/Thelia.js"></script>
<script type="text/javascript" src="js/jeditable.min.js"></script>
<script>
$(document).ready(function(){

    //put online/offline category
    $(".categoryDisplay").click(function(){
        $.ajax({
           url : 'ajax/rubrique.php',
           data : {
               category_id : $(this).attr('category-id'),
               action : 'changeDisplay',
               display : $(this).is(':checked')
           }
        });
    });
    
    $(".productCheckbox").click(function(){
        $.ajax({
            url : 'ajax/produit.php',
            data : {
                product_id : $(this).attr('product-id'),
                action : $(this).attr('product-action'),
                display : $(this).is(':checked')
            }
        });
    });

    //delete a category
    $('.js-category-delete').click(function(){
        $('#explainText').html("<?php echo trad('DeleteCategoryWarning','admin'); ?>");
        $('#deleteLink').attr('href','parcourir.php?parent=<?php echo $parent; ?>&action=deleteCategory&category_id='+$(this).attr('category-id'));
    });
    
    $('.js-product-delete').click(function(){
        $('#explainText').html("<?php echo trad('DeleteProductWarning','admin'); ?>");
        $('#deleteLink').attr('href','parcourir.php?parent=<?php echo $parent; ?>&action=deleteProduct&product_id='+$(this).attr('product-id'));
    });
    
    $('.object_classement_editable').editable(function(value, settings){        
        var form = Thelia.generateForm({
            action : $(this).attr('object-action'),
            parent : "<?php echo $parent; ?>",
            object_name : $(this).attr('object-name'),
            object_id : $(this).attr('object-id'),
            value : value
        });
        
        $(this).prepend(form);
        form.submit();
    },{
        onblur : 'submit',
        select : true
    });
    
<?php if($addCategoryError){ ?>
    $('#categoryAddModal').modal();
    $('#categoryAddModal').on('hidden',function(){
        $('#categoryError').remove();
        $('#categoryCreation tr').each(function(){
            $(this).removeClass('error');
            $(this).find('input').removeAttr('value');
        });
    });
<?php } ?>

<?php if($addProductError): ?>
    $('#productAddModal').modal();
    $('#productAddModal').on('hidden', function(){
        $('#productError').remove();
        $('#productCreation tr').each(function(){
            $(this).removeClass('error');
            $(this).find('input').removeAttr('value');
        });
    });
<?php endif; ?>
});

</script>
</body>
</html>
