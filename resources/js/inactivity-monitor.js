class InactivityMonitor {
    constructor(timeoutSeconds = 300, warningCount = 3) { // Default to 5 minutes (300 seconds)
        this.timeoutSeconds = timeoutSeconds;
        this.warningCount = warningCount;
        this.timeLeft = timeoutSeconds;
        this.warningLevel = 0; // 0 = no warning, 1 = first inactivity, 2 = second inactivity, 3 = logout
        this.timer = null;
        this.isDocumentHidden = false;
        this.init();
    }

    async init() {
        // Fetch the timeout setting from the backend
        try {
            const response = await fetch('/admin/settings/timeout', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
            
            if (response.ok) {
                const data = await response.json();
                this.timeoutSeconds = data.timeout;
                this.timeLeft = data.timeout;
            }
        } catch (error) {
            console.error('Failed to fetch timeout setting:', error);
        }

        // Set up event listeners for user activity
        ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart', 'click'].forEach(eventType => {
            document.addEventListener(eventType, () => this.resetTimer(), true);
        });

        // Handle page visibility changes
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.isDocumentHidden = true;
            } else {
                this.isDocumentHidden = false;
                this.resetTimer();
            }
        });

        // Start the timer
        this.startTimer();
    }

    startTimer() {
        this.clearTimer();
        
        this.timer = setInterval(() => {
            if (!this.isDocumentHidden) {
                this.timeLeft--;
                
                if (this.timeLeft <= 0) {
                    this.handleInactivity();
                } else if (this.timeLeft <= Math.min(30, this.timeoutSeconds * 0.1) && this.warningLevel === 0) { // First warning at 30s or 10% of timeout
                    this.showWarning('You have been inactive. Please move your mouse or press a key to continue.', 1);
                    this.warningLevel = 1;
                } else if (this.timeLeft <= Math.min(15, this.timeoutSeconds * 0.05) && this.warningLevel === 1) { // Second warning at 15s or 5% of timeout
                    this.showWarning('You are about to be logged out due to inactivity. Please move your mouse or press a key to continue.', 2);
                    this.warningLevel = 2;
                }
            }
        }, 1000);
    }

    resetTimer() {
        this.timeLeft = this.timeoutSeconds;
        this.clearTimer();
        this.startTimer();
        
        // Reset warning level when activity is detected
        if (this.warningLevel > 0) {
            this.clearWarning();
            this.warningLevel = 0;
        }
        
        // Send activity ping to the server
        this.sendActivityPing();
    }

    clearTimer() {
        if (this.timer) {
            clearInterval(this.timer);
            this.timer = null;
        }
    }

    async handleInactivity() {
        if (this.warningLevel < this.warningCount - 1) {
            this.warningLevel++;
            this.showWarning(`Session timeout! You have been inactive for too long.`, this.warningLevel + 1);
            this.timeLeft = this.timeoutSeconds; // Reset timer for next warning
        } else {
            // Third inactivity - apply penalty and logout
            await this.applyPenalty();
            this.performLogout();
        }
    }

    showWarning(message, level) {
        // Create or update warning modal
        let warningModal = document.getElementById('inactivity-warning-modal');
        
        if (!warningModal) {
            // Create the modal element
            const modalHtml = `
                <div id="inactivity-warning-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" style="display: block;">
                    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                        <div class="mt-3 text-center">
                            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                                <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="warning-modal-title">Inactivity Warning</h3>
                            <div class="mt-2 px-7 py-3">
                                <p class="text-sm text-gray-500" id="warning-modal-message">${message}</p>
                                <p class="text-xs text-gray-400 mt-2">Time remaining: <span id="countdown-timer">${this.timeLeft}</span> seconds</p>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', modalHtml);
        } else {
            // Update existing modal
            document.getElementById('warning-modal-message').textContent = message;
            document.getElementById('warning-modal-title').textContent = 
                level === 1 ? 'First Inactivity Warning' : 
                level === 2 ? 'Second Inactivity Warning' : 
                'Session Timeout';
        }
    }

    clearWarning() {
        const warningModal = document.getElementById('inactivity-warning-modal');
        if (warningModal) {
            warningModal.remove();
        }
    }

    async applyPenalty() {
        try {
            const response = await fetch('/api/inactivity/penalty', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    reason: 'Excessive idle time',
                    level: this.warningLevel + 1
                })
            });
            
            if (!response.ok) {
                console.error('Failed to apply penalty:', response.statusText);
            }
        } catch (error) {
            console.error('Error applying penalty:', error);
        }
    }

    async performLogout() {
        try {
            await fetch('/logout', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
            
            // Redirect to login page
            window.location.href = '/login';
        } catch (error) {
            console.error('Error during logout:', error);
            // Fallback redirect
            window.location.href = '/login';
        }
    }

    async sendActivityPing() {
        try {
            // Send a lightweight request to confirm the user is active
            await fetch('/api/activity/ping', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
        } catch (error) {
            // Silently handle errors for activity pings
            console.debug('Activity ping failed:', error);
        }
    }
}

// Initialize the inactivity monitor when the document is ready
document.addEventListener('DOMContentLoaded', async function() {
    // Get timeout from settings API
    const monitor = new InactivityMonitor(300, 3); // Default values, will be overridden by API call
});