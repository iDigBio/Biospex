/*
 * Copyright (C) 2014 - 2025, Biospex
 * biospex@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * GeoLocate Field Manager Livewire Component JavaScript
 * Handles Bootstrap Select initialization and modal integration
 */

// Initialize Bootstrap Select components for geolocate fields
function initializeBootstrapSelect() {
    $('.geolocate-field, .header-select').not('.bootstrap-select').selectpicker();
}

// Refresh Bootstrap Select initialization - handles flicker prevention
function refreshBootstrapSelect() {
    // Hide the controls container to prevent flicker
    const $controls = $('#controls, .controls');
    $controls.addClass('bootstrap-select-updating');
    
    // Use requestAnimationFrame to ensure CSS is applied before DOM manipulation
    requestAnimationFrame(function() {
        // Destroy existing Bootstrap Select instances
        $('.geolocate-field, .header-select').each(function() {
            if ($(this).hasClass('bootstrap-select')) {
                $(this).selectpicker('destroy');
            }
        });
        
        // Use a minimal delay for cleanup, then re-initialize
        setTimeout(function() {
            // Re-initialize all select elements
            $('.geolocate-field, .header-select').each(function() {
                $(this).selectpicker();
            });
            
            // Call makeSelect for compatibility
            if (typeof makeSelect === 'function') {
                $('.entry').each(function() {
                    makeSelect($(this));
                });
            }
            
            // Remove the hiding class to show elements again
            setTimeout(function() {
                $controls.removeClass('bootstrap-select-updating');
            }, 10);
        }, 30);
    });
}

// Flag to track if form is being submitted to prevent DOM conflicts
let isFormSubmitting = false;

// Destroy Bootstrap Select components (for modal cleanup)
function destroyBootstrapSelect() {
    $('.geolocate-field, .header-select').each(function() {
        if ($(this).hasClass('bootstrap-select')) {
            $(this).selectpicker('destroy');
        }
    });
}

// Main initialization
$(document).ready(function() {
    // Initialize on page load
    initializeBootstrapSelect();
    
    // Handle Livewire v3 updates - skip if form is being submitted
    document.addEventListener('livewire:navigated', function () {
        if (isFormSubmitting) return;
        setTimeout(function() {
            if (!isFormSubmitting) {
                try {
                    refreshBootstrapSelect();
                } catch (e) {
                    console.warn('GeolocateFieldManager: Error during livewire:navigated refresh', e);
                }
            }
        }, 50);
    });
    
    document.addEventListener('livewire:updated', function () {
        if (isFormSubmitting) return;
        setTimeout(function() {
            if (!isFormSubmitting) {
                try {
                    refreshBootstrapSelect();
                } catch (e) {
                    console.warn('GeolocateFieldManager: Error during livewire:updated refresh', e);
                }
            }
        }, 50);
    });
    
    // Additional event listener for component-specific updates
    document.addEventListener('livewire:load', function () {
        if (isFormSubmitting) return;
        setTimeout(function() {
            if (!isFormSubmitting) {
                try {
                    initializeBootstrapSelect();
                } catch (e) {
                    console.warn('GeolocateFieldManager: Error during livewire:load init', e);
                }
            }
        }, 50);
    });
    
    // Handle modal events for proper cleanup and initialization
    $(document).on('shown.bs.modal', '#global-modal', function () {
        // Reset form submission flag when modal opens
        isFormSubmitting = false;
        // Initialize Bootstrap Select when modal is shown
        setTimeout(function() {
            initializeBootstrapSelect();
            // Reset the Livewire component when modal opens
            if (window.Livewire) {
                window.Livewire.dispatch('resetGeolocateFields');
            }
        }, 100);
    });
    
    $(document).on('hidden.bs.modal', '#global-modal', function () {
        // Set flag to prevent further Livewire updates during cleanup
        isFormSubmitting = true;
        // Clean up Bootstrap Select when modal is hidden
        destroyBootstrapSelect();
        // Reset the component when modal closes
        if (window.Livewire) {
            window.Livewire.dispatch('resetGeolocateFields');
        }
        // Reset flag after cleanup is complete
        setTimeout(function() {
            isFormSubmitting = false;
        }, 200);
    });
    
    // Handle form submission to set the flag and prevent DOM conflicts
    $(document).on('submit', 'form#geolocate-form', function () {
        isFormSubmitting = true;
        
        // Disable Livewire updates during form submission
        if (window.Livewire) {
            try {
                // Stop any pending Livewire requests
                window.Livewire.stop();
            } catch (e) {
                // Silently handle any Livewire stop errors
            }
        }
        
        // Clean up Bootstrap Select to prevent DOM conflicts
        setTimeout(function() {
            $('.geolocate-field, .header-select').each(function() {
                if ($(this).hasClass('bootstrap-select')) {
                    try {
                        $(this).selectpicker('destroy');
                    } catch (e) {
                        // Silently handle destroy errors during form submission
                    }
                }
            });
        }, 10);
    });
    
    // Handle custom tooltips
    $('[data-hover="tooltip"]').tooltip();
    
    // Handle direct wire:click events with Bootstrap Select refresh
    $(document).on('click', '[wire\\:click="addField"], [wire\\:click="removeField"]', function() {
        // Refresh Bootstrap Select after Livewire DOM update
        setTimeout(function() {
            refreshBootstrapSelect();
            $('[data-hover="tooltip"]').tooltip();
        }, 100);
    });
});

// Export functions for external use
window.GeolocateFieldManager = {
    init: initializeBootstrapSelect,
    refresh: refreshBootstrapSelect,
    destroy: destroyBootstrapSelect
};