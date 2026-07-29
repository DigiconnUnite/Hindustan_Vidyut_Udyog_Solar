<?php
$flashSuccess = flash('success');
$flashError = flash('error');
?>
<?php if ($flashSuccess): ?>
  <div class="rounded-lg bg-primary-50 border border-primary-500/30 text-primary-700 px-4 py-3 text-sm mb-4">
    <?= e($flashSuccess) ?>
  </div>
<?php endif; ?>
<?php if ($flashError): ?>
  <div class="rounded-lg bg-red-50 border border-red-300 text-red-700 px-4 py-3 text-sm mb-4">
    <?= e($flashError) ?>
  </div>
<?php endif; ?>
