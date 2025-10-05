<?php
require_once __DIR__ . '/lib/Database.php';

// Backward-compatible mysqli connection handle used by existing pages
try {
	$db = Database::getInstance();
	$con = $db->getConnection();
} catch (Throwable $e) {
	http_response_code(500);
	die('Database connection error');
}
?>
