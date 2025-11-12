<?php
require_once __DIR__ . '/../includes/header.php';
require_admin();
require_once __DIR__ . '/../includes/helpers.php';

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf($_POST['csrf'] ?? '')) {
  $action = $_POST['action'] ?? '';
  if ($action === 'create') {
    $title = trim($_POST['title'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $difficulty = $_POST['difficulty'] ?? 'medium';
    $ppq = (int)($_POST['points_per_question'] ?? 10);
    $qtime = (int)($_POST['question_time'] ?? 30);
    db()->prepare('INSERT INTO quizzes (title, description, category, difficulty, points_per_question, question_time, is_active, created_at) VALUES (?,?,?,?,?,?,1,NOW())')
      ->execute([$title,$desc,$category,$difficulty,$ppq,$qtime]);
    $msg = 'Quiz created.';
  } elseif ($action === 'delete') {
    $id = (int)$_POST['id'];
    db()->prepare('DELETE FROM quizzes WHERE id=?')->execute([$id]);
    $msg = 'Quiz deleted.';
  } elseif ($action === 'update') {
    $id = (int)$_POST['id'];
    $title = trim($_POST['title'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $difficulty = $_POST['difficulty'] ?? 'medium';
    $ppq = (int)($_POST['points_per_question'] ?? 10);
    $qtime = (int)($_POST['question_time'] ?? 30);
    $active = isset($_POST['is_active']) ? 1 : 0;
    db()->prepare('UPDATE quizzes SET title=?, description=?, category=?, difficulty=?, points_per_question=?, question_time=?, is_active=? WHERE id=?')
      ->execute([$title,$desc,$category,$difficulty,$ppq,$qtime,$active,$id]);
    $msg = 'Quiz updated.';
  }
}

$rows = db()->query('SELECT * FROM quizzes ORDER BY id DESC')->fetchAll();
?>
<div class="space-y-6">
  <div class="flex items-center justify-between">
    <h1 class="text-2xl font-bold">Manage Quizzes</h1>
    <?php if($msg): ?><div class="text-green-300 text-sm"><?= h($msg) ?></div><?php endif; ?>
  </div>
  <div class="rounded-2xl p-6 bg-white/5 border border-white/10 shadow-soft">
    <h2 class="text-lg font-semibold mb-4">Create Quiz</h2>
    <form method="post" class="grid grid-cols-1 md:grid-cols-7 gap-3">
      <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>" />
      <input type="hidden" name="action" value="create" />
      <input name="title" placeholder="Title" class="px-4 py-3 rounded-xl bg-white/10 border border-white/10 md:col-span-2" required />
      <input name="description" placeholder="Description" class="px-4 py-3 rounded-xl bg-white/10 border border-white/10 md:col-span-2" />
      <input name="category" placeholder="Category" class="px-4 py-3 rounded-xl bg-white/10 border border-white/10" />
      <select name="difficulty" class="px-4 py-3 rounded-xl bg-white/10 border border-white/10">
        <option value="easy">Easy</option>
        <option value="medium" selected>Medium</option>
        <option value="hard">Hard</option>
      </select>
      <div class="flex gap-2 md:col-span-2">
        <input name="points_per_question" type="number" min="1" value="10" class="w-1/2 px-4 py-3 rounded-xl bg-white/10 border border-white/10" placeholder="Pts/Q" />
        <input name="question_time" type="number" min="5" value="30" class="w-1/2 px-4 py-3 rounded-xl bg-white/10 border border-white/10" placeholder="Sec/Q" />
      </div>
      <div class="md:col-span-7"><button class="px-6 py-3 rounded-xl bg-violet hover:bg-plum font-semibold">Create</button></div>
    </form>
  </div>
  <div class="rounded-2xl p-6 bg-white/5 border border-white/10 shadow-soft overflow-x-auto">
    <table class="w-full text-xs">
      <thead class="text-white/70">
        <tr>
          <th class="text-left py-2">#</th>
          <th class="text-left py-2">Title</th>
          <th class="text-left py-2">Category</th>
          <th class="text-left py-2">Difficulty</th>
          <th class="text-left py-2">Pts/Q</th>
          <th class="text-left py-2">Sec/Q</th>
          <th class="text-left py-2">Active</th>
          <th class="text-left py-2">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php $i=1; foreach($rows as $r): ?>
          <tr class="border-t border-white/10">
            <td class="py-2 pr-3"><?= $i++ ?></td>
            <td class="py-2 pr-3 font-medium">
              <form method="post" class="grid grid-cols-1 md:grid-cols-8 gap-2 items-center">
                <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>" />
                <input type="hidden" name="action" value="update" />
                <input type="hidden" name="id" value="<?= (int)$r['id'] ?>" />
                <input name="title" value="<?= h($r['title']) ?>" class="px-2 py-1 rounded bg-white/10 border border-white/10 md:col-span-2" />
                <input name="description" value="<?= h($r['description']) ?>" class="px-2 py-1 rounded bg-white/10 border border-white/10 md:col-span-2" />
                <input name="category" value="<?= h($r['category']) ?>" class="px-2 py-1 rounded bg-white/10 border border-white/10" />
                <select name="difficulty" class="px-2 py-1 rounded bg-white/10 border border-white/10">
                  <option value="easy" <?= $r['difficulty']==='easy'?'selected':'' ?>>Easy</option>
                  <option value="medium" <?= $r['difficulty']==='medium'?'selected':'' ?>>Medium</option>
                  <option value="hard" <?= $r['difficulty']==='hard'?'selected':'' ?>>Hard</option>
                </select>
                <input name="points_per_question" type="number" min="1" value="<?= (int)$r['points_per_question'] ?>" class="px-2 py-1 rounded bg-white/10 border border-white/10" />
                <input name="question_time" type="number" min="5" value="<?= (int)$r['question_time'] ?>" class="px-2 py-1 rounded bg-white/10 border border-white/10" />
                <label class="flex items-center gap-1 text-xs">
                  <input type="checkbox" name="is_active" value="1" <?= $r['is_active']? 'checked':'' ?> /> Active
                </label>
                <div class="flex gap-3 md:col-span-8 py-1">
                  <button class="text-plum">Save</button>
                  <a class="text-violet" href="<?= url('admin/questions.php?quiz_id='.(int)$r['id']) ?>">Questions</a>
                  <form method="post" class="inline" onsubmit="return confirm('Delete quiz? This removes questions and options.')">
                    <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>" />
                    <input type="hidden" name="action" value="delete" />
                    <input type="hidden" name="id" value="<?= (int)$r['id'] ?>" />
                    <button class="text-red-300">Delete</button>
                  </form>
                </div>
              </form>
            </td>
            <td class="py-2 pr-3"><?= h($r['category']) ?></td>
            <td class="py-2 pr-3"><?= h($r['difficulty']) ?></td>
            <td class="py-2 pr-3"><?= (int)$r['points_per_question'] ?></td>
            <td class="py-2 pr-3"><?= (int)$r['question_time'] ?></td>
            <td class="py-2 pr-3"><?= $r['is_active'] ? 'Yes':'No' ?></td>
            <td class="py-2 pr-3 text-white/60 text-xs">Inline edit above</td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
