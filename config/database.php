<?php
return ['dsn'=>'mysql:host='.env('DB_HOST','127.0.0.1').';port='.env('DB_PORT','3306').';dbname='.env('DB_NAME','brainy_journal').';charset=utf8mb4','user'=>env('DB_USER','root'),'pass'=>env('DB_PASS','')];
