<?php
require_once("auth.php");
require_once("../fonctions/divers.php");
if (!est_autorise("acces_configuration"))
    exit;
$request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();

$currentProfil = new ProfilAdmin();
$currentProfil->charger_id($request->get("profil", 0));

if(!$currentProfil->id)
    $currentProfil->chargerPermier(array(ProfilAdmin::ID_PROFIL_SUPERADMINISTRATEUR));

$currentProfilDesc = new Profildesc();
$currentProfilDesc->charger($currentProfil->id);

try
{
    ActionsAdminProfil::getInstance()->action($request);
} catch(TheliaAdminException $e) {
    $errorCode = $e->getCode();
    
    if($errorCode == TheliaAdminException::BAD_PROFILE_FORMULATION)
        $addError = 1;
}
?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <?php require_once("title.php"); ?>
    </head>
<body>
<?php
	ActionsAdminModules::instance()->inclure_module_admin("droits_top");
$menu = "configuration";
$breadcrumbs = Breadcrumb::getInstance()->getMultipleList(
    array(trad('Configuration', 'admin'), 'configuration.php'),
    array(trad('Gestion_profils', 'admin'), 'gestadm.php')
);
require_once("entete.php");
?>
    <div class="row-fluid">
        <div class="span12">
            <h3>
                <?php echo trad('Gestion_profils', 'admin'); ?>
                <div class="btn-group">
                    <a class="btn btn-large" title="<?php echo trad('ajouter', 'admin'); ?>" href="#profileAddModal" data-toggle="modal">
                        <i class="icon-plus-sign icon-white"></i>
                    </a>
                </div>
            </h3>
        </div>
    </div>

<?php
	ActionsAdminModules::instance()->inclure_module_admin("droits");
