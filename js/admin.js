// JavaScript pour le panneau d'administration

document.addEventListener('DOMContentLoaded', function() {
    // Initialisation des tooltips Bootstrap
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Initialisation des popovers Bootstrap
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
    
    // Gestion des alertes de confirmation
    const confirmButtons = document.querySelectorAll('.confirm-action');
    confirmButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm(this.dataset.confirmMessage || 'Êtes-vous sûr de vouloir effectuer cette action ?')) {
                e.preventDefault();
            }
        });
    });
    
    // Fermeture automatique des alertes après 5 secondes
    const autoCloseAlerts = document.querySelectorAll('.alert-dismissible.auto-close');
    autoCloseAlerts.forEach(alert => {
        setTimeout(() => {
            const closeButton = alert.querySelector('.btn-close');
            if (closeButton) {
                closeButton.click();
            } else {
                alert.style.display = 'none';
            }
        }, 5000);
    });
    
    // Gestion des sélections multiples dans les tableaux
    const selectAllCheckbox = document.getElementById('selectAll');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.item-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            
            // Activer/désactiver les boutons d'action en masse
            const bulkActionButtons = document.querySelectorAll('.bulk-action');
            bulkActionButtons.forEach(button => {
                button.disabled = !this.checked;
            });
        });
        
        // Vérifier si au moins une case est cochée pour activer les boutons d'action en masse
        const itemCheckboxes = document.querySelectorAll('.item-checkbox');
        itemCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const anyChecked = Array.from(itemCheckboxes).some(cb => cb.checked);
                
                const bulkActionButtons = document.querySelectorAll('.bulk-action');
                bulkActionButtons.forEach(button => {
                    button.disabled = !anyChecked;
                });
                
                // Mettre à jour la case "Tout sélectionner"
                const allChecked = Array.from(itemCheckboxes).every(cb => cb.checked);
                selectAllCheckbox.checked = allChecked;
                selectAllCheckbox.indeterminate = anyChecked && !allChecked;
            });
        });
    }
    
    // Prévisualisation des images avant upload
    const imageInputs = document.querySelectorAll('.image-upload');
    imageInputs.forEach(input => {
        input.addEventListener('change', function() {
            const previewId = this.dataset.preview;
            const preview = document.getElementById(previewId);
            
            if (preview && this.files && this.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                
                reader.readAsDataURL(this.files[0]);
            }
        });
    });
});