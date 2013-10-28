<?php

class PromoAdmin extends Promo
{
    public $form_expiration_type;
    public $form_use_type;
            
    public function __construct($id = 0)
    {
        parent::__construct();
        
        if($id > 0)
            $this->charger_id($id);
    }
    
    public static function getInstance($id = 0)
    {
        return new PromoAdmin($id);
    }
    
    public function getRequest($type = 'list', $limit = '')
    {
        if($type == 'count')
        {
            $will = "COUNT(*)";
        }
        else
        {
            $will = "id, code, type, actif, valeur, mini, utilise, limite, IF(datefin='0000-00-00', 0, DATE_FORMAT(datefin, '%d-%m-%Y')) as datefin, DATEDIFF(NOW(), datefin) as datediff, (actif=1 AND (datefin>=CURDATE() OR datefin='0000-00-00') AND (utilise<limite OR limite=0)) AS isActive";
        }
        
        return "SELECT $will
                    FROM " . $this->table . "
                    WHERE actif=1 OR actif=0
                    ORDER BY (actif=1 AND (datefin>=CURDATE() OR datefin='0000-00-00') AND (utilise<limite OR limite=0)) DESC "
                    . $limit;
    }
    
    public function getList($start, $number)
    {
        $result = array();
        
        $qPromos = $this->getRequest('list', "LIMIT $start, $number");
        $rPromos = $this->query($qPromos);
        while($rPromos && $thePromo = $this->fetch_object($rPromos))
        {
            $result[] = $thePromo;
        }
        
        return $result;
    }
    
    public function delete()
    {   
        if($this->id > 0)
        {
            $this->actif = -1;
            $this->maj();

            ActionsModules::instance()->appel_module("suppromo", new Promo($this->code));
        }
        
        redirige("promo.php");
    }
    
    public function add($code, $type, $valeur, $mini, $actif, $limite, $nombre_limite, $expiration, $date_expiration)
    {
        $this->code = $code;
        $this->type = (in_array($type, array(1,2)))?$type:'';
        $this->valeur = is_numeric($valeur)?$valeur:'';
        $this->mini = is_numeric($mini)?$mini:'';
        $this->utilise = 0;
        $this->form_use_type = $limite;
        $this->limite = ($limite==='0')?0:(filter_var($nombre_limite, FILTER_VALIDATE_INT===false)?'':$nombre_limite);
        $this->form_expiration_type = $expiration;
        $this->datefin = ($expiration==='0')?'00-00-0000':((preg_match('#^[0-9]{2}\-[0-9]{2}\-[0-9]{4}$#', $date_expiration))?$date_expiration:'');
        $this->actif = ($actif==='0' || $actif==='1')?$actif:'';
        
        if($this->code!=="" && !self::testCodeExists($this->code) && $this->type!=="" && $this->valeur!=="" && $this->mini!=="" && $this->limite !=="" && $this->datefin!=="" && $this->actif!=="")
        {
            $this->datefin = date('Y-m-d', strtotime($this->datefin));
            $this->id = parent::add();
            
            ActionsModules::instance()->appel_module("ajoutpromo", new Promo($this->code));
            
            redirige("promo.php");
        }
        else
        {
            throw new TheliaAdminException("impossible to add new promo", TheliaAdminException::PROMO_ADD_ERROR, null, $this);
        }
    }
    
    public function edit($id, $type, $valeur, $mini, $actif, $limite, $nombre_limite, $expiration, $date_expiration)
    {
        if(!$this->charger_id($id))
        {echo $type;exit;
            throw new TheliaAdminException("impossible to load promo", TheliaAdminException::PROMO_NOT_FOUND);
        }
        
        $this->type = (in_array($type, array(1,2)))?$type:'';
        $this->valeur = is_numeric($valeur)?$valeur:'';
        $this->mini = is_numeric($mini)?$mini:'';
        $this->limite = ($limite==='0')?0:(filter_var($nombre_limite, FILTER_VALIDATE_INT===false)?'':$nombre_limite);
        $this->datefin = ($expiration==='0')?'00-00-0000':((preg_match('#^[0-9]{2}\-[0-9]{2}\-[0-9]{4}$#', $date_expiration))?$date_expiration:'');
        $this->actif = ($actif==='0' || $actif==='1')?$actif:'';
        
        if($this->type!=="" && $this->valeur!=="" && $this->mini!=="" && $this->limite !=="" && $this->datefin!=="" && $this->actif!=="")
        {
        	$this->datefin = strtotime($this->datefin);
        	if($this->datefin !== false){
	        	$this->datefin = date('Y-m-d', $this->datefin);
        	}else{
	        	$this->datefin = '0000-00-00';
        	}
            parent::maj();
            
            ActionsModules::instance()->appel_module("majpromo", new Promo($this->code));
            
            redirige("promo.php");
        }
        else
        {
            echo 2;
            exit;
            throw new TheliaAdminException("impossible to edit promo", TheliaAdminException::PROMO_EDIT_ERROR, null, $this);
        }
    }
    
    public static function testCodeExists($code)
    {
        $promo = new Promo();
        $q = "SELECT COUNT(*) AS total FROM " . parent::TABLE . " WHERE code='$code' AND actif<>-1";
        return $promo->get_result($promo->query($q), 0, 'total');
    }
}
