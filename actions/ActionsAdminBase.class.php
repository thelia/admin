<?php

use Symfony\Component\HttpFoundation\Request;

class ActionsAdminBase extends ActionsBase
{
    /**
     * 
     * get all param form a form like this :
     * 
     * <input type="text" name="document_titre_1">
     * <input type="text" name="document_titre_1">
     * <input type="text" name="document_description_1">
     * 
     * 
     * <input type="text" name="document_titre_2">
     * <input type="text" name="document_titre_2">
     * <input type="text" name="document_description_2">
     * 
     * <input type="text" name="document_titre_3">
     * <input type="text" name="document_titre_3">
     * <input type="text" name="document_description_3">
     * 
     * 
     * 1, 2, are id's record in database
     * 
     * @param \Symfony\Component\HttpFoundation\Request $request Request object containing all HTTP params
     * @param array $results containing object like Product, Image, Document, etc. Each object must contain id property
     * @param array $mapping array like this : 
     * array(
     *       "titre" => "document_titre_",
     *       "chapo" => "document_chapo_",
     *       "description" => "document_description_"
     *   )
     * The key is used in the return array
     * The value is the name search in the form without the id record
     * 
     * @return array multidimensional array
     * 
     * the key is the id of the record in database. This key contain multiple array. For each array the key is the key of $mapping array and the value is the value containing in the form
     * 
     * this exemple returns : 
     * 
     * array(
     *  1 => array(
     *          "titre" => "foo",
     *          "chapo" => "bar",
     *          "description" => "foobar"
     *      ),
     *  2 => array(
     *          "titre" => "my own title",
     *          "chapo" => "YAC",
     *          "description" => "description write in the form for this record"
     *      ),
     *  3 => array(
     *          "titre" => "an other title",
     *          "chapo" => "an other chapo",
     *          "description" => "an other description"
     *      )
     * )
     */
    protected function extractResult(Request $request, array $results, array $mapping, $method = "query")
    {
        
        //print_r($request);exit;
        
        $return = array();
        $firstKey = array_shift(array_keys($mapping));
        
        foreach($results as $result)
        {
            if ( false !== $request->get($mapping[$firstKey].$result->id, false) )
            {
                foreach($mapping as $key => $param)
                {
                    $return[$result->id][$key] = $request->get($param.$result->id);
                }
            }
        }
        return $return;
    }
    
    /**
     * 
     * search all param in a form for input like this : 
     * 
     * 
     * 
     * 
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string $first
     * @param array $mapping
     * @param string $method
     * @return array
     */
    protected function extractArrayResult(Request $request, $first, array $mapping, $method = "query")
    {
        $return = array();
        $firstParam = $request->$method->get($first);
        
        foreach($firstParam as $id => $value)
        {
            foreach($mapping as $param)
            {   
                $return[$id][$param] = $request->$method->get($param."[".$id."]", null, true);
            }
        }
        return $return;
    }
}