?<php
    require_once("pre.php");
    require_once __DIR__ . "/auth.php";

    if(! est_autorise("acces_configuration")) exit;
    $request = Symfony\Component\HttpFoundation\Request::createFromGlobals();
    $errorCode = 0;
    try
    {
        ActionsAdminModules::instance()->action($request);
    } catch(TheliaAdminException $e) {
        Tlog::error($e->getCode());
        $errorCode = $e->getCode();
    }
    if (!isset($lang))
        $lang = ActionsLang::instance()->get_id_langue_courante();
    if (isset($action) && $action == "modifier") {
        ActionsAdminModules::instance()->mise_a_jour_description($nom, $lang, $titre, $chapo, $description, $devise);
    }

    // Charger les infos modules
    $module = new Modules();
    $module->charger($nom);
    $moduledesc = new Modulesdesc();
    $moduledesc->charger($nom, $lang);
    $existe = $moduledesc->verif($nom, $lang);

    // Initialiser si la description n'existe pas dans cette langue.
    if (!$existe) {
        $moduledesc->lang = $lang;
        $moduledesc->plugin = $nom;
        $moduledesc->devise = 0;

        $moduledesc->titre = '';
        $moduledesc->chapo = '';
        $moduledesc->description = '';
        $moduledesc->devise = 0;
    }

    // Charger les devises
    $devises = array();

    $result = mysql_query('select * from ' . Devise::TABLE . ' order by nom');

    while ($result && $row = mysql_fetch_object($result)) {
        $devises[$row->id] = $row;
    }

    // Charger les langues
    $langues = array();

    $result = mysql_query('select * from ' . Lang::TABLE);

    while ($result && $row = mysql_fetch_object($result)) {
        $langues[$row->id] = $row;
    }
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>     
        <?php 
        $menu = "configuration";
        require_once("title.php"); 
        ?>
    </head>

    <body>           
        <?php
        $menu = "configuration";
        require_once("entete.php");
        ?>

        <div class="row-fluid">
            <div class="span12">
                <p align="left">
                    <a href="accueil.php" class="lien04"><?php echo trad('Accueil', 'admin'); ?> </a>
                    <img src="gfx/suivant.gif" width="12" height="9" border="0" />
                    <a href="configuration.php" class="lien04"><?php echo trad('Configuration', 'admin'); ?></a>
                    <img src="gfx/suivant.gif" width="12" height="9" border="0" />
                    <a href="plugins.php" class="lien04"><?php echo trad('Gestion_plugins', 'admin'); ?></a>
                    <img src="gfx/suivant.gif" width="12" height="9" border="0" />
                    <?php echo ActionsAdminModules::instance()->lire_titre_module($module); ?>
                </p>
            </div>
        </div>
        <div class="row">
            <div class="span12">
                <div class="entete_liste_config">                                            
                    <caption>
                        <h3><?php echo trad('DESCRIPTION DU PLUGIN', 'admin'); ?></h3>  				
                    </caption>
                </div>
                <form action="plugins_modifier.php" id="formulaire" method="post">

                    <input type="hidden" name="action" value="modifier" />
                    <input type="hidden" name="nom" value="<?php echo($module->nom); ?>" />
                    <input type="hidden" name="id" value="<?php echo($moduledesc->id); ?>" />
                    <input type="hidden" name="lang" value="<?php echo($lang); ?>" />

                    <!-- bloc descriptif de la rubrique -->
                    <table class="table table-striped">
                        <tbody>
                            <tr>
                                <td class="span4"><?php echo trad('Changer_langue', 'admin'); ?></td>
                                <td class="span8">
                                    <ul class="nav nav-pills">
                                        <?php foreach (LangAdmin::getInstance()->getList() as $displayLang): ?>
                                            <li class="<?php if ($displayLang->id == $lang) { ?>active<?php } ?>"><a href="plugins_modifier.php?id=<?php echo $module->id; ?>&lang=<?php echo $displayLang->id; ?>" class="change-page"><img src="gfx/lang<?php echo $displayLang->id; ?>.gif" /></a></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </td>
                            </tr>

                            <tr>
                                <td class="designation"><?php echo trad('Titre', 'admin'); ?></td>
                                <td><input name="titre" id="titre" type="text" class="span8" value="<?php echo htmlspecialchars($moduledesc->titre); ?>"/></td>
                            </tr>
                            
                            <tr>
                                <td class="designation"><?php echo trad('Chapo', 'admin'); ?><br /><span class="note"><?php echo trad('courte_descript_intro', 'admin'); ?></span></td>
                                <td> <textarea name="chapo" id="chapo" cols="40" rows="2" class="span8"><?php echo($moduledesc->chapo); ?></textarea></td>
                            </tr>

                            <tr<?php //echo ($module->type != Modules::PAIEMENT) ? 'bottom' : '' ?>>
                                <td class="designation"><?php echo trad('Description', 'admin'); ?><br /><span class="note"><?php echo trad('description_complete', 'admin'); ?></span></td>
                                <td><textarea name="description" id="description" rows="15" class="span8"><?php echo($moduledesc->description); ?></textarea></td>
                            </tr>

                            <?php if ($module->type == Modules::PAIEMENT) { ?>
                                <tr>
                                    <td class="designation"><?php echo trad('Devise', 'admin'); ?><br /><span class="note"><?php echo trad('devis_complete', 'admin'); ?></span></td>
                                    <td>
                                        <select name="devise">
                                            <option value="0"><?php echo trad('Par défaut', 'admin') ?></option>
                                            <?php
                                            foreach ($devises as $devise) {
                                                ?>
                                                <option value="<?php echo $devise->id ?>" <?php echo $devise->id == $moduledesc->devise ? 'selected="selected"' : '' ?>><?php echo $devise->nom ?></option>'
                                            <?php
                                            }
                                            ?>
                                        </select>
                                    </td>
                                </tr>
                            <?php } ?>
                         </tbody>
                    </table>
                    <p>
                        <button class="btn btn-large btn-block btn-primary" type="submit"><?php echo trad('VALIDER_LES_MODIFICATIONS', 'admin'); ?></button>
                    </p>
                </form>
            </div>
        <?php require_once("pied.php"); ?>
        </div>
    </body>
</html>
