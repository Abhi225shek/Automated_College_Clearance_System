<?php
include 'db.php'; // Include your database connection file

$message = ''; // Initialize message variable

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {

        $name = mysqli_real_escape_string($conn, trim($_POST['name']));
        $username = mysqli_real_escape_string($conn, trim($_POST['email'])); // Email as username
        $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);
        $role = mysqli_real_escape_string($conn, trim($_POST['role']));
        $department = !empty($_POST['department']) ? mysqli_real_escape_string($conn, trim($_POST['department'])) : null;

        // Additional student details
        $college_id = !empty($_POST['college_id']) ? mysqli_real_escape_string($conn, trim($_POST['college_id'])) : null;
        $session = !empty($_POST['session']) ? mysqli_real_escape_string($conn, trim($_POST['session'])) : null;
        $roll_number = !empty($_POST['roll_number']) ? mysqli_real_escape_string($conn, trim($_POST['roll_number'])) : null;
        $stream = !empty($_POST['stream']) ? mysqli_real_escape_string($conn, trim($_POST['stream'])) : null;

        // Validate department for department admins
        $valid_departments = ['AIML', 'DS', 'CSBS', 'CSCS', 'IT', 'CSE', 'ECE', 'EIE', 'EE', 'ME', 'CE', 'FT'];
        if ($role === 'department' && (empty($department) || !in_array($department, $valid_departments))) {
            throw new Exception("Please select a valid department for department admin role.");
        }

        // Check if username (email) already exists
        $check_query = "SELECT * FROM users WHERE username = ?";
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $existing_user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($existing_user) {
            $message = "Email already registered!";
        } else {
            // Start transaction for atomicity
            $conn->begin_transaction();

            try {
                // Insert into users table based on role
                if ($role === 'student' || $role === 'department') {
                    // Students are verified by default, department admins need admin approval (0 for unverified)
                    $is_verified = ($role === 'student') ? 1 : 0;
                    $sql = "INSERT INTO users (username, password, name, role, department, is_verified) VALUES (?, ?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("sssssi", $username, $password, $name, $role, $department, $is_verified);
                } else {
                    // Other roles (accountant, librarian, admin, committees) need superadmin verification (0 for unverified)
                    $sql = "INSERT INTO users (username, password, name, role, is_verified) VALUES (?, ?, ?, ?, 0)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ssss", $username, $password, $name, $role);
                }

                if (!$stmt->execute()) {
                    throw new Exception("Error executing user insert: " . $stmt->error);
                }

                $user_id = $stmt->insert_id; // Get the ID of the newly inserted user
                $stmt->close();

                // If the role is student, insert additional student details
                if ($role === 'student') {
                    $student_sql = "INSERT INTO students (student_id, name, college_id, session, roll_number, stream)
                                    VALUES (?, ?, ?, ?, ?, ?)";
                    $student_stmt = $conn->prepare($student_sql);
                    $student_stmt->bind_param("isssss", $user_id, $name, $college_id, $session, $roll_number, $stream);

                    if (!$student_stmt->execute()) {
                        throw new Exception("Error inserting student details: " . $student_stmt->error);
                    }
                    $student_stmt->close();
                }

                // Commit transaction if all inserts are successful
                $conn->commit();
                $message = ucfirst($role) . " registered successfully.";
            } catch (Exception $e) {
                // Rollback transaction on any error
                $conn->rollback();
                throw $e; // Re-throw to be caught by the outer catch block
            }
        }
    } catch (Exception $e) {
        $message = "An error occurred: " . $e->getMessage();
    } finally {
        // Close the database connection in finally block to ensure it always closes
        if ($conn) {
            $conn->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - College Clearance System</title>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- Google Fonts for Poppins and Roboto -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <!-- AOS (Animate On Scroll) library -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        /* CSS Variables for consistent theming */
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
        }

        /* Register Page Body Styles with Blurred Image Background */
        body {
            font-family: 'Poppins', 'Roboto', sans-serif;
            line-height: 1.7;
            color: var(--text-dark);
            display: flex;
            /* Flexbox for centering content */
            flex-direction: column;
            /* Stack content vertically */
            justify-content: center;
            /* Centers content vertically */
            align-items: center;
            /* Centers content horizontally */
            min-height: 100vh;
            /* Minimum height of the viewport */
            background-image: url('https://images.pexels.com/photos/301920/pexels-photo-301920.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            position: relative;
            /* Needed for the ::before overlay */
            z-index: 1;
            /* Ensures body content is above the pseudo-element overlay */
            padding-top: 100px;
            /* Space for the fixed navbar */
            padding-bottom: var(--spacing-md);
            /* Add some padding at the bottom */
        }

        /* Semi-transparent overlay for background image, ensuring readability */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: var(--primary);
            opacity: 0.7;
            /* Semi-transparent blue overlay */
            z-index: -1;
            /* Puts it behind the body content */
        }

        /* Navbar styles - adapted for this page */
        .navbar {
            background-color: var(--text-light);
            /* White background for navbar */
            padding: var(--spacing-sm) 0;
            /* Vertical padding */
            box-shadow: var(--box-shadow-light);
            /* Subtle shadow */
            position: fixed;
            /* Fixed at the top */
            width: 100%;
            top: 0;
            z-index: 1000;
            /* High z-index to stay on top */
        }

        .nav-content {
            display: flex;
            justify-content: space-between;
            /* Space out logo and links */
            align-items: center;
            max-width: 1200px;
            /* Max width for content */
            margin: 0 auto;
            /* Center the content */
            padding: 0 var(--spacing-md);
            /* Horizontal padding */
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
            /* Slight scale on hover */
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
            /* For underline effect */
        }

        /* Underline effect for nav links */
        .nav-links a::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 0;
            /* Start with no width */
            height: 3px;
            background-color: var(--secondary);
            transition: width 0.3s ease-in-out;
        }

        .nav-links a:hover::after {
            width: 100%;
            /* Expand on hover */
        }

        .nav-links a:hover {
            color: var(--primary);
            transform: translateY(-3px);
            /* Slight lift on hover */
        }

        /* Register form specific styles */
        .register-wrapper {
            position: relative;
            width: 500px;
            /* Fixed width for the form container */
            background: var(--text-light);
            /* White background */
            border-radius: var(--border-radius);
            /* Rounded corners */
            box-shadow: var(--box-shadow-medium);
            /* Medium shadow */
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            /* Ensures border-radius applies to content */
            transform: scale(0.9);
            opacity: 0;
            animation: fadeInScaleIn 0.8s ease-out forwards;
            /* AOS animation effect */
            padding: var(--spacing-md);
            /* Internal padding */
            z-index: 5;
            /* Higher z-index than body overlay */
            border: 1px solid rgba(0, 0, 0, 0.05);
            /* Subtle border */
            flex-shrink: 0;
            /* Prevents shrinking on smaller screens */
        }

        /* Keyframe animation for the register wrapper */
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

        .register-form {
            width: 100%;
            padding: 0;
            /* Reset padding */
            color: var(--text-dark);
        }

        .register-form h2 {
            font-size: 2.2rem;
            font-weight: 700;
            text-align: center;
            color: var(--primary);
            margin-bottom: var(--spacing-md);
            position: relative;
            padding-bottom: var(--spacing-xs);
        }

        /* Underline effect for the form title */
        .register-form h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: var(--secondary);
            border-radius: var(--border-radius);
        }

        /* Input box container for consistent styling */
        .input-box {
            position: relative;
            width: 100%;
            height: 55px;
            margin-bottom: var(--spacing-md);
        }

        .input-box:first-of-type {
            margin-top: var(--spacing-md);
            /* Add margin-top to the first input box */
        }

        .input-box:last-of-type {
            margin-bottom: var(--spacing-sm);
            /* Adjust margin for the last input box before button/link */
        }

        .input-box input,
        .input-box select {
            width: 100%;
            height: 100%;
            background: var(--background);
            border: none;
            outline: none;
            border: 2px solid var(--accent);
            border-radius: var(--border-radius);
            font-size: 1.1rem;
            color: var(--text-dark);
            padding: 0 15px 0 45px;
            /* Padding to accommodate icon */
            transition: var(--transition);
            -webkit-appearance: none;
            /* Remove default arrow for selects */
            -moz-appearance: none;
            appearance: none;
            /* Custom arrow for select elements */
            background-image: url("data:image/svg+xml;charset=UTF8,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath fill='%23333' d='M7 10l5 5 5-5z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 15px center;
            background-size: 1.2em;
        }

        .input-box input:focus,
        .input-box input:valid,
        .input-box select:focus,
        .input-box select:valid {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(52, 152, 219, 0.2);
            /* Focus glow */
        }

        .input-box label {
            position: absolute;
            top: 50%;
            left: 45px;
            /* Aligns with input padding */
            transform: translateY(-50%);
            color: var(--text-dark);
            font-size: 1rem;
            pointer-events: none;
            /* Allows clicks to pass through to input */
            transition: var(--transition);
        }

        /* Floating label effect for text inputs */
        .input-box input:focus~label,
        .input-box input:valid~label,
        .input-box input.has-value~label {
            /* Added has-value for autofill */
            top: -10px;
            /* Moves label above input */
            left: 15px;
            /* Adjust left position */
            font-size: 0.8rem;
            color: var(--primary);
            background-color: var(--text-light);
            /* Background behind label */
            padding: 0 8px;
            border-radius: 5px;
            box-shadow: var(--box-shadow-light);
        }

        /* Icon styling */
        .input-box .icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 1.2em;
            color: var(--primary);
            z-index: 2;
            /* Ensures icon is above label and input */
        }

        /* Container for dynamically shown fields */
        #department-field,
        #student-fields {
            padding-top: 0;
            /* No extra padding for these sections */
        }

        /* Submit Button Styling */
        .btn {
            width: 100%;
            height: 50px;
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
            /* For ripple effect */
            z-index: 1;
            box-shadow: var(--box-shadow-medium);
            margin-top: var(--spacing-md);
            /* Ensure good spacing from fields above */
            letter-spacing: 0.6px;
        }

        .btn:hover {
            background: var(--primary-light);
            box-shadow: var(--box-shadow-heavy);
            transform: translateY(-5px);
            /* Lift effect on hover */
        }

        /* Ripple effect on button hover */
        .btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255, 255, 255, 0.3);
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

        /* Login link styling */
        .login-link {
            font-size: 0.9em;
            color: var(--text-dark);
            text-align: center;
            margin-top: var(--spacing-md);
        }

        .login-link p a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
        }

        .login-link p a:hover {
            text-decoration: underline;
            color: var(--primary-light);
        }

        /* Message box styling (success/error) */
        .message {
            color: var(--text-dark);
            background-color: var(--accent);
            border: 1px solid var(--secondary);
            padding: var(--spacing-sm);
            border-radius: var(--border-radius);
            margin-bottom: var(--spacing-md);
            text-align: center;
            font-weight: 600;
            animation: shake 0.5s ease-in-out;
            /* Error message shake */
            box-shadow: var(--box-shadow-light);
        }

        .message.success {
            background-color: #d1e7dd;
            /* Light green for success */
            color: #0f5132;
            /* Dark green text */
            border: 1px solid #badbcc;
            animation: none;
            /* No shake for success */
        }

        /* Shake animation for error messages */
        @keyframes shake {
            0% {
                transform: translateX(0);
            }

            20% {
                transform: translateX(-5px);
            }

            40% {
                transform: translateX(5px);
            }

            60% {
                transform: translateX(-5px);
            }

            80% {
                transform: translateX(5px);
            }

            100% {
                transform: translateX(0);
            }
        }

        /* Responsive Design Media Queries */
        @media (max-width: 768px) {
            .navbar {
                background-color: var(--text-light);
                backdrop-filter: none;
                /* No blur on smaller screens */
            }

            .nav-content {
                flex-direction: column;
                /* Stack logo and links vertically */
                text-align: center;
            }

            .nav-logo {
                margin-bottom: var(--spacing-sm);
            }

            .nav-links {
                margin-top: var(--spacing-sm);
                display: flex;
                flex-wrap: wrap;
                /* Allow links to wrap */
                justify-content: center;
            }

            .nav-links a {
                margin: var(--spacing-xs) var(--spacing-sm);
                /* Adjust link spacing */
            }

            .register-wrapper {
                width: 90%;
                /* Larger width on smaller screens */
                max-width: 450px;
                height: auto;
                padding: var(--spacing-md);
                transform: scale(1);
                /* No initial scale animation on mobile */
                opacity: 1;
                animation: none;
            }

            .register-form h2 {
                font-size: 1.8rem;
                /* Smaller title */
            }

            .input-box label {
                font-size: 0.9rem;
                /* Smaller label font */
            }

            .input-box input,
            .input-box select {
                font-size: 1rem;
                padding: 0 10px 0 40px;
                /* Adjust padding */
            }

            .input-box .icon {
                left: 10px;
                /* Adjust icon position */
            }

            .btn {
                font-size: 1em;
                height: 45px;
                /* Smaller button height */
            }
        }

        @media (max-width: 480px) {
            .register-wrapper {
                max-width: 95%;
                /* Even larger width on very small screens */
                padding: var(--spacing-sm);
            }

            .register-form h2 {
                font-size: 2rem;
            }

            .input-box {
                height: 50px;
                margin-bottom: var(--spacing-sm);
                /* Smaller margin on small screens */
            }

            .input-box:first-of-type {
                margin-top: var(--spacing-sm);
            }

            .input-box:last-of-type {
                margin-bottom: var(--spacing-xs);
            }

            #department-field,
            #student-fields {
                padding-top: 0;
            }

            .btn {
                margin-top: var(--spacing-sm);
            }

            .login-link {
                margin-top: var(--spacing-sm);
            }
        }
    </style>
