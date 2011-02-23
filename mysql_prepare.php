<?php
/**
 * @param string $query
 * @return string
 * @param ressource $link optional
 */
function mysql_prepare($query, $link = null)
{
    $stmt = uniqid();
    $prep = sprintf('PREPARE `%s` FROM \'%s\'', $stmt, mysql_real_escape_string($query, $link));
   
    if(mysql_query($prep))
    {
        return $stmt;
    }
   
    return false;
}

/**
 * @param array $input_parameters optional
 * @param string $stmt
 * @return ressource
 * @param ressource $link optional
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
       
        $sets[$key] = sprintf($sf, $id, mysql_real_escape_string((string) $input_parameter, $link));
    }

    if(!empty($sets))
    {
        $set = sprintf('SET %s', implode(',', $sets));
       
        if(mysql_query($set) === false)
        {
            return false;
        }

        $ext = sprintf('EXECUTE `%s` USING %s', $stmt, implode(',', array_keys($sets)));
    }
    else
    {
        $ext = sprintf('EXECUTE `%s`', $stmt);
    }        

    return mysql_query($ext, $link);
}

/**
 * @param ressource $result
 * @param string $type
 * @return array
 * @param ressource $link optional
 */
function mysql_fetch_all($result, $type = 'array', $link)
{
    if($result === false)
    {
        return false;
    }

    $func = 'mysql_fetch_' . strtolower($type);

    while($row = call_user_func($func, $result, $link))
    {            
        if($row !== false)
        {
            $rows[] = $row;
        }
        else
        {
            return false;
        }
    }

    mysql_free_result($result, $link);
   
    if(!empty($rows))
    {
        return $rows;
    }

    return false;
}