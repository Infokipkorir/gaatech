<!-- Settings Modal -->
<div class="modal fade" id="settingsModal" tabindex="-1" aria-labelledby="settingsModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="settingsForm" class="modal-content" method="POST">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="settingsModalLabel"><i class="fas fa-user-cog me-2"></i>Account Settings</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <?php if (isset($settingsError)): ?>
          <div class="alert alert-danger"><?= $settingsError ?></div>
        <?php elseif (!empty($updateSuccess)): ?>
          <div class="alert alert-success">✅ Account updated successfully.</div>
        <?php endif; ?>

        <div class="mb-3">
          <label for="name" class="form-label">Full Name</label>
          <input type="text" name="name" id="name" class="form-control" value="<?= htmlspecialchars($name) ?>" required>
        </div>

        <div class="mb-3">
          <label for="email" class="form-label">Email Address</label>
          <input type="email" name="email" id="email" class="form-control" value="<?= htmlspecialchars($email) ?>" required>
        </div>

        <hr class="my-3">
        <h6>Change Password</h6>
        <div class="mb-3">
          <input type="password" name="new_password" class="form-control" placeholder="New Password (optional)">
        </div>
        <div class="mb-3">
          <input type="password" name="confirm_password" class="form-control" placeholder="Confirm New Password">
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save Changes</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times"></i> Cancel</button>
      </div>
    </form>
  </div>
</div>
