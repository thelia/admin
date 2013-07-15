<?php

class Breadcrumb
{
    /**
     *
     * @var array
     */
    protected $breadcrumb = array();
    
    /**
     * 
     * @return \Breadcrumb
     */
    public static function getInstance($header = true)
    {
        return new Breadcrumb($header);
    }
    
    public function __construct($header = true) {
        if($header)
            $this->addValue( "accueil.php", trad('Accueil', 'admin') );
    }
    
    /**
     * 
     * Add a new value to breadcrumb at the end of the array
     * 
     * @param string $url
     * @param string $display
     * @param string $edit
     */
    protected function addValue($url, $display, $edit = "", $browse = "")
    {
        array_push($this->breadcrumb, array(
            "url" => $url,
            "display" => $display,
            "edit" => $edit,
            "browse" => $browse,
        ));
    }
    
    /**
     * 
     * @return array
     */
    public function getBreadcrumb()
    {
        return $this->breadcrumb;
    }
    
    /**
     * 
     * generate Breadcrumb for "commande" part
     * 
     * @param int $parent
     */
    public function getCategoryList($parent, $edit = true, $browse = false)
    {
        $this->addValue("parcourir.php", trad('Gestion_catalogue', 'admin'));
        
        foreach(CategoryAdmin::getInstance()->getBreadcrumbList($parent) as $breadcrumb)
        {
            if ($breadcrumb->rubrique == $parent)
            {
                $this->addValue("" , $breadcrumb->titre, ($edit === true)?"rubrique_modifier.php?id=".$breadcrumb->rubrique:"", ($browse === true)?"parcourir.php?parent=".$breadcrumb->rubrique:"");
            } else {
                $this->addValue("parcourir.php?parent=".$breadcrumb->rubrique, $breadcrumb->titre );
            }
           
            
        }
        
        return $this->getBreadcrumb();
    }
    
    /**
     * 
     * generate Breadcrumb for "commande" part
     * 
     * @param int $parent
     */
    public function getFastBrowserCategoryList($parent)
    {
        $this->addValue("0", trad('Root', 'admin'));
        
        foreach(CategoryAdmin::getInstance()->getBreadcrumbList($parent) as $breadcrumb)
        {
            $this->addValue($breadcrumb->rubrique, $breadcrumb->titre );
        }
        
        return $this->getBreadcrumb();
    }
    
    /**
     * 
     * generate Breadcrumb for "contenus" part
     * 
     * @param int $parent
     */
    public function getFolderList($parent, $edit = true)
    {
        $this->addValue("listdos.php", trad('Gestion_contenu', 'admin'));
        
        foreach(FolderAdmin::getInstance()->getBreadcrumbList($parent) as $breadcrumb)
        {
            if ($breadcrumb->dossier == $parent)
            {
                $this->addValue("" , $breadcrumb->titre, ($edit === true)?"dossier_modifier.php?id=".$breadcrumb->dossier:"");
            } else {
                $this->addValue("listdos.php?parent=".$breadcrumb->dossier, $breadcrumb->titre );
            }
           
            
        }
        
        return $this->getBreadcrumb();
    }
    
    public function getContentListe($id, $title)
    {
        $this->addValue("listdos.php", trad('Gestion_contenu', 'admin'));
        
        foreach(FolderAdmin::getInstance()->getBreadcrumbList($id) as $breadcrumb)
        {
  
            $this->addValue("listdos.php?parent=".$breadcrumb->dossier, $breadcrumb->titre );
        }
        
        $this->addValue("", $title);
        
        return $this->getBreadcrumb();
    }
    
    /**
     * 
     * breadcrumb for the homapage
     * 
     * @return array
     */
    public function getHomeList()
    {
        return $this->getBreadcrumb();
    }
    
    public function getProductList($id, $title)
    {
        $this->addValue("parcourir.php", trad('Gestion_catalogue', 'admin'));
        
        foreach(CategoryAdmin::getInstance()->getBreadcrumbList($id) as $breadcrumb)
        {
            $this->addValue("parcourir.php?parent=".$breadcrumb->rubrique, $breadcrumb->titre );
        }
        
        $this->addValue("", $title);
        
        return $this->getBreadcrumb();
    }
    
    

    public function getSimpleList($title, $url = "")
    {
        $this->addValue($url, $title);
        
        return $this->getBreadcrumb();
    }
    
    public function getMultipleList()
    {
        $argList = func_get_args();
        
        for($i=0; $i<func_num_args(); $i++)
        {
            if(is_array($argList[$i]))
            {
                $this->addValue($argList[$i][1], $argList[$i][0]);
            }
        }
        
        return $this->getBreadcrumb();
    }
    
    public function getOrderList($title, $url = "", $editTitle = "")
    {
        $this->addValue("#", trad('Gestion_commandes', 'admin'));
        $this->addValue($url, $title);
        if($editTitle)
            $this->addValue("", $editTitle);
        
        return $this->getBreadcrumb();
    }
    
    public function getConfigurationList($title, $url = "", $editTitle = "")
    {
        $this->addValue("configuration.php", trad('Configuration', 'admin'));
        $this->addValue($url, $title);
        if($editTitle)
            $this->addValue("", $editTitle);
        
        return $this->getBreadcrumb();
    }
    
    public function getCaracList($title)
    {
        $this->addValue("#", trad('Configuration', 'admin'));
        $this->addValue("caracteristique.php", trad('Gestion_caracteristiques', 'admin'));
        
        $this->addValue("", $title);
        
        return $this->getBreadcrumb();
    }
    
    public function getDecliList($title)
    {
        $this->addValue("#", trad('Configuration', 'admin'));
        $this->addValue("declinaison.php", trad('Gestion_declinaison', 'admin'));
        
        $this->addValue("", $title);
        
        return $this->getBreadcrumb();
    }
}
?>
