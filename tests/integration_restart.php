<?php
require_once __DIR__ . '/../lib/Database.php';
require_once __DIR__ . '/../lib/Auth.php';

function out($label, $ok, $extra = '') { echo sprintf("%-30s %s%s\n", $label.':', $ok ? 'OK' : 'FAIL', $extra ? ' - '.$extra : ''); }

$errors = 0;
try {
    $db = Database::getInstance();
    $con = $db->getConnection();

    $uniq = bin2hex(random_bytes(3));
    $email = "restart_{$uniq}@example.com";
    $pass  = "p_{$uniq}";
    $name  = "Restart {$uniq}";
    $col   = "Restart College";

    Auth::registerUser($name, $email, $pass, $col);
    Auth::loginUser($email, $pass);

    // Create a quiz with 1 question worth +2 and wrong -1 so we can observe deltas
    $eid = uniqid('R');
    $title = 'Restart '.$uniq;
    $sahi = 2; $wrong = 1; $total = 1;
    $db->run('INSERT INTO quiz(eid, title, sahi, wrong, total, date) VALUES(?,?,?,?,?, NOW())', [$eid, $title, $sahi, $wrong, $total]);

    // Add 1 question
    $qid = uniqid('Q');
    $db->run('INSERT INTO questions(eid, qid, qns, choice, sn) VALUES(?,?,?,?,?)', [$eid, $qid, '1 + 1 = ?', 4, 1]);
    $oaid = uniqid('A'); $obid = uniqid('B'); $ocid = uniqid('C'); $odid = uniqid('D');
    $db->run('INSERT INTO options(qid, `option`, optionid) VALUES(?, ?, ?)', [$qid, '2', $oaid]);
    $db->run('INSERT INTO options(qid, `option`, optionid) VALUES(?, ?, ?)', [$qid, '1', $obid]);
    $db->run('INSERT INTO options(qid, `option`, optionid) VALUES(?, ?, ?)', [$qid, '0', $ocid]);
    $db->run('INSERT INTO options(qid, `option`, optionid) VALUES(?, ?, ?)', [$qid, '3', $odid]);
    $db->run('INSERT INTO answer(qid, ansid) VALUES(?, ?)', [$qid, $oaid]);

    // Simulate attempt: answer correctly, score +2
    $db->run('INSERT INTO history(email, eid, score, level, sahi, wrong, date) VALUES(?,?,?,?,?,?, NOW())', [$email, $eid, 0, 0, 0, 0]);
    $db->run('UPDATE history SET score = ?, level = ?, sahi = ?, date = NOW() WHERE email = ? AND eid = ?', [2, 1, 1, $email, $eid]);
    // Finalize rank
    $rank = $db->fetchOne('SELECT `score` FROM `rank` WHERE `email` = ?', [$email]);
    if (!$rank) $db->run('INSERT INTO `rank`(`email`, `score`, `time`) VALUES(?, ?, NOW())', [$email, 2]);
    else $db->run('UPDATE `rank` SET `score` = `score` + 2, `time` = NOW() WHERE `email` = ?', [$email]);
    $r1 = $db->fetchOne('SELECT `score` FROM `rank` WHERE `email` = ?', [$email]);
    out('Rank after first finish', (int)$r1['score'] === 2, 'score='.$r1['score']);

    // Simulate restart flow: subtract the last quiz score from rank and delete history
    $con->begin_transaction();
    try {
        $row = $db->fetchOne('SELECT score FROM history WHERE eid = ? AND email = ? FOR UPDATE', [$eid, $email]);
        $s = (int)($row['score'] ?? 0);
        $db->run('DELETE FROM history WHERE eid = ? AND email = ?', [$eid, $email]);
        $rank = $db->fetchOne('SELECT `score` FROM `rank` WHERE `email` = ? FOR UPDATE', [$email]);
        if ($rank) {
            $sun = (int)$rank['score'] - $s;
            if ($sun < 0) $sun = 0;
            $db->run('UPDATE `rank` SET `score` = ?, `time` = NOW() WHERE `email` = ?', [$sun, $email]);
        }
        $con->commit();
    } catch (Throwable $e) { $con->rollback(); throw $e; }

    $r2 = $db->fetchOne('SELECT `score` FROM `rank` WHERE `email` = ?', [$email]);
    out('Rank after restart', (int)$r2['score'] === 0, 'score='.$r2['score']);

    // Cleanup
    $db->run('DELETE FROM options WHERE qid = ?', [$qid]);
    $db->run('DELETE FROM answer WHERE qid = ?', [$qid]);
    $db->run('DELETE FROM questions WHERE eid = ?', [$eid]);
    $db->run('DELETE FROM quiz WHERE eid = ?', [$eid]);
    $db->run('DELETE FROM `rank` WHERE `email` = ?', [$email]);
    $db->run('DELETE FROM user WHERE email = ?', [$email]);
    out('Cleanup', true);

} catch (Throwable $e) { out('Exception', false, $e->getMessage()); $errors++; }

if ($errors) exit(1);
