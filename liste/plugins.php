<?php

function afficher_liste_plugins($type, $label) {
?>
    <div class="bigtable">
    <table class="table table-striped">
        <thead>
        <caption><h4><?php echo $label ?></h4></caption> 
        </thead>
        <tbody>
            <?php foreach(ActionsAdminModules::instance()->lister($type) as $plugin): ?>
            <?php $documentation = '';

                if (trim($plugin->xml->documentation) != "") {

                        $doc_file = sprintf("%s/%s",
                                ActionsAdminModules::instance()->lire_chemin_module($plugin->nom),
                                $plugin->xml->documentation
                        );

                        if (file_exists($doc_file))
                                $documentation = sprintf("%s/%s/%s",
                                        ActionsAdminModules::instance()->lire_url_base(),
                                        $plugin->nom,
                                        $plugin->xml->documentation
                                );
                }
            ?>
            <tr class="<?php if ($plugin->actif != 1) echo "warning"; else echo "success" ?>">
                <td class="span9">
                    <strong><?php echo ActionsAdminModules::instance()->lire_titre_module($plugin); ?><?php echo $plugin->xml->version != '' ? " v".$plugin->xml->version : ''?></strong>
                    <p><small><?php echo ($plugin->xml->descriptif->description != "")?$plugin->xml->descriptif->description:trad('Description non disponible', 'admin') ?></small></p>
                </td>
                <td>
                    <?php if ($plugin->activable && $plugin->type != Modules::FILTRE) { ?>
                        <a href="plugins_modifier.php?nom=<?php echo $plugin->nom ?>&actif=0"><?php echo trad('Editer', 'admin'); ?></a>
                    <?php } ?>
                </td>
                <td>
                    <?php if($plugin->actif): ?>
                        <a href="plugins.php?action=desactiver&nom=<?php echo $plugin->nom ?>"><?php echo trad('Desactiver', 'admin'); ?></a>
                    <?php elseif($plugin->activable): ?>
                        <a href="plugins.php?action=activer&nom=<?php echo $plugin->nom ?>"><?php echo trad('Activer', 'admin'); ?></a>
                    <?php else: ?>
                        <?php if (! empty($plugin->xml->thelia)): ?>
                            <?php echo trad('Nécessite Thelia %s', 'admin', $plugin->xml->thelia); ?>
                        <?php else: ?>
                            <?php echo trad('Incompatible', 'admin'); ?>
                        <?php endif; ?>
                    <?php endif; ?>
                </td>
                <td>
                   <?php if(!empty($documentation)): ?>
                        <a href="<?php echo $documentation; ?>" target="_doc_module" title="<?php echo trad("Lire la documentation ce plugin"); ?>"><?php echo trad('Documentation', 'admin'); ?></a>
                    <?php endif; ?>
                </td>
                <td>
                    <a href="plugins.php?id=<?php echo($plugin->id); ?>&action=modclassement&type=M"><i class="icon-arrow-up"></i></a>
                    <span class="object_classement_editable" object-action="changeClassementPlugin" object-id="<?php echo $plugin->id; ?>"><?php echo $plugin->classement; ?></span>
                    <a href="plugins.php?id=<?php echo($plugin->id); ?>&action=modclassement&type=D"><i class="icon-arrow-down"></i></a>
                </td>
                <td>
                    <div class="btn-group">
                        <a class="btn btn-mini" href="plugins.php?action=supprimer&nom=<?php echo $plugin->nom; ?>" title="<?php echo trad("Supprimer ce plugin", 'admin'); ?>" ><i class="icon-trash"></i></a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php
}

function afficher_plugins() {

   afficher_liste_plugins(Modules::CLASSIQUE, trad('LISTE_PLUGINS_CLASSIQUES', 'admin'));
   afficher_liste_plugins(Modules::PAIEMENT, trad('LISTE_PLUGINS_PAIEMENTS', 'admin'));
   afficher_liste_plugins(Modules::TRANSPORT, trad('LISTE_PLUGINS_TRANSPORTS', 'admin'));
   afficher_liste_plugins(Modules::FILTRE, trad('LISTE_FILTRE', 'admin'));

}
?>
