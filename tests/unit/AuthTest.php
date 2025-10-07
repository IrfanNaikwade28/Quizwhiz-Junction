<?php
require_once __DIR__ . '/../../lib/Auth.php';
require_once __DIR__ . '/../../lib/Database.php';

function assertTrue($cond, $label) {
    echo ($cond ? "OK  " : "FAIL") . " - $label\n";
    if (!$cond) exit(1);
}

$db = Database::getInstance();
$con = $db->getConnection();

// Prepare
$uniq = bin2hex(random_bytes(3));
$email = "unit_{$uniq}@example.com";
$pass  = "p_{$uniq}";
$name  = "Unit {$uniq}";
$col   = "Unit College";

// Register
$reg = Auth::registerUser($name, $email, $pass, $col);
assertTrue((bool)$reg, 'register user');

// Duplicate should fail
$reg2 = Auth::registerUser($name, $email, $pass, $col);
assertTrue($reg2 === null, 'reject duplicate email');

// Login success
$login = Auth::loginUser($email, $pass);
assertTrue((bool)$login, 'login user success');

// Login failure
$bad = Auth::loginUser($email, $pass.'x');
assertTrue($bad === null, 'login user fail');

// Cleanup
$db->run('DELETE FROM user WHERE email = ?', [$email]);
assertTrue(true, 'cleanup');
