// ── Delete Confirmation Modal ───────────────────────────────
(function () {
    var deleteModal = document.getElementById('deleteModal');
    var deleteForm = document.getElementById('deleteForm');
    var deleteName = document.getElementById('deleteName');
    var cancelDelete = document.getElementById('cancelDelete');
    var closeBtn = document.getElementById('closeDeleteModal');

    if (!deleteModal) return;

    function openDeleteModal(id, name) {
        deleteName.textContent = name;
        deleteForm.action = '/admin/delete/' + id;
        deleteModal.classList.add('is-active');
    }

    function closeDeleteModal() {
        deleteModal.classList.remove('is-active');
    }

    // Bind all delete buttons
    document.querySelectorAll('.btn-delete').forEach(function (btn) {
        btn.addEventListener('click', function () {
            openDeleteModal(btn.getAttribute('data-id'), btn.getAttribute('data-name'));
        });
    });

    cancelDelete.addEventListener('click', closeDeleteModal);
    if (closeBtn) closeBtn.addEventListener('click', closeDeleteModal);
    deleteModal.querySelector('.modal-background').addEventListener('click', closeDeleteModal);
})();
