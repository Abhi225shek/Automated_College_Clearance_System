<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>College Clearance System - Patna</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        /* CSS Variables for Consistent Theming */
        :root {
            --primary: #3498db;
            /* A vibrant, professional blue */
            --primary-light: #5dade2;
            /* Lighter shade of primary */
            --secondary: #f39c12;
            /* A warm, complementary orange/yellow */
            --accent: #ecf0f1;
            /* Light gray accent for backgrounds */
            --text-dark: #333;
            /* Dark gray for primary text */
            --text-light: #fff;
            /* White for light text */
            --background: #f9f9f9;
            /* Off-white, slightly warm background */
            --spacing-xs: 0.6rem;
            --spacing-sm: 1.2rem;
            --spacing-md: 2.5rem;
            --spacing-lg: 5rem;
            /* Larger section padding */
            --border-radius: 15px;
            /* More rounded corners */
            --transition: all 0.3s ease-in-out;
            /* Faster, smoother transitions */
            --box-shadow-light: 0 5px 15px rgba(0, 0, 0, 0.1);
            --box-shadow-medium: 0 8px 25px rgba(0, 0, 0, 0.15);
            --box-shadow-heavy: 0 12px 35px rgba(0, 0, 0, 0.2);
        }

        /* Basic Reset and Body Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            scroll-behavior: smooth;
            /* Smooth scrolling for anchor links */
        }

        body {
            font-family: 'Poppins', 'Roboto', sans-serif;
            line-height: 1.7;
            color: var(--text-dark);
            background-color: var(--background);
            overflow-x: hidden;
            /* Prevent horizontal scroll issues */
        }

        /* Reusable Utility Classes */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 var(--spacing-md);
        }

        .section {
            padding: var(--spacing-lg) 0;
            position: relative;
            overflow: hidden;
            /* Ensures content stays within bounds */
        }

        /* Button Styling */
        .btn {
            display: inline-block;
            padding: var(--spacing-sm) var(--spacing-md);
            background-color: var(--primary);
            color: var(--text-light);
            text-decoration: none;
            border-radius: var(--border-radius);
            transition: var(--transition);
            border: none;
            cursor: pointer;
            font-weight: 600;
            letter-spacing: 0.6px;
            box-shadow: var(--box-shadow-light);
        }

        .btn:hover {
            background-color: var(--primary-light);
            transform: translateY(-5px);
            /* Lifts button on hover */
            box-shadow: var(--box-shadow-medium);
            /* Deeper shadow on hover */
        }

        .btn-outline {
            background-color: transparent;
            border: 2px solid var(--primary);
            color: var(--primary);
            box-shadow: none;
        }

        .btn-outline:hover {
            background-color: var(--primary);
            color: var(--text-light);
            box-shadow: var(--box-shadow-light);
        }

        /* Navigation Bar */
        .navbar {
            background-color: var(--text-light);
            padding: var(--spacing-sm) 0;
            box-shadow: var(--box-shadow-light);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
        }

        .nav-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .nav-logo {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary);
            text-decoration: none;
            display: flex;
            align-items: center;
        }

        .nav-logo i {
            margin-right: 10px;
            font-size: 2rem;
        }

        .nav-links a {
            color: var(--text-dark);
            text-decoration: none;
            margin-left: var(--spacing-md);
            transition: var(--transition);
            font-weight: 500;
        }

        .nav-links a:hover {
            color: var(--primary);
            transform: translateY(-3px);
            /* Lifts links on hover */
        }

        /* Hero Section - Dynamic Background and Text */
        .hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            position: relative;
            /* Placeholder for Patna-specific background image */
            background-image: url('https://images.unsplash.com/photo-1596495578065-6f170a049435?q=80&w=2070&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D');
            background-size: cover;
            background-position: center;
            color: var(--text-light); /* Light text for dark background */
            padding-bottom: 10vh; /*Space for the wave effect*/
        }

        /* Dark overlay for readability over hero image */
        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.45);
            /* Increased overlay for better text contrast */
            z-index: 1;
        }

        /* SVG Wave at the bottom of Hero */
        .hero-wave {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: auto;
            z-index: 2;
            /* Ensure wave is above the overlay */
            transform: translateY(1px);
            /* Fix small gap on some browsers */
        }

        .hero-content {
            max-width: 800px;
            margin: 0 auto;
            /* Center content horizontally */
            position: relative;
            z-index: 3;
            /* Ensure content is above the wave and overlay */
            text-align: center;
            animation: fadeInScale 1s ease-out;
            /* Initial animation for hero content */
        }

        @keyframes fadeInScale {
            from {
                opacity: 0;
                transform: scale(0.95);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .hero-title {
            font-size: 4rem;
            /* Larger title */
            margin-bottom: var(--spacing-sm);
            font-weight: 700;
            line-height: 1.2;
            text-shadow: 2px 2px 6px rgba(0, 0, 0, 0.4);
            /* Stronger text shadow */
        }

        .hero-subtitle {
            font-size: 1.5rem;
            margin-bottom: var(--spacing-md);
            font-weight: 400;
            opacity: 0.95;
            /* More opaque */
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.3);
        }

        .hero-buttons {
            display: flex;
            justify-content: center;
            gap: var(--spacing-sm);
        }

        /* About Section */
        .about {
            background-color: var(--text-light);
            /* Clean white background */
            position: relative;
            padding-top: var(--spacing-lg);
            z-index: 1;
            /* Optional: Subtle background pattern/texture for visual interest */
            /* background-image: url('path/to/subtle-pattern.png');
            background-repeat: repeat;
            background-size: 100px; /* Adjust size as needed */
           /* background-blend-mode: overlay; */
        }

        /* Soft white overlay for about section content */
        .about::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.9);
            /* Slightly more opaque overlay */
            z-index: 0;
        }

        .about-content {
            position: relative;
            z-index: 1;
            display: grid;
            grid-template-columns: 1fr;
            /* Single column for simplicity */
            gap: var(--spacing-md);
            align-items: center;
            padding: var(--spacing-lg) var(--spacing-md);
        }

        .about-text {
            padding: var(--spacing-md);
            background: var(--text-light);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow-medium);
        }

        .about-text h2 {
            color: var(--primary);
            margin-bottom: var(--spacing-md);
            font-size: 2.8rem;
            font-weight: 700;
            position: relative;
            padding-bottom: var(--spacing-sm);
        }

        .about-text h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100px;
            height: 5px;
            background: var(--secondary);
            /* Accent color underline */
            border-radius: var(--border-radius);
        }

        .about-text p {
            margin-bottom: var(--spacing-md);
            color: var(--text-dark);
            opacity: 0.9;
            font-weight: 400;
            line-height: 1.8;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: var(--spacing-md);
            margin-top: var(--spacing-md);
        }

        .feature-item {
            background: linear-gradient(145deg, var(--text-light), var(--accent) 80%);
            /* Subtle gradient */
            padding: var(--spacing-md);
            border-radius: var(--border-radius);
            text-align: center;
            transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out, background 0.3s ease-in-out;
            box-shadow: var(--box-shadow-light);
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(0, 0, 0, 0.05);
            /* Very light border */
        }

        .feature-item:hover {
            transform: translateY(-10px) rotateZ(1deg);
            /* Slight lift and rotation */
            box-shadow: var(--box-shadow-heavy);
            background: linear-gradient(145deg, var(--text-light) 20%, var(--primary-light) 100%);
            /* Color shift on hover */
        }

        .feature-item i {
            font-size: 3rem;
            color: var(--primary);
            margin-bottom: var(--spacing-sm);
            transition: color var(--transition), transform 0.3s ease-in-out;
        }

        .feature-item:hover i {
            color: var(--secondary);
            /* Icon color change on hover */
            transform: scale(1.1);
            /* Icon slightly grows */
        }

        .feature-item h3 {
            color: var(--text-dark);
            margin-bottom: var(--spacing-xs);
            font-size: 1.4rem;
            font-weight: 600;
        }

        .feature-item p {
            font-weight: 400;
            font-size: 1rem;
            color: var(--text-dark);
            opacity: 0.8;
        }

        /* New Section: Why Choose Our System? */
        #why-choose-us {
            padding: var(--spacing-lg) 0;
            background-color: var(--accent);
            text-align: center;
        }

        #why-choose-us h2 {
            font-size: 3rem;
            color: var(--primary);
            margin-bottom: var(--spacing-md);
            font-weight: 700;
        }

        .benefits-grid {
            display: flex;
            gap: var(--spacing-md);
            flex-wrap: wrap;
            justify-content: center;
        }

        .benefit-item {
            background: var(--text-light);
            padding: var(--spacing-md);
            border-radius: var(--border-radius);
            width: 350px;
            box-shadow: var(--box-shadow-medium);
            text-align: left;
            transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
            position: relative;
            border-bottom: 8px solid var(--secondary);
            /* Prominent accent border */
            overflow: hidden;
        }

        .benefit-item:hover {
            transform: translateY(-10px);
            /* Lifts card on hover */
            box-shadow: var(--box-shadow-heavy);
            /* Deeper shadow */
        }

        .benefit-item i {
            font-size: 2.8rem;
            color: var(--primary);
            margin-bottom: var(--spacing-sm);
            transition: color var(--transition), transform 0.3s ease-in-out;
            float: left;
            margin-right: var(--spacing-sm);
        }

        .benefit-item h3 {
            color: var(--text-dark);
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: var(--spacing-xs);
            padding-top: 5px;
            /* Align with icon */
        }

        .benefit-item p {
            font-weight: 400;
            font-size: 1rem;
            color: var(--text-dark);
            opacity: 0.8;
            clear: both;
            /* Ensure text flows below icon */
            padding-top: var(--spacing-xs);
        }

        /* New Section: Our Vision */
        #our-vision {
            padding: var(--spacing-lg) 0;
            background-color: var(--primary);
            color: var(--text-light);
            text-align: center;
        }

        #our-vision h2 {
            font-size: 3rem;
            margin-bottom: var(--spacing-md);
            font-weight: 700;
            color: var(--text-light);
        }

        #our-vision p {
            font-size: 1.3rem;
            max-width: 800px;
            margin: 0 auto var(--spacing-md) auto;
            line-height: 1.8;
            opacity: 0.9;
        }

        #our-vision .vision-points {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: var(--spacing-md);
            margin-top: var(--spacing-md);
        }

        #our-vision .vision-point {
            background: rgba(255, 255, 255, 0.15);
            padding: var(--spacing-md);
            border-radius: var(--border-radius);
            width: 280px;
            box-shadow: var(--box-shadow-light);
            transition: transform 0.3s ease-in-out, background-color 0.3s ease-in-out;
            border: 1px solid rgba(255, 255, 255, 0.2);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 180px;
        }

        #our-vision .vision-point:hover {
            transform: translateY(-8px);
            background-color: rgba(255, 255, 255, 0.25);
            box-shadow: var(--box-shadow-medium);
        }

        #our-vision .vision-point i {
            font-size: 3rem;
            margin-bottom: var(--spacing-sm);
            color: var(--secondary);
        }

        #our-vision .vision-point h3 {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-light);
            margin-bottom: 0.5rem;
        }

        #our-vision .vision-point p {
            font-size: 0.95rem;
            opacity: 0.8;
            line-height: 1.6;
            margin-bottom: 0;
        }

        /* Contact Us Section */
        .contact-us {
            background-color: var(--background);
            /* Back to default background */
            padding: var(--spacing-lg) 0;
            text-align: center;
        }

        .contact-us h2 {
            font-size: 3rem;
            color: var(--primary);
            margin-bottom: var(--spacing-sm);
            font-weight: 700;
        }

        .contact-us p {
            font-size: 1.2rem;
            color: var(--text-dark);
            opacity: 0.9;
        }

        .contact-us p a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            transition: color var(--transition);
        }

        .contact-us p a:hover {
            color: var(--secondary);
            /* Changes to secondary color on hover */
            text-decoration: underline;
        }

        /* Footer */
        .footer {
            background-color: var(--text-dark);
            color: var(--text-light);
            padding: var(--spacing-md) 0;
            text-align: center;
            font-size: 1rem;
            opacity: 0.9;
        }

        /* Responsive Design Adjustments */
        @media (max-width: 992px) {
            .hero-title {
                font-size: 3.5rem;
            }

            .hero-subtitle {
                font-size: 1.3rem;
            }

            .nav-links a {
                margin-left: var(--spacing-sm);
            }

            .about-content {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            :root {
                --spacing-lg: 4rem;
                --spacing-md: 2rem;
                --spacing-sm: 1rem;
            }

            .nav-content {
                flex-direction: column;
                text-align: center;
            }

            .nav-logo {
                margin-bottom: var(--spacing-sm);
            }

            .nav-links {
                margin-top: var(--spacing-sm);
                display: flex;
                flex-wrap: wrap;
                justify-content: center;
            }

            .nav-links a {
                margin: var(--spacing-xs) var(--spacing-sm);
            }

            .hero-title {
                font-size: 3rem;
            }

            .hero-subtitle {
                font-size: 1.1rem;
            }

            .features-grid {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            }

            .benefit-item,
            #our-vision .vision-point {
                width: 100%;
                max-width: 400px;
                /* Prevents cards from becoming too wide on smaller screens */
            }

            .hero-buttons {
                flex-direction: column;
                align-items: center;
            }

            .hero-buttons .btn {
                margin-right: 0;
                width: 80%;
                /* Make buttons full width */
            }
        }

        @media (max-width: 480px) {
            :root {
                --spacing-lg: 3rem;
                --spacing-md: 1.5rem;
                --spacing-sm: 0.8rem;
            }

            .hero-title {
                font-size: 2.5rem;
            }

            .about-text h2,
            #why-choose-us h2,
            #our-vision h2,
            .contact-us h2 {
                font-size: 2.5rem;
            }

            .benefit-item p,
            #our-vision .vision-point p {
                font-size: 1rem;
            }
        }
    </style>
