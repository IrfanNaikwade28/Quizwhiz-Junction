<?php
require_once __DIR__ . '/includes/header.php';
require_login();
require_once __DIR__ . '/includes/helpers.php';

// Workflow states stored in session: active_attempt = [attempt_id, quiz_id, question_ids[], current_index, question_start_ts]

function load_attempt($attemptId) {
    $stmt = db()->prepare('SELECT * FROM attempts WHERE id=?');
    $stmt->execute([$attemptId]);
    return $stmt->fetch();
}

$user = current_user();
$canPlay = can_attempt_quiz($user);
if (!$canPlay) {
  echo '<div class="text-white/70">Admins cannot attempt quizzes.</div>';
  require_once __DIR__ . '/includes/footer.php';
  exit;
}
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf'] ?? '')) {
        $error = 'Invalid token.';
    } else {
        $action = $_POST['action'] ?? '';
        if ($action === 'start') {
            $quizId = (int)($_POST['quiz_id'] ?? 0);
            $qStmt = db()->prepare('SELECT id FROM questions WHERE quiz_id=? ORDER BY id ASC');
            $qStmt->execute([$quizId]);
            $questionIds = array_column($qStmt->fetchAll(), 'id');
            if (!$questionIds) {
                $error = 'Quiz has no questions.';
            } else {
                $ins = db()->prepare('INSERT INTO attempts (quiz_id, user_id, score, total_time, created_at) VALUES (?, ?, 0, 0, NOW())');
                $ins->execute([$quizId, $user['id']]);
                $attemptId = db()->lastInsertId();
                $_SESSION['active_attempt'] = [
                    'attempt_id' => $attemptId,
                    'quiz_id' => $quizId,
                    'question_ids' => $questionIds,
                    'current_index' => 0,
                    'question_start_ts' => time(),
                    'per_question_times' => []
                ];
                redirect('quiz.php');
            }
        } elseif ($action === 'answer') {
            $aa = $_SESSION['active_attempt'] ?? null;
            if (!$aa) { $error = 'No active attempt.'; }
            else {
                $questionId = $aa['question_ids'][$aa['current_index']];
                $chosenOption = (int)($_POST['option_id'] ?? 0);
                $elapsed = time() - $aa['question_start_ts'];
                $metaStmt = db()->prepare('SELECT question_time, points_per_question FROM quizzes WHERE id=?');
                $metaStmt->execute([$aa['quiz_id']]);
                $meta = $metaStmt->fetch();
                $qtime = $meta ? (int)$meta['question_time'] : 30;
                $ppq = $meta ? (int)$meta['points_per_question'] : 10;
                if ($qtime <= 0) $qtime = 30;
                if ($elapsed > $qtime) $elapsed = $qtime; // clamp
                // record answer
                $optStmt = db()->prepare('SELECT is_correct FROM options WHERE id=? AND question_id=?');
                $optStmt->execute([$chosenOption, $questionId]);
                $opt = $optStmt->fetch();
                $isCorrect = $opt ? (int)$opt['is_correct'] : 0;
                $ansIns = db()->prepare('INSERT INTO attempt_answers (attempt_id, question_id, option_id, is_correct, time_spent) VALUES (?, ?, ?, ?, ?)');
                $ansIns->execute([$aa['attempt_id'], $questionId, $chosenOption, $isCorrect, $elapsed]);
                $aa['per_question_times'][] = $elapsed;
                $aa['current_index']++;
                $aa['question_start_ts'] = time();
                $_SESSION['active_attempt'] = $aa;
                if ($aa['current_index'] >= count($aa['question_ids'])) {
                    // finalize
                    $scoreStmt = db()->prepare('SELECT SUM(is_correct) AS score, SUM(time_spent) AS total_time FROM attempt_answers WHERE attempt_id=?');
                    $scoreStmt->execute([$aa['attempt_id']]);
                    $sc = $scoreStmt->fetch();
                    $up = db()->prepare('UPDATE attempts SET score=?, total_time=? WHERE id=?');
                    $up->execute([(int)$sc['score'], (int)$sc['total_time'], $aa['attempt_id']]);
                    // dynamic points: points_per_question * score minus time penalty blocks sized by question_time
                    $pointsGain = (int)$sc['score'] * $ppq - (int)floor(((int)$sc['total_time']) / $qtime);
                    if ($pointsGain < 0) $pointsGain = 0;
                    db()->prepare('UPDATE users SET points = points + ? WHERE id=?')->execute([$pointsGain, $user['id']]);
                    // Invite bonus on first attempt with positive score
                    if ((int)$sc['score'] > 0) {
                      $inviter = db()->prepare('SELECT invited_by FROM users WHERE id=?');
                      $inviter->execute([$user['id']]);
                      $inviterId = (int)$inviter->fetchColumn();
                      if ($inviterId) {
                        grant_invite_points($inviterId, $user['id']);
                      }
                    }
                    unset($_SESSION['active_attempt']);
                    redirect('results.php?attempt_id=' . $aa['attempt_id']);
                } else {
                    redirect('quiz.php');
                }
            }
        } elseif ($action === 'timeout') {
            // treat as no-answer (or answer with option_id null)
            $aa = $_SESSION['active_attempt'] ?? null;
            if ($aa) {
                $questionId = $aa['question_ids'][$aa['current_index']];
                $elapsed = time() - $aa['question_start_ts'];
                $metaStmt = db()->prepare('SELECT question_time, points_per_question FROM quizzes WHERE id=?');
                $metaStmt->execute([$aa['quiz_id']]);
                $meta = $metaStmt->fetch();
                $qtime = $meta ? (int)$meta['question_time'] : 30;
                $ppq = $meta ? (int)$meta['points_per_question'] : 10;
                if ($qtime <= 0) $qtime = 30;
                if ($elapsed > $qtime) $elapsed = $qtime;
                $ansIns = db()->prepare('INSERT INTO attempt_answers (attempt_id, question_id, option_id, is_correct, time_spent) VALUES (?, ?, NULL, 0, ?)');
                $ansIns->execute([$aa['attempt_id'], $questionId, $elapsed]);
                $aa['per_question_times'][] = $elapsed;
                $aa['current_index']++;
                $aa['question_start_ts'] = time();
                $_SESSION['active_attempt'] = $aa;
                if ($aa['current_index'] >= count($aa['question_ids'])) {
                    $scoreStmt = db()->prepare('SELECT SUM(is_correct) AS score, SUM(time_spent) AS total_time FROM attempt_answers WHERE attempt_id=?');
                    $scoreStmt->execute([$aa['attempt_id']]);
                    $sc = $scoreStmt->fetch();
                    $up = db()->prepare('UPDATE attempts SET score=?, total_time=? WHERE id=?');
                    $up->execute([(int)$sc['score'], (int)$sc['total_time'], $aa['attempt_id']]);
                    $pointsGain = (int)$sc['score'] * $ppq - (int)floor(((int)$sc['total_time']) / $qtime);
                    if ($pointsGain < 0) $pointsGain = 0;
                    db()->prepare('UPDATE users SET points = points + ? WHERE id=?')->execute([$pointsGain, $user['id']]);
                    if ((int)$sc['score'] > 0) {
                      $inviter = db()->prepare('SELECT invited_by FROM users WHERE id=?');
                      $inviter->execute([$user['id']]);
                      $inviterId = (int)$inviter->fetchColumn();
                      if ($inviterId) {
                        grant_invite_points($inviterId, $user['id']);
                      }
                    }
                    unset($_SESSION['active_attempt']);
                    redirect('results.php?attempt_id=' . $aa['attempt_id']);
                } else {
                    redirect('quiz.php');
                }
            }
        }
    }
}

