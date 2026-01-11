$(document).ready(function() {
    // Page Loading
    $(window).on('load', function() {
        setTimeout(function() {
            $('#loading').addClass('hide');
        }, 500);
    });

    // Scroll Progress Bar
    $(window).on('scroll', function() {
        var winScroll = $(window).scrollTop();
        var height = $(document).height() - $(window).height();
        var scrolled = (winScroll / height) * 100;
        $('#scrollProgress').css('width', scrolled + '%');
    });

    // Navbar Scroll Effect
    $(window).on('scroll', function() {
        if ($(window).scrollTop() > 50) {
            $('#navbar').addClass('scrolled');
        } else {
            $('#navbar').removeClass('scrolled');
        }
    });

    // Mobile Navigation Toggle
    $('#mobileToggle').on('click', function() {
        $('#navLinks').toggleClass('active');
    });

    // Close mobile menu when clicking a link
    $('.nav-links a').on('click', function() {
        $('#navLinks').removeClass('active');
    });

    // Smooth Scrolling
    $('a[href^="#"]').on('click', function(e) {
        e.preventDefault();
        var target = $(this.getAttribute('href'));
        if (target.length) {
            $('html, body').animate({
                scrollTop: target.offset().top - 80
            }, 800);
        }
    });

    // Scroll Animation Observer
    var observerOptions = {
        threshold: 0.15,
        rootMargin: '0px 0px -50px 0px'
    };

    var observer = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) {
                $(entry.target).addClass('visible');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    // Observe all animated elements
    $('[data-animate]').each(function() {
        observer.observe(this);
    });

    // Counter Animation for Stats
    function animateCounter(element) {
        var $element = $(element);
        var target = $element.text();
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
            
            $element.text(displayValue);
        }, 16);
    }

    // Observe stat cards for counter animation
    var statObserver = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) {
                $(entry.target).addClass('visible');
                var counter = $(entry.target).find('.stat-number');
                if (counter.length) {
                    animateCounter(counter[0]);
                }
                statObserver.unobserve(entry.target);
            }
        });
    }, observerOptions);

    $('.stat-card').each(function() {
        statObserver.observe(this);
    });

    // Parallax effect for hero
    $(window).on('scroll', function() {
        var scrolled = $(window).scrollTop();
        var $hero = $('.hero-content');
        if ($hero.length && scrolled < $(window).height()) {
            $hero.css({
                'transform': 'translateY(' + (scrolled * 0.5) + 'px)',
                'opacity': 1 - (scrolled / 700)
            });
        }
    });

    // Add stagger delay to animations
    $('.features-grid .feature-card').each(function(index) {
        $(this).css('animation-delay', (index * 0.1) + 's');
    });

    $('.roles-container .role-card').each(function(index) {
        $(this).css('animation-delay', (index * 0.15) + 's');
    });

    $('.tech-badge').each(function(index) {
        $(this).css('animation-delay', (index * 0.05) + 's');
    });

    $('.stat-card').each(function(index) {
        $(this).css('animation-delay', (index * 0.1) + 's');
    });
});
