<?php
require_once("auth.php");
require_once("../fonctions/divers.php");
if (!est_autorise("acces_configuration"))
    exit;
$request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();

if (false == $lang = $request->get("lang", false))
    $lang = ActionsLang::instance()->get_id_langue_courante();

$autorisation = new Autorisation();

$administrateur = new AdministrateurAdmin($request->get("administrateur", 0));
if(!$administrateur->id || $administrateur->id == $_SESSION['util']->id)
    redirige('gestadm.php');

$profilAdministrateur =  $administrateur->getProfile();


if (false == $langue = $request->get("lang", false))
    $langue = ActionsLang::instance()->get_id_langue_courante();

try
{
    ActionsAdminAdministrateur::getInstance()->action($request);
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
	ActionsAdminModules::instance()->inclure_module_admin("gestadm_droits_top");
$menu = "configuration";
$breadcrumbs = Breadcrumb::getInstance()->getMultipleList(
    array(trad('Configuration', 'admin'), 'configuration.php'),
    array(trad('Gestion_administrateurs', 'admin'), 'gestadm.php'),
    array(trad('Gestion_droit', 'admin'), '')
);
require_once("entete.php");
?>
    <div class="row-fluid">
        <div class="span12">
            <h3><?php echo trad('Gestion_droit', 'admin'); ?> : <?php echo $administrateur->prenom; ?> <?php echo $administrateur->nom; ?></h3>
        </div>
    </div>
<?php
	ActionsAdminModules::instance()->inclure_module_admin("gestadm_droits");
?>    
    <form method="post" action="gestadm_droits.php">

        <input type="hidden" name="administrateur" value="<?php echo $administrateur->id; ?>" />
        <input type="hidden" name="action" value="change_droits_admin" />

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
                                        <option value="0" <?php if($profilAdministrateur == 0) { echo 'selected="selected"'; } ?>><?php echo trad('Personalized_profile', 'admin'); ?></option>
                                    <?php foreach($administrateur->query_liste("SELECT profil, titre FROM ".Profildesc::TABLE." WHERE lang=".$langue) as $profildesc): ?>
                                        <option value="<?php echo $profildesc->profil; ?>" <?php if( $profildesc->profil == $profilAdministrateur ) echo 'selected="selected"';  ?> js-permissions="<?php echo implode('-', ProfilAdmin::getInstance($profildesc->profil)->getPermissionIdList()); ?>">
                                            <?php echo $profildesc->titre; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                       
                    </tbody>
                </table>
            </div>
            
            <div class="bigtable js-not-for-superadmin" style="<?php if($profilAdministrateur == ProfilAdmin::ID_PROFIL_SUPERADMINISTRATEUR) { echo "display: none"; } ?>">
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
                            foreach($autorisation->query_liste("SELECT * FROM ".$autorisation->table) as $row){
                                $autorisationdesc = new Autorisationdesc();
                                $autorisationdesc->charger($row->id, $lang);

                                $autorisation_administrateur = new Autorisation_administrateur();
                                $autorisation_administrateur->charger($row->id, $administrateur->id);
                        ?>
                            <tr>
                                <td><?php echo $autorisationdesc->titre; ?></td>
                                <td><?php echo $autorisationdesc->description; ?></td>
                                <td><input type="checkbox" class="js-genral-permissions" js-id="<?php echo $row->id; ?>" name="droits_g[<?php echo $row->id; ?>]" <?php if($autorisation_administrateur->lecture) { ?> checked="checked" <?php } ?> ></td>
                            </tr>
                        <?php   
                            }
                        ?>
                       
                    </tbody>
                </table>
            </div>
            
            <div class="bigtable js-not-for-superadmin" style="<?php if($profilAdministrateur == ProfilAdmin::ID_PROFIL_SUPERADMINISTRATEUR) { echo "display: none;"; } ?>">
                <table class="table table-striped">
                    <thead>
                        <caption>
                            <h3><?php echo trad('Droits_modules', 'admin'); ?></h3>
                        </caption>
                        <tr>
                            <th><?php echo trad('Module', 'admin'); ?></th>
                            <th><?php echo trad('Description', 'admin'); ?></th>
                            <th><?php echo trad('Acces', 'admin'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                            $liste = ActionsAdminModules::instance()->lister(false, true);

                            foreach($liste as $module)
                            {
                                if (ActionsAdminModules::instance()->est_administrable($module->nom))
                                {
                                    $autorisation_modules = new Autorisation_modules();
                                    $autorisation_modules->charger($module->id, $administrateur->id);
                                
                                    $modulesdesc = new Modulesdesc();
                                    $modulesdesc->charger($module->id);

                                ?>
                                    <tr>
                                        <td><?php echo ActionsAdminModules::instance()->lire_titre_module($module); ?></td>
                                        <td><?php echo $modulesdesc->description; ?></td>
                                        <td><input type="checkbox" name="droits_m[<?php echo $module->id; ?>]" <?php if($autorisation_modules->autorise) { ?> checked="checked" <?php } ?> ></td>
                                    </tr>
                                <?php
                                }
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
<?php
	ActionsAdminModules::instance()->inclure_module_admin("gestadm_droits_bottom");
?>
    
<?php require_once("pied.php"); ?> 
    
    
<script stype="text/javascript">
var superadministrateurId = <?php echo ProfilAdmin::ID_PROFIL_SUPERADMINISTRATEUR; ?>;
jQuery(function($)
{
    $('select[name="profil"]').change(function(e)
    {
        
        
        
        if($(this).val() == superadministrateurId)
        {
            /*hide options if superadminsitrator*/
            $('.js-not-for-superadmin').hide();
        }
        else
        {
            /*show options if superadminsitrator*/
            $('.js-not-for-superadmin').show();
            
            /*load checkboxes witch match profile*/
            $('.js-genral-permissions').attr('checked', false);
            $.each($(this).children(':selected').attr('js-permissions').split('-'), function(k, v)
            {
                $('input[js-id="' + v + '"]').attr('checked', true);
            });
        }
    });
    
    $('.js-genral-permissions').change(function(e)
    {
        $('select[name="profil"]').children('option[value="0"]').attr('selected', true);
    });
})
</script>
    
</body>
</html>