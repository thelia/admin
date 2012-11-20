<?php

class MessageAdmin extends Message
{
    
    public function __construct($id = 0) {
        parent::__construct();
        if($id)
        {
            if(!$this->charger_id($id))
            {
                throw new TheliaAdminException("Message not found",  TheliaAdminException::MESSAGE_NOT_FOUND);
            }
        }
    }
    /**
     * 
     * @return \MessageAdmin
     */
    public static function getInstance($id = 0)
    {
        return new MessageAdmin($id);
    }
    
    public function delete() {
        if($this->id) parent::delete();
        else throw new TheliaAdminException("Message not found",  TheliaAdminException::MESSAGE_NOT_FOUND);
        
        redirige("message.php");
    }
    
    public function modify($lang, $intitule, $titre, $chapo, $description, $descriptiontext)
    {
        $messagedesc = new Messagedesc($this->id, $lang);
        
        $messagedesc->intitule = $intitule;
        $messagedesc->titre = $titre;
        $messagedesc->chapo = $chapo;
        $messagedesc->description = $description;
        $messagedesc->descriptiontext = $descriptiontext;
        
        if($messagedesc->id == "")
        {
            $messagedesc->message = $this->id;
            $messagedesc->lang = $lang;
            $messagedesc->add();
        } else {
            $messagedesc->maj();
        }
        
        redirige("message_modifier.php?id=".$this->id."&lang=".$lang);
    }
    
    public function ajouter($nom)
    {
        $nom = trim($nom);
        if(empty($nom))
        {
            throw new TheliaAdminException("Empty message name", TheliaAdminException::MESSAGE_NAME_EMPTY);
        }
        
        if(Message::exist_nom($nom))
        {
            throw new TheliaAdminException("Message already exists", TheliaAdminException::MESSAGE_ALREADY_EXISTS);
        }
        
        $message = new Message();
        $message->nom = $nom;
        $message->id = $message->add();
        
        redirige("message_modifier.php?id=".$message->id);
    }
    
    public function getList()
    {
        $return = array();
        $query = "SELECT m.id, m.nom FROM ".Message::TABLE." m";
        
        foreach($this->query_liste($query) as $message)
        {
            $messagedesc = new Messagedesc($message->id, ActionsLang::instance()->get_id_langue_courante());
            $return[] = array(
                "id" => $message->id,
                "nom" => $message->nom,
                "intitule" => $messagedesc->intitule
            );
        }
        
        return $return;
    }
}