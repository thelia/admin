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

try
{
    ActionsAdminProfil::getInstance()->action($request);
} catch(TheliaAdminException $e) {
    Tlog::error($e->getMessage());
    $errorCode = $e->getCode();
    $errorMessage = $e->getMessage();
    if($e->getCode() == TheliaAdminException::ADMIN_MULTIPLE_ERRORS)
    {
        $errorMultiple = true;
        $errorMultipleArray = $e->getData();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <?php require_once("title.php"); ?>
    </head>
<body>
<?php
$menu = "configuration";
$breadcrumbs = Breadcrumb::getInstance()->getMultipleList(
    array(trad('Configuration', 'admin'), 'configuration.php'),
    array(trad('Gestion_profils', 'admin'), 'gestadm.php')
);
require_once("entete.php");
?>
    <div class="row-fluid">
        <div class="span12">
            <h3><?php echo trad('Gestion_profils', 'admin'); ?></h3>
        </div>
    </div>
    
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
                            <td>
                                <select name="profil" class="span12">
                                    <?php foreach(ProfilAdmin::getInstance()->getList() as $profildesc):
                                        if($profildesc->profil == ProfilAdmin::ID_PROFIL_SUPERADMINISTRATEUR)
                                            continue;
                                    ?>
                                        <option value="<?php echo $profildesc->profil; ?>" <?php if( $profildesc->profil == $currentProfil->id ) echo 'selected="selected"';  ?> js-permissions="<?php echo implode('-', ProfilAdmin::getInstance($profildesc->profil)->getPermissionIdList()); ?>">
                                            <?php echo $profildesc->titre; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
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
                ActionsAdminModules::instance()->inclure_module_admin("gestadmdroits");
            ?>
        </div>
    </div>
    
    <p>
        <button class="btn btn-large btn-block btn-primary" type="submit"><?php echo trad('VALIDER_LES_MODIFICATIONS', 'admin'); ?></button>
    </p>

    </form>
    
<?php require_once("pied.php"); ?> 
    
    
<script stype="text/javascript">
jQuery(function($)
{
    $('select[name="profil"]').change(function(e)
    {
        /*load checkboxes witch match profile*/
        $('.js-genral-permissions').attr('checked', false);
        $.each($(this).children(':selected').attr('js-permissions').split('-'), function(k, v)
        {
            $('input[js-id="' + v + '"]').attr('checked', true);
        });
    });
})
</script>
    
</body>
</html>