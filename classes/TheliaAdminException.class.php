<?php

class TheliaAdminException extends Exception
{
    private $data;
    
    public function __construct($message = '', $code = 0, Exception $previous = null, $data = array())
    {
        parent::__construct($message, $code, $previous);
        
        $this->data = $data;
    }
    
    public function getData()
    {
        return $this->data;
    }
    
    /**
     * Code des exceptions
     */
    const PRODUCT_NOT_FOUND = 100;
    const ATTACHEMENT_NOT_FOUND = 102;
    const CATEGORY_NOT_FOUND = 101;
    const MESSAGE_NOT_FOUND = 103;
    const DEVISE_NOT_FOUND = 104;
    const PROMO_NOT_FOUND = 105;
    const VARIABLE_NOT_FOUND = 106;
    const FOLDER_NOT_FOUND = 107;
    const CONTENT_NOT_FOUND = 108;
    const PLUGIN_NOT_FOUND = 109;
    const ADMIN_NOT_FOUND = 110;
    
    const CLIENT_EDIT_ERROR = 200;
    const CLIENT_ADRESS_EDIT_ERROR = 201;
    const CLIENT_ADD_ADRESS = 202;
    
    const MESSAGE_NAME_EMPTY = 210;
    const MESSAGE_ALREADY_EXISTS = 211;
    
    const VARIABLE_NAME_EMPTY = 220;
    const VARIABLE_ALREADY_EXISTS = 221;
    const VARIABLE_ADD_ERROR = 222;
    
    const FOLDER_ADD_ERROR = 232;
    
    const CONTENT_ADD_ERROR = 242;
    
    const SMTP_EDIT_ERROR = 251;
    
    const PROMO_EDIT_ERROR = 300;
    const PROMO_ADD_ERROR = 302;
    
    const ADMIN_PASSWORD_NOT_MATCH = 400;
    const ADMIN_PASSWORD_EMPTY = 401;
    const ADMIN_DELETE_HIMSELF = 402;
    const ADMIN_IMPOSSIBLE_DETELE_AUTH = 403;
    const ADMIN_MULTIPLE_ERRORS = 404;
    const ADMIN_ALREADY_EXISTS = 405;
    const ADMIN_PROFIL_DOES_NOT_EXISTS = 406;
    const ADMIN_LOGIN_EMPTY = 407;
    
}
