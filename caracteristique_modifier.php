<?php
require_once("auth.php");
require_once("../fonctions/divers.php");
if (!est_autorise("acces_configuration"))
    exit;
$request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();

try 
{
    ActionsAdminCaracteristique::getInstance()->action($request);
} catch (TheliaAdminException $e) {
    Tlog::error($e->getMessage());
}

if(false === $lang = $request->get("lang", false))
    $lang = ActionsAdminLang::instance()->get_id_langue_courante();

$caract = new Caracteristique($request->get("id"));

$caractDisp = new Caracteristiquedesc($caract->id, $lang);

?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <?php require_once("title.php"); ?>
    </head>
<body>
<?php
	ActionsAdminModules::instance()->inclure_module_admin("caracteristique_modifier_top");
$menu = "configuration";
$breadcrumbs = Breadcrumb::getInstance()->getCaracList($caractDisp->titre);
require_once("entete.php");
?>
    <form method="post" action="caracteristique_modifier.php">
    <div class="row-fluid">
        <div class="span12">
            <h3><?php echo trad('MODIFICATION_DES_CARACTERISTIQUES', 'admin'); ?>
            <div class="btn-group">
                <a class="btn btn-large" title="<?php echo trad('ajouter', 'admin'); ?>" href="#caracteristiqueAddModal" data-toggle="modal">
                    <i class="icon-plus-sign icon-white"></i>
                </a>
            </div>
            </h3>
<?php
	ActionsAdminModules::instance()->inclure_module_admin("caracteristique_modifier");
?>
            <p>
                <button class="btn btn-large btn-block btn-primary" type="submit"><?php echo trad('VALIDER_LES_MODIFICATIONS', 'admin'); ?></button>
            </p>
        </div>
    </div>
    
    <input type="hidden" name="action" value="modifier">
    <input type="hidden" name="id" value="<?php echo $caract->id; ?>">
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
                                    <li class="<?php if ($displayLang->id == $lang) { ?>active<?php } ?>"><a href="caracteristique_modifier.php?id=<?php echo $caract->id; ?>&lang=<?php echo $displayLang->id; ?>" class="change-page"><img src="gfx/lang<?php echo $displayLang->id; ?>.gif" /></a></li>
                                <?php endforeach; ?>
                            </ul>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <?php echo trad('Titre_caracteristique', 'admin'); ?>
                        </td>
                        <td><input class="input-xlarge" type="text" name="titre" value="<?php echo htmlspecialchars($caractDisp->titre); ?>"></td>
                    </tr>
                    <tr>
                        <td>
                            <?php echo trad('Chapo', 'admin'); ?>
                            <p>
                                <small><?php echo trad('courte_descript_intro', 'admin'); ?></small>
                            </p>
                        </td>
                        <td><textarea class="input-xlarge" name="chapo"><?php echo $caractDisp->chapo; ?></textarea></td>
                    </tr>
                    <tr>
                        <td>
                            <?php echo trad('Description', 'admin'); ?>
                            <p>
                                <small><?php echo trad('description_complete', 'admin'); ?></small>
                            </p>
                        </td>
                        <td><textarea class="input-xlarge" name="description"><?php echo $caractDisp->description; ?></textarea></td>
                    </tr>
                    <tr>
                        <td><?php echo trad('Visible', 'admin'); ?></td>
                        <td>
                            <label class="checkbox">
                                <small><?php echo trad('permet', 'admin'); ?></small>
                                <input type="checkbox" name="affiche" <?php if($caract->affiche) echo 'checked="checked"'; ?>>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <td>ID</td>
                        <td><?php echo $caract->id; ?></td>
                    </tr>
                </tbody>
            </table>
            <?php
                ActionsAdminModules::instance()->inclure_module_admin("caracteristiquemodifier");
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
                		dd.*,  IFNULL(ddd.classement,".PHP_INT_MAX.") as classmt
                	from
                		".Caracdisp::TABLE." dd
                	left join
                		".Caracdispdesc::TABLE." ddd on ddd.caracdisp = dd.id and lang = $lang
                	where
                		dd.caracteristique='$caract->id'
                	order by
                		classmt, dd.id";
                    
                        $results = $caract->query_liste($query);
                        foreach($results as $caracdisp)
                        {
                            $caracdispdesc = new Caracdispdesc($caracdisp->id, $lang);
                            $defaultCaracdispdesc = new Caracdispdesc($caracdisp->id, LangAdmin::getDefaultLangInstance()->id);
                    ?>
                    <tr>
                        <td>ID : <?php echo $caracdisp->id; ?></td>
                        <td><input type="text" name="caracdispdesc_titre[<?php echo $caracdisp->id; ?>]" value="<?php
                            echo  htmlspecialchars($caracdispdesc->titre); ?>" placeholder="<?php echo $defaultCaracdispdesc->titre; ?>"></td>
                        <td>
                            <a href="caracteristique_modifier.php?id=<?php echo $caract->id; ?>&cacacdispdesc=<?php echo $caracdispdesc->id; ?>&lang=<?php echo $lang ?>&type=M&action=modClassementCaracdisp"><i class="icon-arrow-up"></i></a>
                            <span class="object_classement_editable" object-action="setclassementcaracdisp" object-name="caracdispdesc" object-id="<?php echo $caracdispdesc->id; ?>"><?php echo intval($caracdispdesc->classement); ?></span>
                            <a href="caracteristique_modifier.php?id=<?php echo $caract->id; ?>&cacacdispdesc=<?php echo $caracdispdesc->id; ?>&lang=<?php echo $lang ?>&type=D&action=modClassementCaracdisp"><i class="icon-arrow-down"></i></a>
                        </td>
                        <td><a class="btn btn-mini js-delete-caracdisp" caracdisp-id="<?php echo $caracdisp->id; ?>" href="#"><i class="icon-trash"></i></a></td>
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
    <div class="modal hide fade in" id="caracteristiqueAddModal">
        <form method="post" action="caracteristique_modifier.php">
        <input type="hidden" name="action" value="ajCaracdisp">
        <input type="hidden" name="lang" value="<?php echo $lang ?>">
        <input type="hidden" name="id" value="<?php echo $caract->id; ?>">
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
<?php
	ActionsAdminModules::instance()->inclure_module_admin("caracteristique_modifier_bottom");
?>
<?php require_once("pied.php"); ?> 
<script type="text/javascript" src="js/Thelia.js"></script>
<script type="text/javascript" src="js/jeditable.min.js"></script>
<script type="text/javascript">
    $(document).ready(function(){
        $('.object_classement_editable').editable(function(value, settings){        
            var form = Thelia.generateForm({
                action : $(this).attr('object-action'),
                object_name : $(this).attr('object-name'),
                object_id : $(this).attr('object-id'),
                target : "caracteristique_modifier.php",
                value : value
            });

            form.prepend('<input type="hidden" name="id" value="<?php echo $caract->id; ?>">')
                .prepend('<input type="hidden" name="lang" value="<?php echo $lang; ?>">');

            $(this).prepend(form);
            form.submit();
        },{
            onblur : 'submit',
            select : true
        });
        
        $(".js-delete-caracdisp").click(function(e){
            e.preventDefault();
            $("#deleteLink").attr("href","caracteristique_modifier.php?id=<?php echo $caract->id; ?>&action=delCaracdisp&caracdisp="+$(this).attr("caracdisp-id")+"&lang=<?php echo $lang; ?>");
            $("#delObject").modal("show");
        })
    });
    
</script>
</body>
</html>