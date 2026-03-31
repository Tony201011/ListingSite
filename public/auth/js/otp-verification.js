function otpVerification(config) {
    return {
        otpDigits: ['', '', '', '', '', ''],
        timer: config.timer || 60,
        verifyUrl: config.verifyUrl,
        resendUrl: config.resendUrl,

        canResend: false,
        resendText: 'Resend Code',
        timerText: '',
        isVerifying: false,
        verificationStatus: null,
        intervalId: null,

        init() {
            this.updateTimerText();

            if (this.timer > 0) {
                this.startTimer();
            } else {
                this.canResend = true;
                this.timerText = 'Code expired';
            }

            this.$nextTick(() => this.focusInput(0));
        },

        getInputs() {
            return this.$root.querySelectorAll('.otp-input');
        },

        focusInput(index) {
            const inputs = this.getInputs();
            if (index >= 0 && index < inputs.length) {
                inputs[index].focus();
                inputs[index].select();
            }
        },

        get isOtpComplete() {
            return this.otpDigits.every(d => d !== '');
        },

        get otpCode() {
            return this.otpDigits.join('');
        },

        updateTimerText() {
            if (this.timer <= 0) {
                this.timerText = 'Code expired';
                return;
            }

            const m = Math.floor(this.timer / 60);
            const s = this.timer % 60;

            this.timerText = `Code expires in ${m.toString().padStart(2,'0')}:${s.toString().padStart(2,'0')}`;
        },

        startTimer() {
            if (this.intervalId) clearInterval(this.intervalId);

            this.canResend = false;

            this.intervalId = setInterval(() => {
                if (this.timer > 0) {
                    this.timer--;
                    this.updateTimerText();
                } else {
                    clearInterval(this.intervalId);
                    this.canResend = true;
                    this.timerText = 'Code expired';
                }
            }, 1000);
        },

        handleInput(index, event) {
            const value = event.target.value.replace(/\D/g, '').slice(0,1);

            this.otpDigits[index] = value;
            event.target.value = value;

            if (value && index < 5) {
                this.focusInput(index + 1);
            }
        },

        handleBackspace(index, event) {
            if (this.otpDigits[index]) {
                this.otpDigits[index] = '';
                event.target.value = '';
                return;
            }

            if (index > 0) {
                this.focusInput(index - 1);
            }
        },

        handlePaste(event) {
            event.preventDefault();

            const pasted = event.clipboardData.getData('text')
                .replace(/\D/g, '')
                .slice(0, 6);

            for (let i = 0; i < 6; i++) {
                this.otpDigits[i] = pasted[i] || '';
            }

            this.$nextTick(() => {
                this.focusInput(pasted.length >= 6 ? 5 : pasted.length);
            });
        },

        async verifyOTP() {
            if (!this.isOtpComplete || this.isVerifying) return;

            this.isVerifying = true;

            try {
                const res = await fetch(this.verifyUrl, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ otp: this.otpCode })
                });

                const data = await res.json();

                if (data.success) {
                    window.location.href = data.redirect || '/my-profile';
                } else {
                    this.verificationStatus = { type: 'error', message: data.message };
                    this.isVerifying = false;
                }
            } catch {
                this.verificationStatus = { type: 'error', message: 'Error verifying OTP' };
                this.isVerifying = false;
            }
        },

        async resendOTP() {
            if (!this.canResend) return;

            this.resendText = "Sending...";

            try {
                const res = await fetch(this.resendUrl, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const data = await res.json();

                if (data.success) {
                    this.otpDigits = ['', '', '', '', '', ''];
                    this.timer = data.timer || 60;
                    this.startTimer();
                    this.focusInput(0);
                }

            } finally {
                this.resendText = "Resend Code";
            }
        }
    };
}
