<?php
// Tiny test runner for Windows/XAMPP environments without global php in PATH from VS Code
// Run this by opening XAMPP Shell or ensure php.exe is in PATH. Alternatively, visit tests/run.php via localhost.

require_once __DIR__.'/unit/AuthTest.php';

// Separation line
echo str_repeat('-', 40), "\n";
require_once __DIR__.'/smoke.php';

