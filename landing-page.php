<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="SMM Telegram Bot - Connect clients with workers for social media engagement campaigns">
    <meta name="keywords" content="Telegram Bot, SMM, Social Media Marketing, PTC, Engagement">
    <title>SMM Telegram Bot - Social Media Engagement Platform</title>
    
    <link rel="icon" href="src/img/favicon-SMM-Telegram-Bot.png" type="image/png">
    
    <!-- Font Awesome from Cloudflare CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <link rel="stylesheet" href="src/css/style.css">
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
</head>
<body>
    <!-- Loading Screen -->
    <div class="loading" id="loading">
        <div class="loader"></div>
    </div>

    <div class="scroll-progress" id="scrollProgress"></div>

    <div class="bg-animation"></div>

    <nav id="navbar">
        <div class="nav-container">
            <div class="logo"><img src="src/img/logo-SMM-Telegram-Bot.png" alt="SMM Bot"> SMM Bot</div>
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

    <section>
        <div class="cta-section">
            <h2>Ready to Get Started?</h2>
            <p>Join our platform and experience the future of social media engagement management</p>
            <a href="https://t.me/smm_demo1_bot" class="btn btn-primary" rel="noopener noreferrer">Try Bot</a>
        </div>
    </section>

    <section id="contact">
        <h2 class="section-title">Get In Touch</h2>
        <p class="section-subtitle">Have questions? We'd love to hear from you</p>
        <div style="text-align: center; margin-top: 1.5rem;">
            <a href="https://faydev.my.id" class="btn btn-primary">Contact Us</a>
        </div>
        <div style="text-align: center; margin-top: 3rem;">
            <p style="color: var(--text-secondary); font-size: 1.1rem;">
                This is an open-source project licensed under BSD 3-Clause License.<br>
                Connect with us on Telegram to learn more about deployment and customization.
            </p>
        </div>
    </section>

    <footer>
        <div class="footer-content">
            <div class="footer-links">
                <a href="#home">Home</a>
                <a href="#features">Features</a>
                <a href="#roles">Roles</a>
                <a href="#tech">Technology</a>
                <a href="https://github.com/FAYnim/SMM-Telegram-Bot" target="_blank" rel="noopener noreferrer">GitHub</a>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2026 SMM Telegram Bot. Licensed under BSD 3-Clause License.</p>
                <p>Built with ❤ by Faris AY</p>
            </div>
        </div>
    </footer>

    <script src="src/js/main.js"></script>
</body>
</html>