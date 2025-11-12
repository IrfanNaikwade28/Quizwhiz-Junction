<?php
require_once __DIR__ . '/../includes/header.php';
require_admin();
require_once __DIR__ . '/../includes/helpers.php';
$msg='';
if($_SERVER['REQUEST_METHOD']==='POST' && verify_csrf($_POST['csrf'] ?? '')){
  $action = $_POST['action'] ?? '';
  $id = (int)($_POST['id'] ?? 0);
  if($action==='toggle'){
    db()->prepare('UPDATE quizzes SET is_active = 1 - is_active WHERE id=?')->execute([$id]);
    $msg='Quiz activation toggled.';
  } elseif($action==='delete'){
    db()->prepare('DELETE FROM quizzes WHERE id=?')->execute([$id]);
    $msg='Quiz deleted.';
  }
}
$rows = db()->query('SELECT id,title,category,difficulty,is_active FROM quizzes ORDER BY id DESC')->fetchAll();
?>
<div class="space-y-6">
  <h1 class="text-2xl font-bold">Remove / Disable Quizzes</h1>
  <?php if($msg): ?><div class="text-green-300 text-sm"><?= h($msg) ?></div><?php endif; ?>
  <div class="rounded-2xl p-6 bg-white/5 border border-white/10 shadow-soft overflow-x-auto">
    <table class="w-full text-sm">
  <thead class="text-white/70"><tr><th class="text-left py-2">#</th><th class="text-left py-2">Title</th><th class="text-left py-2">Category</th><th class="text-left py-2">Difficulty</th><th class="text-left py-2">Active</th><th class="text-left py-2">Actions</th></tr></thead>
      <tbody>
        <?php $i=1; foreach($rows as $r): ?>
          <tr class="border-t border-white/10">
            <td class="py-2 pr-4"><?= $i++ ?></td>
            <td class="py-2 pr-4 font-medium"><?= h($r['title']) ?></td>
            <td class="py-2 pr-4"><?= h($r['category']) ?></td>
            <td class="py-2 pr-4"><?= h($r['difficulty']) ?></td>
            <td class="py-2 pr-4"><?= $r['is_active'] ? 'Yes':'No' ?></td>
            <td class="py-2 pr-4 flex gap-3">
              <form method="post" class="inline" onsubmit="return confirm('Toggle active state?')">
                <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>" />
                <input type="hidden" name="action" value="toggle" />
                <input type="hidden" name="id" value="<?= (int)$r['id'] ?>" />
                <button class="text-plum">Toggle</button>
              </form>
              <form method="post" class="inline" onsubmit="return confirm('Delete this quiz permanently?')">
                <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>" />
                <input type="hidden" name="action" value="delete" />
                <input type="hidden" name="id" value="<?= (int)$r['id'] ?>" />
                <button class="text-red-300">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if(empty($rows)): ?><tr><td colspan="6" class="py-4 text-white/50">No quizzes.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>