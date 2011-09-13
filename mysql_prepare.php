<?php
/**
 * @param string $query
 * @param ressource $link optional
 *
 * @return string
 */
function mysql_prepare($query, $link = null)
{
    if(false === is_string($query))
    {
        trigger_error(sprintf('%s() expects parameter 1 to be string, %s given', 
        __FUNCTION__, gettype($query)), E_USER_WARNING);
        
        return false;    
    }
    
    if(false === is_null($link) && false === is_resource($link))
    {
        trigger_error(sprintf('%s() expects parameter 2 to be resource, %s given', 
        __FUNCTION__, gettype($link)), E_USER_WARNING);
        
        return false;    
    }
    
    if(is_resource($link) && 'mysql link' !== get_resource_type($link))
    {
        trigger_error(sprintf('%s(): supplied resource is not a valid MySQL-Link resource', 
        __FUNCTION__), E_USER_WARNING);
        
        return false;    
    }

    $stmt = uniqid();
    
    if(is_resource($link))
    {
        $query = mysql_real_escape_string($query, $link);
    }
    else
    {
        $query = mysql_real_escape_string($query);
    }
    
    $prep = sprintf('PREPARE `%s` FROM \'%s\'', $stmt, $query);
   
    if(is_resource($link))
    {
        if(mysql_query($prep, $link))
        {
            return $stmt;
        }
    }
    else
    {
        if(mysql_query($prep))
        {
            return $stmt;
        }
    }
   
    return false;
}

/**
 * @param string $stmt
 * @param array $input_parameters optional
 * @param ressource $link optional
 *
 * @return ressource
 */
function mysql_execute($stmt, $input_parameters = array(), $link = null)
{
    if(false === is_string($stmt))
    {
        trigger_error(sprintf('%s() expects parameter 1 to be string, %s given', 
        __FUNCTION__, gettype($stmt)), E_USER_WARNING);
        
        return false;
    }
    
    if(false === is_array($input_parameters))
    {
        trigger_error(sprintf('%s() expects parameter 2 to be an array, %s given', 
        __FUNCTION__, gettype($input_parameters)), E_USER_WARNING);
        
        return false;
    }
    
    if(false === is_null($link) && false === is_resource($link))
    {
        trigger_error(sprintf('%s() expects parameter 3 to be resource, %s given', 
        __FUNCTION__, gettype($link)), E_USER_WARNING);
        
        return false;
    }
    
    if(is_resource($link) && 'mysql link' !== get_resource_type($link))
    {
        trigger_error(sprintf('%s(): supplied resource is not a valid MySQL-Link resource', 
        __FUNCTION__), E_USER_WARNING);
        
        return false;
    }

    foreach($input_parameters as $id => $input_parameter)
    {            
        $key = sprintf('@`%s`', $id);
       
        if(is_numeric($input_parameter))
        {
            $sf = '@`%s` = %s';
        }
        else
        {          
            $sf = '@`%s` = \'%s\'';
        }
        
        settype($input_parameter, 'string');
        
        if(is_resource($link))
        {
            $input_parameter = mysql_real_escape_string($input_parameter, $link);
        }
        else
        {
            $input_parameter = mysql_real_escape_string($input_parameter);
        }
       
        $sets[$key] = sprintf($sf, $id, $input_parameter);
    }

    if(false === empty($sets))
    {
        $set = sprintf('SET %s', implode(',', $sets));
       
        if(is_resource($link))
        {
            if(false === mysql_query($set, $link))
            {
                return false;
            }
        }
        else
        {
            if(false === mysql_query($set))
            {
                return false;
            }
        }        

        $ext = sprintf('EXECUTE `%s` USING %s', $stmt, implode(',', array_keys($sets)));
    }
    else
    {
        $ext = sprintf('EXECUTE `%s`', $stmt);
    }

    if(is_resource($link))
    {
        return mysql_query($ext, $link);
    }

    return mysql_query($ext);
}

/**
 * @param ressource $result
 * @param string $type optional
 * @param boolean $group optional
 *
 * @return mixed
 */
function mysql_fetch_all($result, $type = 'array', $group = false)
{
    if(false === is_resource($result))
    {
        trigger_error(sprintf('%s() expects parameter 1 to be resource, %s given', 
        __FUNCTION__, gettype($result)), E_USER_WARNING);
        
        return false;
    }
    
    if(false === is_string($type))
    {
        trigger_error(sprintf('%s() expects parameter 2 to be string, %s given', 
        __FUNCTION__, gettype($type)), E_USER_WARNING);
        
        return false;
    }
    
    if(false === is_bool($group))
    {
        trigger_error(sprintf('%s() expects parameter 3 to be boolean, %s given', 
        __FUNCTION__, gettype($group)), E_USER_WARNING);
        
        return false;
    }

    if(is_resource($result) && 'mysql result' !== get_resource_type($result))
    {
        trigger_error(sprintf('%s(): supplied resource is not a valid MySQL result resource', 
        __FUNCTION__), E_USER_WARNING);
        
        return false;
    }

    $func = 'mysql_fetch_' . strtolower($type);

    while(false != ($row = call_user_func($func, $result)))
    {            
        if(false !== $row)
        {
            if($group)
            {                   
                if('array' === $type)
                {
                    array_shift($row);
                }

                if('object' === $type)
                {
                    $cols = get_object_vars($row);
                    $col  = array_shift($cols);
                }
                else
                {
                    $col = array_shift($row);
                }
                                
                $rows[$col][] = $row;
            }
            else
            {
                $rows[] = $row;
            }
        }
        else
        {
            return false;
        }
    }

    mysql_free_result($result);
   
    if(false === empty($rows))
    {
        return $rows;
    }

    return false;
}