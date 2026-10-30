<?php /** includes/user-action-modal.php - included by the users list and view pages. */ ?>
<div class="df-modal" id="userActionModal" role="dialog" aria-modal="true" aria-labelledby="uamTitle"
     data-default-password="<?= e(DEFAULT_PASSWORD) ?>">
    <div class="df-modal-dialog">
        <form method="post" action="<?= e(url('admin/users/actions.php')) ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="user_id" id="uamUserId" value="">
            <input type="hidden" name="action"  id="uamAction" value="">
            <input type="hidden" name="return"  id="uamReturn" value="<?= e($modalReturn ?? 'list') ?>">

            <div class="df-modal-icon" id="uamIcon"><i class="bi bi-question-lg"></i></div>
            <h3 id="uamTitle">Are you sure?</h3>
            <p class="df-muted" id="uamText"></p>

            <div class="df-modal-actions">
                <button type="button" class="df-btn df-btn-ghost" data-modal-close>Cancel</button>
                <button type="submit" class="df-btn df-btn-primary" id="uamConfirm">Confirm</button>
            </div>
        </form>
    </div>
</div>