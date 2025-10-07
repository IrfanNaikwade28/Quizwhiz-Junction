<?php
require_once __DIR__ . '/../lib/Database.php';
require_once __DIR__ . '/../lib/Auth.php';

function out($label, $ok, $extra = '') { echo sprintf("%-30s %s%s\n", $label.':', $ok ? 'OK' : 'FAIL', $extra ? ' - '.$extra : ''); }

$errors = 0;
try {
    $db = Database::getInstance();
    $con = $db->getConnection();

    // Seed admin
    $uniq = bin2hex(random_bytes(3));
    $adminEmail = "admin_{$uniq}@local.test";
    $adminPass  = "admin_{$uniq}";
    $db->run('INSERT INTO admin(email, password) VALUES(?, ?)', [$adminEmail, $adminPass]);

    // 1) Admin delete user route
    $uemail = "routeu_{$uniq}@example.com";
    Auth::registerUser('Route User', $uemail, 'p_'.$uniq, 'Route College');
    // add rank + history
    $eidTmp = uniqid('T');
    $db->run('INSERT INTO quiz(eid, title, sahi, wrong, total, date) VALUES(?,?,?,?,?, NOW())', [$eidTmp, 'Tmp', 1, 0, 1]);
    $db->run('INSERT INTO history(email, eid, score, level, sahi, wrong, date) VALUES(?,?,?,?,?,?, NOW())', [$uemail, $eidTmp, 3, 1, 3, 0]);
    $db->run('INSERT INTO `rank`(`email`, `score`, `time`) VALUES(?, ?, NOW())', [$uemail, 3]);

    // invoke helper runner as separate process with env vars
    $php = 'C:\\xampp\\php\\php.exe';
    $runner = __DIR__.'\\helpers\\update_delete_user_runner.php';
    $cmd = sprintf('"%s" -d display_errors=1 -d error_reporting=E_ALL -f "%s"', $php, $runner);
    $env = [
        'ADMIN_EMAIL' => $adminEmail,
        'TARGET_EMAIL' => $uemail
    ];
    // Pass env by prefixing command in Windows PowerShell is non-trivial; we will set via putenv prior to proc_open by writing a tiny temp wrapper
    $wrapper = __DIR__.'\\helpers\\__tmp_run_delete.php';
    file_put_contents($wrapper, '<?php putenv("ADMIN_EMAIL='.addslashes($adminEmail).'"); putenv("TARGET_EMAIL='.addslashes($uemail).'"); require __DIR__."/update_delete_user_runner.php";');
    $cmd = sprintf('"%s" -d display_errors=1 -d error_reporting=E_ALL -f "%s"', $php, $wrapper);
    $out = []; $code = 0; exec($cmd, $out, $code);
    @unlink($wrapper);

    // Verify deletion
    $u = $db->fetchOne('SELECT email FROM user WHERE email = ?', [$uemail]);
    $h = $db->fetchOne('SELECT email FROM history WHERE email = ?', [$uemail]);
    $r = $db->fetchOne('SELECT `email` FROM `rank` WHERE `email` = ?', [$uemail]);
    out('Admin delete user', !$u && !$h && !$r);

    // Clean up temporary quiz inserted for this test to avoid leaving 'Tmp' in real data
    $db->run('DELETE FROM quiz WHERE eid = ?', [$eidTmp]);

    // 2) Admin remove quiz route
    $eid = uniqid('Z');
    $db->run('INSERT INTO quiz(eid, title, sahi, wrong, total, date) VALUES(?,?,?,?,?, NOW())', [$eid, 'Remove Me', 1, 0, 1]);
    $qid = uniqid('Q');
    $db->run('INSERT INTO questions(eid, qid, qns, choice, sn) VALUES(?,?,?,?,?)', [$eid, $qid, 'Q?', 4, 1]);
    $oaid = uniqid('A'); $obid = uniqid('B'); $ocid = uniqid('C'); $odid = uniqid('D');
    $db->run('INSERT INTO options(qid, `option`, optionid) VALUES(?, ?, ?)', [$qid, 'a', $oaid]);
    $db->run('INSERT INTO options(qid, `option`, optionid) VALUES(?, ?, ?)', [$qid, 'b', $obid]);
    $db->run('INSERT INTO options(qid, `option`, optionid) VALUES(?, ?, ?)', [$qid, 'c', $ocid]);
    $db->run('INSERT INTO options(qid, `option`, optionid) VALUES(?, ?, ?)', [$qid, 'd', $odid]);
    $db->run('INSERT INTO answer(qid, ansid) VALUES(?, ?)', [$qid, $oaid]);
    $db->run('INSERT INTO history(email, eid, score, level, sahi, wrong, date) VALUES(?,?,?,?,?,?, NOW())', [$adminEmail, $eid, 0, 0, 0, 0]);

    $wrapper2 = __DIR__.'\\helpers\\__tmp_run_rmquiz.php';
    file_put_contents($wrapper2, '<?php putenv("ADMIN_EMAIL='.addslashes($adminEmail).'"); putenv("TARGET_EID='.addslashes($eid).'"); require __DIR__."/update_remove_quiz_runner.php";');
    $cmd2 = sprintf('"%s" -d display_errors=1 -d error_reporting=E_ALL -f "%s"', $php, $wrapper2);
    $out2 = []; $code2 = 0; exec($cmd2, $out2, $code2);
    @unlink($wrapper2);

    // Verify quiz and related deletions
    $qz = $db->fetchOne('SELECT eid FROM quiz WHERE eid = ?', [$eid]);
    $qs = $db->fetchOne('SELECT qid FROM questions WHERE eid = ?', [$eid]);
    $op = $db->fetchOne('SELECT qid FROM options WHERE qid = ?', [$qid]);
    $an = $db->fetchOne('SELECT qid FROM answer WHERE qid = ?', [$qid]);
    $hh = $db->fetchOne('SELECT eid FROM history WHERE eid = ?', [$eid]);
    out('Admin remove quiz', !$qz && !$qs && !$op && !$an && !$hh);

    // Cleanup admin
    $db->run('DELETE FROM admin WHERE email = ?', [$adminEmail]);

} catch (Throwable $e) { out('Exception', false, $e->getMessage()); $errors++; }

if ($errors) exit(1);
