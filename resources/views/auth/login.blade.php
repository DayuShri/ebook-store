<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - eBook Store</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 420px;
            padding: 40px;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 32px;
        }
        
        .login-header h1 {
            color: #1a202c;
            font-size: 28px;
            margin-bottom: 8px;
        }
        
        .login-header p {
            color: #718096;
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 24px;
        }
        
        .form-group label {
            display: block;
            color: #2d3748;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 8px;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.2s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .btn-login:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 24px;
            font-size: 14px;
        }
        
        .alert-error {
            background: #fee;
            color: #c53030;
            border: 1px solid #fc8181;
        }
        
        .alert-success {
            background: #e6fffa;
            color: #234e52;
            border: 1px solid #81e6d9;
        }
        
        .spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
            margin-right: 8px;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .demo-credentials {
            margin-top: 24px;
            padding: 16px;
            background: #f7fafc;
            border-radius: 8px;
            font-size: 13px;
            color: #4a5568;
        }
        
        .demo-credentials strong {
            color: #2d3748;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>📚 eBook Store</h1>
            <p>Sign in to access your library</p>
        </div>
        
        <div id="alert" style="display: none;"></div>
        
        <form id="loginForm">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    placeholder="your.email@example.com"
                    required
                    autocomplete="email"
                >
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    placeholder="Enter your password"
                    required
                    autocomplete="current-password"
                >
            </div>
            
            <button type="submit" class="btn-login" id="submitBtn">
                Sign In
            </button>
        </form>
        
        <div class="demo-credentials">
            <strong>Demo Account:</strong><br>
            Email: test@example.com<br>
            Password: password123
        </div>
    </div>

    <script>
        const form = document.getElementById('loginForm');
        const submitBtn = document.getElementById('submitBtn');
        const alertDiv = document.getElementById('alert');
        
        // Check if already logged in
        if (localStorage.getItem('auth_token')) {
            window.location.href = '/library';
        }
        
        function showAlert(message, type = 'error') {
            alertDiv.className = `alert alert-${type}`;
            alertDiv.textContent = message;
            alertDiv.style.display = 'block';
            
            setTimeout(() => {
                alertDiv.style.display = 'none';
            }, 5000);
        }
        
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            
            // Disable button and show loading
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner"></span>Signing in...';
            
            try {
                const response = await fetch('/api/v1/auth/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ email, password })
                });
                
                const data = await response.json();
                console.log('Login response:', data);
                
                if (response.ok && data.data?.access_token) {
                    // Save token to localStorage
                    const accessToken = data.data.access_token;
                    
                    console.log('===== LOGIN SUCCESS =====');
                    console.log('Access Token:', accessToken);
                    console.log('Token Type:', data.data.token_type);
                    console.log('User:', data.data.user);
                    
                    // Save to localStorage
                    localStorage.setItem('auth_token', accessToken);
                    localStorage.setItem('user_name', data.data.user?.name || data.data.user?.email || 'User');
                    localStorage.setItem('user_email', data.data.user?.email || email);
                    
                    // Verify saved
                    const savedToken = localStorage.getItem('auth_token');
                    console.log('Token saved to localStorage:', savedToken);
                    console.log('Token matches:', savedToken === accessToken);
                    
                    // Test the token immediately
                    console.log('Testing token with library API...');
                    fetch('/api/v1/library', {
                        headers: {
                            'Authorization': `Bearer ${savedToken}`,
                            'Accept': 'application/json'
                        }
                    })
                    .then(r => {
                        console.log('Test API Response Status:', r.status);
                        if (r.status === 401) {
                            console.error('Token is invalid immediately after login!');
                            console.error('This is a backend issue. Token:', savedToken);
                            alert('Login successful but token is invalid. Please check backend logs.');
                            submitBtn.disabled = false;
                            submitBtn.textContent = 'Sign In';
                            return null;
                        }
                        return r.json();
                    })
                    .then(d => {
                        if (d) {
                            console.log('Test API Response Data:', d);
                            console.log('✅ Token works! Redirecting to dashboard...');
                            
                            // Force a small delay to ensure localStorage is fully written
                            setTimeout(() => {
                                window.location.href = '/dashboard';
                            }, 200);
                        }
                    })
                    .catch(e => {
                        console.error('Test API Failed:', e);
                        alert('Token test failed. Check console. Token: ' + savedToken);
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Sign In';
                    });
                } else {
                    showAlert(data.message || 'Invalid email or password');
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Sign In';
                }
            } catch (error) {
                console.error('Login error:', error);
                showAlert('Network error. Please check your connection and try again.');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Sign In';
            }
        });
    </script>
</body>
</html>
