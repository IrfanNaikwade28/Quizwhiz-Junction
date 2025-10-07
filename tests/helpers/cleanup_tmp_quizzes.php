<?php
// Utility: delete test-generated 'Tmp' quizzes left in the DB.
// Usage (Windows PowerShell):
//   C:\xampp\php\php.exe -d display_errors=1 -d error_reporting=E_ALL -f tests\helpers\cleanup_tmp_quizzes.php

require_once __DIR__ . '/../../lib/Database.php';

try {
    $db = Database::getInstance();

    // Remove any history rows whose quiz title was 'Tmp'
    // (history references eid, so delete by joining through a subquery of quiz eids)
    $db->run('DELETE FROM history WHERE eid IN (SELECT eid FROM quiz WHERE title = ?)', ['Tmp']);

    // Remove the tmp quizzes themselves
    $db->run('DELETE FROM quiz WHERE title = ?', ['Tmp']);

    echo "Cleanup complete: removed 'Tmp' quizzes and related history.\n";
} catch (Throwable $e) {
    fwrite(STDERR, 'Cleanup failed: ' . $e->getMessage() . "\n");
    exit(1);
}
