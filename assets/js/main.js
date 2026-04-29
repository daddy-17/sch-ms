document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const usernameInput = document.getElementById('username').value;
            const passwordInput = document.getElementById('password').value;
            const btn = document.getElementById('login-btn');
            const btnText = document.getElementById('btn-text');
            const alertBox = document.getElementById('login-alert');
            
            // UI Loading state
            btn.disabled = true;
            btnText.innerHTML = '<span class="spinner"></span> Signing In...';
            alertBox.style.display = 'none';
            alertBox.className = 'alert';
            
            try {
                // Prepare form data
                const formData = new FormData();
                formData.append('username', usernameInput);
                formData.append('password', passwordInput);
                
                // Make API request to PHP backend
                const response = await fetch('api/login.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (response.ok && data.success) {
                    // Login successful
                    alertBox.textContent = 'Login successful! Redirecting...';
                    alertBox.classList.add('alert-success');
                    alertBox.style.display = 'block';
                    
                    // Simulate redirect delay for UX
                    setTimeout(() => {
                        window.location.href = 'admin/dashboard.html';
                    }, 1000);
                } else {
                    // Login failed (or rate limited)
                    throw new Error(data.error || 'Invalid credentials');
                }
            } catch (error) {
                // Show error
                alertBox.textContent = error.message;
                alertBox.classList.add('alert-danger');
                alertBox.style.display = 'block';
                
                // Reset button
                btn.disabled = false;
                btnText.textContent = 'Sign In';
            }
        });
    }
});
