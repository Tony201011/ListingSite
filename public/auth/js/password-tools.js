window.PasswordTools = {
    generateRandomPassword(length = 16) {
        const upper = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        const lower = 'abcdefghijklmnopqrstuvwxyz';
        const numbers = '0123456789';
        const symbols = '!@#$%^&*()-_=+[]{}?';
        const all = upper + lower + numbers + symbols;

        let password = '';
        password += upper[Math.floor(Math.random() * upper.length)];
        password += lower[Math.floor(Math.random() * lower.length)];
        password += numbers[Math.floor(Math.random() * numbers.length)];
        password += symbols[Math.floor(Math.random() * symbols.length)];

        for (let i = password.length; i < length; i++) {
            password += all[Math.floor(Math.random() * all.length)];
        }

        return password
            .split('')
            .sort(() => Math.random() - 0.5)
            .join('');
    },

    getPasswordStrength(password) {
        let score = 0;

        if (!password) {
            return { text: '', color: '', width: '0%' };
        }

        if (password.length >= 8) score++;
        if (/[A-Z]/.test(password)) score++;
        if (/[a-z]/.test(password)) score++;
        if (/[0-9]/.test(password)) score++;
        if (/[^A-Za-z0-9]/.test(password)) score++;

        if (score <= 2) {
            return { text: 'Weak', color: 'bg-red-500', width: '33%' };
        }

        if (score <= 4) {
            return { text: 'Medium', color: 'bg-yellow-500', width: '66%' };
        }

        return { text: 'Strong', color: 'bg-green-500', width: '100%' };
    },

    validatePassword(password) {
        if (!password) {
            return 'New password is required.';
        }

        if (password.length < 8) {
            return 'Password must be at least 8 characters.';
        }

        const hasUpper = /[A-Z]/.test(password);
        const hasLower = /[a-z]/.test(password);
        const hasNumber = /[0-9]/.test(password);
        const hasSymbol = /[^A-Za-z0-9]/.test(password);

        if (!(hasUpper && hasLower && hasNumber && hasSymbol)) {
            return 'Use uppercase, lowercase, number and symbol for a stronger password.';
        }

        return '';
    },

    async copyToClipboard(text) {
        try {
            await navigator.clipboard.writeText(text);
            return true;
        } catch (error) {
            return false;
        }
    }
};
