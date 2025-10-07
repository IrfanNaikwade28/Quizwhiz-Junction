<?php
require_once __DIR__.'/database.php';
require_once __DIR__.'/lib/Init.php';
require_once __DIR__.'/lib/Database.php';

Init::startSession();
$email = $_SESSION['email'] ?? null;
$role  = $_SESSION['role'] ?? (isset($_SESSION['key']) && $_SESSION['key']==='suryapinky' ? 'admin' : null);

if (!$email) {
    header('Location: login.php');
    exit;
}

$db = Database::getInstance();
$con = $db->getConnection();

// Admin: Delete user (POST)
if (isset($_GET['demail']) && ($role === 'admin')) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(400);
        exit('Invalid request');
    }
    $demail = (string)($_POST['demail'] ?? '');
    if ($demail !== '') {
        $con->begin_transaction();
        try {
            $db->run('DELETE FROM `rank` WHERE `email` = ?', [$demail]);
            $db->run('DELETE FROM history WHERE email = ?', [$demail]);
            $db->run('DELETE FROM user WHERE email = ?', [$demail]);
            $con->commit();
        } catch (Throwable $e) {
            $con->rollback();
        }
    }
    header('Location: dashboard.php?q=1');
    exit;
}

// Admin: Remove quiz (POST)
if ((isset($_GET['q']) && $_GET['q'] === 'rmquiz') && ($role === 'admin')) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(400);
        exit('Invalid request');
    }
    $eid = (string)($_POST['eid'] ?? '');
    if ($eid !== '') {
        $con->begin_transaction();
        try {
            $qst = $db->fetchAll('SELECT qid FROM questions WHERE eid = ?', [$eid]);
            foreach ($qst as $row) {
                $qid = $row['qid'];
                $db->run('DELETE FROM options WHERE qid = ?', [$qid]);
                $db->run('DELETE FROM answer WHERE qid = ?', [$qid]);
            }
            $db->run('DELETE FROM questions WHERE eid = ?', [$eid]);
            $db->run('DELETE FROM quiz WHERE eid = ?', [$eid]);
            $db->run('DELETE FROM history WHERE eid = ?', [$eid]);
            $con->commit();
        } catch (Throwable $e) {
            $con->rollback();
        }
    }
    header('Location: dashboard.php?q=5');
    exit;
}

// Admin: Add quiz (POST)
if ((isset($_GET['q']) && $_GET['q'] === 'addquiz') && ($role === 'admin')) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(400);
        exit('Invalid request');
    }
    $name = trim((string)($_POST['name'] ?? ''));
    $name = ucwords(strtolower($name));
    $total = (int)($_POST['total'] ?? 0);
    $sahi  = (int)($_POST['right'] ?? 0);
    $wrong = (int)($_POST['wrong'] ?? 0);
    $id = uniqid();
    if ($name !== '' && $total > 0) {
        $db->run('INSERT INTO quiz(eid, title, sahi, wrong, total, date) VALUES(?,?,?,?,?, NOW())', [$id, $name, $sahi, $wrong, $total]);
        header("Location: dashboard.php?q=4&step=2&eid=$id&n=$total");
        exit;
    }
    header('Location: dashboard.php?q=4');
    exit;
}

// Admin: Add questions (POST)
if ((isset($_GET['q']) && $_GET['q'] === 'addqns') && ($role === 'admin')) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(400);
        exit('Invalid request');
    }
    $n = (int)($_GET['n'] ?? 0);
    $eid = (string)($_GET['eid'] ?? '');
    $ch = (int)($_GET['ch'] ?? 4);
    if ($n > 0 && $eid !== '') {
        $con->begin_transaction();
        try {
            for ($i = 1; $i <= $n; $i++) {
                $qid = uniqid();
                $qns = (string)($_POST['qns'.$i] ?? '');
                $db->run('INSERT INTO questions(eid, qid, qns, choice, sn) VALUES(?,?,?,?,?)', [$eid, $qid, $qns, $ch, $i]);
                $oaid = uniqid();
                $obid = uniqid();
                $ocid = uniqid();
                $odid = uniqid();
                $a = (string)($_POST[$i.'1'] ?? '');
                $b = (string)($_POST[$i.'2'] ?? '');
                $c = (string)($_POST[$i.'3'] ?? '');
                $d = (string)($_POST[$i.'4'] ?? '');
                $db->run('INSERT INTO options(qid, `option`, optionid) VALUES(?, ?, ?)', [$qid, $a, $oaid]);
                $db->run('INSERT INTO options(qid, `option`, optionid) VALUES(?, ?, ?)', [$qid, $b, $obid]);
                $db->run('INSERT INTO options(qid, `option`, optionid) VALUES(?, ?, ?)', [$qid, $c, $ocid]);
                $db->run('INSERT INTO options(qid, `option`, optionid) VALUES(?, ?, ?)', [$qid, $d, $odid]);
                $e = (string)($_POST['ans'.$i] ?? 'a');
                switch ($e) {
                    case 'a': $ansid = $oaid; break;
                    case 'b': $ansid = $obid; break;
                    case 'c': $ansid = $ocid; break;
                    case 'd': $ansid = $odid; break;
                    default:  $ansid = $oaid; break;
                }
                $db->run('INSERT INTO answer(qid, ansid) VALUES(?, ?)', [$qid, $ansid]);
            }
            $con->commit();
        } catch (Throwable $e) {
            $con->rollback();
        }
    }
    header('Location: dashboard.php?q=1');
    exit;
}

