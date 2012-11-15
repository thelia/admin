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
    
    const CLIENT_EDIT_ERROR = 200;
    const CLIENT_ADRESS_EDIT_ERROR = 201;
    const CLIENT_ADD_ADRESS = 202;
    
    const MESSAGE_NAME_EMPTY = 210;
    const MESSAGE_ALREADY_EXISTS = 211;
    
    const VARIABLE_NAME_EMPTY = 220;
    const VARIABLE_ALREADY_EXISTS = 221;
    const VARIABLE_ADD_ERROR = 222;
    
    const PROMO_EDIT_ERROR = 300;
    const PROMO_ADD_ERROR = 302;

}
