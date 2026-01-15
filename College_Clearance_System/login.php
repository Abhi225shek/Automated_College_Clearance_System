<?php
session_start();
include 'db.php'; // Assuming 'db.php' is in the same directory

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    try {
        $sql = "SELECT * FROM users WHERE username = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Error preparing statement: " . $conn->error);
        }
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if ($user && password_verify($password, $user['password'])) {
            if ($user['is_verified'] == 1) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['department'] = $user['department'];
                header("Location: dashboard.php"); // Redirect to dashboard on successful login
                exit();
            } else {
                $error = "Your account has not been verified by the Super Admin.";
            }
        } else {
            $error = "Invalid credentials.";
        }
    } catch (Exception $e) {
        $error = "An error occurred: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - College Clearance System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        /* CSS Variables - COPIED DIRECTLY FROM YOUR PROVIDED CONTENT */
        :root {
            --primary: #3498db; /* A vibrant, professional blue */
            --primary-light: #5dade2; /* Lighter shade of primary */
            --secondary: #f39c12; /* A warm, complementary orange/yellow */
            --accent: #ecf0f1; /* Light gray accent for backgrounds */
            --text-dark: #333; /* Dark gray for primary text */
            --text-light: #fff; /* White for light text */
            --background: #f9f9f9; /* Off-white, slightly warm background */
            --spacing-xs: 0.6rem;
            --spacing-sm: 1.2rem;
            --spacing-md: 2.5rem;
            --spacing-lg: 5rem; /* Larger section padding */
            --border-radius: 15px; /* More rounded corners */
            --transition: all 0.3s ease-in-out; /* Faster, smoother transitions */
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
        }

        /* Login Page Body Styles with Blurred Image Background */
        body {
            font-family: 'Poppins', 'Roboto', sans-serif;
            line-height: 1.7;
            color: var(--text-dark);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            /* Updated Pexels image URL */
            background-image: url('https://images.pexels.com/photos/301920/pexels-photo-301920.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            position: relative;
            overflow: hidden;
            z-index: 1; /* Ensure body content is above the pseudo-element if needed */
        }

        /* Semi-transparent overlay to ensure readability of login form */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: var(--primary); /* Use your primary color for the overlay */
            opacity: 0.7; /* Adjusted slightly more opaque for better contrast with new primary */
            z-index: -1; /* Place it behind the login wrapper */
        }

        /* Navbar styles - Adapted from your provided homepage content */
        .navbar {
            background-color: var(--text-light); /* Your white text-light for consistency */
            padding: var(--spacing-sm) 0;
            box-shadow: var(--box-shadow-light); /* Consistent shadow */
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
        }

        .nav-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 var(--spacing-md);
        }

        .nav-logo {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary);
            text-decoration: none;
            display: flex;
            align-items: center;
            transition: var(--transition);
        }
        .nav-logo:hover {
            color: var(--primary-light);
            transform: scale(1.02);
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
            padding: 5px 0;
            position: relative;
        }

        /* Underline effect from your homepage */
        .nav-links a::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 0;
            height: 3px; /* Slightly thicker underline */
            background-color: var(--secondary);
            transition: width 0.3s ease-in-out;
        }

        .nav-links a:hover::after {
            width: 100%;
        }

        .nav-links a:hover {
            color: var(--primary);
            transform: translateY(-3px);
        }

        /* Login form specific styles - Re-themed to your provided homepage aesthetic */
        .login-wrapper {
            position: relative;
            width: 400px; /* Standard width for login form */
            background: var(--text-light); /* White background like panels on your home page */
            border-radius: var(--border-radius); /* Consistent border radius */
            box-shadow: var(--box-shadow-medium); /* Use medium shadow for prominence */
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            transform: scale(0.9);
            opacity: 0;
            animation: fadeInScaleIn 0.8s ease-out forwards;
            padding: var(--spacing-md); /* Consistent internal padding */
            z-index: 5; /* Ensure it's above other elements */
            border: 1px solid rgba(0, 0, 0, 0.05); /* Very light border from feature items */
        }

        /* Animation for the login wrapper */
        @keyframes fadeInScaleIn {
            0% {
                opacity: 0;
                transform: scale(0.8) translateY(20px);
            }
            100% {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        .login-form {
            width: 100%;
            padding: 0; /* Already padded by wrapper */
            color: var(--text-dark); /* Dark text for readability on light background */
        }

        .login-form h2 {
            font-size: 2.2rem;
            font-weight: 700; /* Bolder for headings */
            text-align: center;
            color: var(--primary); /* Primary color for heading */
            margin-bottom: var(--spacing-md);
            text-shadow: none; /* No strong text shadow on light background */
            position: relative;
            padding-bottom: var(--spacing-xs);
        }

        .login-form h2::after { /* Underline from your About section headings */
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px; /* Adjusted width for a clean look */
            height: 4px;
            background: var(--secondary); /* Accent color underline */
            border-radius: var(--border-radius);
        }

        .input-box {
            position: relative;
            width: 100%;
            height: 55px; /* Slightly taller inputs for better touch */
            margin: var(--spacing-md) 0; /* Use your spacing-md */
        }

        .input-box input {
            width: 100%;
            height: 100%;
            background: var(--background); /* Light background for inputs, from your theme */
            border: none;
            outline: none;
            border: 2px solid var(--accent); /* Accent border for inputs */
            border-radius: var(--border-radius); /* Rounded inputs from your theme */
            font-size: 1.1rem;
            color: var(--text-dark); /* Dark text in inputs */
            padding: 0 15px 0 45px; /* More padding, space for icon */
            transition: var(--transition);
        }

        .input-box input:focus,
        .input-box input:valid {
            border-color: var(--primary); /* Primary color on focus/valid */
            box-shadow: 0 0 0 4px rgba(52, 152, 219, 0.2); /* Soft focus glow using your new primary color */
        }

        .input-box label {
            position: absolute;
            top: 50%;
            left: 45px; /* Align with input text */
            transform: translateY(-50%);
            color: var(--text-dark); /* Dark label text */
            font-size: 1rem;
            pointer-events: none;
            transition: var(--transition);
        }

        .input-box input:focus ~ label,
        .input-box input:valid ~ label {
            top: -10px; /* Move label higher above input */
            left: 15px; /* Shift label slightly left */
            font-size: 0.8rem;
            color: var(--primary); /* Primary color for focused label */
            background-color: var(--text-light); /* Background for the lifted label */
            padding: 0 8px;
            border-radius: 5px;
            box-shadow: var(--box-shadow-light); /* Subtle shadow for floating label */
        }

        .input-box .icon {
            position: absolute;
            left: 15px; /* Icon inside input */
            top: 50%;
            transform: translateY(-50%);
            font-size: 1.2em;
            color: var(--primary); /* Primary color for icons */
            z-index: 2; /* Ensure icon is above input text */
        }

        .remember-forgot {
            font-size: 0.9em;
            color: var(--text-dark); /* Dark text */
            margin: -15px 0 15px; /* Adjusted margin */
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .remember-forgot label input {
            accent-color: var(--primary);
            margin-right: 3px;
        }

        .remember-forgot a {
            color: var(--primary); /* Primary color for links */
            text-decoration: none;
            transition: var(--transition);
            font-weight: 500;
        }

        .remember-forgot a:hover {
            text-decoration: underline;
            color: var(--primary-light); /* Lighter primary on hover */
        }

        .btn {
            width: 100%;
            height: 50px; /* Taller button */
            background: var(--primary);
            border: none;
            outline: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-size: 1.1em;
            color: var(--text-light);
            font-weight: 600;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            z-index: 1;
            box-shadow: var(--box-shadow-medium); /* Consistent shadow */
            margin-top: 15px;
            letter-spacing: 0.6px;
        }

        .btn:hover {
            background: var(--primary-light);
            box-shadow: var(--box-shadow-heavy); /* Deeper shadow on hover */
            transform: translateY(-5px); /* Lifts button on hover */
        }

        /* Ripple effect on hover (from your homepage buttons) */
        .btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255, 255, 255, 0.3); /* Lighter ripple */
            border-radius: 50%;
            transform: translate(-50%, -50%);
            opacity: 0;
            transition: width 0.4s ease-out, height 0.4s ease-out, opacity 0.4s ease-out;
            z-index: -1;
        }

        .btn:hover::before {
            width: 250%;
            height: 250%;
            opacity: 1;
        }

        .register-link {
            font-size: 0.9em;
            color: var(--text-dark); /* Dark text */
            text-align: center;
            margin-top: var(--spacing-md);
        }

        .register-link p a {
            color: var(--primary); /* Primary color for links */
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
        }

        .register-link p a:hover {
            text-decoration: underline;
            color: var(--primary-light); /* Lighter primary on hover */
        }

        .error-message {
            color: var(--text-dark); /* Dark text for readability */
            background-color: var(--accent); /* Using accent for error background, like benefit items */
            border: 1px solid var(--secondary); /* Secondary for error border, prominent */
            padding: var(--spacing-sm);
            border-radius: var(--border-radius);
            margin-bottom: var(--spacing-md);
            text-align: center;
            font-weight: 600;
            animation: shake 0.5s ease-in-out;
            box-shadow: var(--box-shadow-light); /* Consistent shadow for alerts */
        }

        @keyframes shake {
            0% { transform: translateX(0); }
            20% { transform: translateX(-5px); }
            40% { transform: translateX(5px); }
            60% { transform: translateX(-5px); }
            80% { transform: translateX(5px); }
            100% { transform: translateX(0); }
        }

        /* Responsive Design (adjusted to match your homepage's media queries) */
        @media (max-width: 768px) {
            .navbar {
                background-color: var(--text-light); /* Ensure solid background on smaller screens */
                backdrop-filter: none;
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

            .login-wrapper {
                width: 90%;
                max-width: 450px; /* Constrain max width for better appearance */
                height: auto;
                padding: var(--spacing-md);
                transform: scale(1);
                opacity: 1;
                animation: none;
            }

            .login-form h2 {
                font-size: 1.8rem;
            }

            .input-box label {
                font-size: 0.9rem;
            }

            .input-box input {
                font-size: 1rem;
                padding: 0 10px 0 40px;
            }

            .input-box .icon {
                left: 10px;
            }

            .btn {
                font-size: 1em;
                height: 45px;
            }
        }

        @media (max-width: 480px) {
            .login-wrapper {
                max-width: 95%;
                padding: var(--spacing-sm);
            }

            .login-form h2 {
                font-size: 2rem;
            }

            .input-box {
                height: 50px;
                margin: var(--spacing-sm) 0;
            }

            .remember-forgot {
                flex-direction: column;
                align-items: flex-start;
                margin: -10px 0 10px;
            }

            .remember-forgot a {
                margin-top: 5px;
            }

            .register-link {
                margin-top: var(--spacing-sm);
            }
        }
    </style>
</head>
<body>

    <nav class="navbar">
        <div class="nav-content">
            <a href="index.php" class="nav-logo">
                <i class="fas fa-university"></i> College Clearance System
            </a>
            <div class="nav-links">
                <a href="index.php"><i class="fas fa-home"></i> Home</a>
                <a href="index.php#about"><i class="fas fa-info-circle"></i> About</a>
                <a href="register.php"><i class="fas fa-user-plus"></i> Register</a>
                <a href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a>
            </div>
        </div>
    </nav>

    <div class="login-wrapper" data-aos="zoom-in" data-aos-duration="1000">
        <div class="login-form">
            <h2>Login</h2>
            <?php if ($error): ?>
                <div class="error-message"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="POST" action="">
                <!-- <div class="input-box">
                    <span class="icon"><i class="fas fa-envelope"></i></span>
                    <input type="email" name="email" required>
                    <label>Email</label>
                </div> -->
                <div class="input-box">
                    <span class="icon"><i class="fas fa-envelope"></i></span>
                    <input type="email" name="email" required
                        pattern="[a-zA-Z0-9._%+-]+@gmail\.com$"
                        title="Please enter a valid Gmail address (e.g., yourname@gmail.com)"
                        oninput="this.classList.toggle('has-value', this.value.length > 0)">
                    <label>Email</label>
                </div>
                <div class="input-box">
                    <span class="icon"><i class="fas fa-lock"></i></span>
                    <input type="password" name="password" required>
                    <label>Password</label>
                </div>
                <button type="submit" class="btn">Login</button>
                <div class="register-link">
                    <p>Don't have an account? <a href="register.php">Register</a></p>
                </div>
            </form>
        </div>
    </div>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 800,
            once: true
        });
            const emailInput = document.querySelector('input[name="email"]');
            if (emailInput) {
                emailInput.addEventListener('input', function() {
                    if (this.value.length > 0 && !this.value.endsWith('@gmail.com') && !this.value.includes('@')) {
                        this.setCustomValidity("Please include '@gmail.com' in your email address.");
                    } else if (this.value.length > 0 && this.value.includes('@') && !this.value.endsWith('@gmail.com')) {
                        this.setCustomValidity("Only Valid Gmail addresses are allowed (e.g., yourname@gmail.com)");
                    } else {
                        this.setCustomValidity(""); // Clear the message if valid
                    }
                    // Trigger browser's default validation UI
                    this.reportValidity();
                });

                // Also run the check on blur in case they paste a value
                emailInput.addEventListener('blur', function() {
                    if (this.value.length > 0 && !this.value.endsWith('@gmail.com')) {
                        this.setCustomValidity("Only Valid Gmail addresses are allowed (e.g., yourname@gmail.com)");
                    } else {
                        this.setCustomValidity("");
                    }
                    this.reportValidity();
                });
            }        
    </script>
</body>
</html>