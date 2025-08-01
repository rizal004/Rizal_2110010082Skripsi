<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Sistem Informasi Wisata Kalteng</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        

        /* Animated background particles */
        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 1;
        }

        .particle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); opacity: 0.7; }
            50% { transform: translateY(-20px) rotate(180deg); opacity: 1; }
        }
        
        .dashboard-container {
            padding: 20px;
            max-width: 1600px;
            margin: 0 auto;
            position: relative;
            z-index: 2;
        }
        
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%,150deg, #0a2e5c 50%, #1877f2 100%);
            border-radius: 30px;
            padding: 80px 50px;
            text-align: center;
            margin-bottom: 50px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 30px 60px rgba(102, 126, 234, 0.4);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: rotate 20s linear infinite;
        }

        @keyframes rotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .hero-content {
            position: relative;
            z-index: 1;
        }
        
        .hero-title {
            font-size: 4rem;
            font-weight: 900;
            color: #ffffff;
            margin-bottom: 20px;
            letter-spacing: -0.02em;
            text-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            background: linear-gradient(45deg, #ffffff, #f0f9ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: glow 2s ease-in-out infinite alternate;
        }

        @keyframes glow {
            from { filter: drop-shadow(0 0 20px rgba(255, 255, 255, 0.5)); }
            to { filter: drop-shadow(0 0 30px rgba(255, 255, 255, 0.8)); }
        }
        
        .hero-subtitle {
            font-size: 1.5rem;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 40px;
            font-weight: 400;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
            line-height: 1.6;
        }

        .hero-cta {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            background: linear-gradient(135deg, #ff6b6b, #ee5a24);
            color: white;
            padding: 18px 40px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 8px 30px rgba(255, 107, 107, 0.4);
            border: 2px solid rgba(255, 255, 255, 0.2);
        }

        .hero-cta:hover {
            transform: translateY(-5px) scale(1.05);
            box-shadow: 0 15px 40px rgba(255, 107, 107, 0.6);
            background: linear-gradient(135deg, #ee5a24, #ff6b6b);
        }
        
        /* Welcome Section with Moving Text */
        .welcome-section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 25px;
            margin-bottom: 60px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
            overflow: hidden;
            position: relative;
            height: 120px;
            display: flex;
            align-items: center;
        }

        .welcome-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, #667eea, #764ba2, #f093fb);
        }

        .moving-text {
            font-size: 3rem;
            font-weight: 800;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            white-space: nowrap;
            animation: moveText 15s linear infinite;
            padding: 0 50px;
        }

        @keyframes moveText {
            0% { transform: translateX(100%); }
            100% { transform: translateX(-100%); }
        }

        .welcome-section:hover .moving-text {
            animation-play-state: paused;
        }
        
        .features-section {
            margin-bottom: 60px;
        }
        
        .section-title {
            font-size: 3rem;
            font-weight: 800;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-align: center;
            margin-bottom: 50px;
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
        }
        
        .feature-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 25px;
            padding: 40px;
            text-align: center;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: 1px solid rgba(255, 255, 255, 0.3);
            cursor: pointer;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: conic-gradient(from 0deg, transparent, rgba(102, 126, 234, 0.1), transparent);
            transform: rotate(0deg);
            transition: transform 0.6s ease;
        }

        .feature-card:hover::before {
            transform: rotate(360deg);
        }
        
        .feature-card:hover {
            transform: translateY(-15px) scale(1.03);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.2);
        }
        
        .feature-icon {
            font-size: 4rem;
            margin-bottom: 25px;
            transition: all 0.4s ease;
            position: relative;
            z-index: 1;
        }
        
        .feature-card:hover .feature-icon {
            transform: scale(1.2) rotateY(360deg);
        }
        
        .icon-wisata { 
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .icon-kuliner { 
            background: linear-gradient(135deg, #f093fb, #f5576c);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .icon-event { 
            background: linear-gradient(135deg, #4facfe, #00f2fe);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .icon-oleh2 { 
            background: linear-gradient(135deg, #43e97b, #38f9d7);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .icon-hotel {
            background: linear-gradient(135deg, #fa709a, #fee140);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .icon-sewa {
            background: linear-gradient(135deg, #a8edea, #fed6e3);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .feature-title {
            font-size: 1.6rem;
            margin-bottom: 15px;
            font-weight: 700;
            background: linear-gradient(135deg, #2d3748, #4a5568);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            position: relative;
            z-index: 1;
        }
        
        .feature-description {
            color: #4a5568;
            font-size: 1rem;
            line-height: 1.7;
            font-weight: 400;
            position: relative;
            z-index: 1;
        }
        
        .stats-section {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(255, 255, 255, 0.8));
            backdrop-filter: blur(20px);
            border-radius: 25px;
            padding: 50px;
            margin-bottom: 50px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }
        
        .stats-title {
            font-size: 2.5rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 40px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 30px;
        }
        
        .stat-item {
            text-align: center;
            padding: 30px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 20px;
            color: white;
            transition: transform 0.3s ease;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }

        .stat-item:hover {
            transform: translateY(-5px) scale(1.05);
        }
        
        .stat-number {
            font-size: 3rem;
            font-weight: 900;
            margin-bottom: 10px;
            color: #ffffff;
        }
        
        .stat-label {
            font-size: 1rem;
            opacity: 0.9;
            color: rgba(255, 255, 255, 0.9);
        }
        
        .cta-section {
            text-align: center;
            padding: 60px;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(255, 255, 255, 0.8));
            backdrop-filter: blur(20px);
            border-radius: 25px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }

        .cta-title {
            font-size: 2.5rem;
            margin-bottom: 20px;
            font-weight: 700;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .cta-description {
            font-size: 1.2rem;
            margin-bottom: 40px;
            color: #4a5568;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .cta-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .cta-button {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 18px 35px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: none;
            cursor: pointer;
            box-shadow: 0 8px 30px rgba(102, 126, 234, 0.4);
        }

        .cta-button.secondary {
            background: linear-gradient(135deg, #f093fb, #f5576c);
            box-shadow: 0 8px 30px rgba(240, 147, 251, 0.4);
        }
        
        .cta-button:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 15px 40px rgba(102, 126, 234, 0.6);
        }

        .cta-button.secondary:hover {
            box-shadow: 0 15px 40px rgba(240, 147, 251, 0.6);
        }

        /* Floating elements */
        .floating {
            position: absolute;
            animation: floating 6s ease-in-out infinite;
        }

        @keyframes floating {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }

        .floating-1 {
            top: 10%;
            left: 10%;
            animation-delay: 0s;
        }

        .floating-2 {
            top: 20%;
            right: 10%;
            animation-delay: 2s;
        }

        .floating-3 {
            bottom: 20%;
            left: 5%;
            animation-delay: 4s;
        }
        
        @media (max-width: 768px) {
            .dashboard-container {
                padding: 15px;
            }
            
            .hero-section {
                padding: 50px 30px;
            }
            
            .hero-title {
                font-size: 2.5rem;
            }
            
            .hero-subtitle {
                font-size: 1.2rem;
            }
            
            .welcome-section {
                height: 80px;
            }

            .moving-text {
                font-size: 2rem;
            }
            
            .features-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .cta-buttons {
                flex-direction: column;
                align-items: center;
            }

            .section-title {
                font-size: 2.2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Animated background particles -->
    <div class="particles"></div>

    <!-- Floating elements -->
    <div class="floating floating-1">
        <i class="fas fa-mountain" style="font-size: 3rem; color: rgba(255, 255, 255, 0.1);"></i>
    </div>
    <div class="floating floating-2">
        <i class="fas fa-tree" style="font-size: 2.5rem; color: rgba(255, 255, 255, 0.1);"></i>
    </div>
    <div class="floating floating-3">
        <i class="fas fa-water" style="font-size: 2rem; color: rgba(255, 255, 255, 0.1);"></i>
    </div>

    <div class="dashboard-container">

        <!-- Welcome Section dengan Teks Bergerak -->
        <section class="welcome-section">
            <div class="moving-text">
                🌟 Selamat Datang di Portal Wisata Kalimantan Tengah 🌟
            </div>
        </section>

        <!-- Features Section -->
        <section class="features-section" id="features">
            <h2 class="section-title">Temukan Pesona Kalimantan Tengah</h2>
            <div class="features-grid">
                <div class="feature-card" onclick="window.location.href='index.php?page=MyApp/data_wisata'">
                    <i class="fas fa-map-marked-alt feature-icon icon-wisata"></i>
                    <h3 class="feature-title">Destinasi Wisata</h3>
                    <p class="feature-description">Jelajahi keindahan alam Kalimantan Tengah dari hutan tropis, sungai cantik, hingga budaya Dayak yang memukau.</p>
                </div>
                
                <div class="feature-card" onclick="window.location.href='index.php?page=MyApp/data_kuliner'">
                    <i class="fas fa-utensils feature-icon icon-kuliner"></i>
                    <h3 class="feature-title">Kuliner</h3>
                    <p class="feature-description">Nikmati cita rasa kuliner tradisional dan modern yang wajib dicoba di setiap daerah.</p>
                </div>
                
                <div class="feature-card" onclick="window.location.href='index.php?page=MyApp/data_event'">
                    <i class="fas fa-calendar-alt feature-icon icon-event"></i>
                    <h3 class="feature-title">Event & Festival</h3>
                    <p class="feature-description">Ikuti berbagai acara budaya, festival, dan event menarik yang menampilkan kekayaan tradisi dan kreativitas masyarakat Kalteng.</p>
                </div>
                
                <div class="feature-card" onclick="window.location.href='index.php?page=MyApp/data_oleh2'">
                    <i class="fas fa-gift feature-icon icon-oleh2"></i>
                    <h3 class="feature-title">Oleh-oleh</h3>
                    <p class="feature-description">Temukan produk lokal terbaik dan souvenir unik sebagai kenang-kenangan dari perjalanan Anda.</p>
                </div>

                <div class="feature-card" onclick="window.location.href='index.php?page=MyApp/data_hotel'">
                    <i class="fas fa-hotel feature-icon icon-hotel"></i>
                    <h3 class="feature-title">Hotel</h3>
                    <p class="feature-description">Temukan tempat menginap yang nyaman untuk perjalanan Anda.</p>
                </div>

                <div class="feature-card" onclick="window.location.href='index.php?page=MyApp/data_sewa'">
                    <i class="fas fa-car feature-icon icon-sewa"></i>
                    <h3 class="feature-title">Sewa Mobil/Motor</h3>
                    <p class="feature-description">Sewa kendaraan dengan mudah untuk menjelajahi setiap sudut Kalimantan Tengah dengan kenyamanan dan fleksibilitas penuh.</p>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="cta-section">
            <h3 class="cta-title">Siap Menjelajahi Kalimantan Tengah?</h3>
            <p class="cta-description">Mulai petualangan Anda dan temukan keindahan tersembunyi di hati Borneo bersama panduan wisata digital terlengkap</p>
        </section>
    </div>

    <script>
        // Create animated particles
        function createParticles() {
            const particlesContainer = document.querySelector('.particles');
            const particleCount = 50;

            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                particle.style.left = Math.random() * 100 + '%';
                particle.style.top = Math.random() * 100 + '%';
                particle.style.animationDelay = Math.random() * 6 + 's';
                particle.style.animationDuration = (Math.random() * 3 + 3) + 's';
                particlesContainer.appendChild(particle);
            }
        }

        // Smooth animations
        document.addEventListener('DOMContentLoaded', function() {
            createParticles();

            // Animate cards on scroll
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, observerOptions);

            // Apply animation to cards
            document.querySelectorAll('.feature-card, .welcome-section').forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(50px)';
                card.style.transition = `opacity 0.8s ease ${index * 0.1}s, transform 0.8s ease ${index * 0.1}s`;
                observer.observe(card);
            });

            // Enhanced hover effects for feature cards
            document.querySelectorAll('.feature-card').forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-15px) scale(1.03) rotateX(5deg)';
                    this.style.boxShadow = '0 25px 50px rgba(0, 0, 0, 0.2)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0) scale(1) rotateX(0deg)';
                    this.style.boxShadow = '0 10px 30px rgba(0, 0, 0, 0.1)';
                });
            });

            // Smooth scroll for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });

            // Interactive button effects
            document.querySelectorAll('.cta-button, .hero-cta').forEach(button => {
                button.addEventListener('click', function(e) {
                    // Create ripple effect
                    const ripple = document.createElement('span');
                    const rect = this.getBoundingClientRect();
                    const size = Math.max(rect.width, rect.height);
                    const x = e.clientX - rect.left - size / 2;
                    const y = e.clientY - rect.top - size / 2;
                    
                    ripple.style.width = ripple.style.height = size + 'px';
                    ripple.style.left = x + 'px';
                    ripple.style.top = y + 'px';
                    ripple.style.position = 'absolute';
                    ripple.style.background = 'rgba(255, 255, 255, 0.3)';
                    ripple.style.borderRadius = '50%';
                    ripple.style.pointerEvents = 'none';
                    ripple.style.transform = 'scale(0)';
                    ripple.style.transition = 'transform 0.6s ease-out, opacity 0.6s ease-out';
                    ripple.style.opacity = '1';
                    
                    this.style.position = 'relative';
                    this.style.overflow = 'hidden';
                    this.appendChild(ripple);
                    
                    setTimeout(() => {
                        ripple.style.transform = 'scale(4)';
                        ripple.style.opacity = '0';
                    }, 10);
                    
                    setTimeout(() => {
                        ripple.remove();
                    }, 600);
                });
            });

            // Dynamic background gradient
            let gradientAngle = 135;
            setInterval(() => {
                gradientAngle += 0.5;
                if (gradientAngle >= 360) gradientAngle = 0;
                
                document.body.style.background = `linear-gradient(${gradientAngle}deg, #667eea 0%, #764ba2 100%)`;
            }, 100);

            // Parallax effect for floating elements
            window.addEventListener('scroll', () => {
                const scrolled = window.pageYOffset;
                const parallaxElements = document.querySelectorAll('.floating');
                
                parallaxElements.forEach((element, index) => {
                    const speed = 0.5 + (index * 0.1);
                    element.style.transform = `translateY(${scrolled * speed}px)`;
                });
            });

            // Enhanced particle interaction
            document.addEventListener('mousemove', (e) => {
                const particles = document.querySelectorAll('.particle');
                const mouseX = e.clientX / window.innerWidth;
                const mouseY = e.clientY / window.innerHeight;
                
                particles.forEach((particle, index) => {
                    const speed = (index % 3 + 1) * 0.01;
                    const x = (mouseX - 0.5) * speed * 100;
                    const y = (mouseY - 0.5) * speed * 100;
                    
                    particle.style.transform = `translate(${x}px, ${y}px)`;
                });
            });

            // Feature card click animation
            document.querySelectorAll('.feature-card').forEach(card => {
                card.addEventListener('click', function(e) {
                    // Prevent multiple clicks
                    if (this.classList.contains('clicking')) return;
                    
                    this.classList.add('clicking');
                    this.style.transform = 'scale(0.95)';
                    
                    setTimeout(() => {
                        this.style.transform = 'translateY(-15px) scale(1.03)';
                        this.classList.remove('clicking');
                    }, 150);
                });
            });

            // Enhanced scroll reveal animation
            const revealElements = document.querySelectorAll('.welcome-section, .cta-section');
            
            const revealObserver = new IntersectionObserver((entries) => {
                entries.forEach((entry, index) => {
                    if (entry.isIntersecting) {
                        setTimeout(() => {
                            entry.target.style.opacity = '1';
                            entry.target.style.transform = 'translateY(0) rotateX(0)';
                        }, index * 200);
                    }
                });
            });

            revealElements.forEach(element => {
                element.style.opacity = '0';
                element.style.transform = 'translateY(50px) rotateX(10deg)';
                element.style.transition = 'all 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.275)';  
                revealObserver.observe(element);
            });
        });
    </script>
</body>
</html> 