<?php
session_start(); // Start the session
include 'db.php'; // Include your database connection file

// Redirect to login page if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Retrieve user information from session
$user_id = $_SESSION['user_id'];
$name = $_SESSION['username']; // Assuming 'username' actually stores the user's name or display name
$role = $_SESSION['role'];
$department = $_SESSION['department'] ?? ''; // Use null coalescing for department

// Handle AJAX requests for content loading
if (isset($_GET['ajax']) && isset($_GET['page'])) {
    ob_start(); // Start output buffering to capture included file's output
    switch ($_GET['page']) {
        case 'welcome': // Default welcome content
            echo '<h1>Welcome to Your Dashboard!</h1>';
            echo '<p>Hello, ' . htmlspecialchars($name) . '. Select an option from the sidebar to get started with your clearance process.</p>';
            break;
        case 'create_clearance_form':
            include 'clearance_form.php'; // Student: Create a new form
            break;
        case 'view_clearance_status':
            include 'view_status.php'; // Student: View their forms
            break;
        case 'review_clearance_requests':
            // This needs to dynamically include the correct review page based on $role
            switch ($role) {
                case 'department':
                case 'accountant':
                case 'librarian':
                case 'sports_committee':
                case 'cultural_committee':
                case 'tech_committee':
                case 'iic_committee':
                case 'samaritans_committee':
                case 'samarth_committee':
                case 'eclectica_committee':
                    include 'review_clearance.php'; // Example: create this file with relevant review logic
                    break;
                default:
                    echo '<div class="alert alert-danger">Access Denied for this role.</div>';
                    break;
            }
            break;
        case 'manage_users':
            include 'admin_management.php'; // Admin: Manage users
            break;
        default:
            echo '<div class="alert alert-warning">Page not found or unauthorized.</div>';
            break;
    }
    $content = ob_get_clean(); // Get content from buffer
    echo $content; // Output the captured content
    exit(); // Stop further execution for AJAX requests
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - College Clearance System</title>
    <!-- Google Fonts for Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        /* CSS Variables for Consistent Theming */
        :root {
            --primary: #6B7FD7;
            --primary-light: #8B9BE0;
            --secondary: #9BA4D9;
            --accent: #D4D9F3;
            --text-dark: #2C3E50;
            --text-light: #ffffff;
            --background: #F8F9FE;
            --sidebar-bg: var(--text-dark);
            --sidebar-text: var(--text-light);
            --sidebar-hover: var(--primary-light);
            --card-bg: #ffffff;
            --card-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            --spacing-xs: 0.5rem;
            --spacing-sm: 1rem;
            --spacing-md: 2rem;
            --spacing-lg: 4rem;
            --border-radius: 8px;
            --transition: all 0.3s ease;
        }

        /* Reset and Base Styles */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Inter', sans-serif;
            line-height: 1.6;
            color: var(--text-dark);
            /* Gradient background from accent to main background color */
            background: linear-gradient(135deg, var(--accent) 0%, var(--background) 100%);
            display: flex; /* Use flexbox for sidebar and main content layout */
            min-height: 100vh; /* Ensure body takes full viewport height */
            overflow-x: hidden; /* Prevent horizontal scrolling */
            transition: background-color var(--transition), color var(--transition); /* Smooth theme transitions */
        }

        /* Dark Mode (Not fully implemented in JS, but styles are here if you add it later) */
        .dark-mode {
            background: #1a1a1a;
            color: #e0e0e0;
        }

        .dark-mode .sidebar {
            background-color: #333;
            color: #e0e0e0;
        }

        .dark-mode .sidebar-header .status {
            background-color: #555;
            color: #eee;
        }

        .dark-mode .nav-link {
            color: #e0e0e0;
        }

        .dark-mode .nav-link:hover {
            background-color: #555;
        }

        .dark-mode .card {
            background-color: #2c2c2c;
            color: #e0e0e0;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
        }

        .dark-mode .menu-toggle {
            background-color: var(--primary-light);
            color: var(--text-light);
        }

        /* --- Sidebar Styles --- */
        .sidebar {
            width: 250px;
            background-color: var(--sidebar-bg);
            color: var(--sidebar-text);
            padding: var(--spacing-md) var(--spacing-sm);
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.2);
            transition: margin-left var(--transition); /* For responsive sliding effect */
            flex-shrink: 0; /* Prevent sidebar from shrinking */
            position: sticky; /* Sticky positioning to keep it in view when scrolling main content */
            top: 0; /* Stick to the top */
            height: 100vh; /* Full viewport height */
            overflow-y: auto; /* Allow scrolling for long navigation lists */
        }

        .sidebar-header {
            margin-bottom: var(--spacing-md);
            text-align: center;
            padding-bottom: var(--spacing-sm);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .user-info h3 {
            font-size: 1.3rem;
            margin-bottom: 5px;
            color: var(--text-light);
        }

        .user-info .status {
            font-size: 0.9rem;
            background-color: var(--primary);
            color: var(--text-light);
            padding: 4px 10px;
            border-radius: 20px;
            display: inline-block;
            margin-top: 5px;
        }

        .nav-menu {
            list-style: none;
            padding: 0;
            margin-top: var(--spacing-sm);
        }

        .nav-item {
            margin-bottom: 10px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 10px 15px;
            text-decoration: none;
            color: var(--sidebar-text);
            border-radius: var(--border-radius);
            transition: background-color var(--transition), color var(--transition);
        }

        .nav-link i {
            margin-right: 15px;
            font-size: 1.2rem;
            width: 20px; /* Fixed width for icon to align text */
            text-align: center;
        }

        .nav-link span {
            font-size: 1.1rem;
        }

        .nav-link:hover,
        .nav-link.active {
            background-color: var(--sidebar-hover);
            color: var(--text-light);
        }

        /* --- Main Content Area --- */
        .main-content {
            flex-grow: 1; /* Allows main content to take remaining space */
            padding: var(--spacing-md);
            transition: margin-left var(--transition);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start; /* Align content to the top */
            min-height: 100vh; /* Ensure it takes full height */
            overflow-y: auto; /* Allow scrolling for its content */
        }

        .content-wrapper {
            width: 100%;
            max-width: 1000px; /* Max width for content within main area */
            margin: auto; /* Center content within the wrapper */
            padding-top: var(--spacing-sm); /* Small padding from the top of main-content */
        }

        .card {
            background-color: var(--card-bg);
            padding: var(--spacing-lg); /* Larger padding inside cards */
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            transition: background-color var(--transition), color var(--transition), box-shadow var(--transition);
            min-height: 300px; /* Minimum height for cards */
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        .card h1 {
            font-size: 2.2rem;
            margin-bottom: var(--spacing-sm);
            color: var(--primary);
        }

        .card p {
            font-size: 1.1rem;
            color: var(--text-dark);
        }

        /* --- Loading Spinner --- */
        .loading {
            display: none; /* Hidden by default */
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 1.5rem;
            z-index: 9999; /* High z-index to overlay everything */
        }

        /* --- Menu Toggle Button (for smaller screens) --- */
        .menu-toggle {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1001; /* Above sidebar when hidden */
            display: none; /* Hidden on larger screens */
            background-color: var(--primary);
            color: var(--text-light);
            border: none;
            padding: 10px 15px;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-size: 1.5rem;
            line-height: 1; /* For better icon alignment */
            transition: background-color var(--transition);
        }

        .menu-toggle:hover {
            background-color: var(--primary-light);
        }

        /* --- Responsive Design --- */
        @media (max-width: 768px) {
            .sidebar {
                margin-left: -250px; /* Hide sidebar off-screen */
                position: fixed; /* Fix sidebar position for sliding */
                height: 100%;
                z-index: 1000;
            }

            .sidebar.active {
                margin-left: 0; /* Slide sidebar into view */
            }

            .main-content {
                width: 100%; /* Main content takes full width */
                padding-left: var(--spacing-sm);
                padding-right: var(--spacing-sm);
            }

            .menu-toggle {
                display: block; /* Show menu toggle button */
            }

            /* Blur effect on main content when sidebar is open */
            body.sidebar-open .main-content {
                filter: blur(2px);
                pointer-events: none; /* Disable interaction with blurred content */
            }

            .card {
                padding: var(--spacing-md); /* Adjust card padding for smaller screens */
            }
        }
    </style>
</head>
<body>
    <!-- Menu toggle button for mobile -->
    <button class="menu-toggle" onclick="toggleSidebar()">☰</button>

    <!-- Sidebar Navigation -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="user-info">
                <h3>Welcome, <?php echo htmlspecialchars($name); ?></h3>
                <div class="status"><?= ucfirst(str_replace('_', ' ', $role)) ?><?= !empty($department) ? " ({$department})" : "" ?></div>
            </div>
        </div>
        <nav>
            <ul class="nav-menu">
                <!-- Navigation links based on user role -->
                <?php if ($role === 'student'): ?>
                    <li class="nav-item">
                        <a href="#" class="nav-link" data-page="create_clearance_form">
                            <i class="fas fa-file-alt"></i>
                            <span>Create Clearance Form</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link" data-page="view_clearance_status">
                            <i class="fas fa-eye"></i>
                            <span>View My Clearance Status</span>
                        </a>
                    </li>
                <?php elseif (in_array($role, ['department', 'accountant', 'librarian', 'sports_committee', 'cultural_committee', 'tech_committee', 'iic_committee', 'samaritans_committee', 'samarth_committee', 'eclectica_committee'])): ?>
                    <li class="nav-item">
                        <a href="#" class="nav-link" data-page="review_clearance_requests">
                            <i class="fas fa-clipboard-list"></i>
                            <span>Review Clearance Requests</span>
                        </a>
                    </li>
                <?php elseif ($role === 'admin'): ?>
                    <li class="nav-item">
                        <a href="#" class="nav-link" data-page="manage_users">
                            <i class="fas fa-users-cog"></i>
                            <span>Manage Users</span>
                        </a>
                    </li>
                <?php endif; ?>

                <li class="nav-item">
                    <a href="logout.php" class="nav-link">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </nav>
    </aside>

    <!-- Main Content Area -->
    <main class="main-content">
        <div class="content-wrapper">
            <div class="card">
                <div id="dynamic-content">
                    <!-- Content will be loaded here via AJAX -->
                </div>
            </div>
        </div>
    </main>

    <!-- Loading Spinner -->
    <div class="loading" id="loading">Loading...</div>

    <script>
        // Function to toggle sidebar visibility on mobile
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('active');
            document.body.classList.toggle('sidebar-open'); // Add class to body for blur effect
        }

        // Add event listeners to navigation links for AJAX content loading
        document.querySelectorAll('.nav-link[data-page]').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault(); // Prevent default link behavior
                const page = this.dataset.page; // Get the target page from data-page attribute
                loadContent(page); // Load content using AJAX

                // Update active state of navigation links
                document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
                this.classList.add('active');

                // Close sidebar on mobile after clicking a link
                if (window.innerWidth <= 768) {
                    document.getElementById('sidebar').classList.remove('active');
                    document.body.classList.remove('sidebar-open');
                }
            });
        });

        /**
         * Loads content into the #dynamic-content div using AJAX.
         * @param {string} page - The page identifier to load (e.g., 'create_clearance_form').
         */
        function loadContent(page) {
            const loading = document.getElementById('loading');
            const content = document.getElementById('dynamic-content');

            loading.style.display = 'flex'; // Show loading spinner
            content.innerHTML = ''; // Clear previous content

            // Fetch content from the server
            fetch(`dashboard.php?ajax=1&page=${page}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.text(); // Get response as text (HTML)
                })
                .then(html => {
                    content.innerHTML = html; // Insert fetched HTML into content area
                })
                .catch(error => {
                    // Display error message if fetch fails
                    content.innerHTML = '<div class="alert alert-danger" style="color: red; padding: 15px; border: 1px solid red; border-radius: 5px;">Error loading content. Please try again.</div>';
                    console.error('Error:', error);
                })
                .finally(() => {
                    loading.style.display = 'none'; // Hide loading spinner regardless of success or failure
                });
        }

        // Auto-load a default page for all roles on dashboard load
        document.addEventListener("DOMContentLoaded", function() {
            const role = "<?php echo $role; ?>";
            let defaultPage = 'welcome'; // Default for non-specific roles or initial load

            // Set default page based on user role
            if (role === 'student') {
                defaultPage = 'create_clearance_form';
            } else if (['department', 'accountant', 'librarian', 'sports_committee', 'cultural_committee', 'tech_committee', 'iic_committee', 'samaritans_committee', 'samarth_committee', 'eclectica_committee'].includes(role)) {
                defaultPage = 'review_clearance_requests';
            } else if (role === 'admin') {
                defaultPage = 'manage_users';
            }

            loadContent(defaultPage); // Load the determined default content

            // Set initial active state for the default page in the sidebar
            const defaultLink = document.querySelector(`.nav-link[data-page="${defaultPage}"]`);
            if (defaultLink) {
                defaultLink.classList.add('active');
            }
        });
    </script>
</body>
</html>
