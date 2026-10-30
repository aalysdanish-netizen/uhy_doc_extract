<?php /** includes/confirm-modal.php - generic confirmation dialog, driven by assets/js/confirm.js */ ?>
<div class="df-modal" id="confirmModal" role="dialog" aria-modal="true" aria-labelledby="cmTitle">
    <div class="df-modal-dialog">
        <form method="post" action="">
            <?= csrf_field() ?>
            <span data-confirm-fields></span>

            <div class="df-modal-icon" id="cmIcon"><i class="bi bi-question-lg"></i></div>
            <h3 id="cmTitle">Are you sure?</h3>
            <p class="df-muted" id="cmText"></p>

            <div class="df-modal-actions">
                <button type="button" class="df-btn df-btn-ghost" data-modal-close>Cancel</button>
                <button type="submit" class="df-btn df-btn-primary" id="cmButton">Confirm</button>
            </div>
        </form>
    </div>
</div>