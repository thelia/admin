<?php

use Symfony\Component\HttpFoundation\Request;

class ActionsAdminCategory extends ActionsAdminBase
{
    private static $instance = false;
    
    protected function __construct() {}
    /**
     * 
     * @return ActionsAdminCategory
     */
    public static function getInstance(){
        if(self::$instance === false) self::$instance = new ActionsAdminCategory();
        
        return self::$instance;
    }
    
    public function action(Request $request){
        switch($request->get('action'))
        {
            /*listrub actions*/
            case 'addCategory':
                CategoryAdmin::getInstance()->add($request->request->get('title'), $request->request->get('parent'));
                break;
            case 'deleteCategory':
                CategoryAdmin::getInstance($request->query->get('category_id'))->delete();
                break;
            case 'modClassementCategory':
                CategoryAdmin::getInstance($request->query->get("category_id"))->modifyOrder($request->query->get("type"), $request->query->get("parent"));
                break;
            case 'changeClassementCategory':
                CategoryAdmin::getInstance($request->request->get("category_id"))->changeOrder($request->request->get("newClassement"), $request->request->get("parent"));
                break;
            
            case "modifier" : 
                $categoryAdmin = CategoryAdmin::getInstance($request->request->get('id'));
                $categoryAdmin->modify(
                        $request->request->get('lang', ActionsLang::instance()->get_id_langue_courante()),
                        $request->request->get('parent'),
                        $request->request->get('lien'),
                        $request->request->get('ligne'),
                        $request->request->get('titre'),
                        $request->request->get('chapo'),
                        $request->request->get('description'),
                        $request->request->get('postscriptum'),
                        $request->request->get('urlsuiv'),
                        $request->request->get('urlreecrite'),
                        $this->getImages($request, $categoryAdmin),
                        $this->getDocuments($request, $categoryAdmin),
                        $request->request->get('tab')
                );
                break;
            case "modifyAttachementPosition":
                CategoryAdmin::getInstance($request->query->get('id'))->changeAttachementPosition(
                        $request->query->get('attachement'),
                        $request->query->get('attachement_id'),
                        $request->query->get('direction'),
                        $request->query->get('lang'),
                        $request->query->get('tab')
                );
                break;
            case "deleteAttachement":
                CategoryAdmin::getInstance($request->query->get('id'))->deleteAttachement(
                        $request->query->get('attachement'),
                        $request->query->get('attachement_id'),
                        $request->query->get('lang'),
                        $request->query->get('tab')
                );
                break;
            
            /*association*/
            case 'deleteAssociatedContent':
                AssociatedContentAdmin::getInstance()->delete($request->query->get('associatedContent'));
                break;
            case 'addAssociatedContent':
                AssociatedContentAdmin::getInstance()->add($request->query->get('contenu'), 0, $request->query->get('id'));
                break;

            case 'deleteAssociatedFeature':
                AssociatedFeatureAdmin::getInstance()->delete($request->query->get('associatedFeature'));
                break;
            case 'addAssociatedFeature':
                AssociatedFeatureAdmin::getInstance()->add($request->query->get('feature'), $request->query->get('id'));
                break;

            case 'deleteAssociatedVariant':
                AssociatedVariantAdmin::getInstance()->delete($request->query->get('associatedVariant'));
                break;
            case 'addAssociatedVariant':
                AssociatedVariantAdmin::getInstance()->add($request->query->get('variant'), $request->query->get('id'));
                break;
        }
    }
    
    /**
     * 
     * search caracteristique in request and return an array. Indexes are the id of each caracteristique and value are the caracdisp selected
     * 
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return Array
     */
    /*protected function getCaracteristique(Request $request, \ProductAdmin $product)
    {        
        $return = array();
        
        if($product->id == ''){
            return $return;
        }
        
        $query = 'select caracteristique from '.Rubcaracteristique::TABLE.' where rubrique='.$product->rubrique;
        
        foreach($product->query_liste($query) as $caracteristique)
        {
            if(false !== $carac = $request->request->get('caract'.$caracteristique->caracteristique, false)){                
                $return[$caracteristique->caracteristique] = $carac;
            }
        }
        
        return $return;
    }
    
    /**
     * 
     * search declinaison in request and return an array.
     * 
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return Array
     */
    /*protected function getDeclinaison(Request $request, \ProductAdmin $product)
    {
        $return = array();
        
        if($product->id == ''){
            return $return;
        }
        
        $query = "select dc.id from ".Declidisp::TABLE." dc left join ".Rubdeclinaison::TABLE." rd on rd.declinaison=dc.declinaison where rd.rubrique=".$product->rubrique;
        
        $return = $this->extractResult($request, $product->query_liste($query), array(
            "stock" => "stock",
            "surplus" => "surplus"
        ));
        
        return $return;
    }*/
    
    protected function getImages(Request $request, \CategoryAdmin $category)
    {
        $return = array();
        
        if($category->id == ''){
            return $return;
        }
        
        $query = 'select id from '.Image::TABLE.' where rubrique='.$category->id;

        
        $return = $this->extractResult(
            $request,
            $category->query_liste($query),
            array(
                "titre" => "photo_titre_",
                "chapo" => "photo_chapo_",
                "description" => "photo_description_"
            ),
            'request'
        );
        
        return $return;
    }
    
    protected function getDocuments(Request $request, \CategoryAdmin $category)
    {
        $return = array();
        
        if($category->id == ''){
            return array();
        }
        
        $query = "select id from ".Document::TABLE.' where rubrique='.$category->id;
        
        $return = $this->extractResult(
            $request,
            $category->query_liste($query),
            array(
                "titre" => "document_titre_",
                "chapo" => "document_chapo_",
                "description" => "document_description_"
            ),
            'request'
        );
        
        return $return;
    }
    
    
}