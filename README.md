Add three functions to mysql extension :

 - mysql_prepare
 - mysql_execute
 - mysql_fetch_all


Exemple : 

``` php
ini_set('mysql.trace_mode', true);
mysql_connect('localhost', 'root', '');
mysql_select_db('test');
mysql_set_charset('utf8');

$stmt = mysql_prepare('SELECT * FROM `test` WHERE `id` > ? LIMIT ?');
$result = mysql_execute($stmt, array(5, 3));

print_r(mysql_fetch_all($result, 'object'));
```