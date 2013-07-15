<?php
require_once __DIR__ . '/auth.php';


if(! est_autorise("acces_configuration")) exit; 

$request = Symfony\Component\HttpFoundation\Request::createFromGlobals();

$pays = new Pays($request->query->get("id"));
?>
<form method="post" action="pays.php">
    <input type="hidden" name="action" value="editCountry">
    <input type="hidden" name="id" value="<?php echo $pays->id; ?>"
    <p>
        <button class="btn btn-large btn-block btn-primary" type="submit"><?php echo trad('VALIDER_LES_MODIFICATIONS', 'admin'); ?></button>
    </p> 
<table class="table table-striped">
    <tbody>
        <tr>
            <td><?php echo trad('ISO-3166', 'admin') ?></td>
            <td><input type="text" name="isocode" value="<?php echo htmlspecialchars($pays->isocode); ?>"></td>
        </tr>
        <tr>
            <td><?php echo trad('alpha-2', 'admin') ?></td>
            <td><input type="text" name="isoalpha2" value="<?php echo htmlspecialchars($pays->isoalpha2); ?>"</td>
        </tr>
        <tr>
            <td><?php echo trad('alpha-3', 'admin') ?></td>
            <td><input type="text" name="isoalpha3" value="<?php echo htmlspecialchars($pays->isoalpha3); ?>"</td>
        </tr>
        <tr>
            <td><?php echo trad('TVA_francaise', 'admin') ?></td>
            <td>
                <input type="radio" name="tva" value="1" <?php echo $pays->tva == 1 ? 'checked="checked"' : '' ?>>&nbsp; <?php echo trad('Oui', 'admin'); ?> &nbsp;
                <input type="radio" name="tva" value="0" <?php echo $pays->tva == 0 ? 'checked="checked"' : '' ?>>&nbsp; <?php echo trad('Non', 'admin'); ?>
            </td>
        </tr>
        <tr>
            <td><?php echo trad('Zone_transport', 'admin'); ?></td>
            <td>
                <select name="zone">
                    <option value="0"><?php echo trad('Aucune', 'admin'); ?></option>
                    <?php 
                        $query = "SELECT * FROM ".Zone::TABLE." ORDER BY nom";
                        foreach($pays->query_liste($query) as $zone):
                    ?>
                    <option value="<?php echo $zone->id; ?>" <?php if($zone->id == $pays->zone): ?>selected="selected"<?php endif; ?>><?php echo $zone->nom; ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
    </tbody>
</table>
<?php foreach ($pays->query_liste("SELECT id, description FROM ".Lang::TABLE." order by id") as $lang): ?>
    <?php $paysdesc = new Paysdesc($pays->id, $lang->id); ?>
<input type="hidden" name="lang[<?php echo $lang->id; ?>]" value="<?php echo $lang->id; ?>" />
<table class="table table-striped">
    <thead>
        <caption class="pull-left">
            <img src="gfx/lang<?php echo $lang->id; ?>.gif" alt="<?php echo $lang->description; ?>">&nbsp;<?php echo $lang->description; ?>
        </caption>
    </thead>
    <tbody>
        <tr>
            <td><?php echo trad('Nom', 'admin'); ?></td>
            <td><input type="text" name="titre[<?php echo $lang->id; ?>]" value="<?php echo $paysdesc->titre; ?>"></td>
        </tr>
        <tr>
            <td><?php echo trad('Chapo', 'admin'); ?></td>
            <td><input type="text" name="chapo[<?php echo $lang->id; ?>]" value="<?php echo $paysdesc->chapo; ?>"></td>
        </tr>
        <tr>
            <td><?php echo trad('Description', 'admin'); ?></td>
            <td><textarea name="description[<?php echo $lang->id; ?>]"><?php echo $paysdesc->description; ?></textarea></td>
        </tr>
    </tbody>
</table>
<?php endforeach; ?>
<p>
    <button class="btn btn-large btn-block btn-primary" type="submit"><?php echo trad('VALIDER_LES_MODIFICATIONS', 'admin'); ?></button>
</p> 
</form>