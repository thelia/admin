<?php
require_once("auth.php");
require_once("../fonctions/divers.php");
if (!est_autorise("acces_configuration"))
    exit;

$request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();

ActionsAdminParseur::getInstance()->action($request->get("action"));

$adm = new AdmParseur();

$adm->prepare_page();
?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <?php require_once("title.php"); ?>
    </head>
<body>
<?php
$menu = "configuration";
$breadcrumbs = Breadcrumb::getInstance()->getConfigurationList(trad('Gestion_cache', 'admin'));
require_once("entete.php");
?>
    <div class="row-fluid">
        <div class="span12">
            <h3><?php echo trad('CONFIGURATION', 'admin'); ?></h3>
            <div class="span9 bigtable">
            <form method="post" action="cache.php">
            <input type="hidden" name="action" value="maj_config">        
            <table class="table table-striped">
                <tbody>
                    <tr>
                        <td class="span6"><?php echo trad('Utiliser_cache', 'admin'); ?>
                            <p>
                                <small><?php echo trad('ameliore_parseur', 'admin'); ?></small>
                            </p>
                        </td>
                        <td class="span3">
                            <?php $adm->make_yes_no_radio(Parseur::PREFIXE.'_use_cache') ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <?php echo trad('duree_vie', 'admin'); ?>
                            <p>
                                <small><?php echo trad('detail_duree_vie', 'admin'); ?></small>
                            </p>
                        </td>
                        <td >
                            <input type="text" class="input-medium" name="<?php echo Parseur::PREFIXE.'_cache_file_lifetime' ?>" value="<?php echo intval(Variable::lire(Parseur::PREFIXE.'_cache_file_lifetime')); ?>">
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <?php echo trad('Periode_examen', 'admin'); ?>
                            <p>
                                <small><?php echo trad('detail_periode_examen', 'admin'); ?></small>
                            </p>
                        </td>
                        <td>
                            <input type="text" class="input-medium" name="<?php echo Parseur::PREFIXE.'_cache_check_period' ?>" value="<?php echo intval(Variable::lire(Parseur::PREFIXE.'_cache_check_period')); ?>">
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <p><?php echo trad('Fichier_actuellement', 'admin'); ?> : <?php echo $adm->cache_count; ?></p>
                            <p><?php echo trad('Dernier_examen', 'admin'); ?> : <?php echo $adm->last_date;  ?></p>
                            <p><?php echo trad('Prochain_examen', 'admin'); ?> : <?php echo $adm->next_date;  ?></p>
                        </td>
                        <td>
                            <a href="cache.php?action=check_cache" class="btn"><?php echo trad('examiner_cache', 'admin'); ?></a>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <?php echo trad('Vider_cache_parseur', 'admin'); ?>
                            <p>
                                <small><?php echo trad('Avant_mise_production', 'admin'); ?></small>
                            </p>
                        </td>
                        <td>
                            <a href="cache.php?action=clear_cache" class="btn"><?php echo trad('Vider_cache', 'admin'); ?></a>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <?php echo trad('Ajouter_temps', 'admin'); ?>
                            <p>
                                <small><?php echo trad('Commentaire_avant_alt', 'admin'); ?></small>
                            </p>
                        </td>
                        <td>
                            <?php $adm->make_yes_no_radio(Parseur::PREFIXE.'_show_time') ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <?php echo trad('Permettre_affichage', 'admin'); ?>
                            <p>
                                <small><?php echo trad('detail_info_debog', 'admin'); ?></small>
                            </p>
                        </td>
                        <td>
                            <?php $adm->make_yes_no_radio(Parseur::PREFIXE.'_allow_debug') ?>
                        </td>
                    </tr>
                </tbody>
            </table>
            <p>
                <button class="btn btn-large btn-block btn-primary" type="submit"><?php echo trad('VALIDER_LES_MODIFICATIONS', 'admin'); ?></button>
            </p>
            </form>
            </div>
        </div>
    </div>
<?php require_once("pied.php"); ?> 
</body>
</html>