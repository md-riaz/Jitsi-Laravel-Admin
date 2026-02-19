<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jitsi Admin - Professional Meeting Management</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #ffffff;
            color: #1a1a1a;
            line-height: 1.6;
        }
        
        .navbar {
            background: #ffffff;
            border-bottom: 1px solid #e5e7eb;
            padding: 1rem 2rem;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }
        
        .nav-container {
            max-width: 1280px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 1.5rem;
            font-weight: 600;
            color: #1a1a1a;
            text-decoration: none;
        }
        
        .logo-icon {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.25rem;
        }
        
        .nav-links {
            display: flex;
            gap: 2rem;
            align-items: center;
        }
        
        .nav-links a {
            color: #6b7280;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }
        
        .nav-links a:hover {
            color: #3b82f6;
        }
        
        .btn {
            padding: 0.625rem 1.25rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
            font-size: 0.875rem;
            display: inline-block;
        }
        
        .btn-primary {
            background: #3b82f6;
            color: white;
        }
        
        .btn-primary:hover {
            background: #2563eb;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }
        
        .btn-secondary {
            background: transparent;
            color: #3b82f6;
            border: 1px solid #e5e7eb;
        }
        
        .btn-secondary:hover {
            background: #f9fafb;
            border-color: #3b82f6;
        }
        
        .hero {
            text-align: center;
            padding: 5rem 2rem 4rem;
            max-width: 1280px;
            margin: 0 auto;
        }
        
        .hero-badge {
            display: inline-block;
            background: #eff6ff;
            color: #3b82f6;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
        }
        
        .hero h1 {
            font-size: 3.5rem;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 1.5rem;
            line-height: 1.2;
            letter-spacing: -0.02em;
        }
        
        .hero p {
            font-size: 1.25rem;
            color: #6b7280;
            margin-bottom: 2.5rem;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .hero-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .features {
            max-width: 1280px;
            margin: 4rem auto;
            padding: 0 2rem 4rem;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 2rem;
        }
        
        .feature-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            padding: 2rem;
            border-radius: 12px;
            transition: all 0.2s;
        }
        
        .feature-card:hover {
            border-color: #3b82f6;
            box-shadow: 0 10px 30px rgba(59, 130, 246, 0.1);
            transform: translateY(-2px);
        }
        
        .feature-icon {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1.25rem;
        }
        
        .feature-card h3 {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 0.75rem;
        }
        
        .feature-card p {
            color: #6b7280;
            font-size: 0.9375rem;
            line-height: 1.7;
        }
        
        .stats-section {
            background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
            padding: 4rem 2rem;
            margin: 4rem 0;
        }
        
        .stats-container {
            max-width: 1280px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 3rem;
            text-align: center;
        }
        
        .stat h4 {
            font-size: 2.5rem;
            font-weight: 700;
            color: #3b82f6;
            margin-bottom: 0.5rem;
        }
        
        .stat p {
            color: #6b7280;
            font-size: 1rem;
        }
        
        .footer {
            background: #f9fafb;
            border-top: 1px solid #e5e7eb;
            padding: 3rem 2rem;
            margin-top: 4rem;
        }
        
        .footer-content {
            max-width: 1280px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 3rem;
        }
        
        .footer-section h5 {
            font-size: 1rem;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 1rem;
        }
        
        .footer-section ul {
            list-style: none;
        }
        
        .footer-section ul li {
            margin-bottom: 0.5rem;
        }
        
        .footer-section a {
            color: #6b7280;
            text-decoration: none;
            transition: color 0.2s;
        }
        
        .footer-section a:hover {
            color: #3b82f6;
        }
        
        .footer-bottom {
            max-width: 1280px;
            margin: 2rem auto 0;
            padding-top: 2rem;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            color: #6b7280;
            font-size: 0.875rem;
        }
        
        @media (max-width: 768px) {
            .navbar {
                padding: 1rem;
            }
            
            .nav-container {
                flex-direction: column;
                gap: 1rem;
            }
            
            .hero h1 {
                font-size: 2.5rem;
            }
            
            .hero p {
                font-size: 1.125rem;
            }
            
            .hero-buttons {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                text-align: center;
            }
            
            .features {
                grid-template-columns: 1fr;
            }
            
            .stats-container {
                grid-template-columns: 1fr;
                gap: 2rem;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <a href="/" class="logo">
                <div class="logo-icon">📅</div>
                <span>Jitsi Admin</span>
            </a>
            <div class="nav-links">
                @auth
                    <a href="{{ url('/dashboard') }}">Dashboard</a>
                    <a href="{{ url('/dashboard/my-meetings') }}">My Meetings</a>
                    <a href="{{ url('/dashboard/create-meeting') }}" class="btn btn-primary">New Meeting</a>
                @else
                    <a href="{{ route('tyro-login.login') }}">Login</a>
                    <a href="{{ route('register') }}" class="btn btn-primary">Get Started</a>
                @endauth
            </div>
        </div>
    </nav>

    <div class="hero">
        <div class="hero-badge">🚀 Enterprise-Ready Meeting Platform</div>
        <h1>Professional Meeting<br>Management Made Simple</h1>
        <p>Secure, scalable video conferencing with enterprise-grade access control. Built on Jitsi Meet with powerful scheduling and management tools.</p>
        <div class="hero-buttons">
            @auth
                <a href="{{ url('/dashboard/create-meeting') }}" class="btn btn-primary">Create Meeting</a>
                <a href="{{ url('/dashboard/my-meetings') }}" class="btn btn-secondary">View Meetings</a>
            @else
                <a href="{{ route('register') }}" class="btn btn-primary">Start Free Trial</a>
                <a href="{{ route('tyro-login.login') }}" class="btn btn-secondary">Sign In</a>
            @endauth
        </div>
    </div>

    <div class="stats-section">
        <div class="stats-container">
            <div class="stat">
                <h4>100%</h4>
                <p>Open Source</p>
            </div>
            <div class="stat">
                <h4>Secure</h4>
                <p>JWT Authentication</p>
            </div>
            <div class="stat">
                <h4>Scalable</h4>
                <p>Multi-Tenant Ready</p>
            </div>
            <div class="stat">
                <h4>Reliable</h4>
                <p>Jitsi Powered</p>
            </div>
        </div>
    </div>

    <div class="features">
        <div class="feature-card">
            <div class="feature-icon">📅</div>
            <h3>Smart Scheduling</h3>
            <p>Create single or recurring meetings with full timezone support. Define join windows and control participant access with precision.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">🔐</div>
            <h3>Enterprise Security</h3>
            <p>Role-based access control with JWT authentication. Every meeting is protected with time-based access windows and participant validation.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">👥</div>
            <h3>Team Management</h3>
            <p>Multi-tenant architecture with flexible role assignments. Manage teams, assign moderators, and track participation across your organization.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">📊</div>
            <h3>Complete Audit Trail</h3>
            <p>Full visibility into meeting lifecycle events. Track who joined, when they joined, and all administrative actions for compliance.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">📧</div>
            <h3>Smart Notifications</h3>
            <p>Automated email invitations with calendar attachments. Reminder notifications ensure participants never miss important meetings.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">⚡</div>
            <h3>Jitsi Infrastructure</h3>
            <p>Built on Jitsi Meet's proven video platform. Get enterprise features with open-source reliability and unlimited scalability.</p>
        </div>
    </div>

    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h5>Product</h5>
                <ul>
                    <li><a href="#features">Features</a></li>
                    <li><a href="#pricing">Pricing</a></li>
                    <li><a href="#documentation">Documentation</a></li>
                    <li><a href="#api">API Reference</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h5>Company</h5>
                <ul>
                    <li><a href="#about">About Us</a></li>
                    <li><a href="#contact">Contact</a></li>
                    <li><a href="#blog">Blog</a></li>
                    <li><a href="#careers">Careers</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h5>Resources</h5>
                <ul>
                    <li><a href="#guides">User Guides</a></li>
                    <li><a href="#support">Support</a></li>
                    <li><a href="#community">Community</a></li>
                    <li><a href="#status">System Status</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h5>Legal</h5>
                <ul>
                    <li><a href="#privacy">Privacy Policy</a></li>
                    <li><a href="#terms">Terms of Service</a></li>
                    <li><a href="#security">Security</a></li>
                    <li><a href="#compliance">Compliance</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2026 Jitsi Admin. Built with Laravel & Jitsi Meet. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
