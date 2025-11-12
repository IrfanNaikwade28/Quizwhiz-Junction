<?php
require_once __DIR__ . '/includes/header.php';
require_login();
require_once __DIR__ . '/includes/helpers.php';
$me = current_user();
if (is_admin()) {
  echo '<div class="text-white/70">Admins do not have quiz history.</div>';
  require_once __DIR__ . '/includes/footer.php';
  exit;
}
$stmt = db()->prepare('SELECT a.*, q.title, q.points_per_question, q.question_time FROM attempts a JOIN quizzes q ON a.quiz_id=q.id WHERE a.user_id=? ORDER BY a.id DESC');
$stmt->execute([$me['id']]);
$attempts = $stmt->fetchAll();
?>
<div class="space-y-6">
  <h1 class="text-2xl font-bold">History</h1>
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <?php foreach($attempts as $a): ?>
  <?php $ppq = isset($a['points_per_question']) ? (int)$a['points_per_question'] : 10; $qtime = isset($a['question_time']) && (int)$a['question_time']>0 ? (int)$a['question_time'] : 30; $pointsGain = max(0, ((int)$a['score']) * $ppq - (int)floor(((int)$a['total_time'])/$qtime)); ?>
      <div class="rounded-2xl p-6 bg-white/5 border border-white/10 shadow-soft flex items-center justify-between">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 rounded-2xl bg-gradient-purple flex items-center justify-center text-lg font-bold">Q</div>
          <div>
            <div class="font-semibold text-lg"><?= h($a['title']) ?></div>
            <div class="text-sm text-white/60">Score <?= (int)$a['score'] ?> • <?= format_time((int)$a['total_time']) ?></div>
          </div>
        </div>
        <div class="text-right">
          <div class="text-sm">Points</div>
          <div class="text-xl font-bold <?= $pointsGain>0?'text-green-300':'text-white' ?>">+<?= $pointsGain ?></div>
        </div>
      </div>
    <?php endforeach; ?>
    <?php if(empty($attempts)): ?>
      <div class="text-white/70">No attempts yet.</div>
    <?php endif; ?>
  </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
