// Authentication JavaScript

class AuthManager {
    constructor() {
        this.currentForm = null;
        this.init();
    }

    init() {
        this.setupFormValidation();
        this.setupPasswordStrength();
        this.setupPasswordMatch();
        this.setupThemeToggle();
        this.setupFormSubmission();
    }

    // Form Validation
    setupFormValidation() {
        const forms = document.querySelectorAll('.auth-form');
        forms.forEach(form => {
            form.addEventListener('submit', (e) => {
                if (!this.validateForm(form)) {
                    e.preventDefault();
                }
            });

            // Real-time validation
            const inputs = form.querySelectorAll('input[required]');
            inputs.forEach(input => {
                input.addEventListener('blur', () => {
                    this.validateField(input);
                });

                input.addEventListener('input', () => {
                    this.clearFieldError(input);
                });
            });
        });
    }

    validateForm(form) {
        let isValid = true;
        const inputs = form.querySelectorAll('input[required]');
        
        inputs.forEach(input => {
            if (!this.validateField(input)) {
                isValid = false;
            }
        });

        // Special validation for registration form
        if (form.id === 'register-form') {
            const password = form.querySelector('#password');
            const confirmPassword = form.querySelector('#confirm_password');
            const terms = form.querySelector('#terms');

            if (password && confirmPassword && password.value !== confirmPassword.value) {
                this.showFieldError(confirmPassword, 'Passwords do not match');
                isValid = false;
            }

            if (terms && !terms.checked) {
                this.showFieldError(terms, 'You must agree to the terms');
                isValid = false;
            }
        }

        return isValid;
    }

    validateField(input) {
        const value = input.value.trim();
        const type = input.type;
        const name = input.name;

        // Clear previous errors
        this.clearFieldError(input);

        // Required field validation
        if (input.hasAttribute('required') && !value) {
            this.showFieldError(input, 'This field is required');
            return false;
        }

        // Email validation
        if (type === 'email' && value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) {
                this.showFieldError(input, 'Please enter a valid email address');
                return false;
            }
        }

        // Username validation
        if (name === 'username' && value) {
            if (value.length < 3) {
                this.showFieldError(input, 'Username must be at least 3 characters long');
                return false;
            }
            if (!/^[a-zA-Z0-9_]+$/.test(value)) {
                this.showFieldError(input, 'Username can only contain letters, numbers, and underscores');
                return false;
            }
        }

        // Password validation
        if (name === 'password' && value) {
            if (value.length < 6) {
                this.showFieldError(input, 'Password must be at least 6 characters long');
                return false;
            }
        }

