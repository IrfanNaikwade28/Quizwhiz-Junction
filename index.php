<?php require_once __DIR__ . '/includes/header.php'; ?>
<section class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
  <div class="space-y-6">
    <h1 class="text-4xl font-extrabold leading-tight">Master Quizzes. Climb Ranks. Enjoy the Purple Realm.</h1>
    <p class="text-white/80 text-lg max-w-prose">Challenge yourself with timed quizzes, earn points, unlock badges, and rise through the global leaderboard. Built for desktop power users—fluid, responsive, and vibrant.</p>
    <div class="flex gap-4 pt-4">
      <?php if(!current_user()): ?>
        <a href="<?= url('register.php') ?>" class="px-6 py-3 rounded-xl bg-violet hover:bg-plum font-semibold shadow-soft">Get Started</a>
        <a href="<?= url('login.php') ?>" class="px-6 py-3 rounded-xl bg-white/20 hover:bg-white/30 font-semibold">Login</a>
      <?php else: ?>
        <a href="<?= url('dashboard.php') ?>" class="px-6 py-3 rounded-xl bg-violet hover:bg-plum font-semibold shadow-soft">Go to Dashboard</a>
      <?php endif; ?>
    </div>
  </div>
  <div class="relative">
    <div class="rounded-2xl p-10 bg-card-glow backdrop-blur border border-white/10 shadow-soft">
      <h2 class="text-xl font-semibold mb-4">Live Rankings Snapshot</h2>
      <?php
  // Exclude admins from public snapshot
  $rows = db()->query("SELECT u.name, COALESCE(SUM(a.score),0) AS total_score, COALESCE(SUM(a.total_time),0) AS total_time FROM users u LEFT JOIN attempts a ON u.id=a.user_id WHERE u.is_admin=0 GROUP BY u.id ORDER BY total_score DESC, total_time ASC LIMIT 5")->fetchAll();
      ?>
      <ul class="space-y-3">
        <?php foreach($rows as $i=>$r): ?>
          <li class="flex items-center justify-between bg-white/5 rounded-xl px-4 py-3">
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 rounded-full bg-gradient-purple flex items-center justify-center text-sm font-bold"><?= $i+1 ?></div>
              <span class="font-medium"><?= h($r['name']) ?></span>
            </div>
            <div class="text-sm text-white/70">Score: <?= (int)$r['total_score'] ?> • Time: <?= (int)$r['total_time'] ?>s</div>
          </li>
        <?php endforeach; ?>
        <?php if(empty($rows)): ?>
          <li class="text-white/60">No rankings yet. Be the first!</li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
