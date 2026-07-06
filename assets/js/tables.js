/**
 * Index Tables Interactions
 * 
 * Manages bulk checkboxes selection, bottom actions drawer, and confirmation prompts
 * 
 * @package Clairvoyant_Core
 * @since 1.0.0
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // Select All and Row Checkbox Management
    const selectAllCheckbox = document.getElementById('cv-select-all');
    const rowCheckboxes = document.querySelectorAll('.cv-row-select');
    const bulkBar = document.getElementById('cv-bulk-bar');
    const selectedCountSpan = document.getElementById('cv-selected-count');
    const bulkActionInput = document.getElementById('cv-bulk-action-target');
    const bulkForm = document.getElementById('cv-bulk-form');

    if (selectAllCheckbox && rowCheckboxes.length > 0) {
        
        // Toggle all row checkboxes
        selectAllCheckbox.addEventListener('change', function() {
            rowCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBulkBar();
        });

        // Toggle select all status based on row changes
        rowCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const checkedCount = document.querySelectorAll('.cv-row-select:checked').length;
                selectAllCheckbox.checked = checkedCount === rowCheckboxes.length;
                updateBulkBar();
            });
        });
    }

    /**
     * Shows or hides the floating bulk action bar at the bottom
     */
    function updateBulkBar() {
        if (!bulkBar || !selectedCountSpan) return;
        
        const checkedBoxes = document.querySelectorAll('.cv-row-select:checked');
        const count = checkedBoxes.length;

        if (count > 0) {
            selectedCountSpan.innerText = count;
            bulkBar.classList.add('active');
        } else {
            bulkBar.classList.remove('active');
        }
    }

    // Connect bulk triggers inside the drawer bar
    const bulkButtons = document.querySelectorAll('.cv-bulk-trigger-btn');
    bulkButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const action = this.dataset.action;
            const actionLabel = this.innerText;

            if (!bulkForm || !bulkActionInput) return;

            window.cvModals.confirm(
                'Confirm Bulk Action',
                `Are you sure you want to perform "${actionLabel}" on the selected items?`,
                function() {
                    // Inject bulk action details and submit form
                    bulkActionInput.value = action;
                    bulkForm.submit();
                }
            );
        });
    });

    // Confirmation Prompts for Single Record Deletions
    const deleteButtons = document.querySelectorAll('.cv-delete-record-btn');
    deleteButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const deleteUrl = this.href;
            const recordName = this.dataset.name || 'this item';

            window.cvModals.confirm(
                'Confirm Deletion',
                `Are you sure you want to delete "${recordName}"?<br>This action cannot be undone.`,
                function() {
                    // Redirect to perform PHP deletion route
                    window.location.href = deleteUrl;
                }
            );
        });
    });
});
