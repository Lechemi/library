<div class="modal fade" id="returnCopyModal" tabindex="-1" aria-labelledby="returnCopyModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="returnCopyModalLabel">Confirm Return Copy</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to mark this copy as returned?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="post" action="">
                    <input type="hidden" name="returnCopy" id="returnCopyInput">
                    <button type="submit" class="btn btn-success">Confirm Return</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const returnCopyModal = document.getElementById('returnCopyModal');
        returnCopyModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget; // Button that triggered the modal
            const loanId = button.getAttribute('data-loan-id'); // Extract loan ID
            const input = document.getElementById('returnCopyInput'); // Hidden input field
            input.value = loanId; // Set the value to the loan ID
        });
    });
</script>