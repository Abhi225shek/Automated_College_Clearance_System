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

// --- START: Conditional Rendering for Admin vs. Other Roles ---
if ($role === 'admin') {
    // If user is an admin, show the simple dashboard
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - College Clearance System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* CSS Variables - Kept for potential future use or consistent color palette */
        :root {
            --primary: #6B7FD7;
            --primary-light: #8B9BE0;
            --secondary: #9BA4D9;
            --accent: #D4D9F3;
            --text-dark: #2C3E50;
            --text-light: #ffffff;
            --background: #F8F9FE;
            --spacing-xs: 0.5rem;
            --spacing-sm: 1rem;
            --spacing-md: 2rem;
            --spacing-lg: 4rem;
            --border-radius: 8px;
            --transition: all 0.3s ease;
            --card-shadow: 0 4px 10px rgba(0, 0, 0, 0.05); /* Lighter shadow */
        }

        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: var(--spacing-md);
            background-color: #f0f2f5;
            color: var(--text-dark);
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
        }

        .dashboard-container {
            width: 100%;
            max-width: 600px;
            margin: var(--spacing-md) auto;
            background-color: var(--background);
            border-radius: var(--border-radius);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
            padding: var(--spacing-lg);
            text-align: center;
        }

        .dashboard-container h2 {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: var(--spacing-xs);
            position: relative;
            padding-bottom: var(--spacing-sm);
        }

        .dashboard-container h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 120px;
            height: 5px;
            background: var(--secondary);
            border-radius: var(--border-radius);
        }

        .dashboard-container h4 {
            font-size: 1.4rem;
            color: var(--text-dark);
            margin-top: var(--spacing-sm);
            margin-bottom: var(--spacing-md);
        }

        .card {
            background-color: var(--accent);
            border-radius: var(--border-radius);
            padding: var(--spacing-md);
            margin-bottom: var(--spacing-md);
            box-shadow: var(--card-shadow);
            border: none;
        }

        .card-title {
            font-size: 1.6rem;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: var(--spacing-md);
        }

        .btn {
            padding: 12px 25px;
            border: none;
            outline: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-size: 1em;
            color: var(--text-light);
            font-weight: 600;
            transition: var(--transition);
            margin: var(--spacing-xs) 0;
            width: 100%;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            text-decoration: none;
            display: inline-block;
        }

        /* Using Bootstrap classes for button colors, can customize with --vars */
        .btn-warning { background-color: #ffc107; color: var(--text-dark); }
        .btn-warning:hover { background-color: #e0a800; transform: translateY(-2px); box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15); }

        .btn-logout {
            background-color: #dc3545;
            color: var(--text-light);
            margin-top: var(--spacing-md);
        }
        .btn-logout:hover {
            background-color: #c82333;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
        }

        .alert {
            padding: var(--spacing-sm);
            border-radius: var(--border-radius);
            text-align: center;
            margin: var(--spacing-md) auto;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        @media (max-width: 480px) {
            .dashboard-container {
                padding: var(--spacing-md);
                margin: var(--spacing-sm) auto;
            }
            .dashboard-container h2 {
                font-size: 2rem;
            }
            .dashboard-container h4 {
                font-size: 1.2rem;
            }
            .card-title {
                font-size: 1.4rem;
            }
            .btn {
                padding: 10px 20px;
                font-size: 0.95em;
            }
        }
    </style>
</head>
<body>

<div class="dashboard-container">
    <div class="text-center welcome">
        <h2>Welcome, <?= htmlspecialchars($name) ?></h2>
        <h4>Your Role: <?= ucfirst(str_replace('_', ' ', $role)) ?></h4>
        <hr>
    </div>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Super Admin Options</h5>
            <a href="admin_management.php" class="btn btn-warning">Manage Users</a>
        </div>
    </div>

    <a href="logout.php" class="btn btn-logout">Logout</a>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
    exit(); // Stop further execution for admin role
}
// --- END: Conditional Rendering for Admin vs. Other Roles ---


// --- START: Original Sidebar Dashboard for Student, Department, etc. ---
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
            // Admin: Manage users - This case should theoretically not be hit now for admins
            // as they are redirected to a different HTML output.
            // However, keep it here for robustness or if a non-admin somehow accesses it.
            include 'admin_management.php';
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
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
            background: linear-gradient(135deg, var(--accent) 0%, var(--background) 100%);
            display: flex;
            min-height: 100vh;
            overflow-x: hidden;
            transition: background-color var(--transition), color var(--transition);
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
            transition: margin-left var(--transition);
            flex-shrink: 0;
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
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
            width: 20px;
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
            flex-grow: 1;
            padding: var(--spacing-md);
            transition: margin-left var(--transition);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            min-height: 100vh;
            overflow-y: auto;
        }

        .content-wrapper {
            width: 100%;
            max-width: 1000px;
            margin: auto;
            padding-top: var(--spacing-sm);
        }

        .card {
            background-color: var(--card-bg);
            padding: var(--spacing-lg);
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            transition: background-color var(--transition), color var(--transition), box-shadow var(--transition);
            min-height: 300px;
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
            display: none;
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
            z-index: 9999;
        }

        /* --- Menu Toggle Button (for smaller screens) --- */
        .menu-toggle {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1001;
            display: none;
            background-color: var(--primary);
            color: var(--text-light);
            border: none;
            padding: 10px 15px;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-size: 1.5rem;
            line-height: 1;
            transition: background-color var(--transition);
        }

        .menu-toggle:hover {
            background-color: var(--primary-light);
        }

        /* --- Responsive Design --- */
        @media (max-width: 768px) {
            .sidebar {
                margin-left: -250px;
                position: fixed;
                height: 100%;
                z-index: 1000;
            }

            .sidebar.active {
                margin-left: 0;
            }

            .main-content {
                width: 100%;
                padding-left: var(--spacing-sm);
                padding-right: var(--spacing-sm);
            }

            .menu-toggle {
                display: block;
            }

            body.sidebar-open .main-content {
                filter: blur(2px);
                pointer-events: none;
            }

            .card {
                padding: var(--spacing-md);
            }
        }
    </style>
</head>
<body>
    <button class="menu-toggle" onclick="toggleSidebar()">☰</button>

    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="user-info">
                <h3>Welcome, <?php echo htmlspecialchars($name); ?></h3>
                <div class="status"><?= ucfirst(str_replace('_', ' ', $role)) ?><?= !empty($department) ? " ({$department})" : "" ?></div>
            </div>
        </div>
        <nav>
            <ul class="nav-menu">
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

    <main class="main-content">
        <div class="content-wrapper">
            <div class="card">
                <div id="dynamic-content">
                    </div>
            </div>
        </div>
    </main>

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

        // Auto-load a default page for non-admin roles on dashboard load
        document.addEventListener("DOMContentLoaded", function() {
            const role = "<?php echo $role; ?>";
            let defaultPage = 'welcome'; // Default for non-specific roles or initial load

            // Set default page based on user role (excluding admin as it's handled above)
            if (role === 'student') {
                defaultPage = 'create_clearance_form';
            } else if (['department', 'accountant', 'librarian', 'sports_committee', 'cultural_committee', 'tech_committee', 'iic_committee', 'samaritans_committee', 'samarth_committee', 'eclectica_committee'].includes(role)) {
                defaultPage = 'review_clearance_requests';
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
<?php
// --- END: Original Sidebar Dashboard for Student, Department, etc. ---
?>