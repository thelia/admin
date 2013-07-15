<?php
require_once("auth.php");

//error_reporting(E_ALL);
if(! est_autorise("acces_clients"))
    exit;

if(!empty($action))
{
    switch($action)
    {
        case 'delete':
            $tempcli = new Client();
            if($tempcli->charger_ref($ref))
            {
                $tempcli->delete();
                ActionsModules::instance()->appel_module("supcli", $tempcli);
            }
            break;
        case 'addCustomer':
            $clientToAdd = new Client();
            $clientToAdd->raison = strip_tags($raison);
            $clientToAdd->nom = strip_tags($nom);
            $clientToAdd->entreprise = strip_tags($entreprise);
            $clientToAdd->prenom = strip_tags($prenom);
            $clientToAdd->telfixe = strip_tags($telfixe);
            $clientToAdd->telport =strip_tags($telport);
            if(filter_var($email, FILTER_VALIDATE_EMAIL) && $email)
                $clientToAdd->email = strip_tags($email);
            $clientToAdd->adresse1 = strip_tags($adresse1);
            $clientToAdd->adresse2 = strip_tags($adresse2);
            $clientToAdd->adresse3 = strip_tags($adresse3);
            $clientToAdd->cpostal = strip_tags($cpostal);
            $clientToAdd->ville = strip_tags($ville);
            $clientToAdd->siret = strip_tags($siret);
            $clientToAdd->intracom = strip_tags($intracom);
            $clientToAdd->pays = strip_tags($pays);
            $clientToAdd->type = ($type=='on')?1:0;
            $clientToAdd->lang = ActionsLang::instance()->get_id_langue_courante();

            $clientMentor = new Client();
            if($clientMentor->charger_mail($parrain))
            {
                $clientToAdd->parrain = $clientMentor->id;
            }

            $pass = genpass(8);
            $clientToAdd->motdepasse = $pass;

            if($clientToAdd->raison!="" && $clientToAdd->prenom!="" && $clientToAdd->nom!="" && $clientToAdd->email!="" && $clientToAdd->motdepasse!="" && $clientToAdd->email && ! $clientToAdd->existe($email) && $clientToAdd->adresse1 !="" && $clientToAdd->cpostal!="" && $clientToAdd->ville !="" && $clientToAdd->pays !="" && $clientMentor->email==$parrain)
            {
                $clientToAdd->crypter();

                $clientToAdd->id = $clientToAdd->add();

                $clientToAdd->ref = date("ymdHi") . genid($clientToAdd->id, 6);
                $clientToAdd->maj();

                ClientAdmin::getInstance()->sendMailCreation($clientToAdd, $pass);
                
                ActionsModules::instance()->appel_module("ajoutclient", $clientToAdd);

                redirige('client_visualiser.php?ref=' . $clientToAdd->ref);
            }
            else
            {
                $adderror = 1;
            }
            break;
    }
}

if(!isset($page)) $page=0;
if($page=="") $page=1;

$query = ClientAdmin::getInstance()->getRequest('count');

$pagination = new PaginationAdmin($query, $page);

if(isset($classement) && $classement != "") $ordclassement = "ORDER BY ".$classement;
else $ordclassement = "ORDER BY nom ASC";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<?php require_once("title.php");?>
</head>
<body>
<?php

	ActionsAdminModules::instance()->inclure_module_admin("client_top");
$menu = "client";
$breadcrumbs = Breadcrumb::getInstance()->getSimpleList(trad('Gestion_clients', 'admin'));
require_once("entete.php");
?>
    <div class="row-fluid">
        
        <div class="span12">
            
            <h3>
                <?php echo trad('LISTE_CLIENTS', 'admin'); ?>
                
                <div class="btn-group">
                    <a class="btn btn-large" title="<?php echo trad('ajouter', 'admin'); ?>" href="#clientAddModal" data-toggle="modal">
                        <i class="icon-plus-sign icon-white"></i>
                    </a>
                </div>
            </h3>
