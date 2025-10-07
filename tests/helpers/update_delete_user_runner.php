<?php
// Runner to invoke update.php admin delete user branch
$root = realpath(__DIR__ . '/..'); // tests/
$_SERVER['REQUEST_METHOD'] = 'POST';
session_id('cli-test');
// Not starting session to avoid headers; Init::startSession() is a no-op in CLI; we can use $_SESSION array directly
$_SESSION = [
  'email' => getenv('ADMIN_EMAIL') ?: 'admin@test.local',
  'role'  => 'admin',
  'key'   => 'suryapinky'
];
$_GET = [ 'demail' => '1' ]; // flag presence; actual email is in POST
$_POST = [ 'demail' => getenv('TARGET_EMAIL') ?: '' ];

require realpath(__DIR__.'/../../update.php');
