/**
 * Yearbook Loader Manager
 * Waits for yearbook to fully load, render cover, and navigate to student (if specified)
 */

(function() {
    'use strict';

    const LoaderManager = {
        loaderElement: null,
        iframe: null,
        isLoaded: false,
        timeout: null,
        maxWaitTime: 15000, // Maximum 15 seconds for complete loading
        checkInterval: null,
        magazineReady: false,
        navigationComplete: false,
        coverVisible: false,

        init: function() {
            console.log('[Loader] Initializing yearbook loader...');
            this.loaderElement = document.querySelector('.yearbook-loader-overlay');
            this.iframe = document.getElementById('yearbook-iframe');

            if (!this.loaderElement || !this.iframe) {
                console.warn('[Loader] Loader or iframe not found');
                return;
            }

            this.setupEventListeners();
            this.setMaxTimeout();
            this.startChecking();
        },

        setupEventListeners: function() {
            const self = this;

            // Listen for messages from the yearbook iframe
            window.addEventListener('message', function(event) {
                if (!event.data || !event.data.type) return;

                console.log('[Loader] Received message:', event.data.type);

                switch(event.data.type) {
                    case 'yearbook-magazine-initialized':
                        console.log('[Loader] Magazine initialized');
                        self.magazineReady = true;
                        self.checkReadiness();
                        break;
                    
                    case 'yearbook-cover-visible':
                        console.log('[Loader] Cover is visible');
                        self.coverVisible = true;
                        self.checkReadiness();
                        break;
                    
                    case 'yearbook-navigation-complete':
                        console.log('[Loader] Navigation to student complete');
                        self.navigationComplete = true;
                        self.checkReadiness();
                        break;
                    
                    case 'yearbook-ready':
                        console.log('[Loader] Yearbook fully ready signal received');
                        self.magazineReady = true;
                        self.coverVisible = true;
                        self.navigationComplete = true;
                        self.checkReadiness();
                        break;
                }
            });

            // Fallback: Basic iframe load detection
            this.iframe.addEventListener('load', function() {
                console.log('[Loader] Iframe loaded');
                // Don't hide immediately - wait for proper signals
            });
        },

        startChecking: function() {
            const self = this;
            let checkCount = 0;
            const maxChecks = 50; // Check for up to 10 seconds (50 * 200ms)

            this.checkInterval = setInterval(function() {
                checkCount++;

                if (checkCount > maxChecks) {
                    console.log('[Loader] Max checks reached, forcing hide');
                    clearInterval(self.checkInterval);
                    self.hideLoader();
                    return;
                }

                // Check if iframe content is accessible and ready
                try {
                    const iframeDoc = self.iframe.contentDocument || self.iframe.contentWindow.document;
                    
                    if (iframeDoc && iframeDoc.readyState === 'complete') {
                        // Check for magazine element
                        const canvas = iframeDoc.getElementById('canvas');
                        if (canvas) {
                            const canvasVisible = window.getComputedStyle(canvas).display !== 'none';
                            if (canvasVisible && !self.coverVisible) {
                                console.log('[Loader] Canvas is visible');
                                self.coverVisible = true;
                            }
                        }

                        // Check for magazine with turn.js
                        const magazine = iframeDoc.querySelector('.magazine');
                        if (magazine && typeof iframeDoc.defaultView.$ !== 'undefined') {
                            const $magazine = iframeDoc.defaultView.$('.magazine');
                            if ($magazine.length > 0 && typeof $magazine.turn === 'function' && $magazine.turn('is')) {
                                if (!self.magazineReady) {
                                    console.log('[Loader] Magazine detected and ready');
                                    self.magazineReady = true;
                                }
                            }
                        }

                        self.checkReadiness();
                    }
                } catch (e) {
                    // Cross-origin or not ready yet - rely on postMessage
                    console.log('[Loader] Waiting for postMessage signals...');
                }
            }, 200);
        },

        checkReadiness: function() {
            // Check if student navigation is expected
            const urlParams = new URLSearchParams(window.location.search);
            const hasStudent = urlParams.has('student_id') && urlParams.has('student_name');

            console.log('[Loader] Checking readiness:', {
                magazineReady: this.magazineReady,
                coverVisible: this.coverVisible,
                navigationComplete: this.navigationComplete,
                hasStudent: hasStudent
            });

            // If student navigation is expected, wait for it
            if (hasStudent) {
                if (this.magazineReady && this.coverVisible && this.navigationComplete) {
                    console.log('[Loader] All conditions met (with student navigation)');
                    this.hideLoader();
                }
            } else {
                // No student navigation - just wait for cover to be visible
                if (this.magazineReady && this.coverVisible) {
                    console.log('[Loader] All conditions met (without student navigation)');
                    this.hideLoader();
                }
            }
        },

        setMaxTimeout: function() {
            const self = this;
            this.timeout = setTimeout(function() {
                if (!self.isLoaded) {
                    console.log('[Loader] Max wait time reached, forcing hide');
                    self.hideLoader();
                }
            }, this.maxWaitTime);
        },

        hideLoader: function() {
            if (this.isLoaded) return;
            
            console.log('[Loader] Hiding loader...');
            this.isLoaded = true;

            if (this.timeout) {
                clearTimeout(this.timeout);
            }

            if (this.checkInterval) {
                clearInterval(this.checkInterval);
            }

            if (this.loaderElement) {
                this.loaderElement.classList.add('hidden');
                setTimeout(function() {
                    if (this.loaderElement && this.loaderElement.parentNode) {
                        this.loaderElement.remove();
                        console.log('[Loader] Loader removed from DOM');
                    }
                }.bind(this), 400);
            }

            // Clear URL parameters after giving iframe time to process
            // Delay to ensure iframe detects the student parameters first
            setTimeout(function() {
                this.clearURLParameters();
            }.bind(this), 2000); // Wait 2 seconds before clearing
        },

        clearURLParameters: function() {
            const urlParams = new URLSearchParams(window.location.search);
            const hasStudentParams = urlParams.has('student_id') || urlParams.has('student_name');
            
            if (hasStudentParams) {
                console.log('[Loader] Clearing student URL parameters...');
                
                // Remove student parameters but keep the page parameter
                urlParams.delete('student_id');
                urlParams.delete('student_name');
                
                // Build new URL
                const newUrl = window.location.pathname + '?' + urlParams.toString();
                
                // Update URL without reload
                window.history.replaceState({}, '', newUrl);
                
                console.log('[Loader] URL cleaned:', newUrl);
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

    // Monitor URL changes for subsequent student searches
    let lastStudentId = null;
    let isFirstLoad = true;
    
    function checkForStudentURLChange() {
        const urlParams = new URLSearchParams(window.location.search);
        const currentStudentId = urlParams.get('student_id');
        const currentStudentName = urlParams.get('student_name');
        
        // Skip the first load (it's handled by initial page load)
        if (isFirstLoad && currentStudentId) {
            lastStudentId = currentStudentId;
            isFirstLoad = false;
            return;
        }
        
        if (currentStudentId && currentStudentId !== lastStudentId) {
            console.log('[Loader] New student search detected in URL:', currentStudentId);
            lastStudentId = currentStudentId;
            
            // Show loader again
            const loader = document.querySelector('.yearbook-loader-overlay');
            if (loader) {
                loader.classList.remove('hidden');
                loader.style.display = 'flex';
            }
            
            // Send message to iframe to navigate to new student
            const iframe = document.getElementById('yearbook-iframe');
            if (iframe && iframe.contentWindow) {
                console.log('[Loader] Sending navigate message to iframe for student:', currentStudentId);
                
                // Update the iframe URL parameters directly without full reload
                try {
                    const iframeSrc = new URL(iframe.src);
                    iframeSrc.searchParams.set('student_id', currentStudentId);
                    iframeSrc.searchParams.set('student_name', currentStudentName);
                    
                    // Use replaceState to update URL without reload
                    iframe.contentWindow.postMessage({
                        type: 'navigate-to-student',
                        studentId: currentStudentId,
                        studentName: currentStudentName
                    }, '*');
                    
                    console.log('[Loader] Posted navigate message to iframe');
                    
                    // Clear any existing timers
                    if (LoaderManager.timeout) {
                        clearTimeout(LoaderManager.timeout);
                    }
                    if (LoaderManager.checkInterval) {
                        clearInterval(LoaderManager.checkInterval);
                    }
                    
                    // Reset loader state for new navigation
                    LoaderManager.isLoaded = false;
                    LoaderManager.magazineReady = false;
                    LoaderManager.coverVisible = false;
                    LoaderManager.navigationComplete = false;
                    
                    // Restart loading process
                    LoaderManager.setMaxTimeout();
                    LoaderManager.startChecking();
                    
                } catch (e) {
                    console.error('[Loader] Error communicating with iframe:', e);
                    // Fallback: full reload if postMessage fails
                    const iframeSrc = new URL(iframe.src);
                    iframeSrc.searchParams.set('student_id', currentStudentId);
                    iframeSrc.searchParams.set('student_name', currentStudentName);
                    iframe.src = iframeSrc.toString();
                }
            }
        }
    }
    
    // Check for URL changes every 300ms
    setInterval(checkForStudentURLChange, 300);

    window.YearbookLoader = LoaderManager;
})();