// User/Admin: Submit quiz answer (POST)
if ((isset($_GET['q']) && $_GET['q'] === 'quiz') && (isset($_GET['step']) && (int)$_GET['step'] === 2)) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(400);
        exit('Invalid request');
    }
    $eid = (string)($_GET['eid'] ?? '');
    $sn  = (int)($_GET['n'] ?? 0);
    $total = (int)($_GET['t'] ?? 0);
    $ans = (string)($_POST['ans'] ?? '');
    $qid = (string)($_GET['qid'] ?? '');

    if ($eid === '' || $qid === '' || $sn <= 0 || $total <= 0) {
        header('Location: welcome.php?q=1');
        exit;
    }

    $correct = $db->fetchOne('SELECT ansid FROM answer WHERE qid = ?', [$qid]);
    $isCorrect = $correct && hash_equals((string)$correct['ansid'], $ans);
    $quizMeta = $db->fetchOne('SELECT sahi, wrong FROM quiz WHERE eid = ?', [$eid]);
    $sahi = (int)($quizMeta['sahi'] ?? 0);
    $wrong = (int)($quizMeta['wrong'] ?? 0);

    $con->begin_transaction();
    try {
        if ($sn === 1) {
            $db->run('INSERT INTO history(email, eid, score, level, sahi, wrong, date) VALUES(?,?,?,?,?,?, NOW())', [$email, $eid, 0, 0, 0, 0]);
        }
        $hist = $db->fetchOne('SELECT score, sahi, wrong FROM history WHERE eid = ? AND email = ? FOR UPDATE', [$eid, $email]);
        $score = (int)($hist['score'] ?? 0);
        $right = (int)($hist['sahi'] ?? 0);
        $wrongCt = (int)($hist['wrong'] ?? 0);
        if ($isCorrect) {
            $right++;
            $score += $sahi;
            $db->run('UPDATE history SET score = ?, level = ?, sahi = ?, date = NOW() WHERE email = ? AND eid = ?', [$score, $sn, $right, $email, $eid]);
        } else {
            $wrongCt++;
            $score -= $wrong;
            $db->run('UPDATE history SET score = ?, level = ?, wrong = ?, date = NOW() WHERE email = ? AND eid = ?', [$score, $sn, $wrongCt, $email, $eid]);
        }
        $con->commit();
    } catch (Throwable $e) {
        $con->rollback();
    }

    if ($sn !== $total) {
        $sn++;
        header("Location: welcome.php?q=quiz&step=2&eid=".urlencode($eid)."&n=$sn&t=$total");
        exit;
    }

    // Finalize rank only for non-admin participant
    if ($role !== 'admin') {
        $row = $db->fetchOne('SELECT score FROM history WHERE eid = ? AND email = ?', [$eid, $email]);
        $s = (int)($row['score'] ?? 0);
    $rankRow = $db->fetchOne('SELECT `score` FROM `rank` WHERE `email` = ?', [$email]);
        if (!$rankRow) {
            $db->run('INSERT INTO `rank`(`email`, `score`, `time`) VALUES(?, ?, NOW())', [$email, $s]);
        } else {
            $new = (int)$rankRow['score'] + $s;
            $db->run('UPDATE `rank` SET `score` = ?, `time` = NOW() WHERE `email` = ?', [$new, $email]);
        }
    }
    header('Location: welcome.php?q=result&eid='.urlencode($eid));
    exit;
}

// Quiz restart (POST)
if ((isset($_GET['q']) && $_GET['q'] === 'quizre') && (isset($_GET['step']) && (int)$_GET['step'] === 25)) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(400);
        exit('Invalid request');
    }
    $eid = (string)($_POST['eid'] ?? ($_GET['eid'] ?? ''));
    $t   = (int)($_POST['t'] ?? ($_GET['t'] ?? 0));
    if ($eid === '') { header('Location: welcome.php?q=1'); exit; }
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
    } catch (Throwable $e) {
        $con->rollback();
    }
    header('Location: welcome.php?q=quiz&step=2&eid='.urlencode($eid).'&n=1&t='.$t);
    exit;
}

// Fallback
header('Location: index.php');
exit;
?>



