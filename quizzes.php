<?php
require_once __DIR__ . '/includes/header.php';
require_login();
require_once __DIR__ . '/includes/helpers.php';
$rows = db()->query('SELECT q.*, (SELECT COUNT(*) FROM questions WHERE quiz_id=q.id) AS question_count FROM quizzes q WHERE q.is_active=1 ORDER BY q.id DESC')->fetchAll();
$me = current_user();
?>
<div class="flex items-center justify-between mb-6">
  <h1 class="text-2xl font-bold">Quizzes</h1>
</div>
<div class="grid lg:grid-cols-3 md:grid-cols-2 grid-cols-1 gap-6">
  <?php foreach($rows as $q): ?>
    <div class="rounded-2xl p-6 bg-white/5 border border-white/10 shadow-soft flex flex-col justify-between">
      <div>
        <div class="flex items-center justify-between mb-2">
          <h2 class="text-lg font-semibold"><?= h($q['title']) ?></h2>
          <span class="text-xs px-2 py-1 rounded-full bg-plum/30">
            <?= (int)$q['question_count'] ?> Qs
          </span>
        </div>
        <p class="text-sm text-white/70 mb-2"><?= h($q['description']) ?></p>
        <div class="flex flex-wrap gap-2 text-xs mb-4">
          <?php if($q['category']): ?><span class="px-2 py-1 rounded bg-plum/30"><?= h($q['category']) ?></span><?php endif; ?>
          <span class="px-2 py-1 rounded bg-violet/30"><?= h(ucfirst($q['difficulty'])) ?></span>
          <span class="px-2 py-1 rounded bg-white/10">Pts/Q: <?= (int)$q['points_per_question'] ?></span>
          <span class="px-2 py-1 rounded bg-white/10">Sec/Q: <?= (int)$q['question_time'] ?></span>
        </div>
      </div>
      <?php if (can_attempt_quiz($me)): ?>
        <form method="post" action="quiz.php" class="mt-4">
          <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>" />
          <input type="hidden" name="action" value="start" />
          <input type="hidden" name="quiz_id" value="<?= (int)$q['id'] ?>" />
          <button class="w-full py-3 rounded-xl bg-violet hover:bg-plum font-semibold">Start Quiz</button>
        </form>
      <?php else: ?>
        <div class="mt-4 text-xs text-white/50 italic">Admins cannot attempt quizzes.</div>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
  <?php if(empty($rows)): ?>
    <div class="text-white/70">No quizzes yet.</div>
  <?php endif; ?>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
