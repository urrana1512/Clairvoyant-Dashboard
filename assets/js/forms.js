/**
 * Form Interactivity and Validation
 * 
 * Handles color pickers, media library uploads, client-side validation, and star selectors
 * 
 * @package Clairvoyant_Core
 * @since 1.0.0
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // Initialize WordPress Color Pickers if available
    if (window.jQuery && jQuery.fn.wpColorPicker) {
        jQuery('.cv-color-picker').wpColorPicker();
    }

    // Interactive Star Rating Selector
    const ratingSelector = document.querySelector('.cv-rating-selector');
    if (ratingSelector) {
        const ratingInputs = ratingSelector.querySelectorAll('input[type="radio"]');
        ratingInputs.forEach(input => {
            input.addEventListener('change', function() {
                // Perform additional UI feedback if needed
            });
        });
    }

    // Media Library File Upload (Logo & Client Image)
    const uploadButtons = document.querySelectorAll('.cv-upload-trigger');
    uploadButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetInputId = this.dataset.target;
            const previewImageId = this.dataset.preview;
            const inputField = document.getElementById(targetInputId);
            const previewImg = document.getElementById(previewImageId);

            if (!inputField) return;

            // Instantiate native WordPress media frame
            const frame = wp.media({
                title: 'Select or Upload Media',
                button: {
                    text: 'Use this Media'
                },
                multiple: false
            });

            // Handle selection event
            frame.on('select', function() {
                const attachment = frame.state().get('selection').first().toJSON();
                inputField.value = attachment.url;
                
                if (previewImg) {
                    previewImg.src = attachment.url;
                    previewImg.style.display = 'block';
                }
            });

            frame.open();
        });
    });

    // Form Client-Side Validation Listeners
    const validatedForms = document.querySelectorAll('.cv-validated-form');
    validatedForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            let hasErrors = false;
            
            // Clear previous errors
            form.querySelectorAll('.cv-error-message').forEach(el => el.remove());
            form.querySelectorAll('.cv-field-error').forEach(el => el.classList.remove('cv-field-error'));

            // Check predictions / review text field if any
            const predictionText = form.querySelector('[name="prediction"]');
            if (predictionText && predictionText.value.trim().length < 50) {
                // If it is regular textarea, validate character count
                if (!form.querySelector('#wp-prediction-wrap')) {
                    showFieldError(predictionText, 'Prediction must be at least 50 characters long.');
                    hasErrors = true;
                }
            }

            // Check numeric inputs (e.g. lucky number)
            const numericFields = form.querySelectorAll('.cv-validate-numeric');
            numericFields.forEach(field => {
                if (field.value.trim() !== '' && !/^\d+$/.test(field.value.trim())) {
                    showFieldError(field, 'This field must be numeric.');
                    hasErrors = true;
                }
            });

            // Required fields check
            const requiredFields = form.querySelectorAll('[required]');
            requiredFields.forEach(field => {
                if (field.value.trim() === '') {
                    showFieldError(field, 'This field is required.');
                    hasErrors = true;
                }
            });

            if (hasErrors) {
                e.preventDefault();
                // Scroll to first error
                const firstError = form.querySelector('.cv-field-error');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        });
    });

    /**
     * Show Field Error Message in UI
     */
    function showFieldError(element, message) {
        element.classList.add('cv-field-error');
        const errSpan = document.createElement('span');
        errSpan.className = 'cv-error-message';
        errSpan.innerText = message;
        element.parentNode.appendChild(errSpan);
    }
});
