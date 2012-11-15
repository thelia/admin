<?php

use Symfony\Component\HttpFoundation\Request;

class ActionsAdminBase extends ActionsBase
{
    protected function extractResult(Request $request, array $results, array $mapping)
    {
        
        $return = array();
        $firstKey = array_shift(array_keys($mapping));
        
        foreach($results as $result)
        {
            if ( false !== $request->request->get($mapping[$firstKey].$result->id, false) )
            {
                foreach($mapping as $key => $param)
                {
                    $return[$result->id][$key] = $request->request->get($param.$result->id);
                }
            }
        }
        return $return;
    }
}