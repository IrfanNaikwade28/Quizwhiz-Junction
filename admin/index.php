<?php
require_once __DIR__ . '/../includes/header.php';
require_admin();
?>
<div class="space-y-8">
  <h1 class="text-2xl font-bold">Admin Dashboard</h1>
  <div class="grid lg:grid-cols-3 md:grid-cols-2 grid-cols-1 gap-6">
    <a href="<?= url('admin/quizzes.php') ?>" class="rounded-2xl p-6 bg-white/5 border border-white/10 shadow-soft hover:bg-white/10">
      <div class="text-lg font-semibold">Manage Quizzes</div>
      <div class="text-sm text-white/70">Create and update quizzes</div>
    </a>
    <a href="<?= url('admin/questions.php') ?>" class="rounded-2xl p-6 bg-white/5 border border-white/10 shadow-soft hover:bg-white/10">
      <div class="text-lg font-semibold">Manage Questions</div>
      <div class="text-sm text-white/70">Add, edit, delete questions</div>
    </a>
    <a href="<?= url('admin/users.php') ?>" class="rounded-2xl p-6 bg-white/5 border border-white/10 shadow-soft hover:bg-white/10">
      <div class="text-lg font-semibold">Users</div>
      <div class="text-sm text-white/70">Overview and roles</div>
    </a>
    <a href="<?= url('admin/ranks.php') ?>" class="rounded-2xl p-6 bg-white/5 border border-white/10 shadow-soft hover:bg-white/10">
      <div class="text-lg font-semibold">Ranking</div>
      <div class="text-sm text-white/70">Scores and timing</div>
    </a>
    <a href="<?= url('admin/admins.php') ?>" class="rounded-2xl p-6 bg-white/5 border border-white/10 shadow-soft hover:bg-white/10">
      <div class="text-lg font-semibold">Admin Accounts</div>
      <div class="text-sm text-white/70">Manage admin metadata</div>
    </a>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
