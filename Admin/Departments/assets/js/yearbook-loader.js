/**
 * Simple Yearbook Loader Manager
 * Handles automatic hiding when yearbook is ready
 */

(function() {
    'use strict';

    const LoaderManager = {
        loaderElement: null,
        iframe: null,
        isLoaded: false,
        timeout: null,
        maxWaitTime: 10000, // Maximum 10 seconds

        init: function() {
            this.loaderElement = document.querySelector('.yearbook-loader-overlay');
            this.iframe = document.getElementById('yearbook-iframe');

            if (!this.loaderElement || !this.iframe) {
                return;
            }

            this.setupEventListeners();
            this.setMaxTimeout();
        },

        setupEventListeners: function() {
            const self = this;

            this.iframe.addEventListener('load', function() {
                setTimeout(function() {
                    self.hideLoader();
                }, 800);
            });

            // Listen for ready message from iframe
            window.addEventListener('message', function(event) {
                if (event.data && event.data.type === 'yearbook-ready') {
                    self.hideLoader();
                }
            });
        },

        setMaxTimeout: function() {
            const self = this;
            this.timeout = setTimeout(function() {
                self.hideLoader();
            }, this.maxWaitTime);
        },

        hideLoader: function() {
            if (this.isLoaded) return;
            
            this.isLoaded = true;

            if (this.timeout) {
                clearTimeout(this.timeout);
            }

            if (this.loaderElement) {
                this.loaderElement.classList.add('hidden');
                setTimeout(function() {
                    if (this.loaderElement && this.loaderElement.parentNode) {
                        this.loaderElement.remove();
                    }
                }.bind(this), 400);
            }
        }
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            LoaderManager.init();
        });
    } else {
        LoaderManager.init();
    }

    window.YearbookLoader = LoaderManager;
})();

