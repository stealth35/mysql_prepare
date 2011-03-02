<?php
/**
 * @param string $query
 * @param ressource $link optional
 *
 * @return string
 */
function mysql_prepare($query, $link = null)
{
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
 * @param array $input_parameters optional
 * @param string $stmt
 * @param ressource $link optional
 *
 * @return ressource
 */
function mysql_execute(array $input_parameters = array(), $stmt, $link = null)
{
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
        
        $input_parameter = settype($input_parameter, 'string');
        
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
    if(false === $result)
    {
        return false;
    }

    $func = 'mysql_fetch_' . strtolower($type);

    while(false !== ($row = call_user_func($func, $result)))
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