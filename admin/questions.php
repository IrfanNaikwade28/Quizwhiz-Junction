<?php
require_once __DIR__ . '/../includes/header.php';
require_admin();
require_once __DIR__ . '/../includes/helpers.php';

$quizId = (int)($_GET['quiz_id'] ?? 0);
$quiz = null;
if ($quizId) {
  $st = db()->prepare('SELECT * FROM quizzes WHERE id=?');
  $st->execute([$quizId]);
  $quiz = $st->fetch();
}

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf($_POST['csrf'] ?? '')) {
    $action = $_POST['action'] ?? '';
    if ($action === 'create_question') {
        $text = trim($_POST['text'] ?? '');
        db()->prepare('INSERT INTO questions (quiz_id, text) VALUES (?, ?)')->execute([$quizId, $text]);
        $msg = 'Question created.';
    } elseif ($action === 'delete_question') {
        $qid = (int)$_POST['id'];
        db()->prepare('DELETE FROM questions WHERE id=?')->execute([$qid]);
        $msg = 'Question deleted.';
    } elseif ($action === 'add_option') {
        $qid = (int)$_POST['question_id'];
        $text = trim($_POST['text'] ?? '');
        $isCorrect = isset($_POST['is_correct']) ? 1 : 0;
        db()->prepare('INSERT INTO options (question_id, text, is_correct) VALUES (?, ?, ?)')->execute([$qid, $text, $isCorrect]);
        $msg = 'Option added.';
    } elseif ($action === 'delete_option') {
        $oid = (int)$_POST['id'];
        db()->prepare('DELETE FROM options WHERE id=?')->execute([$oid]);
        $msg = 'Option deleted.';
    }
}

$questions = db()->prepare('SELECT * FROM questions WHERE quiz_id=? ORDER BY id ASC');
$questions->execute([$quizId]);
$qRows = $questions->fetchAll();
?>
<div class="space-y-6">
  <div class="flex items-center justify-between">
    <h1 class="text-2xl font-bold">Manage Questions <?= $quiz ? 'for "' . h($quiz['title']) . '"' : '' ?></h1>
    <a href="<?= url('admin/quizzes.php') ?>" class="text-sm text-plum">Back to Quizzes</a>
  </div>
  <?php if($msg): ?><div class="text-green-300 text-sm"><?= h($msg) ?></div><?php endif; ?>
  <div class="rounded-2xl p-6 bg-white/5 border border-white/10 shadow-soft">
    <h2 class="text-lg font-semibold mb-4">Create Question</h2>
    <form method="post" class="grid grid-cols-1 md:grid-cols-6 gap-3">
      <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>" />
      <input type="hidden" name="action" value="create_question" />
      <input name="text" placeholder="Question text" class="px-4 py-3 rounded-xl bg-white/10 border border-white/10 md:col-span-5" required />
      <button class="px-6 py-3 rounded-xl bg-violet hover:bg-plum font-semibold">Add</button>
    </form>
  </div>
  <div class="space-y-6">
    <?php $qIndex=1; foreach($qRows as $qr): ?>
      <?php $ops = db()->prepare('SELECT * FROM options WHERE question_id=? ORDER BY id ASC'); $ops->execute([$qr['id']]); $oRows = $ops->fetchAll(); ?>
      <div class="rounded-2xl p-6 bg-white/5 border border-white/10 shadow-soft">
        <div class="flex items-center justify-between mb-3">
          <div class="font-semibold">Q<?= $qIndex++ ?>. <?= h($qr['text']) ?></div>
          <form method="post" onsubmit="return confirm('Delete question?')">
            <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>" />
            <input type="hidden" name="action" value="delete_question" />
            <input type="hidden" name="id" value="<?= (int)$qr['id'] ?>" />
            <button class="text-red-300 text-sm">Delete</button>
          </form>
        </div>
        <div class="space-y-2">
          <?php foreach($oRows as $op): ?>
            <div class="flex items-center justify-between bg-white/5 rounded-xl px-4 py-2">
              <div class="<?= $op['is_correct'] ? 'text-green-300' : '' ?>">• <?= h($op['text']) ?></div>
              <form method="post" onsubmit="return confirm('Delete option?')">
                <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>" />
                <input type="hidden" name="action" value="delete_option" />
                <input type="hidden" name="id" value="<?= (int)$op['id'] ?>" />
                <button class="text-red-300 text-xs">Remove</button>
              </form>
            </div>
          <?php endforeach; ?>
        </div>
        <form method="post" class="grid grid-cols-1 md:grid-cols-6 gap-3 mt-3">
          <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>" />
          <input type="hidden" name="action" value="add_option" />
          <input type="hidden" name="question_id" value="<?= (int)$qr['id'] ?>" />
          <input name="text" placeholder="Option text" class="px-4 py-3 rounded-xl bg-white/10 border border-white/10 md:col-span-4" required />
          <label class="flex items-center gap-2 text-sm md:col-span-1"><input type="checkbox" name="is_correct" class="rounded" /> Correct</label>
          <button class="px-6 py-3 rounded-xl bg-white/10 hover:bg-white/20 font-semibold">Add Option</button>
        </form>
      </div>
    <?php endforeach; ?>
    <?php if(empty($qRows)): ?><div class="text-white/70">No questions yet.</div><?php endif; ?>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
