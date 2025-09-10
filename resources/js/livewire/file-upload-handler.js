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
 * Base Livewire File Upload Handler
 * Provides common functionality for handling Livewire file upload events across components
 */
export class LivewireFileUploadHandler {
    constructor(options = {}) {
        this.options = {
            debug: false,
            ...options
        };
        this.setupEventListeners();
        this.log('FileUploadHandler initialized with options:', this.options);
    }

    /**
     * Set up multiple event listeners for maximum compatibility across Livewire versions
     */
    setupEventListeners() {
        // Method 1: Modern Livewire 3 approach using document events
        document.addEventListener('livewire:fileUploaded', (event) => {
            this.handleFileUpload(event.detail);
        });

        // Method 2: Window-level event listener
        window.addEventListener('livewire:fileUploaded', (event) => {
            this.handleFileUpload(event.detail);
        });

        // Method 3: Traditional Livewire.on approach (fallback for older versions)
        if (typeof Livewire !== 'undefined') {
            document.addEventListener('livewire:init', () => {
                try {
                    Livewire.on('fileUploaded', (eventData) => {
                        this.handleFileUpload(eventData);
                    });
                } catch (e) {
                    this.log('Failed to set up Livewire.on event listener:', e);
                }
            });
        }

        this.log('Event listeners set up for file upload handling');
    }

    /**
     * Main file upload handler - processes upload events and delegates to specific handlers
     */
    handleFileUpload(eventData) {
        if (!eventData) {
            this.log('No event data received');
            return;
        }

        // Handle both array-wrapped and direct object formats
        let data = eventData;
        if (Array.isArray(eventData) && eventData.length > 0) {
            data = eventData[0];
        }

        this.log('Processing file upload:', data);

        // Call the appropriate handler based on model type and field name
        if (this.shouldHandle(data)) {
            this.processUpload(data);
        } else {
            this.log('Upload event ignored - does not match handler criteria');
        }
    }

    /**
     * Check if this handler should process the upload event
     * Override in subclasses for specific filtering
     */
    shouldHandle(data) {
        return true; // Base handler processes all events
    }

    /**
     * Process the upload - override in subclasses for specific behavior
     */
    processUpload(data) {
        this.log('Base handler processing upload:', data);
        
        // Dispatch a custom event to confirm processing
        const updateEvent = new CustomEvent('fileUploadProcessed', {
            detail: data
        });
        document.dispatchEvent(updateEvent);
    }

    /**
     * Update a hidden form field with the uploaded file path
     */
    updateHiddenField(fieldId, filePath) {
        const hiddenField = document.getElementById(fieldId);
        if (hiddenField) {
            this.log(`Updating field ${fieldId} from:`, hiddenField.value, 'to:', filePath);
            hiddenField.value = filePath;
            return true;
        } else {
            this.log(`Hidden field ${fieldId} not found`);
            return false;
        }
    }

    /**
     * Create a hidden form field if it doesn't exist
     */
    createHiddenField(fieldId, fieldName, filePath, parentElement = null) {
        let hiddenField = document.getElementById(fieldId);
        if (!hiddenField) {
            hiddenField = document.createElement('input');
            hiddenField.type = 'hidden';
            hiddenField.id = fieldId;
            hiddenField.name = fieldName;
            
            if (parentElement) {
                parentElement.appendChild(hiddenField);
            } else {
                // Append to the first form found
                const form = document.querySelector('form');
                if (form) {
                    form.appendChild(hiddenField);
                }
            }
            this.log(`Created hidden field: ${fieldId}`);
        }
        
        hiddenField.value = filePath;
        return hiddenField;
    }

    /**
     * Dispatch a custom confirmation event
     */
    dispatchUpdateEvent(eventName, data) {
        const updateEvent = new CustomEvent(eventName, {
            detail: data
        });
        document.dispatchEvent(updateEvent);
        this.log(`Dispatched ${eventName} event:`, data);
    }

    /**
     * Debug logging
     */
    log(...args) {
        if (this.options.debug) {
            console.log('[FileUploadHandler]', ...args);
        }
    }
}