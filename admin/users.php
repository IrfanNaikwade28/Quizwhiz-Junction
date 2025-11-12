<?php
require_once __DIR__ . '/../includes/header.php';
require_admin();
require_once __DIR__ . '/../includes/helpers.php';

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf($_POST['csrf'] ?? '')) {
  $action = $_POST['action'] ?? '';
  if ($action === 'toggle_admin') {
    $id = (int)$_POST['id'];
    db()->prepare('UPDATE users SET is_admin = 1 - is_admin WHERE id=?')->execute([$id]);
    $msg = 'Role toggled.';
  }
}

$rows = db()->query('SELECT id, name, email, points, is_admin FROM users ORDER BY id DESC')->fetchAll();
?>
<div class="space-y-6">
  <h1 class="text-2xl font-bold">Users</h1>
  <?php if($msg): ?><div class="text-green-300 text-sm"><?= h($msg) ?></div><?php endif; ?>
  <div class="rounded-2xl p-6 bg-white/5 border border-white/10 shadow-soft overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="text-white/70">
  <tr><th class="text-left py-2">#</th><th class="text-left py-2">Name</th><th class="text-left py-2">Email</th><th class="text-left py-2">Points</th><th class="text-left py-2">Admin</th><th class="text-left py-2">Actions</th></tr>
      </thead>
      <tbody>
        <?php $i=1; foreach($rows as $r): ?>
          <tr class="border-t border-white/10">
            <td class="py-2 pr-4"><?= $i++ ?></td>
            <td class="py-2 pr-4 font-medium"><?= h($r['name']) ?></td>
            <td class="py-2 pr-4 text-white/70"><?= h($r['email']) ?></td>
            <td class="py-2 pr-4"><?= (int)$r['points'] ?></td>
            <td class="py-2 pr-4"><?= $r['is_admin'] ? 'Yes' : 'No' ?></td>
            <td class="py-2 pr-4">
              <form method="post" class="inline" onsubmit="return confirm('Toggle admin role?')">
                <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>" />
                <input type="hidden" name="action" value="toggle_admin" />
                <input type="hidden" name="id" value="<?= (int)$r['id'] ?>" />
                <button class="text-plum">Toggle Admin</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
