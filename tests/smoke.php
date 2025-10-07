<?php
require_once __DIR__ . '/../lib/Database.php';
require_once __DIR__ . '/../lib/Auth.php';
require_once __DIR__ . '/../lib/Init.php';

function out($label, $ok, $extra = '') {
    echo sprintf("%-30s %s%s\n", $label.':', $ok ? 'OK' : 'FAIL', $extra ? ' - '.$extra : '');
}

$errors = 0;

try {
    $db = Database::getInstance();
    $con = $db->getConnection();
    out('DB ping', $con->ping());

    // Unique test data
    $uniq = bin2hex(random_bytes(3));
    $userEmail = "test_{$uniq}@example.com";
    $userPass  = "pass_{$uniq}";
    $userName  = "Tester {$uniq}";
    $college   = "Test College";

    // 1) Register user
    $reg = Auth::registerUser($userName, $userEmail, $userPass, $college);
    out('Register user', (bool)$reg);

    // 2) Login user
    $login = Auth::loginUser($userEmail, $userPass);
    out('Login user', (bool)$login);

    // 3) Admin login (seed a temporary admin to avoid env coupling)
    $adminEmail = 'admin_'.$uniq.'@local.test';
    $adminPass  = 'admin_'.$uniq;
    $existsAdmin = $db->fetchOne('SELECT email FROM admin WHERE email = ?', [$adminEmail]);
    if (!$existsAdmin) {
        // insert with auto-increment id
        $db->run('INSERT INTO admin(email, password) VALUES(?, ?)', [$adminEmail, $adminPass]);
    }
    $adminOk = Auth::loginAdmin($adminEmail, $adminPass);
    out('Login admin', $adminOk);

    // 4) Create quiz
    $eid = uniqid();
    $title = 'Smoke '.$uniq;
    $sahi = 1; $wrong = 0; $total = 1;
    $db->run('INSERT INTO quiz(eid, title, sahi, wrong, total, date) VALUES(?,?,?,?,?, NOW())', [$eid, $title, $sahi, $wrong, $total]);
    out('Create quiz', true, $eid);

    // 5) Add one question + 4 options (a correct)
    $qid = uniqid();
    $db->run('INSERT INTO questions(eid, qid, qns, choice, sn) VALUES(?,?,?,?,?)', [$eid, $qid, '2 + 2 = ?', 4, 1]);
    $oaid = uniqid(); $obid = uniqid(); $ocid = uniqid(); $odid = uniqid();
    $db->run('INSERT INTO options(qid, `option`, optionid) VALUES(?, ?, ?)', [$qid, '4', $oaid]);
    $db->run('INSERT INTO options(qid, `option`, optionid) VALUES(?, ?, ?)', [$qid, '3', $obid]);
    $db->run('INSERT INTO options(qid, `option`, optionid) VALUES(?, ?, ?)', [$qid, '2', $ocid]);
    $db->run('INSERT INTO options(qid, `option`, optionid) VALUES(?, ?, ?)', [$qid, '5', $odid]);
    $db->run('INSERT INTO answer(qid, ansid) VALUES(?, ?)', [$qid, $oaid]);
    out('Add question+options', true);

    // 6) Simulate attempt (same logic as update.php)
    $db->run('INSERT INTO history(email, eid, score, level, sahi, wrong, date) VALUES(?,?,?,?,?,?, NOW())', [$userEmail, $eid, 0, 0, 0, 0]);
    $hist = $db->fetchOne('SELECT score, sahi, wrong FROM history WHERE eid = ? AND email = ? FOR UPDATE', [$eid, $userEmail]);
    $score = (int)($hist['score'] ?? 0);
    $right = (int)($hist['sahi'] ?? 0);
    $wrongCt = (int)($hist['wrong'] ?? 0);
    // choose correct answer
    $correct = $db->fetchOne('SELECT ansid FROM answer WHERE qid = ?', [$qid]);
    $isCorrect = $correct && $correct['ansid'] === $oaid;
    if ($isCorrect) {
        $right++; $score += $sahi;
        $db->run('UPDATE history SET score = ?, level = ?, sahi = ?, date = NOW() WHERE email = ? AND eid = ?', [$score, 1, $right, $userEmail, $eid]);
    } else {
        $wrongCt++; $score -= $wrong;
        $db->run('UPDATE history SET score = ?, level = ?, wrong = ?, date = NOW() WHERE email = ? AND eid = ?', [$score, 1, $wrongCt, $userEmail, $eid]);
    }
    out('Submit answer', $isCorrect);

    // 7) Finalize rank
    $row = $db->fetchOne('SELECT score FROM history WHERE eid = ? AND email = ?', [$eid, $userEmail]);
    $s = (int)($row['score'] ?? 0);
    $rankRow = $db->fetchOne('SELECT `score` FROM `rank` WHERE `email` = ?', [$userEmail]);
    if (!$rankRow) {
        $db->run('INSERT INTO `rank`(`email`, `score`, `time`) VALUES(?, ?, NOW())', [$userEmail, $s]);
    } else {
        $new = (int)$rankRow['score'] + $s;
        $db->run('UPDATE `rank` SET `score` = ?, `time` = NOW() WHERE `email` = ?', [$new, $userEmail]);
    }
    $ver = $db->fetchOne('SELECT `score` FROM `rank` WHERE `email` = ?', [$userEmail]);
    out('Rank updated', isset($ver['score']) && (int)$ver['score'] >= 1, 'score='.$ver['score']);

    // Cleanup
    $db->run('DELETE FROM history WHERE email = ? AND eid = ?', [$userEmail, $eid]);
    $db->run('DELETE FROM `rank` WHERE `email` = ?', [$userEmail]);
    $db->run('DELETE FROM options WHERE qid = ?', [$qid]);
    $db->run('DELETE FROM answer WHERE qid = ?', [$qid]);
    $db->run('DELETE FROM questions WHERE eid = ?', [$eid]);
    $db->run('DELETE FROM quiz WHERE eid = ?', [$eid]);
    $db->run('DELETE FROM user WHERE email = ?', [$userEmail]);
    $db->run('DELETE FROM admin WHERE email = ?', [$adminEmail]);
    out('Cleanup', true);

} catch (Throwable $e) {
    out('Exception', false, $e->getMessage());
    $errors++;
}

if ($errors) {
    exit(1);
}

?>
