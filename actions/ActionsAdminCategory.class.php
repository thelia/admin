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
    
    public function action(Request $request)
    {
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
                        $this->getAssociatedContents($request, $categoryAdmin),
                        $this->getAssociatedFeatures($request, $categoryAdmin),
                        $this->getAssociatedVariants($request, $categoryAdmin),
                        $this->getImages($request, $categoryAdmin),
                        $this->getDocuments($request, $categoryAdmin),
                        $request->request->get('tab')
                );
                break;
            
            /*association*/
            /*case 'deleteAssociatedContent':
                AssociatedContentAdmin::getInstance()->delete($request->query->get('associatedContent'));
                break;
            case 'addAssociatedContent':
                AssociatedContentAdmin::getInstance()->add($request->query->get('contenu'), 0, $request->query->get('id'));
                break;*/
        }
    }
    
    protected function getAssociatedContents(Request $request, \CategoryAdmin $category)
    {
        $return = array();
        
        if($category->id == ''){
            return $return;
        }
        
        $query = 'SELECT id from ' . Contenuassoc::TABLE . ' WHERE type=0 AND objet=' . $category->id;

        $return = $this->extractResult(
            $request,
            $category->query_liste($query),
            array(
                "alive" => "alive_associated_content_",
            ),
            'request'
        );
        
        $toAdd = $request->get('new_associated_content');
        foreach(explode('-', $toAdd) as $idContent)
        {
            $return[] = array(
                'add' => '1',
                'content' => $idContent,
            );
        }
        
        return $return;
    }
    
    protected function getAssociatedFeatures(Request $request, \CategoryAdmin $category)
    {
        $return = array();
        
        if($category->id == ''){
            return $return;
        }
        
        $query = 'SELECT id from ' . Caracteristique::TABLE;

        $return = $this->extractResult(
            $request,
            $category->query_liste($query),
            array(
                "alive" => "alive_associated_feature_",
            ),
            'request'
        );
        
        return $return;
    }
    
    protected function getAssociatedVariants(Request $request, \CategoryAdmin $category)
    {
        $return = array();
        
        if($category->id == ''){
            return $return;
        }
        
        $query = 'SELECT id from ' . Declinaison::TABLE;

        $return = $this->extractResult(
            $request,
            $category->query_liste($query),
            array(
                "alive" => "alive_associated_variant_",
            ),
            'request'
        );
        
        return $return;
    }
    
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
                "description" => "photo_description_",
                "toDelete" => "image_to_delete_",
                "rank" => "rank_",
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
                "description" => "document_description_",
                "toDelete" => "document_to_delete_",
                "rank" => "rank_",
            ),
            'request'
        );
        
        return $return;
    }
    
    
}