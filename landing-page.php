<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="SMM Telegram Bot - Connect clients with workers for social media engagement campaigns">
    <meta name="keywords" content="Telegram Bot, SMM, Social Media Marketing, PTC, Engagement">
    <title>SMM Telegram Bot - Social Media Engagement Platform</title>
    
    <!-- Font Awesome from Cloudflare CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --telegram-blue: #0088cc;
            --telegram-dark: #1a1a2e;
            --telegram-darker: #0f0f1e;
            --telegram-light-blue: #00b4d8;
            --accent-cyan: #00f5ff;
            --text-primary: #ffffff;
            --text-secondary: #b0b0b0;
            --gradient-1: linear-gradient(135deg, #0088cc 0%, #005f8f 100%);
            --gradient-2: linear-gradient(135deg, #00b4d8 0%, #0088cc 100%);
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--telegram-darker);
            color: var(--text-primary);
            line-height: 1.6;
            overflow-x: hidden;
        }

        /* Animated Background */
        .bg-animation {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            background: var(--telegram-darker);
            overflow: hidden;
        }

        .bg-animation::before {
            content: '';
            position: absolute;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle at 20% 50%, rgba(0, 136, 204, 0.1) 0%, transparent 50%),
                        radial-gradient(circle at 80% 80%, rgba(0, 180, 216, 0.1) 0%, transparent 50%);
            animation: float 20s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            33% { transform: translate(30px, -30px) rotate(120deg); }
            66% { transform: translate(-20px, 20px) rotate(240deg); }
        }

        /* Navigation */
        nav {
            position: fixed;
            top: 0;
            width: 100%;
            background: rgba(26, 26, 46, 0.95);
            backdrop-filter: blur(10px);
            padding: 1rem 0;
            z-index: 1000;
            border-bottom: 1px solid rgba(0, 136, 204, 0.3);
            transition: all 0.3s ease;
        }

        nav.scrolled {
            padding: 0.5rem 0;
            box-shadow: 0 4px 20px rgba(0, 136, 204, 0.2);
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 2rem;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: bold;
            background: var(--gradient-2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .logo i {
            background: var(--gradient-2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
            list-style: none;
        }

        .nav-links a {
            color: var(--text-secondary);
            text-decoration: none;
            transition: color 0.3s ease;
            font-weight: 500;
        }

        .nav-links a:hover {
            color: var(--telegram-blue);
        }

        .mobile-toggle {
            display: none;
            flex-direction: column;
            gap: 5px;
            cursor: pointer;
        }

        .mobile-toggle span {
            width: 25px;
            height: 3px;
            background: var(--telegram-blue);
            transition: all 0.3s ease;
        }

        /* Hero Section */
        .hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 6rem 2rem 2rem;
            position: relative;
        }

        .hero-content {
            max-width: 800px;
            opacity: 0;
            transform: translateY(30px);
            animation: fadeInUp 1s ease forwards;
        }

        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .hero h1 {
            font-size: 3.5rem;
            margin-bottom: 1rem;
            background: var(--gradient-2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1.2;
        }

        .hero .subtitle {
            font-size: 1.3rem;
            color: var(--text-secondary);
            margin-bottom: 2rem;
        }

        .cta-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            padding: 1rem 2.5rem;
            border: none;
            border-radius: 50px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: var(--gradient-1);
            color: white;
            box-shadow: 0 4px 15px rgba(0, 136, 204, 0.4);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 136, 204, 0.6);
        }

        .btn-secondary {
            background: transparent;
            color: var(--telegram-blue);
            border: 2px solid var(--telegram-blue);
        }

        .btn-secondary:hover {
            background: var(--telegram-blue);
            color: white;
        }

        /* Section Styles */
        section {
            padding: 5rem 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .section-title {
            text-align: center;
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: var(--telegram-light-blue);
        }

        .section-subtitle {
            text-align: center;
            color: var(--text-secondary);
            margin-bottom: 3rem;
            font-size: 1.1rem;
        }

        /* Features Grid */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }

        .feature-card {
            background: rgba(26, 26, 46, 0.6);
            border: 1px solid rgba(0, 136, 204, 0.3);
            border-radius: 15px;
            padding: 2rem;
            transition: all 0.3s ease;
            opacity: 0;
            transform: translateY(30px);
        }

        .feature-card.visible {
            animation: fadeInUp 0.6s ease forwards;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            border-color: var(--telegram-blue);
            box-shadow: 0 10px 30px rgba(0, 136, 204, 0.3);
        }

        .feature-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            display: block;
            color: var(--telegram-light-blue);
        }

        .feature-icon i {
            display: inline-block;
        }

        .feature-card h3 {
            color: var(--telegram-light-blue);
            margin-bottom: 1rem;
            font-size: 1.4rem;
        }

        .feature-card p {
            color: var(--text-secondary);
            line-height: 1.8;
        }

        /* Roles Section */
        .roles-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }

        .role-card {
            background: var(--gradient-1);
            border-radius: 20px;
            padding: 2.5rem;
            text-align: center;
            transition: all 0.3s ease;
            opacity: 0;
            transform: translateY(30px);
        }

        .role-card.visible {
            animation: fadeInUp 0.6s ease forwards;
        }

        .role-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 15px 40px rgba(0, 136, 204, 0.5);
        }

        .role-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }

        .role-icon i {
            display: inline-block;
        }

        .role-card h3 {
            font-size: 1.8rem;
            margin-bottom: 1rem;
        }

        .role-card ul {
            list-style: none;
            text-align: left;
            margin-top: 1.5rem;
        }

        .role-card li {
            padding: 0.5rem 0;
            padding-left: 1.5rem;
            position: relative;
        }

        .role-card li::before {
            content: '✓';
            position: absolute;
            left: 0;
            color: var(--accent-cyan);
            font-weight: bold;
        }

        /* Tech Stack */
        .tech-stack {
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
            justify-content: center;
            margin-top: 3rem;
        }

        .tech-badge {
            background: rgba(0, 136, 204, 0.2);
            border: 2px solid var(--telegram-blue);
            border-radius: 50px;
            padding: 1rem 2rem;
            font-weight: 600;
            transition: all 0.3s ease;
            opacity: 0;
            transform: scale(0.8);
        }

        .tech-badge.visible {
            animation: popIn 0.5s ease forwards;
        }

        @keyframes popIn {
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .tech-badge:hover {
            background: var(--telegram-blue);
            transform: scale(1.1);
        }

        /* Stats Section */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }

        .stat-card {
            text-align: center;
            padding: 2rem;
            background: rgba(0, 136, 204, 0.1);
            border-radius: 15px;
            border: 1px solid rgba(0, 136, 204, 0.3);
            opacity: 0;
            transform: translateY(30px);
        }

        .stat-card.visible {
            animation: fadeInUp 0.6s ease forwards;
        }

        .stat-number {
            font-size: 3rem;
            font-weight: bold;
            color: var(--telegram-light-blue);
            display: block;
        }

        .stat-label {
            color: var(--text-secondary);
            margin-top: 0.5rem;
        }

        /* CTA Section */
        .cta-section {
            background: var(--gradient-1);
            border-radius: 20px;
            padding: 4rem 2rem;
            text-align: center;
            margin: 5rem auto;
            max-width: 1000px;
        }

        .cta-section h2 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .cta-section p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }

        /* Footer */
        footer {
            background: var(--telegram-dark);
            padding: 3rem 2rem 1rem;
            text-align: center;
            border-top: 1px solid rgba(0, 136, 204, 0.3);
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
        }

        .footer-links {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }

        .footer-links a {
            color: var(--text-secondary);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer-links a:hover {
            color: var(--telegram-blue);
        }

        .footer-bottom {
            padding-top: 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--text-secondary);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .nav-links {
                display: none;
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: var(--telegram-dark);
                flex-direction: column;
                padding: 1rem;
                gap: 0;
            }

            .nav-links.active {
                display: flex;
            }

            .nav-links a {
                padding: 1rem;
                border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            }

            .mobile-toggle {
                display: flex;
            }

            .hero h1 {
                font-size: 2.5rem;
            }

            .hero .subtitle {
                font-size: 1.1rem;
            }

            .section-title {
                font-size: 2rem;
            }

            .cta-buttons {
                flex-direction: column;
                align-items: stretch;
            }

            .btn {
                width: 100%;
            }
        }

        @media (max-width: 480px) {
            .hero h1 {
                font-size: 2rem;
            }

            section {
                padding: 3rem 1rem;
            }

            .feature-card,
            .role-card {
                padding: 1.5rem;
            }
        }

        /* Scroll Progress Bar */
        .scroll-progress {
            position: fixed;
            top: 0;
            left: 0;
            height: 3px;
            background: var(--gradient-2);
            z-index: 1001;
            transition: width 0.1s ease;
        }

        /* Loading Animation */
        .loading {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: var(--telegram-darker);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            transition: opacity 0.5s ease;
        }

        .loading.hide {
            opacity: 0;
            pointer-events: none;
        }

        .loader {
            width: 50px;
            height: 50px;
            border: 3px solid rgba(0, 136, 204, 0.3);
            border-top: 3px solid var(--telegram-blue);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <!-- Loading Screen -->
    <div class="loading" id="loading">
        <div class="loader"></div>
    </div>

    <!-- Scroll Progress Bar -->
    <div class="scroll-progress" id="scrollProgress"></div>

    <!-- Animated Background -->
    <div class="bg-animation"></div>

    <!-- Navigation -->
    <nav id="navbar">
        <div class="nav-container">
            <div class="logo"><i class="fas fa-bolt"></i> SMM Bot</div>
            <div class="mobile-toggle" id="mobileToggle">
                <span></span>
                <span></span>
                <span></span>
            </div>
            <ul class="nav-links" id="navLinks">
                <li><a href="#home">Home</a></li>
                <li><a href="#features">Features</a></li>
                <li><a href="#roles">Roles</a></li>
                <li><a href="#tech">Technology</a></li>
                <li><a href="#contact">Contact</a></li>
            </ul>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero" id="home">
        <div class="hero-content">
            <h1>Social Media Engagement, Reimagined</h1>
            <p class="subtitle">
                Connect clients with skilled workers through our intelligent Telegram bot platform. 
                Streamline your social media campaigns with automated task management and proof verification.
            </p>
            <div class="cta-buttons">
                <a href="#features" class="btn btn-primary">Explore Features</a>
                <a href="#contact" class="btn btn-secondary">Get Started</a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features">
        <h2 class="section-title">Powerful Features</h2>
        <p class="section-subtitle">Everything you need to manage social media engagement campaigns</p>
        
        <div class="features-grid">
            <div class="feature-card" data-animate>
                <span class="feature-icon"><i class="fab fa-telegram"></i></span>
                <h3>Telegram Integration</h3>
                <p>Seamless integration with Telegram Bot API. All interactions happen directly within Telegram for maximum convenience and accessibility.</p>
            </div>

            <div class="feature-card" data-animate>
                <span class="feature-icon"><i class="fas fa-bullseye"></i></span>
                <h3>Campaign Management</h3>
                <p>Create and manage multi-platform campaigns. Support for Instagram, TikTok, Facebook, and more. Set targets, prices, and monitor progress in real-time.</p>
            </div>

            <div class="feature-card" data-animate>
                <span class="feature-icon"><i class="fas fa-wallet"></i></span>
                <h3>Wallet System</h3>
                <p>Integrated wallet with real-time balance tracking. Secure transaction logs for deposits, rewards, and withdrawals with complete transparency.</p>
            </div>

            <div class="feature-card" data-animate>
                <span class="feature-icon"><i class="fas fa-check-circle"></i></span>
                <h3>Proof Verification</h3>
                <p>Manual verification system via screenshot uploads. Admin review process ensures quality and authenticity of all completed tasks.</p>
            </div>

            <div class="feature-card" data-animate>
                <span class="feature-icon"><i class="fas fa-users"></i></span>
                <h3>Account Management</h3>
                <p>Workers can register and validate their social media accounts. Automated username validation ensures legitimate task completion.</p>
            </div>

            <div class="feature-card" data-animate>
                <span class="feature-icon"><i class="fas fa-chart-bar"></i></span>
                <h3>Analytics & Logs</h3>
                <p>Comprehensive audit logs for all admin actions. Transaction history, activity tracking, and error monitoring built-in.</p>
            </div>
        </div>
    </section>

    <!-- Roles Section -->
    <section id="roles">
        <h2 class="section-title">Three Powerful Roles</h2>
        <p class="section-subtitle">Designed for clients, workers, and administrators</p>
        
        <div class="roles-container">
            <div class="role-card" data-animate>
                <div class="role-icon"><i class="fas fa-briefcase"></i></div>
                <h3>Client</h3>
                <p>Create and manage engagement campaigns</p>
                <ul>
                    <li>Create campaigns for any social platform</li>
                    <li>Set target numbers and pricing</li>
                    <li>Monitor campaign progress</li>
                    <li>Top-up wallet balance</li>
                    <li>View detailed analytics</li>
                </ul>
            </div>

            <div class="role-card" data-animate>
                <div class="role-icon"><i class="fas fa-bolt"></i></div>
                <h3>Worker</h3>
                <p>Complete tasks and earn rewards</p>
                <ul>
                    <li>Browse available tasks</li>
                    <li>Upload screenshot proofs</li>
                    <li>Track earnings in real-time</li>
                    <li>Request withdrawals</li>
                    <li>Manage social accounts</li>
                </ul>
            </div>

            <div class="role-card" data-animate>
                <div class="role-icon"><i class="fas fa-crown"></i></div>
                <h3>Admin</h3>
                <p>Moderate and manage the platform</p>
                <ul>
                    <li>Review and verify proofs</li>
                    <li>Approve/reject submissions</li>
                    <li>Process top-ups and withdrawals</li>
                    <li>Moderate users</li>
                    <li>Access audit logs</li>
                </ul>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section>
        <h2 class="section-title">By The Numbers</h2>
        <div class="stats-grid">
            <div class="stat-card" data-animate>
                <span class="stat-number">15+</span>
                <span class="stat-label">Database Tables</span>
            </div>
            <div class="stat-card" data-animate>
                <span class="stat-number">50+</span>
                <span class="stat-label">Reply Handlers</span>
            </div>
            <div class="stat-card" data-animate>
                <span class="stat-number">3</span>
                <span class="stat-label">User Roles</span>
            </div>
            <div class="stat-card" data-animate>
                <span class="stat-number">100%</span>
                <span class="stat-label">PHP Native</span>
            </div>
        </div>
    </section>

    <!-- Technology Section -->
    <section id="tech">
        <h2 class="section-title">Built With Modern Technology</h2>
        <p class="section-subtitle">Robust, scalable, and efficient tech stack</p>
        
        <div class="tech-stack">
            <div class="tech-badge" data-animate>PHP 7.4+</div>
            <div class="tech-badge" data-animate>MySQL/MariaDB</div>
            <div class="tech-badge" data-animate>PDO</div>
            <div class="tech-badge" data-animate>Telegram Bot API</div>
            <div class="tech-badge" data-animate>Webhook Method</div>
            <div class="tech-badge" data-animate>HTML5</div>
            <div class="tech-badge" data-animate>CSS3</div>
            <div class="tech-badge" data-animate>JavaScript</div>
        </div>
    </section>

    <!-- CTA Section -->
    <section>
        <div class="cta-section">
            <h2>Ready to Get Started?</h2>
            <p>Join our platform and experience the future of social media engagement management</p>
            <a href="#contact" class="btn btn-secondary">Contact Us</a>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact">
        <h2 class="section-title">Get In Touch</h2>
        <p class="section-subtitle">Have questions? We'd love to hear from you</p>
        <div style="text-align: center; margin-top: 2rem;">
            <p style="color: var(--text-secondary); font-size: 1.1rem;">
                This is an open-source project licensed under BSD 3-Clause License.<br>
                Connect with us on Telegram to learn more about deployment and customization.
            </p>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="footer-content">
            <div class="footer-links">
                <a href="#home">Home</a>
                <a href="#features">Features</a>
                <a href="#roles">Roles</a>
                <a href="#tech">Technology</a>
                <a href="https://github.com" target="_blank">GitHub</a>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2026 SMM Telegram Bot. Licensed under BSD 3-Clause License.</p>
                <p>Built with PHP Native & Telegram Bot API</p>
            </div>
        </div>
    </footer>

    <script>
        // Page Loading
        window.addEventListener('load', function() {
            setTimeout(function() {
                document.getElementById('loading').classList.add('hide');
            }, 500);
        });

        // Scroll Progress Bar
        window.addEventListener('scroll', function() {
            var winScroll = document.body.scrollTop || document.documentElement.scrollTop;
            var height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
            var scrolled = (winScroll / height) * 100;
            document.getElementById('scrollProgress').style.width = scrolled + '%';
        });

        // Navbar Scroll Effect
        var navbar = document.getElementById('navbar');
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Mobile Navigation Toggle
        var mobileToggle = document.getElementById('mobileToggle');
        var navLinks = document.getElementById('navLinks');
        
        mobileToggle.addEventListener('click', function() {
            navLinks.classList.toggle('active');
        });

        // Close mobile menu when clicking a link
        var links = document.querySelectorAll('.nav-links a');
        links.forEach(function(link) {
            link.addEventListener('click', function() {
                navLinks.classList.remove('active');
            });
        });

        // Smooth Scrolling
        document.querySelectorAll('a[href^="#"]').forEach(function(anchor) {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                var target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    var offset = 80;
                    var targetPosition = target.offsetTop - offset;
                    window.scrollTo({
                        top: targetPosition,
                        behavior: 'smooth'
                    });
                }
            });
        });

        // Scroll Animation Observer
        var observerOptions = {
            threshold: 0.15,
            rootMargin: '0px 0px -50px 0px'
        };

        var observer = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        // Observe all animated elements
        var animatedElements = document.querySelectorAll('[data-animate]');
        animatedElements.forEach(function(element) {
            observer.observe(element);
        });

        // Counter Animation for Stats
        function animateCounter(element) {
            var target = element.innerText;
            var isPercentage = target.includes('%');
            var isPlus = target.includes('+');
            var number = parseInt(target.replace(/\D/g, ''));
            var duration = 2000;
            var step = number / (duration / 16);
            var current = 0;
            
            var timer = setInterval(function() {
                current += step;
                if (current >= number) {
                    current = number;
                    clearInterval(timer);
                }
                
                var displayValue = Math.floor(current);
                if (isPlus) displayValue += '+';
                if (isPercentage) displayValue += '%';
                
                element.innerText = displayValue;
            }, 16);
        }

        // Observe stat cards for counter animation
        var statObserver = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    var counter = entry.target.querySelector('.stat-number');
                    if (counter) {
                        animateCounter(counter);
                    }
                    statObserver.unobserve(entry.target);
                }
            });
        }, observerOptions);

        var statCards = document.querySelectorAll('.stat-card');
        statCards.forEach(function(card) {
            statObserver.observe(card);
        });

        // Parallax effect for hero
        window.addEventListener('scroll', function() {
            var scrolled = window.pageYOffset;
            var hero = document.querySelector('.hero-content');
            if (hero && scrolled < window.innerHeight) {
                hero.style.transform = 'translateY(' + (scrolled * 0.5) + 'px)';
                hero.style.opacity = 1 - (scrolled / 700);
            }
        });

        // Add stagger delay to animations
        document.querySelectorAll('.features-grid .feature-card').forEach(function(card, index) {
            card.style.animationDelay = (index * 0.1) + 's';
        });

        document.querySelectorAll('.roles-container .role-card').forEach(function(card, index) {
            card.style.animationDelay = (index * 0.15) + 's';
        });

        document.querySelectorAll('.tech-badge').forEach(function(badge, index) {
            badge.style.animationDelay = (index * 0.05) + 's';
        });

        document.querySelectorAll('.stat-card').forEach(function(card, index) {
            card.style.animationDelay = (index * 0.1) + 's';
        });
    </script>
</body>
</html>