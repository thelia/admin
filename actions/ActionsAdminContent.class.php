<?php

use Symfony\Component\HttpFoundation\Request;

class ActionsAdminContent extends ActionsAdminBase
{
    private static $instance = false;
    
    protected function __construct() {}
    /**
     * 
     * @return \ActionsAdminContent
     */
    public static function getInstance(){
        if(self::$instance === false) self::$instance = new ActionsAdminContent();
        
        return self::$instance;
    }
    
    public function action(Request $request){
        switch($request->get('action')){
            case "modifier" : 
                $contentAdmin = ContentAdmin::getInstance($request->request->get('id'));
                $contentAdmin->modify(
                        $request->request->get('lang', ActionsLang::instance()->get_id_langue_courante()),
                        $request->request->get('dossier'),
                        $request->request->get('ligne'),
                        $request->request->get('titre'),
                        $request->request->get('chapo'),
                        $request->request->get('description'),
                        $request->request->get('postscriptum'),
                        $request->request->get('urlsuiv'),
                        $request->request->get('urlreecrite'),
                        $this->getImages($request, $contentAdmin),
                        $this->getDocuments($request, $contentAdmin),
                        $request->request->get('tab')
                );
                break;
            
            /*listdos actions*/
            case "addContent":
                ContentAdmin::getInstance()->add($request->request->get('title'), $request->request->get('parent'));
                break;
            case 'deleteContent':
                ContentAdmin::getInstance($request->query->get('content_id'))->delete();
                break;
            case 'modClassementContent':
                ContentAdmin::getInstance($request->query->get("content_id"))->modifyOrder($request->query->get("type"), $request->query->get("parent"));
                break;
            case 'changeClassementContent':
                ContentAdmin::getInstance($request->request->get("content_id"))->changeOrder($request->request->get("newClassement"), $request->request->get("parent"));
                break;
        }
    }
    
    protected function getImages(Request $request, \ContentAdmin $content)
    {
        $return = array();
        
        if($content->id == ''){
            return $return;
        }
        
        $query = 'select id from '.Image::TABLE.' where contenu='.$content->id;

        
        $return = $this->extractResult($request, $content->query_liste($query), array(
            "titre" => "photo_titre_",
            "chapo" => "photo_chapo_",
            "description" => "photo_description_",
            "toDelete" => "image_to_delete_",
            "rank" => "rank_",
        ));
        
        return $return;
    }
    
    protected function getDocuments(Request $request, \ContentAdmin $content)
    {
        $return = array();
        
        if($content->id == ''){
            return array();
        }
        
        $query = "select id from ".Document::TABLE.' where contenu='.$content->id;
        
        $return = $this->extractResult($request, $content->query_liste($query), array(
            "titre" => "document_titre_",
            "chapo" => "document_chapo_",
            "description" => "document_description_",
            "toDelete" => "document_to_delete_",
            "rank" => "rank_",
        ));
        
        return $return;
    }
    
    
}