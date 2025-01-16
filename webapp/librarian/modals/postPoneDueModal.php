<div class="modal fade" id="postponeDueModal" tabindex="-1" aria-labelledby="postponeDueModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="postponeDueModalLabel">Postpone Due Date</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="postponeDays" class="form-label">Number of days to postpone (1-30):</label>
                        <input type="number" class="form-control" name="postponeDays" id="postponeDays" min="1" max="30"
                               required>
                    </div>
                    <input type="hidden" name="postponeDue" id="postponeDueInput">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Postpone</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const postponeDueModal = document.getElementById('postponeDueModal');
        postponeDueModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const loanId = button.getAttribute('data-loan-id');
            const input = document.getElementById('postponeDueInput');
            input.value = loanId;
        });
    });
</script>