<div class="modal fade" id="resetDelaysModal" tabindex="-1" aria-labelledby="resetDelaysModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="resetDelaysModalLabel">Confirm Reset Delays</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to reset the number of delays for this user? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="post" action="">
                    <input type="hidden" name="resetDelays" value="<?= isset($userInfo) ? $userInfo['id'] : '' ?>">
                    <button type="submit" class="btn btn-danger">Confirm Reset</button>
                </form>
            </div>
        </div>
    </div>
</div>