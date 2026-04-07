<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AloraMeet - Meeting Orchestration Platform</title>
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
            color: #fff;
        }
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 50px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
        }
        .logo {
            font-size: 1.8rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .logo-icon {
            width: 40px;
            height: 40px;
            background: white;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        .nav-links {
            display: flex;
            gap: 30px;
        }
        .nav-links a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: opacity 0.3s;
        }
        .nav-links a:hover {
            opacity: 0.8;
        }
        .btn {
            padding: 12px 30px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            font-size: 1rem;
        }
        .btn-primary {
            background: white;
            color: #667eea;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
        .btn-secondary {
            background: transparent;
            color: white;
            border: 2px solid white;
        }
        .btn-secondary:hover {
            background: white;
            color: #667eea;
        }
        .hero {
            text-align: center;
            padding: 100px 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .hero h1 {
            font-size: 4rem;
            margin-bottom: 20px;
            font-weight: 800;
            line-height: 1.2;
        }
        .hero p {
            font-size: 1.5rem;
            margin-bottom: 40px;
            opacity: 0.95;
        }
        .hero-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .features {
            max-width: 1200px;
            margin: 80px auto;
            padding: 0 20px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }
        .feature-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 40px;
            border-radius: 16px;
            transition: transform 0.3s;
        }
        .feature-card:hover {
            transform: translateY(-5px);
        }
        .feature-icon {
            font-size: 3rem;
            margin-bottom: 20px;
        }
        .feature-card h3 {
            font-size: 1.5rem;
            margin-bottom: 15px;
        }
        .feature-card p {
            opacity: 0.9;
            line-height: 1.6;
        }
        .footer {
            text-align: center;
            padding: 40px 20px;
            margin-top: 100px;
            background: rgba(0, 0, 0, 0.2);
        }
        @media (max-width: 768px) {
            .navbar {
                padding: 20px;
                flex-direction: column;
                gap: 20px;
            }
            .hero h1 {
                font-size: 2.5rem;
            }
            .hero p {
                font-size: 1.2rem;
            }
            .hero-buttons {
                flex-direction: column;
                align-items: center;
            }
            .btn {
                width: 100%;
                max-width: 300px;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="logo">
            <div class="logo-icon">🎥</div>
            <span>AloraMeet</span>
        </div>
        <div class="nav-links">
            @auth
                <a href="{{ url('/dashboard') }}">Dashboard</a>
                <a href="{{ url('/dashboard/my-meetings') }}">My Meetings</a>
            @else
                <a href="{{ route('tyro-login.login') }}">Login</a>
                <a href="{{ route('tyro-login.register') }}" class="btn btn-primary">Get Started</a>
            @endauth
        </div>
    </nav>

    <div class="hero">
        <h1>Professional Meeting<br>Orchestration</h1>
        <p>Schedule, manage, and host secure video meetings with enterprise-grade access control powered by Jitsi Meet</p>
        <div class="hero-buttons">
            @auth
                <a href="{{ url('/dashboard/create-meeting') }}" class="btn btn-primary">Create Meeting</a>
                <a href="{{ url('/dashboard/my-meetings') }}" class="btn btn-secondary">View Meetings</a>
            @else
                <a href="{{ route('tyro-login.register') }}" class="btn btn-primary">Start Free Trial</a>
                <a href="{{ route('tyro-login.login') }}" class="btn btn-secondary">Login</a>
            @endauth
        </div>
    </div>

    <div class="features">
        <div class="feature-card">
            <div class="feature-icon">📅</div>
            <h3>Smart Scheduling</h3>
            <p>Create single or recurring meetings with timezone support. Set up join windows and control exactly when participants can access your meetings.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">🔐</div>
            <h3>Access Control</h3>
            <p>Enterprise-grade security with role-based permissions. JWT authentication ensures only authorized participants can join your meetings.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">👥</div>
            <h3>Team Management</h3>
            <p>Organize your teams with multi-tenant architecture. Assign roles, manage permissions, and track meeting participation effortlessly.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">📊</div>
            <h3>Audit Trail</h3>
            <p>Complete visibility into meeting activities. Track who joined, when they joined, and all meeting lifecycle events for compliance.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">📧</div>
            <h3>Notifications</h3>
            <p>Automated email invitations and reminders. Generate calendar files (.ics) so participants never miss an important meeting.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">⚡</div>
            <h3>Jitsi Powered</h3>
            <p>Built on Jitsi Meet's robust video infrastructure. Get enterprise features with open-source reliability and flexibility.</p>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; 2026 AloraMeet. Powered by Jitsi Meet & Laravel.</p>
        <p style="margin-top: 10px; opacity: 0.8;">Professional Meeting Orchestration Platform</p>
    </footer>
</body>
</html>
