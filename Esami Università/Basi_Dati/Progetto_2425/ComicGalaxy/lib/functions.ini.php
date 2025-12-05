<?php

    function open_pg_connection() {
    require_once __DIR__ . '/../conf/conf.php';
    
    $connection = "host=".myhost." dbname=".mydb." user=".myuser." password=".mypsw;
    
    return pg_connect ($connection);
    
}

function close_pg_connection($db) {
        
    return pg_close ($db);
    
}
?>