/**
 * AID JavaScript SDK
 * Professional SDK for integrating AID verification into any website.
 */

(function(window) {
    'use strict';

    const AID = {
        version: '1.0.0',
        config: {},

        /**
         * Initialize AID SDK
         */
        init: function(options) {
            this.config = {
                clientId: options.clientId,
                baseUrl: options.baseUrl || 'https://aid.example.com',
                container: options.container || '#aid-verify',
                onSuccess: options.onSuccess || function(res) { console.log('AID Success:', res); },
                onError: options.onError || function(err) { console.error('AID Error:', err); }
            };
            console.log('AID SDK Initialized');
        },

        /**
         * Start a verification flow
         */
        verify: async function(options = {}) {
            const action = options.action || 'default';
            const container = options.container || this.config.container;
            
            try {
                // 1. Request verification session
                const response = await fetch(`${this.config.baseUrl}/api/verify/start`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        clientId: this.config.clientId,
                        action: action,
                        fingerprint: this._getFingerprint()
                    })
                });

                const data = await response.json();
                if (!data.success) throw new Error(data.message);

                // 2. Render the challenge UI
                this._renderChallenge(data.challenge, container);

            } catch (err) {
                this.config.onError(err.message);
            }
        },

        _renderChallenge: function(challenge, containerId) {
            const container = document.querySelector(containerId);
            if (!container) return;

            if (challenge.type === 'click_verify') {
                container.innerHTML = `
                    <div class="aid-card">
                        <p>${challenge.message}</p>
                        <button id="aid-verify-btn" class="aid-btn">Continue</button>
                    </div>
                `;
                document.getElementById('aid-verify-btn').onclick = () => this._completeNative();
            } else if (challenge.type === 'qr_code') {
                container.innerHTML = `
                    <div class="aid-card">
                        <p>${challenge.message}</p>
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodeURIComponent(challenge.url)}" />
                        <div id="aid-qr-status">Waiting for approval...</div>
                    </div>
                `;
                this._pollQRStatus(challenge.verification_id);
            }
        },

        _completeNative: async function() {
            // Simulate completion
            this.config.onSuccess({ status: 'VERIFIED', method: 'AID_NATIVE' });
        },

        _pollQRStatus: function(verifId) {
            const interval = setInterval(async () => {
                const res = await fetch(`${this.config.baseUrl}/api/verify/status?id=${verifId}`);
                const data = await res.json();
                if (data.status === 'VERIFIED') {
                    clearInterval(interval);
                    this.config.onSuccess({ status: 'VERIFIED', method: 'QR' });
                }
            }, 3000);
        },

        _getFingerprint: function() {
            return btoa(navigator.userAgent + screen.width + screen.height);
        },

        // UI Components
        Button: function(id, text = 'Verify with AID') {
            const btn = document.createElement('button');
            btn.id = id;
            btn.innerText = text;
            btn.className = 'aid-btn';
            btn.onclick = () => this.verify();
            return btn;
        }
    };

    window.AID = AID;
})(window);
