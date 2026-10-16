<?php
/*
 * Edit this file on the hosting server with your AwardSpace database settings.
 * Leave the values as-is locally if you are testing with XAMPP/MySQL.
 */
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'your_db_user');
define('DB_PASSWORD', getenv('DB_PASSWORD') ?: 'your_db_password');
define('DB_NAME', getenv('DB_NAME') ?: 'your_db_name');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DATABASE_URL', getenv('DATABASE_URL') ?: '');
