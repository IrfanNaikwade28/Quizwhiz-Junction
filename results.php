<?php
require_once __DIR__ . '/includes/header.php';
require_login();
require_once __DIR__ . '/includes/helpers.php';
$attemptId = (int)($_GET['attempt_id'] ?? 0);
$stmt = db()->prepare('SELECT a.*, q.title FROM attempts a JOIN quizzes q ON a.quiz_id=q.id WHERE a.id=? AND a.user_id=?');
$stmt->execute([$attemptId, current_user()['id']]);
$attempt = $stmt->fetch();
if (!$attempt) { echo '<div class="text-white/70">Attempt not found.</div>'; require_once __DIR__ . '/includes/footer.php'; exit; }
$ans = db()->prepare('SELECT aa.*, qu.text AS question_text, o.text AS option_text FROM attempt_answers aa JOIN questions qu ON aa.question_id=qu.id LEFT JOIN options o ON aa.option_id=o.id WHERE aa.attempt_id=? ORDER BY aa.id ASC');
$ans->execute([$attemptId]);
$answers = $ans->fetchAll();
?>
<div class="max-w-3xl mx-auto space-y-6">
  <div class="rounded-2xl p-6 bg-white/5 border border-white/10 shadow-soft">
    <h1 class="text-2xl font-bold mb-2">Results: <?= h($attempt['title']) ?></h1>
    <div class="text-white/80">Score: <span class="font-semibold"><?= (int)$attempt['score'] ?></span> • Total Time: <span class="font-semibold"><?= format_time((int)$attempt['total_time']) ?></span></div>
  </div>
  <div class="rounded-2xl p-6 bg-white/5 border border-white/10 shadow-soft">
    <h2 class="text-xl font-semibold mb-4">Breakdown</h2>
    <ul class="space-y-3">
      <?php foreach($answers as $i=>$a): ?>
        <li class="bg-white/5 rounded-xl px-4 py-3 flex items-center justify-between">
          <div>
            <div class="font-medium">Q<?= $i+1 ?>: <?= h($a['question_text']) ?></div>
            <div class="text-xs text-white/60">Your answer: <?= h($a['option_text'] ?? '—') ?></div>
          </div>
          <div class="text-sm">
            <span class="<?= $a['is_correct'] ? 'text-green-300' : 'text-red-300' ?> font-semibold"><?= $a['is_correct'] ? 'Correct' : 'Wrong' ?></span>
            <span class="text-white/60 ml-3"><?= format_time((int)$a['time_spent']) ?></span>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
  <div class="flex gap-4">
    <a href="<?= url('quizzes.php') ?>" class="px-6 py-3 rounded-xl bg-violet hover:bg-plum font-semibold">Play Again</a>
    <a href="<?= url('dashboard.php') ?>" class="px-6 py-3 rounded-xl bg-white/10 hover:bg-white/20 font-semibold">Back to Dashboard</a>
  </div>
  
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
