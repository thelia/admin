<?php
require_once("auth.php");
require_once("../fonctions/divers.php");
if (!est_autorise("acces_catalogue"))
    exit;

use Symfony\Component\HttpFoundation\Request;
$request = Request::createFromGlobals();

$page = $request->get('page', 1);

$pagination = new PaginationAdmin(PromoAdmin::getInstance()->getRequest('count'), $page);

$errorCode = 0;
$addError = 0;

$editError = array();


try {
    ActionsAdminPromo::getInstance()->action($request);
} catch (TheliaAdminException $e) {
    $errorCode = $e->getCode();
    switch ($errorCode)
    {
        case TheliaAdminException::PROMO_ADD_ERROR:
            $addError = 1;
            $errorData = $e->getData();
            break;
        case TheliaAdminException::PROMO_EDIT_ERROR:
            $editError[$request->get("id")] = 1;
            break;
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
	ActionsAdminModules::instance()->inclure_module_admin("promo_top");
$menu = "paiement";
$breadcrumbs = Breadcrumb::getInstance()->getSimpleList(trad('Gestion_codes_promos', 'admin'));
require_once("entete.php");
?>
<div class="row-fluid">
    <div class="span12">
        <h3>
            <?php echo strtoupper(trad('Gestion_codes_promos', 'admin')); ?>
            
            <div class="btn-group">
                <a class="btn btn-large" title="<?php echo trad('ajouter', 'admin'); ?>" href="#promoModal" data-toggle="modal">
                    <i class="icon-plus-sign icon-white"></i>
                </a>
            </div>
        </h3>
    </div>
</div>

<?php
	ActionsAdminModules::instance()->inclure_module_admin("promo");
?>

<div class="row-fluid">
    <div class="span12">
        <div class="bigtable">
        <table class="table table-striped" id="the_table_id">
            <thead>
                <tr>
                    <th class="span1"><?php echo trad('Code', 'admin'); ?></th>
                    <th class="span2"><?php echo trad('Type', 'admin'); ?></th>
                    <th class="span1"><?php echo trad('Montant', 'admin'); ?></th>
                    <th class="span1"><?php echo trad('Achat_mini', 'admin'); ?></th>
                    <th class="span1"><?php echo trad('Code_actif', 'admin'); ?></th>
                    <th class="span1">Nb util.</th>
                    <th class="span2">Limite</th>
                    <th class="span2"><?php echo trad('Date_expi', 'admin'); ?></th>
                    <th class="span1"></th>
                </tr>
            </thead>
            <tbody>
<?php
foreach(PromoAdmin::getInstance()->getList($pagination->getStarted(), $pagination->getViewPerPage()) as $codePromo)
{
?>
                    <tr id="js_promo_<?php echo $codePromo->id; ?>">
                        <td>
                            <span class="js-code">
                                <?php echo $codePromo->code ; ?>
                            </span>
                        </td>
                        <td>
                            <span class="js-type">
                                <?php echo ($codePromo->type == Promo::TYPE_SOMME)?trad('somme', 'admin'):trad('pourcentage', 'admin'); ?>
                            </span>
                        </td>
                        <td>
                            <span class="js-valeur">
                                <?php echo $codePromo->valeur . (($codePromo->type == Promo::TYPE_SOMME)?'€':'%'); ?>
                            </span>
                        </td>
                        <td>
                            <span class="js-mini">
                                <?php echo $codePromo->mini ; ?>
                            </span>
                        </td>
                        <td>
                            <span class="js-actif">
                                <?php echo ($codePromo->actif==1)?trad('oui', 'admin'):trad('non', 'admin') . ' <i class="icon-warning-sign"></i>'; ?>
                            </span>
                        </td>
                        <td>
                            <span class="js-utilise">
                                <?php echo $codePromo->utilise . (($codePromo->limite!=0 && $codePromo->utilise >= $codePromo->limite)?' <i class="icon-warning-sign"></i>':''); ?>
                            </span>
                        </td>
                        <td>
                            <span class="js-limite">
                                <?php echo ($codePromo->limite==0)?trad('Illimite', 'admin'):$codePromo->limite; ?>
                            </span>
                        </td>
                        <td>
                            <span class="js-date-expi">
                                <?php echo ($codePromo->datefin==0)?trad('N\'expire pas', 'admin'):$codePromo->datefin . (($codePromo->datediff > 0)?' <i class="icon-warning-sign"></i>':''); ?>
                            </span>
                        </td>
                        <td>
                            <div class="btn-group">
                                <a class="btn btn-mini js-edit-promo" title="<?php echo trad('editer', 'admin'); ?>" promo-id="<?php echo $codePromo->id; ?>" promo-code="<?php echo $codePromo->code; ?>" promo-type="<?php echo $codePromo->type; ?>" promo-valeur="<?php echo $codePromo->valeur; ?>" promo-mini="<?php echo $codePromo->mini; ?>" promo-actif="<?php echo $codePromo->actif; ?>" promo-nb-util="<?php echo $codePromo->utilise; ?>" promo-limite="<?php echo $codePromo->limite; ?>" promo-date-expi="<?php echo $codePromo->datefin; ?>"><i class="icon-edit"></i></a>
                                <a class="btn btn-mini js-delete-promo" title="<?php echo trad('supprimer', 'admin'); ?>" href="#deleteModal" data-toggle="modal" promo-code="<?php echo $codePromo->code; ?>" promo-id="<?php echo $codePromo->id ?>"><i class="icon-trash"></i></a>
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
                            <a href="promo.php?page=<?php echo $pagination->getPreviousPage(); ?>">Prev</a>
                        </li>   
                    <?php endif; ?>
                        
                    <?php if($pagination->getTotalPages() > $pagination->getMaxPagesDisplayed() && $pagination->getCurrentPage() > 1): ?>
                        <li>
                            <a href="promo.php?page=1">...</a>
                        </li>
                    <?php endif; ?>
                    
                    <?php for($i = $pagination->getStartedPagination(); $i <= $pagination->getEndPagination(); $i ++ ): ?>
                        <?php if($pagination->getCurrentPage() == $i): ?>
                            <li class="active"><a><?php echo $i; ?></a></li>
                        <?php else: ?>
                            <li><a href="promo.php?page=<?php echo $i; ?>"><?php echo $i; ?></a></li>
                        <?php endif; ?>
                    
                    <?php endfor; ?>
                        
                    <?php if($pagination->getTotalPages() > $pagination->getMaxPagesDisplayed() && $pagination->getCurrentPage() < $pagination->getTotalPages()): ?>
                        <li>
                            <a href="promo.php?page=<?php echo $pagination->getTotalPages(); ?>">...</a>
                        </li>
                    <?php endif; ?>
                        
                    <?php if($pagination->getCurrentPage() == $pagination->getTotalPages()): ?>
                        <li class="disabled">
                            <a>Next</a>
                        </li>
                    <?php else: ?>
                        <li>
                            <a href="promo.php?page=<?php echo $pagination->getNextPage(); ?>">Next</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="row-fluid">
        <div class="span12">
           
            <!-- promo delation -->
            <div class="modal hide" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h3><?php echo trad('Cautious', 'admin'); ?></h3>
                </div>
                <div class="modal-body">
                    <p><?php echo trad('DeletePromoWarning', 'admin'); ?></p>
                    <p id="promoDelationInfo"></p>
                </div>
                <div class="modal-footer">
                    <a class="btn" data-dismiss="modal" aria-hidden="true"><?php echo trad('Non', 'admin'); ?></a>
                    <a class="btn btn-primary" id="promoDelationLink"><?php echo trad('Oui', 'admin'); ?></a>
                </div>
            </div>
            
            <!-- promo add -->
            <div class="modal hide" id="promoModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <form method="POST" action="promo.php">
                <input type="hidden" name="action" value="add" />
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h3 ><?php echo trad('CREATION_PROMO', 'admin'); ?></h3>
                </div>
                <div class="modal-body">
                    
<?php if($addError){ ?>
                    <div class="alert alert-block alert-error fade in">
                        <h4 class="alert-heading"><?php echo trad('Cautious', 'admin'); ?></h4>
                    <p><?php echo trad('check_information', 'admin'); ?></p>
                    </div>
<?php } ?>
                    
                    <table class="table table-striped">
                        <tbody>
                            <tr class="<?php if($addError && ($errorData->code==='' || PromoAdmin::testCodeExists($errorData->code))){ ?>error<?php } ?>">
                                <td>
                                    <?php echo trad('Code', 'admin'); ?> *
                                    <?php if($addError && PromoAdmin::testCodeExists($errorData->code)){ ?>
                                    <br /><?php echo trad('promo_already_exists', 'admin'); ?>
                                    <?php } ?>
                                </td>
                                <td>
                                    <input type="text" name="code" value="<?php echo ($addError)?$errorData->code:''; ?>" />
                                </td>
                            </tr>
                            <tr class="<?php if($addError && $errorData->type===''){ ?>error<?php } ?>">
                                <td><?php echo trad('Type', 'admin'); ?> *</td>
                                <td>
                                    <label class="radio"><?php echo trad('somme', 'admin'); ?>
                                    <input name="type" type="radio" value="<?php echo Promo::TYPE_SOMME ?>" <?php if($addError && $errorData->type==Promo::TYPE_SOMME){ ?>checked="checked"<?php } ?> /></label>
                                    <label class="radio"><?php echo trad('pourcentage', 'admin'); ?>
                                    <input name="type" type="radio" value="<?php echo Promo::TYPE_POURCENTAGE ?>" <?php if($addError && $errorData->type==Promo::TYPE_POURCENTAGE){ ?>checked="checked"<?php } ?> /></label>
                                </td>
                            </tr>
                            <tr class="<?php if($addError && $errorData->valeur===''){ ?>error<?php } ?>">
                                <td><?php echo trad('Montant_code_promo', 'admin'); ?> *</td>
                                <td>
                                    <input type="text" class="input-mini" name="valeur" value="<?php echo ($addError)?$errorData->valeur:''; ?>"  />
                                </td>
                            </tr>
                            <tr class="<?php if($addError && $errorData->mini===''){ ?>error<?php } ?>">
                                <td><?php echo trad('Montant_achat_mini', 'admin'); ?> *</td>
                                <td>
                                    <input type="text" class="input-mini" value="<?php echo $request->request->get('mini'); ?>" name="mini" value="<?php echo ($addError)?$errorData->mini:''; ?>" />
                                </td>
                            </tr>
                            <tr class="<?php if($addError && $errorData->actif===''){ ?>error<?php } ?>">
                                <td><?php echo trad('Code_actif', 'admin'); ?> *</td>
                                <td>
                                    <label class="radio"><?php echo trad('Oui', 'admin'); ?>
                                        <input name="actif" type="radio" value="1" <?php if($addError && $errorData->actif==='1'){ ?>checked="checked"<?php } ?> />
                                    </label>
                                    <label class="radio"><?php echo trad('Non', 'admin'); ?>
                                        <input name="actif" type="radio" value="0" <?php if($addError && $errorData->actif==='0'){ ?>checked="checked"<?php } ?> />
                                    </label>
                                </td>
                            </tr>
                            <tr class="<?php if($addError && $errorData->limite === ''){ ?>error<?php } ?>">
                                <td><?php echo trad('Utilisation', 'admin'); ?> *</td>
                                <td>
                                    <label><?php echo trad('Limitee_a', 'admin'); ?>
                                    <div class="input-prepend">
                                        <span class="add-on">
                                            <input name="limite" id="limit_fixed_radio" type="radio"  value="1" <?php if($addError && $errorData->form_use_type==='1'){ ?>checked="checked"<?php } ?> />
                                        </span>
                                        <input type="text" id="limit_value" class="input-mini" name="nombre_limite" value="<?php echo ($addError && $errorData->limite>0)?$errorData->limite:''; ?>" />
                                    </div></label>
                                    <label class="radio"><?php echo trad('Illimite', 'admin'); ?>
                                    <input name="limite" type="radio"  value="0" <?php if($addError && $errorData->form_use_type==='0'){ ?>checked="checked"<?php } ?> /></label>
                                </td>
                            </tr>
                            <tr class="<?php if($addError && $errorData->datefin === ''){ ?>error<?php } ?>">
                                <td><?php echo trad('Date_expi', 'admin'); ?> *</td>
                                <td>
                                    <label><?php echo trad('Expire_le', 'admin'); ?>
                                    <div class="input-prepend input-append">
                                        <span class="add-on">
                                            <input name="expiration" id="expiration_fixed_radio" type="radio"  value="1" <?php if($addError && $errorData->form_expiration_type==='1'){ ?>checked="checked"<?php } ?> />
                                        </span>
                                        <input type="text" id="expiration_value" class="input-small" name="date_expi" value="<?php echo ($addError && $errorData->form_expiration_type==='1')?$errorData->datefin:''; ?>" />
                                        <button class="btn" id="expiration_calendar_button" type="button"><i class="icon-calendar"></i></button>
                                    </div></label>
                                    <label class="radio"><?php echo trad('N_expire_pas', 'admin'); ?>
                                    <input name="expiration" type="radio"  value="0" <?php if($addError && $errorData->form_expiration_type==='0'){ ?>checked="checked"<?php } ?> /></label>
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
        
</div>
    
<div id="module_bloc" style="display:none">
test
</div>

<?php
	ActionsAdminModules::instance()->inclure_module_admin("promo_bottom");
?>

<?php require_once("pied.php"); ?>
<link type="text/css" href="js/jquery-ui-1.9.1/css/ui-lightness/jquery-ui-1.9.1.custom.min.css" rel="stylesheet" />
<script type="text/javascript" src="js/jquery-ui-1.9.1/js/jquery-ui-1.9.1.custom.min.js"></script>
<script type="text/javascript">

jQuery(function($)
{
    /*modal*/
    var cancelModal = false;
    $('#deleteModal').on('show', function (e, x)
    {
        if(cancelModal)
            e.preventDefault();
    })
<?php if($addError){ ?>
    $('#promoModal').modal();
<?php } ?>
    
    $('.js-delete-promo').click(function()
    {
        if($(this).is('.disabled'))
            return;
        
        $('#promoDelationInfo').html($(this).attr('promo-code'));
        $('#promoDelationLink').attr('href', 'promo.php?action=delete&id=' + $(this).attr('promo-id'));
    });
    
    $('.js-edit-promo').click(function()
    {
        if($(this).is('.disabled'))
            return;
        
        $('.js-edit-promo, .js-delete-promo').addClass('disabled');
        cancelModal = true;
        
        var promoId = $(this).attr('promo-id');
        
        Thelia_promo.generateRowModule($(this).attr('promo-id')).insertAfter(
            Thelia_promo.generateRow(
                $(this).attr('promo-code'),
                $(this).attr('promo-type'),
                $(this).attr('promo-valeur'),
                $(this).attr('promo-mini'),
                $(this).attr('promo-actif'),
                $(this).attr('promo-nb-util'),
                $(this).attr('promo-limite'),
                $(this).attr('promo-date-expi'),
                function(rowResult)
                {
                    /*valid*/

                    $('.control-group').removeClass('error');

                    /*do few cheking*/
                    var allGood = true;
                    if(!(rowResult.type.val()==1 || rowResult.type.val()==2))
                    {
                        allGood = false;
                        rowResult.type.parent().addClass('error');
                    }
                    if(parseFloat(rowResult.valeur.val()) != rowResult.valeur.val())
                    {
                        allGood = false;
                        rowResult.valeur.parent().addClass('error');
                    }
                    if(parseFloat(rowResult.mini.val()) != rowResult.mini.val())
                    {
                        allGood = false;
                        rowResult.mini.parent().addClass('error');
                    }
                    if(!(rowResult.actif.val()==='0' || rowResult.actif.val()==1))
                    {
                        allGood = false;
                        rowResult.actif.parent().addClass('error');
                    }
                    if(!(rowResult.limite.val()==='0' || rowResult.limite.val()==1) || (rowResult.limite.val()==1 && parseFloat(rowResult.nombreLimite.val()) != rowResult.nombreLimite.val()))
                    {
                        allGood = false;
                        if(rowResult.limite.val()==='0')
                            rowResult.limite.parent().parent().addClass('error');
                        else
                            rowResult.limite.parent().parent().parent().addClass('error');
                    }
                    masqueDate = /^[0-9]{2}\-[0-9]{2}\-[0-9]{4}$/;
                    if(!(rowResult.expiration.val()==='0' || rowResult.expiration.val()==1) || (rowResult.expiration.val()==1 && !masqueDate.test(rowResult.dateExpi.val())))
                    {
                        allGood = false;
                        if(rowResult.expiration.val()==='0')
                            rowResult.expiration.parent().parent().addClass('error');
                        else
                            rowResult.expiration.parent().parent().parent().addClass('error');
                    }

                    if(allGood)
                    {
                        /*send the mashed potatoes*/
                        Thelia_promo.generateForm(promoId).appendTo($('body')).submit();
                    }

                },
                function()
                {
                    /*cancel*/
                    $('#js_promo_' + promoId).show();
                    $('#promo_edit_row').unbind().remove();
                    $('#promo_edit_row_module').unbind().remove();
                    $('.js-edit-promo, .js-delete-promo').removeClass('disabled');
                    cancelModal = false;
                }
            ).insertAfter('#js_promo_' + promoId) 
        );
        
        $('#js_promo_' + promoId).hide();
    });
    
    /*auto-check in modal*/
    $('#limit_value').focus(function()
    {
        $('#limit_fixed_radio').attr('checked', true);
    });
    
    $('#expiration_value').focus(function()
    {
        $('#expiration_fixed_radio').attr('checked', true);
    });
    
    $('#expiration_value').datepicker({
        changeYear: true,
        yearRange: '0Y:+5Y',
        dayNamesMin: ['<?php echo trad('date_D', 'admin'); ?>','<?php echo trad('date_L', 'admin'); ?>','<?php echo trad('date_M', 'admin'); ?>','<?php echo trad('date_Me', 'admin'); ?>','<?php echo trad('date_J', 'admin'); ?>','<?php echo trad('date_V', 'admin'); ?>','<?php echo trad('date_S', 'admin'); ?>'],
        dateFormat:'dd-mm-yy',
        monthNames: ['<?php echo trad('date_Janvier', 'admin'); ?>','<?php echo trad('date_Fevrier', 'admin'); ?>','<?php echo trad('date_Mars', 'admin'); ?>','<?php echo trad('date_Avril', 'admin'); ?>','<?php echo trad('date_Mai', 'admin'); ?>','<?php echo trad('date_Juin', 'admin'); ?>','<?php echo trad('date_Juillet', 'admin'); ?>','<?php echo trad('date_Aout', 'admin'); ?>','<?php echo trad('date_Septembre', 'admin'); ?>','<?php echo trad('date_Octobre', 'admin'); ?>','<?php echo trad('date_Novembre', 'admin'); ?>','<?php echo trad('date_Decembre', 'admin'); ?>'],
        nextText: "<?php echo trad('date_Suivant', 'admin'); ?>",
        prevText: "<?php echo trad('date_Precedent', 'admin'); ?>",
        firstDay: 1,
        minDate: 'd'
    });
    
    $('#expiration_calendar_button').click(function()
    {
        if($('#expiration_value').datepicker('widget').is(':hidden'))
            $('#expiration_value').datepicker('show');
    });
});

/*THELIA PROMO*/
var Thelia_promo = {
    generateRow: function(code, type, valeur, mini, actif, nb_util, limite, date_expi, valid_callback, quit_callback)
    {
        return $('<tr />').attr('id', 'promo_edit_row').addClass('warning').append(
                    /*code*/
                    $('<td />').append($('<span />').html(code)),
                    /*type*/
                    $('<td />').append(
                        $('<div />').addClass('control-group').append(
                            $('<select />').attr('id', 'promo_edit_type').addClass('input-medium').append(
                                $('<option />').attr("value","<?php echo Promo::TYPE_SOMME; ?>").html("<?php echo htmlentities(trad('somme', 'admin'), ENT_QUOTES, 'UTF-8'); ?>"),
                                $('<option />').attr("value","<?php echo Promo::TYPE_POURCENTAGE; ?>").html("<?php echo htmlentities(trad('pourcentage', 'admin'), ENT_QUOTES, 'UTF-8'); ?>")
                            ).val(type)
                        )
                    ),
                    /*valeur*/
                    $('<td />').append(
                        $('<div />').addClass('control-group').append(
                            $('<input />').attr('type', 'text').attr('id', 'promo_edit_valeur').addClass('input-mini').val(valeur)
                        )
                    ),
                    /*mini*/
                    $('<td />').append(
                        $('<div />').addClass('control-group').append(
                            $('<input />').attr('type', 'text').attr('id', 'promo_edit_mini').addClass('input-mini').val(mini)
                        )
                    ),
                    /*actif*/
                    $('<td />').append(
                        $('<div />').addClass('control-group').append(
                            $('<select />').attr('id', 'promo_edit_actif').addClass('input-mini').append(
                                $('<option />').attr("value","1").html("<?php echo htmlentities(trad('oui', 'admin'), ENT_QUOTES, 'UTF-8'); ?>"),
                                $('<option />').attr("value","0").html("<?php echo htmlentities(trad('non', 'admin'), ENT_QUOTES, 'UTF-8'); ?>")
                            ).val(actif)
                        )
                    ),
                    /*nb_util*/
                    $('<td />').append($('<span />').html(nb_util)),
                    /*limite*/
                    $('<td />').append(
                        $('<div />').addClass('control-group').append(
                            $('<label />').html('<?php echo htmlentities(trad('Limitee_a', 'admin'), ENT_QUOTES, 'UTF-8'); ?>').append(
                                $('<div />').addClass('input-prepend').append(
                                    $('<span />').addClass('add-on').append(
                                        $('<input />').attr('type', 'radio').attr('id', 'promo_edit_limit_fixed').attr('name', 'limite').addClass('js-promo-edit-limite').val(1).attr('checked', limite!=0?true:false)
                                    ),
                                    $('<input />').attr('type', 'text').attr('id', 'promo_edit_nombre_limite').addClass('input-mini').val(limite==0?'':limite).focus(function()
                                    {
                                        $('#promo_edit_limit_fixed').attr('checked', true);
                                    })
                                    
                                )
                            ),
                            $('<label />').addClass('radio').html('<?php echo htmlentities(trad('Illimite', 'admin'), ENT_QUOTES, 'UTF-8'); ?>').append(
                                $('<input />').attr('type', 'radio').attr('name', 'limite').addClass('js-promo-edit-limite').val(0).attr('checked', limite==0?true:false)
                            )
                        )
                    ),
                    /*date expi*/
                    $('<td />').append(
                        $('<div />').addClass('control-group').append(
                            $('<label />').html('<?php echo htmlentities(trad('Expire_le', 'admin'), ENT_QUOTES, 'UTF-8'); ?>').append(
                                $('<div />').addClass('input-prepend').addClass('input-append').append(
                                    $('<span />').addClass('add-on').append(
                                        $('<input />').attr('type', 'radio').attr('id', 'promo_edit_expiration_fixed').attr('name', 'expiration').addClass('js-promo-edit-expiration').val(1).attr('checked', date_expi!=0?true:false)
                                    ),
                                    $('<input />').attr('type', 'text').attr('id', 'promo_edit_date_expi').addClass('input-small').val(date_expi==0?'':date_expi).datepicker({
                                        changeYear: true,
                                        yearRange: '0Y:+5Y',
                                        dayNamesMin: ['<?php echo htmlentities(trad('date_D', 'admin'), ENT_QUOTES, 'UTF-8'); ?>','<?php echo htmlentities(trad('date_L', 'admin'), ENT_QUOTES, 'UTF-8'); ?>','<?php echo htmlentities(trad('date_M', 'admin'), ENT_QUOTES, 'UTF-8'); ?>','<?php echo htmlentities(trad('date_Me', 'admin'), ENT_QUOTES, 'UTF-8'); ?>','<?php echo htmlentities(trad('date_J', 'admin'), ENT_QUOTES, 'UTF-8'); ?>','<?php echo htmlentities(trad('date_V', 'admin'), ENT_QUOTES, 'UTF-8'); ?>','<?php echo htmlentities(trad('date_S', 'admin'), ENT_QUOTES, 'UTF-8'); ?>'],
                                        dateFormat:'dd-mm-yy',
                                        monthNames: ['<?php echo htmlentities(trad('date_Janvier', 'admin'), ENT_QUOTES, 'UTF-8'); ?>','<?php echo htmlentities(trad('date_Fevrier', 'admin'), ENT_QUOTES, 'UTF-8'); ?>','<?php echo htmlentities(trad('date_Mars', 'admin'), ENT_QUOTES, 'UTF-8'); ?>','<?php echo htmlentities(trad('date_Avril', 'admin'), ENT_QUOTES, 'UTF-8'); ?>','<?php echo htmlentities(trad('date_Mai', 'admin'), ENT_QUOTES, 'UTF-8'); ?>','<?php echo htmlentities(trad('date_Juin', 'admin'), ENT_QUOTES, 'UTF-8'); ?>','<?php echo htmlentities(trad('date_Juillet', 'admin'), ENT_QUOTES, 'UTF-8'); ?>','<?php echo htmlentities(trad('date_Aout', 'admin'), ENT_QUOTES, 'UTF-8'); ?>','<?php echo htmlentities(trad('date_Septembre', 'admin'), ENT_QUOTES, 'UTF-8'); ?>','<?php echo htmlentities(trad('date_Octobre', 'admin'), ENT_QUOTES, 'UTF-8'); ?>','<?php echo htmlentities(trad('date_Novembre', 'admin'), ENT_QUOTES, 'UTF-8'); ?>','<?php echo htmlentities(trad('date_Decembre', 'admin'), ENT_QUOTES, 'UTF-8'); ?>'],
                                        nextText: "<?php echo htmlentities(trad('date_Suivant', 'admin'), ENT_QUOTES, 'UTF-8'); ?>",
                                        prevText: "<?php echo htmlentities(trad('date_Precedent', 'admin'), ENT_QUOTES, 'UTF-8'); ?>",
                                        firstDay: 1,
                                        minDate: 'd'
                                    }).focus(function()
                                    {
                                        $('#promo_edit_expiration_fixed').attr('checked', true);
                                    }),
                                    $('<button />').attr('type', 'button').addClass('btn').append(
                                        $('<i />').addClass('icon-calendar')
                                    ).click(function()
                                    {
                                        if($('#promo_edit_date_expi').datepicker('widget').is(':hidden'))
                                            $('#promo_edit_date_expi').datepicker('show');
                                    })
                                )
                            ),
                            $('<label />').addClass('radio').html('<?php echo htmlentities(trad('N_expire_pas', 'admin'), ENT_QUOTES, 'UTF-8'); ?>').append(
                                $('<input />').attr('type', 'radio').attr('name', 'expiration').addClass('js-promo-edit-expiration').val(0).attr('checked', date_expi==0?true:false)
                            )
                        )
                    ),
                    /*buttons*/
                    $('<td />').append(
                        $('<div />').addClass('btn-group').append(
                            $('<button />').attr('title', '<?php echo htmlentities(trad('modifier', 'admin'), ENT_QUOTES, 'UTF-8'); ?>').addClass('btn').addClass('btn-mini').append(
                                $('<i />').addClass('icon-check')
                            ).click(function()
                            {
                                /*validation*/
                                valid_callback(Thelia_promo.getRowResult());
                            }),
                            $('<button />').attr('title', '<?php echo htmlentities(trad('annuler', 'admin'), ENT_QUOTES, 'UTF-8'); ?>').addClass('btn').addClass('btn-mini').append(
                                $('<i />').addClass('icon-remove-sign')
                            ).click(function()
                            {
                                /*annulation*/
                                quit_callback();
                            })
                        )
                    )
                );
    },
    generateRowModule: function(id)
    {
        return $('<tr />').attr('id', 'promo_edit_row_module').addClass('warning').append(
                    $('<td />').attr('colspan', '9').load(
                        'ajax/promo.php',
                        {
                            id : id
                        }
                    )
                );
    },
    getRowResult: function()
    {
        this.type = $('#promo_edit_type');
        this.valeur = $('#promo_edit_valeur');
        this.mini = $('#promo_edit_mini');
        this.actif = $('#promo_edit_actif');
        this.nombreLimite = $('#promo_edit_nombre_limite');
        this.limite = $('.js-promo-edit-limite:checked');
        this.dateExpi = $('#promo_edit_date_expi');
        this.expiration = $('.js-promo-edit-expiration:checked');
        
        return this;
    },
    generateForm : function(promoId){
        var rowResult = Thelia_promo.getRowResult();
                    
        return $('<form />').attr("action","promo.php").attr("method","post")
                    .prepend(
                        $('<input />').attr("type","hidden").attr("name","action").attr("value","edit"),
                        $('<input />').attr("type","hidden").attr("name","id").attr("value",promoId),
                        $('<input />').attr("type","hidden").attr("name","type").attr("value",rowResult.type.val()),
                        $('<input />').attr("type","hidden").attr("name","valeur").attr("value",rowResult.valeur.val()),
                        $('<input />').attr("type","hidden").attr("name","mini").attr("value",rowResult.mini.val()),
                        $('<input />').attr("type","hidden").attr("name","actif").attr("value",rowResult.actif.val()),
                        $('<input />').attr("type","hidden").attr("name","nombre_limite").attr("value",rowResult.nombreLimite.val()),
                        $('<input />').attr("type","hidden").attr("name","limite").attr("value",rowResult.limite.val()),
                        $('<input />').attr("type","hidden").attr("name","date_expi").attr("value",rowResult.dateExpi.val()),
                        $('<input />').attr("type","hidden").attr("name","expiration").attr("value",rowResult.expiration.val())
                    )
    }
}

</script>
</body>
</html>