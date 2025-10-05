<?php 
require_once __DIR__.'/lib/Init.php';
Init::startSession();
Init::destroy();
$ref = isset($_GET['q']) ? $_GET['q'] : 'index.php';
header("Location: $ref");
exit;
?>