</head>

<body>
    <nav class="navbar">
        <div class="container nav-content">
            <a href="index.php" class="nav-logo">
                <i class="fas fa-university"></i> College Clearance System
            </a>
            <div class="nav-links">
                <a href="index.php"><i class="fas fa-home"></i> Home</a>
                <a href="#about"><i class="fas fa-info-circle"></i> About</a>
                <a href="register.php"><i class="fas fa-user-plus"></i> Register</a>
                <a href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a>
            </div>
        </div>
    </nav>

    <section class="hero section">
        <div class="container">
            <div class="hero-content" data-aos="fade-up" data-aos-duration="1500">
                <h1 class="hero-title" id="typing"></h1>
                <p class="hero-subtitle">Streamline your clearance process with our efficient online system. Get started today!</p>
                <div class="hero-buttons">
                    <a href="login.php" class="btn">Login Now</a>
                    <a href="register.php" class="btn btn-outline">Register</a>
                </div>
            </div>
        </div>
        <svg class="hero-wave" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320">
            <path fill="#f9f9f9" fill-opacity="1"
                d="M0,192L48,170.7C96,149,192,107,288,112C384,117,480,171,576,192C672,213,768,203,864,181.3C960,160,1056,128,1152,117.3C1248,107,1344,117,1392,122.7L1440,128L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z">
            </path>
        </svg>
    </section>

    <section id="about" class="about section">
        <div class="container">
            <div class="about-content">
                <div class="about-text" data-aos="fade-right" data-aos-duration="1000">
                    <h2>Streamline Your Clearance Process</h2>
                    <p>The College Clearance System is your comprehensive digital solution for managing and obtaining departmental clearances. We understand that the traditional clearance process can be time-consuming and challenging. That's why we've developed an efficient online platform that connects students with various college departments seamlessly.</p>
                    <p>Whether you need clearance from the Library, Hostel, Accounts, or any other department, our system simplifies the entire process into a few easy steps. No more running between departments or waiting in long queues!</p>
                    <div class="features-grid">
                        <div class="feature-item" data-aos="fade-up" data-aos-delay="100">
                            <i class="fas fa-clock"></i>
                            <h3>Time-Saving</h3>
                            <p>Process clearances quickly without physical visits.</p>
                        </div>
                        <div class="feature-item" data-aos="fade-up" data-aos-delay="200">
                            <i class="fas fa-tasks"></i>
                            <h3>Track Progress</h3>
                            <p>Monitor your clearance status in real-time.</p>
                        </div>
                        <div class="feature-item" data-aos="fade-up" data-aos-delay="300">
                            <i class="fas fa-shield-alt"></i>
                            <h3>Secure Process</h3>
                            <p>Digital verification and secure data handling.</p>
                        </div>
                        <div class="feature-item" data-aos="fade-up" data-aos-delay="400">
                            <i class="fas fa-headset"></i>
                            <h3>Dedicated Support</h3>
                            <p>Get assistance whenever you need it from our team.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    ---

    <section id="why-choose-us" class="section">
        <div class="container">
            <h2 data-aos="fade-in" data-aos-duration="1000">Why Choose Our System?</h2>
            <div class="benefits-grid">
                <div class="benefit-item" data-aos="zoom-in" data-aos-delay="100">
                    <i class="fas fa-rocket"></i>
                    <h3>Accelerated Process</h3>
                    <p>Expedite your clearance with our fast, automated workflows, designed to save you valuable time.</p>
                </div>
                <div class="benefit-item" data-aos="zoom-in" data-aos-delay="200">
                    <i class="fas fa-mobile-alt"></i>
                    <h3>Convenient Access</h3>
                    <p>Complete your clearance from anywhere, anytime, using any device with internet access.</p>
                </div>
                <div class="benefit-item" data-aos="zoom-in" data-aos-delay="300">
                    <i class="fas fa-sync-alt"></i>
                    <h3>Real-time Updates</h3>
                    <p>Stay informed with instant notifications and real-time status updates on your clearance requests.</p>
                </div>
                <div class="benefit-item" data-aos="zoom-in" data-aos-delay="400">
                    <i class="fas fa-handshake"></i>
                    <h3>Seamless Integration</h3>
                    <p>Our system integrates smoothly with college departments, ensuring a unified and consistent experience.</p>
                </div>
            </div>
        </div>
    </section>

    ---

    <section id="our-vision" class="section">
        <div class="container">
            <h2 data-aos="fade-in" data-aos-duration="1000">Our Vision for Academic Excellence</h2>
            <p>We envision a future where academic administrative processes are effortless and transparent. Our College Clearance System is built to be the cornerstone of this transformation, empowering students and institutions alike.</p>
            <div class="vision-points">
                <div class="vision-point" data-aos="flip-up" data-aos-delay="100">
                    <i class="fas fa-lightbulb"></i>
                    <h3>Innovation</h3>
                    <p>Continuously evolving with cutting-edge technology to provide the best solutions.</p>
                </div>
                <div class="vision-point" data-aos="flip-up" data-aos-delay="200">
                    <i class="fas fa-users"></i>
                    <h3>User-Centric</h3>
                    <p>Designing with students and administrators at the heart of every feature for intuitive use.</p>
                </div>
                <div class="vision-point" data-aos="flip-up" data-aos-delay="300">
                    <i class="fas fa-cogs"></i>
                    <h3>Efficiency</h3>
                    <p>Dedicated to minimizing bureaucracy and maximizing productivity for all.</p>
                </div>
            </div>
        </div>
    </section>

    ---

    <section class="contact-us section" data-aos="fade-up" data-aos-duration="1000">
        <div class="container">
            <h2>Need Assistance?</h2>
            <p>Our dedicated support team is here to help. Contact your admin for immediate assistance or reach out to us directly:</p>
            <p><a href="mailto:support@collegeclearance.edu"><i class="fas fa-envelope"></i> support@collegeclearance.edu</a></p>
        </div>
    </section>

    <footer class="footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> College Clearance System. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        // Initialize AOS animations
        AOS.init({
            duration: 1000, // Animations will take 1000ms
            once: true, // Whether animation should happen only once - while scrolling down
        });

        // Smooth scroll for anchor links in the navigation
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });

        // Typing effect for the Hero Title
        const typingEl = document.getElementById('typing');
        const typingText = 'Welcome to the Online College Clearance Portal';
        let charIndex = 0;

        function typeWriter() {
            if (charIndex < typingText.length) {
                typingEl.innerHTML += typingText.charAt(charIndex);
                charIndex++;
                setTimeout(typeWriter, 60); // Slightly faster typing for professionalism
            } else {
                setTimeout(() => {
                    typingEl.innerHTML = '';
                    charIndex = 0;
                    setTimeout(typeWriter, 3000); // Longer pause before re-typing
                }, 3000);
            }
        }

        // Start the typing effect when the page loads
        typeWriter();
    </script>
</body>

</html>