</head>

<body>

    <!-- Navbar Section -->
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

    <!-- Register Form Wrapper -->
    <div class="register-wrapper" data-aos="zoom-in" data-aos-duration="1000">
        <div class="register-form">
            <h2>Register</h2>

            <!-- Message Display Area (PHP driven) -->
            <?php if ($message): ?>
                <div class="message <?= str_contains($message, 'successfully') ? 'success' : '' ?>">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <!-- Registration Form -->
            <form method="POST">
                <!-- Name Input -->
                <div class="input-box">
                    <span class="icon"><i class="fas fa-user"></i></span>
                    <input type="text" name="name" required oninput="this.classList.toggle('has-value', this.value.length > 0)">
                    <label>Full Name</label>
                </div>
                <!-- Email Input -->
                <!-- <div class="input-box">
                    <span class="icon"><i class="fas fa-envelope"></i></span>
                    <input type="email" name="email" required oninput="this.classList.toggle('has-value', this.value.length > 0)">
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
                <!-- Password Input -->
                <div class="input-box">
                    <span class="icon"><i class="fas fa-lock"></i></span>
                    <input type="password" name="password" required oninput="this.classList.toggle('has-value', this.value.length > 0)">
                    <label>Password</label>
                </div>
                <!-- Role Selection -->
                <div class="input-box">
                    <span class="icon"><i class="fas fa-user-tag"></i></span>
                    <select name="role" onchange="toggleFields()" required>
                        <option value="">Select Role</option>
                        <option value="student">Student</option>
                        <option value="department">Department Admin</option>
                        <option value="accountant">Accountant</option>
                        <option value="librarian">Librarian</option>
                        <option value="admin">Superadmin</option>
                        <option value="sports_committee">Sports Committee Convenor</option>
                        <option value="cultural_committee">Cultural Committee Convenor</option>
                        <option value="tech_committee">Technical Committee Convenor</option>
                        <option value="iic_committee">IIC Convenor</option>
                        <option value="samaritans_committee">Samaritans Convenor</option>
                        <option value="samarth_committee">Samarth Convenor</option>
                        <option value="eclectica_committee">Eclectica Convenor</option>
                    </select>
                </div>

                <!-- Department Field (Dynamically shown for Department Admin) -->
                <div id="department-field" style="display: none;">
                    <div class="input-box">
                        <span class="icon"><i class="fas fa-building"></i></span>
                        <select name="department">
                            <option value="">Select Department</option>
                            <option value="AIML">AIML</option>
                            <option value="DS">Data Science</option>
                            <option value="CSBS">Computer Science Business System</option>
                            <option value="CSCS">Computer Science Cyber Security</option>
                            <option value="IT">Information Technology</option>
                            <option value="CSE">Computer Science Engineering</option>
                            <option value="ECE">Electronics and Communication Engineering</option>
                            <option value="EIE">Electronics and Instrumentation Engineering</option>
                            <option value="EE">Electrical Engineering</option>
                            <option value="ME">Mechanical Engineering</option>
                            <option value="CE">Civil Engineering</option>
                            <option value="FT">Food Technology</option>
                        </select>
                    </div>
                </div>

                <!-- Student-specific fields (Dynamically shown for Student) -->
                <div id="student-fields" style="display: none;">
                    <div class="input-box">
                        <span class="icon"><i class="fas fa-id-card"></i></span>
                        <input type="text" name="college_id" oninput="this.classList.toggle('has-value', this.value.length > 0)">
                        <label>College ID</label>
                    </div>
                    <div class="input-box">
                        <span class="icon"><i class="fas fa-calendar-alt"></i></span>
                        <input type="text" name="session" oninput="this.classList.toggle('has-value', this.value.length > 0)">
                        <label>Session (e.g. 2021-2025)</label>
                    </div>
                    <div class="input-box">
                        <span class="icon"><i class="fas fa-hashtag"></i></span>
                        <input type="text" name="roll_number" oninput="this.classList.toggle('has-value', this.value.length > 0)">
                        <label>Roll Number</label>
                    </div>
                    <div class="input-box">
                        <span class="icon"><i class="fas fa-graduation-cap"></i></span>
                        <select name="stream" onchange="this.classList.toggle('has-value', this.value.length > 0)">
                            <option value="">Select Stream</option>
                            <option value="AIML">AIML</option>
                            <option value="DS">Data Science</option>
                            <option value="CSBS">Computer Science Business System</option>
                            <option value="CSCS">Computer Science Cyber Security</option>
                            <option value="IT">Information Technology</option>
                            <option value="CSE">Computer Science Engineering</option>
                            <option value="ECE">Electronics and Communication Engineering</option>
                            <option value="EIE">Electronics and Instrumentation Engineering</option>
                            <option value="EE">Electrical Engineering</option>
                            <option value="ME">Mechanical Engineering</option>
                            <option value="CE">Civil Engineering</option>
                            <option value="FT">Food Technology</option>
                        </select>
                    </div>
                </div>

                <!-- Login Link -->
                <p class="login-link">
                    Already have an account?
                    <a href="login.php">Login</a>
                </p>

                <!-- Register Button -->
                <button type="submit" class="btn">Register</button>
            </form>
        </div>
    </div>

    <!-- AOS JavaScript for animations -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        // Initialize AOS library
        AOS.init({
            duration: 800, // Animation duration
            once: true // Only animate once
        });


        function toggleFields() {
            const role = document.querySelector('select[name="role"]').value;
            const departmentField = document.getElementById("department-field");
            const studentFields = document.getElementById("student-fields");

            // Toggle display based on role
            departmentField.style.display = (role === "department") ? "block" : "none";
            studentFields.style.display = (role === "student") ? "block" : "none";

            // Manage required attributes for student fields
            document.querySelectorAll('#student-fields input').forEach(input => {
                if (role === "student") {
                    input.setAttribute('required', 'required');
                } else {
                    input.removeAttribute('required');
                }
                // Also remove 'has-value' class if field is hidden and empty
                if (!input.value && role !== "student") {
                    input.classList.remove('has-value');
                }
            });

            // Manage required attribute for department select
            const departmentSelect = document.querySelector('#department-field select[name="department"]');
            if (departmentSelect) {
                if (role === "department") {
                    departmentSelect.setAttribute('required', 'required');
                } else {
                    departmentSelect.removeAttribute('required');
                }
            }

            // For select elements, ensure the label logic is handled if needed
            // (The current CSS handles icons on selects, and labels are removed from HTML for selects)
        }

        // Add 'has-value' class on page load for autofilled inputs and set up event listeners
        document.addEventListener("DOMContentLoaded", function() {
            document.querySelectorAll('.input-box input').forEach(input => {
                // If input has a value (e.g., from browser autofill), add 'has-value' class
                if (input.value) {
                    input.classList.add('has-value');
                }
                // Add/remove 'has-value' on focus/blur to control label position
                input.addEventListener('focus', () => {
                    input.classList.add('has-value');
                });
                input.addEventListener('blur', () => {
                    if (!input.value) {
                        input.classList.remove('has-value');
                    }
                });
            });
            const emailInput = document.querySelector('input[name="email"]');
            if (emailInput) {
                emailInput.addEventListener('input', function() {
                    if (this.value.length > 0 && !this.value.endsWith('@gmail.com') && !this.value.includes('@')) {
                        this.setCustomValidity("Please include '@gmail.com' in your email address.");
                    } else if (this.value.length > 0 && this.value.includes('@') && !this.value.endsWith('@gmail.com')) {
                        this.setCustomValidity("Only Gmail addresses are allowed (e.g., yourname@gmail.com)");
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

            // Ensure fields are correctly toggled on page load based on initial selection (if any)
            toggleFields();
        });
    </script>
</body>

</html>