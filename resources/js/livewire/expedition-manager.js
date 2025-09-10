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

import { LivewireFileUploadHandler } from './file-upload-handler.js';

/**
 * Expedition Manager - handles Expedition-specific file uploads
 * Manages expedition logo uploads
 */
export class ExpeditionManager extends LivewireFileUploadHandler {
    constructor(options = {}) {
        const defaultOptions = {
            modelType: 'Expedition',
            supportedFields: ['logo'],
            debug: false
        };
        
        super({ ...defaultOptions, ...options });
        this.log('ExpeditionManager initialized');
    }

    /**
     * Check if this handler should process the upload event
     */
    shouldHandle(data) {
        if (!data || !data.modelType || !data.fieldName) {
            return false;
        }

        // Handle Expedition logo uploads
        return data.modelType === this.options.modelType && 
               this.options.supportedFields.includes(data.fieldName);
    }

    /**
     * Process expedition logo uploads
     */
    processUpload(data) {
        if (data.fieldName === 'logo') {
            this.handleExpeditionLogoUpload(data);
        }
    }

    /**
     * Handle expedition logo uploads
     */
    handleExpeditionLogoUpload(data) {
        this.log('Processing expedition logo upload:', data.filePath);
        
        // Update the hidden field with the uploaded file path
        if (this.updateHiddenField('logo_path', data.filePath)) {
            this.dispatchUpdateEvent('expeditionLogoUpdated', {
                filePath: data.filePath
            });
            
            this.log('Expedition logo uploaded successfully:', data.filePath);
        } else {
            this.log('Failed to update logo_path field for expedition logo');
        }
    }

    /**
     * Initialize expedition-specific event listeners
     */
    init() {
        // Listen for confirmation events
        document.addEventListener('expeditionLogoUpdated', (event) => {
            this.log('Expedition logo updated confirmation:', event.detail);
            
            // Optionally update the displayed image immediately
            this.updateDisplayedImage(event.detail.filePath);
        });

        this.log('ExpeditionManager fully initialized');
    }

    /**
     * Update the displayed expedition logo image (if needed)
     */
    updateDisplayedImage(filePath) {
        // This could be implemented if we need to update the preview image
        // For now, we'll just log it as the original implementation didn't update the display
        this.log('Could update displayed expedition logo with:', filePath);
    }
}