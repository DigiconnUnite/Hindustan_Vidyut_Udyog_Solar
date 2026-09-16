<?php
$flashSuccess = flash('success');
$flashError = flash('error');
?>
<?php if ($flashSuccess): ?>
  <!-- Custom overlay wrapper with custom CSS classes -->
  <div
    id="admin-success-popup"
    class="custom-modal-overlay active"
    role="alertdialog"
    aria-modal="true"
    aria-labelledby="admin-success-title">
    <div class="custom-modal-card text-center">
      <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-primary-50 text-xl text-primary-600">
        <i class="fa-solid fa-check" aria-hidden="true"></i>
      </div>
      <h2 id="admin-success-title" class="mt-4 text-lg font-semibold text-gray-900">Done</h2>
      <p class="mt-2 text-sm text-gray-600"><?= e($flashSuccess) ?></p>
      <button type="button" data-popup-close class="btn-primary mt-6 w-full flex flex-1 items-center justify-center">Okay</button>
    </div>
  </div>
<?php endif; ?>

<?php if ($flashError): ?>
  <div class="rounded-lg bg-red-50 border border-red-300 text-red-700 px-4 py-3 text-sm mb-4">
    <?= e($flashError) ?>
  </div>
<?php endif; ?>

<!-- Shared confirmation dialog for destructive admin actions. -->
<div
  id="admin-confirm-popup"
  class="custom-modal-overlay hidden"
  role="dialog"
  aria-modal="true"
  aria-labelledby="admin-confirm-title">
  <div class="custom-modal-card">
    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-red-50 text-xl text-red-600">
      <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
    </div>
    <h2 id="admin-confirm-title" class="mt-4 text-lg font-semibold text-gray-900">Confirm action</h2>
    <p id="admin-confirm-message" class="mt-2 text-sm text-gray-600">Are you sure you want to continue?</p>
    <div class="mt-6 flex gap-3">
      <button type="button" data-confirm-cancel class="btn-outline flex flex-1 items-center justify-center">Cancel</button>
      <button type="button" data-confirm-continue class="flex flex-1 items-center justify-center rounded-lg bg-red-600 px-4 py-2 text-sm font-medium btn-primary transition hover:bg-red-700">Yes, continue</button>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const successPopup = document.getElementById('admin-success-popup');
    const confirmPopup = document.getElementById('admin-confirm-popup');
    const confirmMessage = document.getElementById('admin-confirm-message');
    const destructiveActions = {
      delete: 'remove this item',
      deactivate: 'deactivate this team member',
      logout: 'sign out of your account'
    };
    let confirmedForm = null;
    let isSubmittingConfirmedForm = false;

    if (successPopup) {
      successPopup.querySelector('[data-popup-close]').addEventListener('click', function() {
        successPopup.remove();
      });
    }

    document.addEventListener('submit', function(event) {
      const form = event.target;

      // Allow exactly the programmatic submit that follows a positive confirmation.
      if (form.dataset.confirmedSubmission === 'true') {
        delete form.dataset.confirmedSubmission;
        return;
      }

      const actionInput = form.querySelector('input[name="action"]');
      const action = actionInput ? actionInput.value : '';
      const description = destructiveActions[action];

      if (!description) {
        return;
      }

      event.preventDefault();
      confirmedForm = form;
      confirmMessage.textContent = 'Are you sure you want to ' + description + '? This action cannot be undone.';
      confirmPopup.classList.remove('hidden');
      confirmPopup.classList.add('active');
    });

    function closeConfirmPopup() {
      confirmedForm = null;
      isSubmittingConfirmedForm = false;
      confirmPopup.classList.remove('active');
      confirmPopup.classList.add('hidden');
    }

    confirmPopup.querySelector('[data-confirm-cancel]').addEventListener('click', closeConfirmPopup);
    confirmPopup.querySelector('[data-confirm-continue]').addEventListener('click', function() {
      if (!confirmedForm || isSubmittingConfirmedForm) return;

      const form = confirmedForm;
      isSubmittingConfirmedForm = true;
      confirmPopup.classList.remove('active');
      confirmPopup.classList.add('hidden');
      form.dataset.confirmedSubmission = 'true';

      if (typeof form.requestSubmit === 'function') {
        form.requestSubmit();
      } else {
        form.submit();
      }
    });
  });
</script>
