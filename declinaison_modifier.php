<?php
require_once("auth.php");
require_once("../fonctions/divers.php");
if (!est_autorise("acces_configuration"))
    exit;

$request = Symfony\Component\HttpFoundation\Request::createFromGlobals();

try 
{
    ActionsAdminDeclinaison::getInstance()->action($request);
} catch(TheliaAdminException $e) {
    Tlog::error($e->getMessage());
}

if(false === $lang = $request->get("lang", false))
    $lang = ActionsAdminLang::instance()->get_id_langue_courante();

$declinaison = new Declinaison($request->get("id"));

$declinaisondesc = new Declinaisondesc($declinaison->id, $lang);

?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <?php require_once("title.php"); ?>
    </head>
<body>
<?php
	ActionsAdminModules::instance()->inclure_module_admin("declinaison_modifier_top");
$menu = "configuration";
$breadcrumbs = Breadcrumb::getInstance()->getDecliList($declinaisondesc->titre);
require_once("entete.php");
?>
<?php
	ActionsAdminModules::instance()->inclure_module_admin("declinaison_modifier");
?>
    <form method="post" action="declinaison_modifier.php">
    <div class="row-fluid">
        <div class="span12">
            <h3><?php echo trad('MODIFICATION_DECLINAISONS', 'admin'); ?>
            <div class="btn-group">
                <a class="btn btn-large" title="<?php echo trad('ajouter', 'admin'); ?>" href="#declidispAddModal" data-toggle="modal">
                    <i class="icon-plus-sign icon-white"></i>
                </a>
            </div>
            </h3>
            <p>
                <button class="btn btn-large btn-block btn-primary" type="submit"><?php echo trad('VALIDER_LES_MODIFICATIONS', 'admin'); ?></button>
            </p>
        </div>
    </div>
    <input type="hidden" name="action" value="modifier">
    <input type="hidden" name="id" value="<?php echo $declinaison->id; ?>">
    <input type="hidden" name="lang" value="<?php echo $lang; ?>">
    <div class="row-fluid">
        <div class="span6">
            <table class="table table-striped">
                <tbody>
                    <tr>
                        <td><?php echo trad('Changer_langue', 'admin'); ?></td>
                        <td>
                            <ul class="nav nav-pills">
                                <?php foreach (LangAdmin::getInstance()->getList() as $displayLang): ?>
                                    <li class="<?php if ($displayLang->id == $lang) { ?>active<?php } ?>"><a href="declinaison_modifier.php?id=<?php echo $declinaison->id; ?>&lang=<?php echo $displayLang->id; ?>" class="change-page"><img src="gfx/lang<?php echo $displayLang->id; ?>.gif" /></a></li>
                                <?php endforeach; ?>
                            </ul>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <?php echo trad('Titre_declinaison', 'admin'); ?>
                        </td>
                        <td><input class="input-xlarge" type="text" name="titre" value="<?php echo $declinaisondesc->titre; ?>"></td>
                    </tr>
                    <tr>
                        <td>
                            <?php echo trad('Chapo', 'admin'); ?>
                            <p>
                                <small><?php echo trad('courte_descript_intro', 'admin'); ?></small>
                            </p>
                        </td>
                        <td><textarea class="input-xlarge" name="chapo"><?php echo $declinaisondesc->chapo; ?></textarea></td>
                    </tr>
                    <tr>
                        <td>
                            <?php echo trad('Description', 'admin'); ?>
                            <p>
                                <small><?php echo trad('description_complete', 'admin'); ?></small>
                            </p>
                        </td>
                        <td><textarea class="input-xlarge" name="description"><?php echo $declinaisondesc->description; ?></textarea></td>
                    </tr>
                    <tr>
                        <td>ID</td>
                        <td><?php echo $declinaison->id; ?></td>
                    </tr>
                </tbody>
            </table>
            <?php
                ActionsAdminModules::instance()->inclure_module_admin("declinaisonmodifier");
            ?>
        </div>
        <div class="span6">
            <div class="bigtable">
            <table class="table table-striped">
                <thead>
                    <caption>
                        <h4><?php echo trad('VALEURS_DISPONIBLES', 'admin'); ?></h4>
                    </caption>
                </thead>
                <tbody>
                    <?php $query = "
	                	select
	                		dd.*, IFNULL(ddd.classement,".PHP_INT_MAX.") as classmt
	                	from
	                		".Declidisp::TABLE." dd
	                	left join
	                		".Declidispdesc::TABLE." ddd on ddd.declidisp = dd.id and lang = $lang
	                	where
	                		dd.declinaison=".$declinaison->id."
	                	order by
	                		classmt, dd.id";
                    
                        $results = $declinaison->query_liste($query);
                        foreach($results as $declidisp)
                        {
                            $declidispdesc = new Declidispdesc($declidisp->id, $lang);
                    ?>
                    <tr>
                        <td>ID : <?php echo $declidisp->id; ?></td>
                        <td><input type="text" name="declinaisondesc_titre[<?php echo $declidisp->id; ?>]" value="<?php echo $declidispdesc->titre; ?>"></td>
                        <td>
                            <a href="declinaison_modifier.php?id=<?php echo $declinaison->id; ?>&declidispdesc=<?php echo $declidispdesc->id; ?>&lang=<?php echo $lang ?>&type=M&action=modClassementDeclidisp"><i class="icon-arrow-up"></i></a>
                            <span class="object_classement_editable" object-action="setclassementdeclidisp" object-name="desclidispdesc" object-id="<?php echo $declidispdesc->id; ?>"><?php echo intval($declidispdesc->classement); ?></span>
                            <a href="declinaison_modifier.php?id=<?php echo $declinaison->id; ?>&declidispdesc=<?php echo $declidispdesc->id; ?>&lang=<?php echo $lang ?>&type=D&action=modClassementDeclidisp"><i class="icon-arrow-down"></i></a>
                        </td>
                        <td><a class="btn btn-mini js-delete-declidisp" declidisp-id="<?php echo $declidisp->id; ?>" href="#"><i class="icon-trash"></i></a></td>
                    </tr>
                    
                    <?php
                        }
                    ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>     
   </form>
    <div class="modal hide fade in" id="delObject">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h3><?php echo trad('supprimer', 'admin'); ?></h3>
        </div>
        <div class="modal-body">
            
        </div>
        <div class="modal-footer">
            <a class="btn" data-dismiss="modal" aria-hidden="true"><?php echo trad('non', 'admin'); ?></a>
            <a class="btn btn-primary" id="deleteLink"><?php echo trad('Oui', 'admin'); ?></a>
        </div>
    </div>
    
    <div class="modal hide fade in" id="declidispAddModal">
        <form method="post" action="declinaison_modifier.php">
        <input type="hidden" name="action" value="ajDeclidisp">
        <input type="hidden" name="lang" value="<?php echo $lang ?>">
        <input type="hidden" name="id" value="<?php echo $declinaison->id; ?>">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h3><?php echo trad('AJOUTER_VALEUR', 'admin'); ?></h3>
        </div>
        <div class="modal-body">
            <table class="table table-striped">
                <tbody>
                    <tr>
                        <td><input class="input-xlarge" type="text" name="titre"></td>
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
	ActionsAdminModules::instance()->inclure_module_admin("declinaison_modifier_bottom");
?>
<?php require_once("pied.php"); ?> 
<script type="text/javascript" src="js/Thelia.js"></script>
<script type="text/javascript" src="js/jeditable.min.js"></script>
<script type="text/javascript">
    $(document).ready(function(){
        $(".js-delete-declidisp").click(function(e){
            e.preventDefault();
            $("#deleteLink").attr("href","declinaison_modifier.php?id=<?php echo $declinaison->id ?>&declidisp_id="+$(this).attr("declidisp-id")+"&action=delDeclidisp&lang=<?php echo $lang; ?>");
            $("#delObject").modal("show");
        });
        
        $('.object_classement_editable').editable(function(value, settings){        
            var form = Thelia.generateForm({
                action : $(this).attr('object-action'),
                object_name : $(this).attr('object-name'),
                object_id : $(this).attr('object-id'),
                target : "declinaison_modifier.php",
                value : value
            });

            form.prepend('<input type="hidden" name="id" value="<?php echo $declinaison->id; ?>">')
                .prepend('<input type="hidden" name="lang" value="<?php echo $lang; ?>">');

            $(this).prepend(form);
            form.submit();
        },{
            onblur : 'submit',
            select : true
        });
    });
</script>
</body>
</html>