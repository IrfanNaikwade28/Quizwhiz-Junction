<?php
require_once __DIR__ . '/../includes/header.php';
require_admin();
require_once __DIR__ . '/../includes/helpers.php';
$userId = (int)($_GET['user_id'] ?? 0);
$uStmt = db()->prepare('SELECT id, name, email FROM users WHERE id=? AND is_admin=0');
$uStmt->execute([$userId]);
$user = $uStmt->fetch();
if(!$user){ echo '<div class="text-white/70">User not found or is admin.</div>'; require_once __DIR__ . '/../includes/footer.php'; exit; }
$msg='';
if($_SERVER['REQUEST_METHOD']==='POST' && verify_csrf($_POST['csrf'] ?? '')){
  $action = $_POST['action'] ?? '';
  if($action==='delete_attempt'){
    $aid = (int)$_POST['attempt_id'];
    db()->prepare('DELETE FROM attempts WHERE id=? AND user_id=?')->execute([$aid,$userId]);
    $msg='Attempt removed.';
  }
}
$rows = db()->prepare('SELECT a.id, a.score, a.total_time, a.created_at, q.title FROM attempts a JOIN quizzes q ON a.quiz_id=q.id WHERE a.user_id=? ORDER BY a.id DESC');
$rows->execute([$userId]);
$attempts = $rows->fetchAll();
?>
<div class="space-y-6">
  <h1 class="text-2xl font-bold">Attempts for <?= h($user['name']) ?></h1>
  <?php if($msg): ?><div class="text-green-300 text-sm"><?= h($msg) ?></div><?php endif; ?>
  <div class="rounded-2xl p-6 bg-white/5 border border-white/10 shadow-soft overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="text-white/70"><tr><th class="text-left py-2">ID</th><th class="text-left py-2">Quiz</th><th class="text-left py-2">Score</th><th class="text-left py-2">Time</th><th class="text-left py-2">Created</th><th class="text-left py-2">Actions</th></tr></thead>
      <tbody>
        <?php foreach($attempts as $a): ?>
          <tr class="border-t border-white/10">
            <td class="py-2 pr-4"><?= (int)$a['id'] ?></td>
            <td class="py-2 pr-4 font-medium"><?= h($a['title']) ?></td>
            <td class="py-2 pr-4"><?= (int)$a['score'] ?></td>
            <td class="py-2 pr-4"><?= format_time((int)$a['total_time']) ?></td>
            <td class="py-2 pr-4 text-white/60"><?= h(substr($a['created_at'],0,16)) ?></td>
            <td class="py-2 pr-4">
              <form method="post" class="inline" onsubmit="return confirm('Remove this attempt?')">
                <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>" />
                <input type="hidden" name="action" value="delete_attempt" />
                <input type="hidden" name="attempt_id" value="<?= (int)$a['id'] ?>" />
                <button class="text-red-300">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if(empty($attempts)): ?><tr><td colspan="6" class="py-4 text-white/50">No attempts.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>