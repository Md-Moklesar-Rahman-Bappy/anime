<!-- LOGIN MODAL -->
<div class="modal-overlay" id="loginModal">
    <div class="modal-container">
        <button class="modal-close" id="loginModalClose"><i class="fas fa-times"></i></button>
        <div class="modal-header">
            <h2>Welcome Back</h2>
            <p>Sign in to continue watching</p>
        </div>
        <form class="modal-form" action="<?= url('auth/login') ?>" method="POST">
            <div class="form-group">
                <label for="loginEmail"><i class="fas fa-envelope"></i> Email</label>
                <input type="email" id="loginEmail" name="email" class="form-input" placeholder="your@email.com" required>
            </div>
            <div class="form-group">
                <label for="loginPassword"><i class="fas fa-lock"></i> Password</label>
                <input type="password" id="loginPassword" name="password" class="form-input" placeholder="••••••••" required>
            </div>
            <div class="form-options">
                <label class="checkbox-label">
                    <input type="checkbox" name="remember"> Remember me
                </label>
                <button type="button" class="link-btn" id="forgotLink">Forgot password?</button>
            </div>
            <button type="submit" class="btn btn-primary btn-full">Sign In</button>
        </form>
        <div class="modal-footer">
            <p>Don't have an account? <button type="button" class="link-btn" id="registerLink">Sign up</button></p>
        </div>
    </div>
</div>

<!-- REGISTER MODAL -->
<div class="modal-overlay" id="registerModal">
    <div class="modal-container">
        <button class="modal-close" id="registerModalClose"><i class="fas fa-times"></i></button>
        <div class="modal-header">
            <h2>Create Account</h2>
            <p>Join Anikoto for free</p>
        </div>
        <form class="modal-form" action="<?= url('auth/register') ?>" method="POST">
            <div class="form-group">
                <label for="regUsername"><i class="fas fa-user"></i> Username</label>
                <input type="text" id="regUsername" name="username" class="form-input" placeholder="cooluser123" required minlength="3">
            </div>
            <div class="form-group">
                <label for="regEmail"><i class="fas fa-envelope"></i> Email</label>
                <input type="email" id="regEmail" name="email" class="form-input" placeholder="your@email.com" required>
            </div>
            <div class="form-group">
                <label for="regPassword"><i class="fas fa-lock"></i> Password</label>
                <input type="password" id="regPassword" name="password" class="form-input" placeholder="••••••••" required minlength="6">
            </div>
            <button type="submit" class="btn btn-primary btn-full">Create Account</button>
        </form>
        <div class="modal-footer">
            <p>Already have an account? <button type="button" class="link-btn" id="loginLink">Sign in</button></p>
        </div>
    </div>
</div>

<!-- PASSWORD RESET MODAL -->
<div class="modal-overlay" id="resetModal">
    <div class="modal-container">
        <button class="modal-close" id="resetModalClose"><i class="fas fa-times"></i></button>
        <div class="modal-header">
            <h2>Reset Password</h2>
            <p>Enter your email to receive a reset link</p>
        </div>
        <form class="modal-form" action="<?= url('auth/reset') ?>" method="POST">
            <div class="form-group">
                <label for="resetEmail"><i class="fas fa-envelope"></i> Email</label>
                <input type="email" id="resetEmail" name="email" class="form-input" placeholder="your@email.com" required>
            </div>
            <button type="submit" class="btn btn-primary btn-full">Send Reset Link</button>
        </form>
        <div class="modal-footer">
            <p>Remember your password? <button type="button" class="link-btn" id="loginLink2">Sign in</button></p>
        </div>
    </div>
</div>
