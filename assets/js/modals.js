/**
 * Custom Confirmation Modals
 * 
 * Provides highly stylized, premium modal overlays for delete and bulk actions
 * 
 * @package Clairvoyant_Core
 * @since 1.0.0
 */

(function() {
    window.cvModals = {
        /**
         * Render a dynamic confirmation modal overlay
         * 
         * @param {string} title Modal heading
         * @param {string} message Modal description body
         * @param {function} onConfirm Callback running if confirmed
         */
        confirm: function(title, message, onConfirm) {
            // Remove any existing modal
            const existingModal = document.getElementById('cv-confirm-modal');
            if (existingModal) {
                existingModal.remove();
            }

            // Create container
            const overlay = document.createElement('div');
            overlay.id = 'cv-confirm-modal';
            overlay.className = 'cv-modal-overlay';
            
            // Build modal inner HTML
            overlay.innerHTML = `
                <div class="cv-modal-box">
                    <div class="cv-modal-header">
                        <span class="cv-modal-warn-icon">⚠️</span>
                        <h3 class="cv-modal-title">${title}</h3>
                    </div>
                    <div class="cv-modal-body">
                        <p>${message}</p>
                    </div>
                    <div class="cv-modal-footer">
                        <button type="button" class="cv-form-button secondary" id="cv-modal-cancel">Cancel</button>
                        <button type="button" class="cv-form-button danger" id="cv-modal-confirm">Confirm</button>
                    </div>
                </div>
            `;

            // Append style directly if not loaded
            if (!document.getElementById('cv-modal-injected-styles')) {
                const style = document.createElement('style');
                style.id = 'cv-modal-injected-styles';
                style.innerHTML = `
                    .cv-modal-overlay {
                        position: fixed;
                        top: 0;
                        left: 0;
                        width: 100vw;
                        height: 100vh;
                        background-color: rgba(27, 27, 27, 0.4);
                        backdrop-filter: blur(4px);
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        z-index: 999999;
                        opacity: 0;
                        transition: opacity 0.3s ease;
                    }
                    .cv-modal-overlay.active {
                        opacity: 1;
                    }
                    .cv-modal-box {
                        background: #FFF;
                        border: 1px solid #E8DFD0;
                        border-radius: 14px;
                        padding: 24px;
                        width: 90%;
                        max-width: 420px;
                        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
                        transform: translateY(20px);
                        transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
                    }
                    .cv-modal-overlay.active .cv-modal-box {
                        transform: translateY(0);
                    }
                    .cv-modal-header {
                        display: flex;
                        align-items: center;
                        gap: 12px;
                        margin-bottom: 12px;
                    }
                    .cv-modal-warn-icon {
                        font-size: 24px;
                    }
                    .cv-modal-title {
                        font-family: 'Cormorant Garamond', serif;
                        font-size: 20px;
                        font-weight: 700;
                        margin: 0;
                        color: #1b1b1b;
                    }
                    .cv-modal-body p {
                        font-family: 'Poppins', sans-serif;
                        font-size: 14px;
                        color: #666;
                        margin: 0 0 20px 0;
                        line-height: 1.6;
                    }
                    .cv-modal-footer {
                        display: flex;
                        justify-content: flex-end;
                        gap: 12px;
                    }
                `;
                document.head.appendChild(style);
            }

            document.body.appendChild(overlay);

            // Trigger animation
            setTimeout(() => {
                overlay.classList.add('active');
            }, 10);

            // Handle clean close
            const closeModal = () => {
                overlay.classList.remove('active');
                setTimeout(() => {
                    overlay.remove();
                }, 300);
            };

            // Bind cancel event
            document.getElementById('cv-modal-cancel').addEventListener('click', closeModal);
            overlay.addEventListener('click', (e) => {
                if (e.target === overlay) {
                    closeModal();
                }
            });

            // Bind confirm event
            document.getElementById('cv-modal-confirm').addEventListener('click', () => {
                closeModal();
                if (typeof onConfirm === 'function') {
                    onConfirm();
                }
            });
        }
    };
})();
