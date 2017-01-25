<?php
/**
 * Example entities generation script
 * Run this in CLI
 */

require_once 'vendor/autoload.php';

try {
    $gen = new Phiber\oosql\driver\mysql('mysql:host=127.0.0.1;port=3356;dbname=mono_dev', 'root', '1234');
    $gen->generate();
} catch (\PDOException $e) {
    print $e->getMessage();
}



?>