$aa = $_SESSION['active_attempt'] ?? null;
if (!$aa) {
    echo '<div class="text-white/70">No active attempt. Start a quiz from the list.</div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$currentQuestionId = $aa['question_ids'][$aa['current_index']];
$qStmt = db()->prepare('SELECT * FROM questions WHERE id=?');
$qStmt->execute([$currentQuestionId]);
$question = $qStmt->fetch();
$optStmt = db()->prepare('SELECT * FROM options WHERE question_id=? ORDER BY id ASC');
$optStmt->execute([$currentQuestionId]);
$options = $optStmt->fetchAll();

// Recompute remaining time (server authoritative)
// Load quiz to determine per-question time and points metadata
$metaStmt = db()->prepare('SELECT question_time, points_per_question FROM quizzes WHERE id=?');
$metaStmt->execute([$aa['quiz_id']]);
$meta = $metaStmt->fetch();
$qtime = $meta ? (int)$meta['question_time'] : 30;
$ppq = $meta ? (int)$meta['points_per_question'] : 10;
if ($qtime <= 0) { $qtime = 30; }
$elapsed = time() - $aa['question_start_ts'];
if ($elapsed > $qtime) $elapsed = $qtime;
$remaining = $qtime - $elapsed;
// Server refresh integrity: if remaining <=0 finalize as timeout
if ($remaining <= 0) {
  // emulate timeout submission without user interaction
  $_POST['csrf'] = csrf_token();
  $_POST['action'] = 'timeout';
  // Re-run logic to record timeout cleanly
  // (Minimal duplication; could refactor into a function.)
  $aa = $_SESSION['active_attempt'];
  $questionId = $aa['question_ids'][$aa['current_index']];
  $elapsedFinal = $qtime;
  $ansIns = db()->prepare('INSERT INTO attempt_answers (attempt_id, question_id, option_id, is_correct, time_spent) VALUES (?, ?, NULL, 0, ?)');
  $ansIns->execute([$aa['attempt_id'], $questionId, $elapsedFinal]);
  $aa['per_question_times'][] = $elapsedFinal;
  $aa['current_index']++;
  $aa['question_start_ts'] = time();
  $_SESSION['active_attempt'] = $aa;
  if ($aa['current_index'] >= count($aa['question_ids'])) {
    $scoreStmt = db()->prepare('SELECT SUM(is_correct) AS score, SUM(time_spent) AS total_time FROM attempt_answers WHERE attempt_id=?');
    $scoreStmt->execute([$aa['attempt_id']]);
    $sc = $scoreStmt->fetch();
    $up = db()->prepare('UPDATE attempts SET score=?, total_time=? WHERE id=?');
    $up->execute([(int)$sc['score'], (int)$sc['total_time'], $aa['attempt_id']]);
  $pointsGain = (int)$sc['score'] * $ppq - (int)floor(((int)$sc['total_time']) / $qtime);
    if ($pointsGain < 0) $pointsGain = 0;
    db()->prepare('UPDATE users SET points = points + ? WHERE id=?')->execute([$pointsGain, $user['id']]);
    if ((int)$sc['score'] > 0) {
      $inviter = db()->prepare('SELECT invited_by FROM users WHERE id=?');
      $inviter->execute([$user['id']]);
      $inviterId = (int)$inviter->fetchColumn();
      if ($inviterId) { grant_invite_points($inviterId, $user['id']); }
    }
    unset($_SESSION['active_attempt']);
    redirect('results.php?attempt_id=' . $aa['attempt_id']);
  } else {
    redirect('quiz.php');
  }
  exit;
}
?>
<div class="max-w-3xl mx-auto">
  <div class="flex items-center justify-between mb-6">
    <h1 class="text-xl font-semibold">Question <?= $aa['current_index'] + 1 ?> / <?= count($aa['question_ids']) ?></h1>
    <div id="timer" class="text-lg font-bold px-4 py-2 rounded-xl bg-violet"></div>
  </div>
  <div class="rounded-2xl p-6 bg-white/5 border border-white/10 shadow-soft space-y-6">
    <p class="text-lg font-medium"><?= h($question['text']) ?></p>
    <form method="post" class="space-y-3" id="answerForm">
      <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>" />
      <input type="hidden" name="action" value="answer" />
      <?php foreach($options as $o): ?>
        <button name="option_id" value="<?= (int)$o['id'] ?>" class="block w-full text-left px-4 py-3 rounded-xl bg-white/10 hover:bg-white/20 focus:outline-none">
          <?= h($o['text']) ?>
        </button>
      <?php endforeach; ?>
    </form>
  </div>
</div>
<form method="post" id="timeoutForm" class="hidden">
  <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>" />
  <input type="hidden" name="action" value="timeout" />
</form>
<script>
  // Initialize countdown after scripts and DOM are ready to avoid race conditions
  window.addEventListener('DOMContentLoaded', function() {
    const timerEl = document.getElementById('timer');
    const start = function() {
      if (typeof window.startCountdown === 'function') {
        window.startCountdown(<?= $remaining ?>, timerEl, function() {
          document.getElementById('timeoutForm').submit();
        });
      } else {
        // Fallback lightweight countdown if global isn't available
        let remaining = <?= $remaining ?>;
        const render = function() {
          const m = String(Math.floor(remaining / 60)).padStart(2, '0');
          const s = String(remaining % 60).padStart(2, '0');
          if (timerEl) timerEl.textContent = m + ':' + s;
        };
        render();
        if (remaining <= 0) {
          document.getElementById('timeoutForm').submit();
          return;
        }
        const intId = setInterval(function() {
          remaining -= 1;
          render();
          if (remaining <= 0) {
            clearInterval(intId);
            document.getElementById('timeoutForm').submit();
          }
        }, 1000);
      }
    };
    // Use requestAnimationFrame to ensure paint, then start
    if ('requestAnimationFrame' in window) {
      requestAnimationFrame(start);
    } else {
      start();
    }
  });
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
