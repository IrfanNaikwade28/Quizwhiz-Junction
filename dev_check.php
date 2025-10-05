<?php
require_once __DIR__.'/lib/Database.php';
require_once __DIR__.'/lib/Auth.php';
echo "DB ping: ";
$db = Database::getInstance()->getConnection();
echo $db->ping() ? "ok\n" : "fail\n";
echo "Hash sample: ";
$h = password_hash('test', PASSWORD_DEFAULT);
echo (password_verify('test', $h) ? 'ok' : 'fail') . "\n";
?>
