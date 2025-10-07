<?php
// Runner to invoke update.php admin remove quiz branch
$root = realpath(__DIR__ . '/..');
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SESSION = [
  'email' => getenv('ADMIN_EMAIL') ?: 'admin@test.local',
  'role'  => 'admin',
  'key'   => 'suryapinky'
];
$_GET = [ 'q' => 'rmquiz' ];
$_POST = [ 'eid' => getenv('TARGET_EID') ?: '' ];

require realpath(__DIR__.'/../../update.php');