        return true;
    }

    showFieldError(input, message) {
        const inputGroup = input.closest('.input-group') || input.parentElement;
        const errorDiv = document.createElement('div');
        errorDiv.className = 'field-error';
        errorDiv.textContent = message;
        errorDiv.style.color = '#ef4444';
        errorDiv.style.fontSize = '0.75rem';
        errorDiv.style.marginTop = '0.25rem';

        inputGroup.appendChild(errorDiv);
        input.style.borderColor = '#ef4444';
    }

    clearFieldError(input) {
        const inputGroup = input.closest('.input-group') || input.parentElement;
        const errorDiv = inputGroup.querySelector('.field-error');
        
        if (errorDiv) {
            errorDiv.remove();
        }
        
        input.style.borderColor = '';
    }

    // Password Strength
    setupPasswordStrength() {
        const passwordInput = document.getElementById('password');
        if (!passwordInput) return;

        passwordInput.addEventListener('input', () => {
            this.checkPasswordStrength(passwordInput.value);
        });
    }

    checkPasswordStrength(password) {
        const strengthFill = document.getElementById('strength-fill');
        const strengthText = document.getElementById('strength-text');
        
        if (!strengthFill || !strengthText) return;

        let score = 0;
        let feedback = [];

        // Length check
        if (password.length >= 8) {
            score += 25;
        } else {
            feedback.push('At least 8 characters');
        }

        // Lowercase check
        if (/[a-z]/.test(password)) {
            score += 25;
        } else {
            feedback.push('Lowercase letter');
        }

        // Uppercase check
        if (/[A-Z]/.test(password)) {
            score += 25;
        } else {
            feedback.push('Uppercase letter');
        }

        // Number check
        if (/\d/.test(password)) {
            score += 25;
        } else {
            feedback.push('Number');
        }

        // Special character check (bonus)
        if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) {
            score += 10;
        }

        // Update UI
        strengthFill.className = 'strength-fill';
        
        if (score < 25) {
            strengthFill.classList.add('weak');
            strengthText.textContent = 'Very Weak';
        } else if (score < 50) {
            strengthFill.classList.add('weak');
            strengthText.textContent = 'Weak';
        } else if (score < 75) {
            strengthFill.classList.add('fair');
            strengthText.textContent = 'Fair';
        } else if (score < 100) {
            strengthFill.classList.add('good');
            strengthText.textContent = 'Good';
        } else {
            strengthFill.classList.add('strong');
            strengthText.textContent = 'Strong';
        }

        // Show feedback for weak passwords
        if (score < 75 && feedback.length > 0) {
            strengthText.textContent += ` - Add: ${feedback.slice(0, 2).join(', ')}`;
        }
    }

    // Password Match
    setupPasswordMatch() {
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirm_password');
        
        if (!passwordInput || !confirmPasswordInput) return;

        const checkMatch = () => {
            const password = passwordInput.value;
            const confirmPassword = confirmPasswordInput.value;
            const matchDiv = document.getElementById('password-match');
            
            if (!matchDiv) return;

            if (confirmPassword.length === 0) {
                matchDiv.classList.remove('show', 'match', 'mismatch');
                return;
            }

            matchDiv.classList.add('show');
            
            if (password === confirmPassword) {
                matchDiv.classList.add('match');
                matchDiv.classList.remove('mismatch');
                matchDiv.querySelector('span').textContent = 'Passwords match';
            } else {
                matchDiv.classList.add('mismatch');
                matchDiv.classList.remove('match');
                matchDiv.querySelector('span').textContent = 'Passwords do not match';
            }
        };

        passwordInput.addEventListener('input', checkMatch);
        confirmPasswordInput.addEventListener('input', checkMatch);
    }

    // Theme Toggle
    setupThemeToggle() {
        const themeBtn = document.getElementById('theme-toggle');
        if (themeBtn) {
            themeBtn.addEventListener('click', () => {
                this.toggleTheme();
            });
        }
    }

    toggleTheme() {
        const currentTheme = localStorage.getItem('theme') || 'light';
        const newTheme = currentTheme === 'light' ? 'dark' : 'light';
        
        localStorage.setItem('theme', newTheme);
        document.documentElement.setAttribute('data-theme', newTheme);
        
        const themeBtn = document.getElementById('theme-toggle');
        if (themeBtn) {
            const icon = themeBtn.querySelector('i');
            icon.className = newTheme === 'light' ? 'fas fa-moon' : 'fas fa-sun';
        }
    }

    // Form Submission
    setupFormSubmission() {
        const forms = document.querySelectorAll('.auth-form');
        forms.forEach(form => {
            form.addEventListener('submit', (e) => {
                if (this.validateForm(form)) {
                    this.showLoading();
                }
            });
        });
    }

    showLoading() {
        const overlay = document.getElementById('loading-overlay');
        if (overlay) {
            overlay.classList.add('active');
        }
    }

    hideLoading() {
        const overlay = document.getElementById('loading-overlay');
        if (overlay) {
            overlay.classList.remove('active');
        }
    }

    // Social Login Handlers
    setupSocialLogin() {
        const googleBtn = document.querySelector('.btn-google');
        const facebookBtn = document.querySelector('.btn-facebook');

        if (googleBtn) {
            googleBtn.addEventListener('click', () => {
                this.handleSocialLogin('google');
            });
        }

        if (facebookBtn) {
            facebookBtn.addEventListener('click', () => {
                this.handleSocialLogin('facebook');
            });
        }
    }

    handleSocialLogin(provider) {
        this.showLoading();
        
        // Simulate social login
        setTimeout(() => {
            this.hideLoading();
            this.showNotification(`${provider} login is not implemented yet. Please use email/password.`, 'info');
        }, 2000);
    }

    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 5000);
    }
}

// Initialize authentication
document.addEventListener('DOMContentLoaded', () => {
    window.authManager = new AuthManager();
});

// Global functions for HTML onclick handlers
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const toggleBtn = input.parentElement.querySelector('.password-toggle');
    const icon = toggleBtn.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'fas fa-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'fas fa-eye';
    }
}

// Add CSS for notifications
const notificationCSS = `
    .notification {
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 1rem 1.5rem;
        border-radius: 0.5rem;
        color: white;
        z-index: 10000;
        max-width: 300px;
        animation: slideInRight 0.3s ease;
    }

    .notification-info {
        background: #3b82f6;
    }

    .notification-success {
        background: #10b981;
    }

    .notification-error {
        background: #ef4444;
    }

    .notification-warning {
        background: #f59e0b;
    }

    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    .field-error {
        color: #ef4444;
        font-size: 0.75rem;
        margin-top: 0.25rem;
    }
`;

// Inject notification CSS
const style = document.createElement('style');
style.textContent = notificationCSS;
document.head.appendChild(style); 