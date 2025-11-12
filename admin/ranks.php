<?php
require_once __DIR__ . '/../includes/header.php';
require_admin();
require_once __DIR__ . '/../includes/helpers.php';

$rows = db()->query("SELECT u.id, u.name, u.email, COALESCE(SUM(a.score),0) AS total_score, COALESCE(SUM(a.total_time),0) AS total_time FROM users u LEFT JOIN attempts a ON u.id=a.user_id WHERE u.is_admin=0 GROUP BY u.id")->fetchAll();
foreach ($rows as &$r) { $r['rank_score'] = $r['total_score']; $r['rank_time'] = $r['total_time']; }
usort($rows, function($a,$b){ if ($a['rank_score'] === $b['rank_score']) return $a['rank_time'] <=> $b['rank_time']; return $b['rank_score'] <=> $a['rank_score']; });
?>
<div class="space-y-6">
  <h1 class="text-2xl font-bold">Ranking Overview</h1>
  <div class="rounded-2xl p-6 bg-white/5 border border-white/10 shadow-soft overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="text-white/70">
        <tr><th class="text-left py-2">Rank</th><th class="text-left py-2">Name</th><th class="text-left py-2">Email</th><th class="text-left py-2">Total Score</th><th class="text-left py-2">Total Time</th><th class="text-left py-2">Actions</th></tr>
      </thead>
      <tbody>
        <?php $i=1; foreach($rows as $r): ?>
          <tr class="border-t border-white/10">
            <td class="py-2 pr-4">#<?= $i++ ?></td>
            <td class="py-2 pr-4 font-medium"><?= h($r['name']) ?></td>
            <td class="py-2 pr-4 text-white/70"><?= h($r['email']) ?></td>
            <td class="py-2 pr-4"><?= (int)$r['total_score'] ?></td>
            <td class="py-2 pr-4"><?= format_time((int)$r['total_time']) ?></td>
            <td class="py-2 pr-4">
              <a href="<?= url('admin/user_attempts.php?user_id='.(int)$r['id']) ?>" class="text-plum">View Attempts</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