?>
    
    <form method="post" action="droits.php">
        <input type="hidden" name="action" value="modify" />

    <p>
        <button class="btn btn-large btn-block btn-primary" type="submit"><?php echo trad('VALIDER_LES_MODIFICATIONS', 'admin'); ?></button>
    </p>
    
    <div class="row-fluid">
        <div class="span12">
            
            <div class="bigtable">
                <table class="table table-striped">
                    <thead>
                        <caption>
                            <h3><?php echo trad('Droits_profil', 'admin'); ?></h3>
                        </caption>
                    </thead>
                    <tbody>
                        
                        <tr>
                            <td colspan="2">
                                <select name="profil" class="span12">
                                    <?php foreach(ProfilAdmin::getInstance()->getList() as $profildesc):
                                        if($profildesc->profil == ProfilAdmin::ID_PROFIL_SUPERADMINISTRATEUR)
                                            continue;
                                    ?>
                                        <option value="<?php echo $profildesc->profil; ?>" <?php if( $profildesc->profil == $currentProfil->id ) echo 'selected="selected"';  ?>>
                                            <?php echo $profildesc->titre; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <?php echo trad('Formulation', 'admin'); ?>
                            </td>
                            <td>
                                <?php echo $currentProfil->nom; ?>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <?php echo trad('Nom', 'admin'); ?>
                            </td>
                            <td>
                                <input type="text" name="name" value="<?php echo $currentProfilDesc->titre; ?>" >
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <?php echo trad('Description', 'admin'); ?>
                            </td>
                            <td>
                                <input type="text" name="description" value="<?php echo $currentProfilDesc->description; ?>" >
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <div class="btn-group">
                                    <a class="btn btn-mini js-delete-profile" title="<?php echo trad('supprimer', 'admin'); ?>" href="#deleteModal" data-toggle="modal" profile-title="<?php echo $currentProfilDesc->titre ?>" profile-id="<?php echo $currentProfil->id ?>"><i class="icon-trash"></i></a>
                                </div>
                            </td>
                        </tr>
                       
                    </tbody>
                </table>
            </div>
            
            <div class="bigtable js-not-for-superadmin">
                <table class="table table-striped">
                    <thead>
                        <caption>
                            <h3><?php echo trad('Droits_generaux', 'admin'); ?></h3>
                        </caption>
                    <tr>
                        <th><?php echo trad('Autorisation', 'admin'); ?></th>
                        <th><?php echo trad('Description', 'admin'); ?></th>
                        <th><?php echo trad('Acces', 'admin'); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                        <?php 
                            foreach($currentProfil->query_liste("SELECT a.id, ap.ecriture, ap.lecture FROM " . Autorisation::TABLE . " a LEFT JOIN " . Autorisation_profil::TABLE . " ap ON a.id=ap.autorisation AND ap.profil='$currentProfil->id'") as $row) {
                                $autorisationdesc = new Autorisationdesc();
                                $autorisationdesc->charger($row->id);
                        ?>
                            <tr>
                                <td><?php echo $autorisationdesc->titre; ?></td>
                                <td><?php echo $autorisationdesc->description; ?></td>
                                <td><input type="checkbox" class="js-genral-permissions" js-id="<?php echo $row->id; ?>" name="droits_g[<?php echo $row->id; ?>]" <?php if($row->lecture==1) { ?> checked="checked" <?php } ?> ></td>
                            </tr>
                        <?php   
                            }
                        ?>
                       
                    </tbody>
                </table>
            </div>
            
        </div>
    </div>
    
    <div class="row-fluid">
        <div class="span12">
            <?php
                ActionsAdminModules::instance()->inclure_module_admin("droits");
            ?>
        </div>
    </div>
    
    <p>
        <button class="btn btn-large btn-block btn-primary" type="submit"><?php echo trad('VALIDER_LES_MODIFICATIONS', 'admin'); ?></button>
    </p>

    </form>
    
    <!-- profile delation -->
    <div class="modal hide" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h3><?php echo trad('Cautious', 'admin'); ?></h3>
        </div>
        <div class="modal-body">
            <p><?php echo trad('DeleteProfileWarning', 'admin'); ?></p>
            <p id="profileDelationTitle"></p>
        </div>
        <div class="modal-footer">
            <a class="btn" data-dismiss="modal" aria-hidden="true"><?php echo trad('Non', 'admin'); ?></a>
            <a class="btn btn-primary" id="profileDelationLink"><?php echo trad('Oui', 'admin'); ?></a>
        </div>
    </div>
    
    <!-- profile add -->
    <div class="modal hide" id="profileAddModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <form method="POST" action="droits.php">
        <input type="hidden" name="action" value="addProfile" />
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h3 ><?php echo trad('ADD_PROFILE', 'admin'); ?></h3>
        </div>
        <div class="modal-body">

<?php if($adderror){ ?>
            <div class="alert alert-block alert-error fade in">
                <h4 class="alert-heading"><?php echo trad('Cautious', 'admin'); ?></h4>
            <p><?php echo trad('check_information', 'admin'); ?></p>
            </div>
<?php } ?>

            <table class="table table-striped">
                <tbody>
                    <tr class="<?php if($addError && $errorCode == TheliaAdminException::BAD_PROFILE_FORMULATION) { echo "error"; } ?>">
                        <td><?php echo trad('Formulation', 'admin'); ?></td>
                        <td>
                            <input type="text" value="<?php echo $addError ? $formulation : '' ; ?>" name="formulation"  />
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo trad('Nom', 'admin'); ?></td>
                        <td>
                            <input type="text" value="<?php echo $addError ? $name : '' ; ?>" name="name"  />
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo trad('Description', 'admin'); ?></td>
                        <td>
                            <input type="text" value="<?php echo $addError ? $description : '' ; ?>" name="description"  />
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
	ActionsAdminModules::instance()->inclure_module_admin("droits_bottom");
?>    
    
<?php require_once("pied.php"); ?> 
    
    
<script stype="text/javascript">
jQuery(function($)
{
    $('select[name="profil"]').change(function(e)
    {
        window.location = 'droits.php?profil=' + $(this).val();
    });
    
    $('.js-delete-profile').click(function()
    {
        $('#profileDelationTitle').html($(this).attr('profile-title'));
        $('#profileDelationLink').attr('href', 'droits.php?action=delete&profil=' + $(this).attr('profile-id'));
    });
    
<?php if($addError){ ?>
    $('#profileAddModal').modal();
<?php } ?>
})
</script>
    
</body>
</html>