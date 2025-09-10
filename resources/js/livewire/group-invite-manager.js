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
 * GroupInviteManager
 * Handles debugging and interaction for the group invite Livewire component
 */
export class GroupInviteManager {
    constructor(options = {}) {
        this.debug = options.debug ?? false;
        this.initialized = false;
    }

    /**
     * Initialize the group invite manager
     */
    init() {
        if (this.initialized) {
            return;
        }

        this.initialized = true;
        this.logDebugInfo();
        this.attachEventListeners();
    }

    /**
     * Log debug information about Livewire availability
     */
    logDebugInfo() {
        if (!this.debug) {
            return;
        }

        console.log('GroupInviteManager template loaded');
        console.log('Livewire available:', typeof Livewire !== 'undefined');
        
        if (typeof Livewire !== 'undefined') {
            setTimeout(() => {
                console.log('Livewire components:', Livewire.components);
                console.log('Wire directives found:', document.querySelectorAll('[wire\\:click]').length);
            }, 1000);
        }
    }

    /**
     * Attach event listeners for debugging
     */
    attachEventListeners() {
        if (!this.debug) {
            return;
        }

        // Add click handlers for debugging add button clicks
        const addButtons = document.querySelectorAll('[data-debug*="add-invite-btn"]');
        addButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                console.log('Add button clicked - wire:click should trigger addInvite method');
            });
        });
    }

    /**
     * Re-initialize after AJAX modal content is loaded
     */
    reinitialize() {
        this.initialized = false;
        this.init();
    }
}