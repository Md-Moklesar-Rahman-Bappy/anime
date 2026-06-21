export function registerForm() {
    return {
        password: '',
        passwordConfirm: '',
        showPassword: false,

        // ----- STRENGTH CALCULATION -----
        get strengthScore() {
            const pwd = this.password;
            let score = 0;

            if (pwd.length >= 8)  score++;
            if (pwd.length >= 12) score++;
            if (/[A-Z]/.test(pwd)) score++;
            if (/[0-9]/.test(pwd)) score++;
            if (/[^A-Za-z0-9]/.test(pwd)) score++;

            return Math.min(score, 5);
        },

        get strengthPercent() {
            return (this.strengthScore / 5) * 100;
        },

        get strengthLabel() {
            const labels = ['Very weak', 'Weak', 'Fair', 'Good', 'Strong', 'Very strong'];
            return this.password ? labels[this.strengthScore] : '';
        },

        get strengthColor() {
            return {
                'bg-red-500':     this.strengthScore <= 1,
                'bg-orange-500':  this.strengthScore === 2,
                'bg-yellow-500':  this.strengthScore === 3,
                'bg-emerald-500': this.strengthScore === 4,
                'bg-green-500':   this.strengthScore === 5,
            };
        },

        get strengthTextColor() {
            return {
                'text-red-400':     this.strengthScore <= 1,
                'text-orange-400':  this.strengthScore === 2,
                'text-yellow-400':  this.strengthScore === 3,
                'text-emerald-400': this.strengthScore === 4,
                'text-green-400':   this.strengthScore === 5,
            };
        },
    };
}