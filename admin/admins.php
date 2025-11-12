<?php
require_once __DIR__ . '/../includes/header.php';
require_admin();
require_once __DIR__ . '/../includes/helpers.php';

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf($_POST['csrf'] ?? '')) {
    $action = $_POST['action'] ?? '';
    $uid = (int)($_POST['user_id'] ?? 0);
    if ($action === 'toggle_status') {
        $row = db()->prepare('SELECT status FROM admins WHERE user_id=?');
        $row->execute([$uid]);
        $status = $row->fetchColumn();
        if ($status) {
            $new = $status === 'active' ? 'disabled' : 'active';
            db()->prepare('UPDATE admins SET status=?, updated_at=NOW() WHERE user_id=?')->execute([$new, $uid]);
            $msg = 'Status updated.';
        }
    } elseif ($action === 'promote_super') {
        db()->prepare('UPDATE admins SET super_admin=1, updated_at=NOW() WHERE user_id=?')->execute([$uid]);
        $msg = 'Super admin set.';
    } elseif ($action === 'demote_super') {
        db()->prepare('UPDATE admins SET super_admin=0, updated_at=NOW() WHERE user_id=?')->execute([$uid]);
        $msg = 'Super admin removed.';
    }
}

$rows = db()->query('SELECT a.user_id, a.super_admin, a.status, a.created_at, a.updated_at, u.email, u.name FROM admins a JOIN users u ON a.user_id=u.id ORDER BY a.super_admin DESC, a.created_at ASC')->fetchAll();
?>
<div class="space-y-6">
  <div class="flex items-center justify-between">
    <h1 class="text-2xl font-bold">Admin Accounts</h1>
    <?php if($msg): ?><div class="text-green-300 text-sm"><?= h($msg) ?></div><?php endif; ?>
  </div>
  <div class="rounded-2xl p-6 bg-white/5 border border-white/10 shadow-soft overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="text-white/70">
        <tr>
          <th class="text-left py-2">User</th>
          <th class="text-left py-2">Email</th>
          <th class="text-left py-2">Super</th>
          <th class="text-left py-2">Status</th>
          <th class="text-left py-2">Created</th>
          <th class="text-left py-2">Updated</th>
          <th class="text-left py-2">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($rows as $r): ?>
          <tr class="border-t border-white/10">
            <td class="py-2 pr-4 font-medium"><?= h($r['name']) ?></td>
            <td class="py-2 pr-4 text-white/70"><?= h($r['email']) ?></td>
            <td class="py-2 pr-4"><?= $r['super_admin'] ? 'Yes' : 'No' ?></td>
            <td class="py-2 pr-4 <?= $r['status']==='active' ? 'text-green-300':'text-red-300' ?>"><?= h($r['status']) ?></td>
            <td class="py-2 pr-4 text-white/60"><?= h(substr($r['created_at'],0,16)) ?></td>
            <td class="py-2 pr-4 text-white/60"><?= $r['updated_at'] ? h(substr($r['updated_at'],0,16)) : '—' ?></td>
            <td class="py-2 pr-4">
              <form method="post" class="inline">
                <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>" />
                <input type="hidden" name="user_id" value="<?= (int)$r['user_id'] ?>" />
                <input type="hidden" name="action" value="toggle_status" />
                <button class="text-plum mr-3" onclick="return confirm('Toggle status?')">Toggle Status</button>
              </form>
              <?php if(!$r['super_admin']): ?>
                <form method="post" class="inline">
                  <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>" />
                  <input type="hidden" name="user_id" value="<?= (int)$r['user_id'] ?>" />
                  <input type="hidden" name="action" value="promote_super" />
                  <button class="text-violet mr-3" onclick="return confirm('Promote to super admin?')">Promote</button>
                </form>
              <?php else: ?>
                <form method="post" class="inline">
                  <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>" />
                  <input type="hidden" name="user_id" value="<?= (int)$r['user_id'] ?>" />
                  <input type="hidden" name="action" value="demote_super" />
                  <button class="text-red-300" onclick="return confirm('Demote super admin?')">Demote</button>
                </form>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if(empty($rows)): ?>
          <tr><td colspan="7" class="py-4 text-center text-white/60">No admin entries found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
