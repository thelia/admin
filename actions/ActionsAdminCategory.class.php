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
            /*association : RO REVIEW*/
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

            /*information & description*/
            case 'changeInformation':
                CategoryAdmin::getInstance($request->request->get('id'))->editInformation($request->request->get('ligne'), $request->request->get('parent'), $request->request->get('lien'));
                break;
            case 'changeDescription':
                CategoryAdmin::getInstance($request->request->get('id'))->editDescription($request->request->get('lang'), $request->request->get('titre'), $request->request->get('chapo'), $request->request->get('description'), $request->request->get('postscriptum'), $request->request->get('url'));
                break;

            /*attachement : picture*/
            case 'addPicture':
                CategoryAdmin::getInstance($request->request->get('id'))->addPicture();
                break;
            case 'editPicture':
                CategoryAdmin::getInstance($request->request->get('id'))->updateImage($this->getImages($request, CategoryAdmin::getInstance($request->request->get('id'))), $request->request->get('lang'));
                break;
            case 'deletePicture':
                CategoryAdmin::getInstance($request->query->get('id'))->deleteImage($request->query->get('picture'), $request->query->get('lang'));
                break;
            case 'modifyPictureClassement':
                CategoryAdmin::getInstance($request->query->get('id'))->modifyImageOrder($request->query->get('picture'), $request->query->get('will'), $request->query->get('lang'));
                break;
            
            /*attachement : document*/
            case 'addDocument':
                CategoryAdmin::getInstance($request->request->get('id'))->addDocument();
                break;
            case 'editDocument':
                CategoryAdmin::getInstance($request->request->get('id'))->updateDocument($this->getDocuments($request, CategoryAdmin::getInstance($request->request->get('id'))), $request->request->get('lang'));
                break;
            case 'deleteDocument':
                CategoryAdmin::getInstance($request->query->get('id'))->deleteDocument($request->query->get('document'), $request->query->get('lang'));
                break;
            case 'modifyDocumentClassement':
                CategoryAdmin::getInstance($request->query->get('id'))->modifyDocumentOrder($request->query->get('document'), $request->query->get('will'), $request->query->get('lang'));
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

        
        $return = $this->extractResult($request, $category->query_liste($query), array(
            "titre" => "photo_titre_",
            "chapo" => "photo_chapo_",
            "description" => "photo_description_"
        ));
        
        return $return;
    }
    
    protected function getDocuments(Request $request, \CategoryAdmin $category)
    {
        $return = array();
        
        if($category->id == ''){
            return array();
        }
        
        $query = "select id from ".Document::TABLE.' where rubrique='.$category->id;
        
        $return = $this->extractResult($request, $category->query_liste($query), array(
            "titre" => "document_titre_",
            "chapo" => "document_chapo_",
            "description" => "document_description_"
        ));
        
        return $return;
    }
    
    
}