<?php
	ActionsAdminModules::instance()->inclure_module_admin("client");
?>
            <div class="bigtable">
            <table class="table table-striped" id="clientTable">
                <thead>
                    <tr>
                        <th><?php echo trad('Num_client', 'admin'); ?></th>
                        <th><?php echo trad('Societe', 'admin'); ?></th>
                        <th>
                                <?php echo trad('Nom', 'admin'); ?> &amp; <?php echo trad('Prenom', 'admin'); ?>
                        </th>
                        <th><?php echo trad('Derniere_commande', 'admin'); ?></th>
                        <th><?php echo trad('Montant_commande', 'admin'); ?></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
<?php
foreach(ClientAdmin::getInstance()->getList('ASC', 'nom', $pagination->getStarted(), $pagination->getViewPerPage()) as $client)
{
?>
                    <tr>
                        <td><?php echo $client['ref'] ?></td>
                        <td><?php echo $client['entreprise'] ?></td>
                        <td><?php echo $client['nom'] ?> <?php echo $client['prenom'] ?></td>
                        <td><?php echo $client['date'] ?></td>
                        <td><?php echo $client['somme'] ?></td>
                        <td>
                            <div class="btn-group">
                                <a class="btn btn-mini" title="<?php echo trad('editer', 'admin'); ?>" href="client_visualiser.php?ref=<?php echo $client['ref']; ?>"><i class="icon-edit"></i></a>
                                <a class="btn btn-mini" title="<?php echo trad('send_email', 'admin'); ?>" href="mailto:<?php echo($client['email']); ?>"><i class="icon-envelope"></i></a>
                                <a class="btn btn-mini js-delete-client" title="<?php echo trad('supprimer', 'admin'); ?>" href="#deleteModal" data-toggle="modal" client-info="<?php echo $client['nom'] ?> <?php echo $client['prenom'] ?>" client-ref="<?php echo $client['ref'] ?>"><i class="icon-trash"></i></a>
                            </div>
                        </td>
                    </tr>
<?php
}
?>
                </tbody>
            </table>
            </div>
        </div>
        
    </div>
    
    <?php if($pagination->getTotalPages() > 0): ?>
    <div class="row-fluid">
        <div class="span12 spacetop18">
            <div class="pagination pagination-centered">
                <ul>
                    <?php if($pagination->getCurrentPage() == 1 ): ?>
                        <li class="disabled">
                            <a>Prev</a>
                        </li>
                    <?php else: ?>
                        <li>
                            <a href="client.php?page=<?php echo $pagination->getPreviousPage(); ?>">Prev</a>
                        </li>   
                    <?php endif; ?>
                        
                    <?php if($pagination->getTotalPages() > $pagination->getMaxPagesDisplayed() && $pagination->getCurrentPage() > 1): ?>
                        <li>
                            <a href="client.php?page=1">...</a>
                        </li>
                    <?php endif; ?>
                    
                    <?php for($i = $pagination->getStartedPagination(); $i <= $pagination->getEndPagination(); $i ++ ): ?>
                        <?php if($pagination->getCurrentPage() == $i): ?>
                            <li class="active"><a><?php echo $i; ?></a></li>
                        <?php else: ?>
                            <li><a href="client.php?page=<?php echo $i; ?>"><?php echo $i; ?></a></li>
                        <?php endif; ?>
                    
                    <?php endfor; ?>
                        
                    <?php if($pagination->getTotalPages() > $pagination->getMaxPagesDisplayed() && $pagination->getCurrentPage() < $pagination->getTotalPages()): ?>
                        <li>
                            <a href="client.php?page=<?php echo $pagination->getTotalPages(); ?>">...</a>
                        </li>
                    <?php endif; ?>
                        
                    <?php if($pagination->getCurrentPage() == $pagination->getTotalPages()): ?>
                        <li class="disabled">
                            <a>Next</a>
                        </li>
                    <?php else: ?>
                        <li>
                            <a href="client.php?page=<?php echo $pagination->getNextPage(); ?>">Next</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="row-fluid">
        <div class="span12">
            
            <!-- client delation -->
            <div class="modal hide" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h3><?php echo trad('Cautious', 'admin'); ?></h3>
                </div>
                <div class="modal-body">
                    <p><?php echo trad('DeleteClientWarning', 'admin'); ?></p>
                    <p id="clientDelationInfo"></p>
                </div>
                <div class="modal-footer">
                    <a class="btn" data-dismiss="modal" aria-hidden="true"><?php echo trad('Non', 'admin'); ?></a>
                    <a class="btn btn-primary" id="clientDelationLink"><?php echo trad('Oui', 'admin'); ?></a>
                </div>
            </div>
            
            <!-- client add -->
            <div class="modal hide" id="clientAddModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <form method="POST" action="client.php">
                <input type="hidden" name="action" value="addCustomer" />
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h3 ><?php echo trad('CREATION_CLIENT', 'admin'); ?></h3>
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
                            <tr>
                                <td><?php echo trad('Societe', 'admin'); ?></td>
                                <td>
                                    <input type="text" value="<?php echo $entreprise ?>" name="entreprise"  />
                                </td>
                            </tr>
                            <tr>
                                <td><?php echo trad('Siret', 'admin'); ?></td>
                                <td>
                                    <input type="text" value="<?php echo $siret ?>"" name="siret"  />
                                </td>
                            </tr>
                            <tr>
                                <td><?php echo trad('Numintracom', 'admin'); ?></td>
                                <td>
                                    <input type="text" value="<?php echo $intracom ?>" name="intracom"  />
                                </td>
                            </tr>
                            <tr>
                                <td><?php echo trad('Civilite', 'admin'); ?></td>
                                <td>
                                    <select name="raison" >
