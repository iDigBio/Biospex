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

import {LivewireFileUploadHandler} from './file-upload-handler.js';

/**
 * Project Manager - handles Project-specific file uploads
 * Manages both project logo uploads and project asset downloads
 */
export class ProjectManager extends LivewireFileUploadHandler {
    constructor(options = {}) {
        const defaultOptions = {
            modelType: 'Project',
            supportedFields: ['logo'],
            resourceModelType: 'ProjectAsset',
            resourceFieldPrefix: 'download_',
            debug: false
        };

        super({...defaultOptions, ...options});
        this.log('ProjectManager initialized');
    }

    /**
     * Check if this handler should process the upload event
     */
    shouldHandle(data) {
        if (!data || !data.modelType || !data.fieldName) {
            return false;
        }

        // Handle Project logo uploads
        if (data.modelType === this.options.modelType &&
            this.options.supportedFields.includes(data.fieldName)) {
            return true;
        }

        // Handle ProjectAsset download uploads
        if (data.modelType === this.options.resourceModelType &&
            data.fieldName.startsWith(this.options.resourceFieldPrefix)) {
            return true;
        }

        return false;
    }

    /**
     * Process the upload based on type
     */
    processUpload(data) {
        if (data.modelType === this.options.modelType) {
            this.handleProjectUpload(data);
        } else if (data.modelType === this.options.resourceModelType) {
            this.handleResourceUpload(data);
        }
    }

    /**
     * Handle project logo uploads
     */
    handleProjectUpload(data) {
        if (data.fieldName === 'logo') {
            this.log('Processing project logo upload:', data.filePath);

            if (this.updateHiddenField('logo_path', data.filePath)) {
                this.dispatchUpdateEvent('logoPathUpdated', {
                    filePath: data.filePath
                });

                // Set up polling fallback for logo path changes
                this.setupLogoPathPolling();
            }
        }
    }

    /**
     * Handle project asset download uploads
     */
    handleResourceUpload(data) {
        if (data.fieldName.startsWith(this.options.resourceFieldPrefix)) {
            // Extract the project asset index from fieldName (e.g., 'download_0' -> '0')
            const resourceIndex = data.fieldName.replace(this.options.resourceFieldPrefix, '');
            const hiddenFieldId = `resources[${resourceIndex}][download_path]`;
            const hiddenFieldName = `resources[${resourceIndex}][download_path]`;

            this.log('Processing project asset download upload for index:', resourceIndex);

            // Find the appropriate fieldset to append the hidden field if needed
            const fieldsets = document.querySelectorAll('fieldset');
            const parentElement = fieldsets[resourceIndex] || null;

            const hiddenField = this.createHiddenField(
                hiddenFieldId,
                hiddenFieldName,
                data.filePath,
                parentElement
            );

            if (hiddenField) {
                this.dispatchUpdateEvent('downloadPathUpdated', {
                    filePath: data.filePath,
                    resourceIndex: resourceIndex
                });
            }
        }
    }

    /**
     * Set up polling fallback for logo path changes (from original implementation)
     */
    setupLogoPathPolling() {
        if (this.pollingInterval) {
            clearInterval(this.pollingInterval);
        }

        let lastKnownLogoPath = document.getElementById('logo_path')?.value || '';

        this.pollingInterval = setInterval(() => {
            const logoPathField = document.getElementById('logo_path');
            if (logoPathField) {
                const currentLogoPath = logoPathField.value;
                if (currentLogoPath !== lastKnownLogoPath && currentLogoPath !== '') {
                    this.log('Logo path changed via polling:', currentLogoPath);
                    lastKnownLogoPath = currentLogoPath;
                }
            }
        }, 1000);

        this.log('Logo path polling set up');
    }

    /**
     * Set up form submission handling
     */
    setupFormHandling() {
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', (event) => {
                const logoPathValue = document.getElementById('logo_path')?.value;
                this.log('Form submitting with logo_path:', logoPathValue);
            });
        }
    }

    /**
     * Initialize project-specific event listeners
     */
    init() {
        this.setupFormHandling();

        // Listen for confirmation events
        document.addEventListener('logoPathUpdated', (event) => {
            this.log('Logo path updated confirmation:', event.detail);
        });

        document.addEventListener('downloadPathUpdated', (event) => {
            this.log('Download path updated confirmation:', event.detail);
        });

        this.log('ProjectManager fully initialized');
    }

    /**
     * Clean up polling intervals
     */
    destroy() {
        if (this.pollingInterval) {
            clearInterval(this.pollingInterval);
            this.pollingInterval = null;
        }
    }
}