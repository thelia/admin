<?php

use Symfony\Component\HttpFoundation\Request;

class ActionsAdminFolder extends ActionsAdminBase
{
    private static $instance = false;
    
    protected function __construct() {}
    /**
     * 
     * @return ActionsAdminFolder
     */
    public static function getInstance(){
        if(self::$instance === false) self::$instance = new ActionsAdminFolder();
        
        return self::$instance;
    }
    
    public function action(Request $request){
        switch($request->get('action'))
        {
            /*listdos actions*/
            case 'addFolder':
                FolderAdmin::getInstance()->add($request->request->get('title'), $request->request->get('parent'));
                break;
            case 'deleteFolder':
                FolderAdmin::getInstance($request->query->get('folder_id'))->delete();
                break;
            case 'modClassementFolder':
                FolderAdmin::getInstance($request->query->get("folder_id"))->modifyOrder($request->query->get("type"), $request->query->get("parent"));
                break;
            case 'changeClassementFolder':
                FolderAdmin::getInstance($request->request->get("folder_id"))->changeOrder($request->request->get("newClassement"), $request->request->get("parent"));
                break;
            
            case "modifier" : 
                $folderAdmin = FolderAdmin::getInstance($request->request->get('id'));
                $folderAdmin->modify(
                        $request->request->get('lang', ActionsLang::instance()->get_id_langue_courante()),
                        $request->request->get('dossier'),
                        $request->request->get('ligne'),
                        $request->request->get('titre'),
                        $request->request->get('chapo'),
                        $request->request->get('description'),
                        $request->request->get('postscriptum'),
                        $request->request->get('urlsuiv'),
                        $request->request->get('urlreecrite'),
                        $this->getImages($request, $folderAdmin),
                        $this->getDocuments($request, $folderAdmin),
                        $request->request->get('tab')
                );
                break;
            
        }
    }
    
    protected function getImages(Request $request, \FolderAdmin $folder)
    {
        $return = array();
        
        if($folder->id == ''){
            return $return;
        }
        
        $query = 'select id from '.Image::TABLE.' where dossier='.$folder->id;

        
        $return = $this->extractResult($request, $folder->query_liste($query), array(
            "titre" => "photo_titre_",
            "chapo" => "photo_chapo_",
            "description" => "photo_description_",
            "toDelete" => "image_to_delete_",
            "rank" => "rank_",
        ));
        
        return $return;
    }
    
    protected function getDocuments(Request $request, \FolderAdmin $folder)
    {
        $return = array();
        
        if($folder->id == ''){
            return array();
        }
        
        $query = "select id from ".Document::TABLE.' where dossier='.$folder->id;
        
        $return = $this->extractResult($request, $folder->query_liste($query), array(
            "titre" => "document_titre_",
            "chapo" => "document_chapo_",
            "description" => "document_description_",
            "toDelete" => "document_to_delete_",
            "rank" => "rank_",
        ));
        
        return $return;
    }
    
    
}