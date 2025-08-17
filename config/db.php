<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'whitewolf');

function getDBConnection() {
    static $db = null;
    
    if ($db === null) {
        $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($db->connect_error) {
            // Для production логируем в файл
            error_log("DB Error: ".$db->connect_error);
            die(json_encode([
                'success' => false,
                'error' => 'Database error'
            ]));
        }
        $db->set_charset("utf8mb4");
    }
    
    return $db;
}