<?php
$raisondesc = new Raisondesc();
$qListTitles = "SELECT * FROM " . Raisondesc::TABLE . " WHERE lang=" . ActionsLang::instance()->get_id_langue_courante();
$rListTitles = $raisondesc->query($qListTitles);
while($rListTitles && $theTitle = $raisondesc->fetch_object($rListTitles, 'Raisondesc'))
{
?>
                                        <option value="<?php echo $theTitle->raison; ?>" <?php if($raison==$theTitle->raison){ ?>selected="selected"<?php } ?>>
                                            <?php echo $theTitle->long; ?>
                                        </option>
<?php
}
?>
                                    </select>
                                </td>
                            </tr>
                            <tr class="<?php if($adderror && empty($nom)){ ?>error<?php } ?>">
                                <td><?php echo trad('Nom', 'admin'); ?> *</td>
                                <td>
                                    <input type="text" value="<?php echo $nom ?>" name="nom"  />
                                </td>
                            </tr>
                            <tr class="<?php if($adderror && empty($prenom)){ ?>error<?php } ?>">
                                <td><?php echo trad('Prenom', 'admin'); ?> *</td>
                                <td>
                                    <input type="text" value="<?php echo $prenom ?>" name="prenom"  />
                                </td>
                            </tr>
                            <tr class="<?php if($adderror && empty($adresse1)){ ?>error<?php } ?>">
                                <td><?php echo trad('Adresse', 'admin'); ?> *</td>
                                <td>
                                    <input type="text" value="<?php echo $adresse1 ?>" name="adresse1"  />
                                </td>
                            </tr>
                            <tr>
                                <td><?php echo trad('Adressesuite', 'admin'); ?></td>
                                <td>
                                    <input type="text" value="<?php echo $adresse2 ?>" name="adresse2"  />
                                </td>
                            </tr>
                            <tr>
                                <td><?php echo trad('Adressesuite', 'admin'); ?></td>
                                <td>
                                    <input type="text" value="<?php echo $adresse3 ?>" name="adresse3"  />
                                </td>
                            </tr>
                            <tr class="<?php if($adderror && empty($cpostal)){ ?>error<?php } ?>">
                                <td><?php echo trad('CP', 'admin'); ?> *</td>
                                <td>
                                    <input type="text" value="<?php echo $cpostal ?>" name="cpostal"  />
                                </td>
                            </tr>
                            <tr class="<?php if($adderror && empty($ville)){ ?>error<?php } ?>">
                                <td><?php echo trad('Ville', 'admin'); ?> *</td>
                                <td>
                                    <input type="text" value="<?php echo $ville ?>" name="ville"  />
                                </td>
                            </tr>
                            <tr>
                                <td><?php echo trad('Pays', 'admin'); ?></td>
                                <td>
                                    <select name="pays" >
