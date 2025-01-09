<div class="modal fade" id="changeCategoryModal" tabindex="-1" aria-labelledby="changeCategoryModalLabel"
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changeCategoryModalLabel">Change patron's category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="selectedCategory" class="form-label">Select the new category</label>
                        <select class="form-select" name="selectedCategory" id="selectedCategory"
                                aria-label="Default select example" required>
                            <?php
                            foreach (get_category_names() as $category) {
                                if (isset($userInfo)) {
                                    if ($category['name'] != $userInfo['patronInfo']['category']) {
                                        echo '<option value="' . $category['name'] . '">' . $category['name'] . '</option>';
                                    }
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <input type="hidden" name="changingPatron" value="<?= isset($userInfo) ? $userInfo['id'] : '' ?>">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Change</button>
                </div>
            </form>
        </div>
    </div>
</div>