<?php
$paysdesc = new Paysdesc();
$qListCountries = "SELECT * FROM " . Paysdesc::TABLE . " WHERE lang=" . ActionsLang::instance()->get_id_langue_courante();
$rListCountries = $paysdesc->query($qListCountries);
while($rListCountries && $theCountry = $paysdesc->fetch_object($rListCountries, 'Paysdesc'))
{
?>
                                        <option value="<?php echo $theCountry->pays; ?>" <?php if($pays==$theCountry->pays){ ?>selected="selected"<?php } ?>>
                                            <?php echo $theCountry->titre; ?>
                                        </option>
<?php
}
?>
                                    </select>
                            </tr>
                            <tr>
                                <td><?php echo trad('Telfixe', 'admin'); ?></td>
                                <td>
                                    <input type="text" value="<?php echo $telfixe ?>" name="telfixe"  />
                                </td>
                            </tr>
                            <tr>
                                <td><?php echo trad('Telport', 'admin'); ?></td>
                                <td>
                                    <input type="text" value="<?php echo $telport ?>" name="telport"  />
                                </td>
                            </tr>
                            <tr class="<?php if($adderror && (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL) || $clientToAdd->existe($email))){ ?>error<?php } ?>">
                                <td>
                                    <?php echo trad('Email', 'admin'); ?> *
                                    <?php if($adderror && !filter_var($email, FILTER_VALIDATE_EMAIL)){ ?><br /><?php echo trad('email_bad_format', 'admin'); ?><?php }
                                    elseif($adderror && $clientToAdd->existe($email)){ ?><br /><?php echo trad('email_already_exists', 'admin'); ?><?php } ?>
                                </td>
                                <td>
                                    <input type="text" value="<?php echo $email ?>" name="email"  />
                                </td>
                            </tr>
                            <tr>
                                <td><?php echo trad('Remise', 'admin'); ?></td>
                                <td>
                                    <input type="text" value="<?php echo $pourcentage ?>" name="pourcentage"  />%
                                </td>
                            </tr>
                            <tr>
                                <td><?php echo trad('Revendeur', 'admin'); ?></td>
                                <td>
                                    <input type="checkbox" name="type" <?php if($adderror && $type=='on') { ?>checked="checked"<?php } ?> />
                                </td>
                            </tr>
                            <tr class="<?php if($clientMentor && ($clientMentor->email!=$parrain)){ ?>error<?php } ?>">
                                <td>
                                    <?php echo trad('Parrain', 'admin'); ?> (email)
                                    <?php if($clientMentor && ($clientMentor->email!=$parrain)){ ?><br /><?php echo trad('mentor_email_incorrect', 'admin'); ?><?php } ?>
                                </td>
                                <td>
                                    <input type="text" value="<?php echo $parrain ?>" name="parrain"  />
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
            
        </div>
    </div>
<?php
	ActionsAdminModules::instance()->inclure_module_admin("client_bottom");
?>
<?php require_once("pied.php");?>
<script type="text/javascript">
jQuery(function($)
{
    $('.js-delete-client').click(function()
    {
        $('#clientDelationInfo').html($(this).attr('client-ref') + ' - ' + $(this).attr('client-info'));
        $('#clientDelationLink').attr('href', 'client.php?action=delete&ref=' + $(this).attr('client-ref'));
    });
    
<?php if($adderror){ ?>
    $('#clientAddModal').modal();
<?php } ?>
    
});
</script>
</body>
</html>
