<?php
session_start(); // Start the session - IMPORTANT: This was commented out in your code.
include 'db.php'; // Include your database connection

// Redirect to login page if user is not logged in or is not an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Initialize an empty array to store messages
$messages = [];

// Handle user addition
if (isset($_POST['add_user'])) {
    $name = trim($_POST['name']);
    $username = trim($_POST['username']);
    $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);
    $role = $_POST['role'];
    // Department is optional and only relevant for 'department' role
    // Also, enforce department selection if role is 'department'
    if ($role === 'department' && empty($_POST['department'])) {
        $messages[] = ['type' => 'error', 'text' => 'Error: Department must be selected for Department Admin role.'];
    } else {
        $department = isset($_POST['department']) && !empty($_POST['department']) ? $_POST['department'] : null;
        // Students and admins are verified by default, others are pending (0)
        $is_verified = ($role == 'student' || $role == 'admin') ? 1 : 0;

        // Check if username already exists to prevent duplicates
        $check_sql = "SELECT user_id FROM users WHERE username = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $username);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            $messages[] = ['type' => 'error', 'text' => 'Error: Username already exists!'];
        } else {
            try {
                // Insert new user into the database
                $sql = "INSERT INTO users (name, username, password, role, department, is_verified) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssssi", $name, $username, $password, $role, $department, $is_verified);
                
                if ($stmt->execute()) {
                    $messages[] = ['type' => 'success', 'text' => 'User added successfully!'];
                } else {
                    $messages[] = ['type' => 'error', 'text' => 'Error adding user: ' . $stmt->error];
                }
            } catch (mysqli_sql_exception $e) {
                // Catch database-related exceptions
                $messages[] = ['type' => 'error', 'text' => 'Database error: ' . htmlspecialchars($e->getMessage())];
            }
        }
    }
}

// Handle user deletion
if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    
    // Prevent the admin from deleting their own account
    if ($delete_id == $_SESSION['user_id']) {
        $messages[] = ['type' => 'error', 'text' => 'Error: You cannot delete your own account!'];
    } else {
        // Delete user from the database
        $sql = "DELETE FROM users WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $delete_id);
        
        if ($stmt->execute()) {
            $messages[] = ['type' => 'success', 'text' => 'User deleted successfully!'];
        } else {
            $messages[] = ['type' => 'error', 'text' => 'Error deleting user: ' . $stmt->error];
        }
    }
}

// Handle user verification
if (isset($_GET['verify'])) {
    $verify_id = intval($_GET['verify']);
    // Update user's verification status to 1 (verified)
    $sql = "UPDATE users SET is_verified = 1 WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $verify_id);
    
    if ($stmt->execute()) {
        $messages[] = ['type' => 'success', 'text' => 'User verified successfully!'];
    } else {
        $messages[] = ['type' => 'error', 'text' => 'Error verifying user: ' . $stmt->error];
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Management</title>
    <style>
        /* Using the provided CSS variables for a consistent theme */
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
            --card-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }

        body {
            font-family: 'Inter', sans-serif; /* Using Inter as per dashboard */
            background-color: #f0f2f5; /* Light grey background */
            color: var(--text-dark);
            margin: 0;
            padding: var(--spacing-md);
            padding-top: 0; /* Remove extra padding at the top of the body itself */
            line-height: 1.6;
            display: flex;
            justify-content: center; /* Center content horizontally */
            align-items: flex-start; /* Align content to the top */
            min-height: 100vh; /* Full viewport height */
        }

        .admin-container {
            width: 100%;
            max-width: 1000px; /* Max width for the entire admin panel */
            margin: 0 auto; /* Center the container */
            margin-top: 0; /* Remove any default top margin */
            padding: var(--spacing-lg); /* Padding inside the main container */
            background-color: var(--background);
            border-radius: var(--border-radius);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1); /* Deeper shadow */
            text-align: left;
        }

        .admin-container h2 {
            font-size: 2.5rem;
            font-weight: 700;
            text-align: center;
            color: var(--primary);
            margin-bottom: var(--spacing-xs); /* Reduces space below the heading */
            position: relative;
            padding-bottom: var(--spacing-sm); /* Space for the underline */
        }

        .admin-container h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 120px;
            height: 5px;
            background: var(--secondary); /* Secondary color for the underline */
            border-radius: var(--border-radius);
        }

        .section-card {
            background-color: var(--accent); /* Using accent for sections to differentiate */
            border-radius: var(--border-radius);
            padding: var(--spacing-md);
            margin-bottom: var(--spacing-lg); /* Space between sections */
            box-shadow: var(--card-shadow);
        }

        .section-card h3 {
            font-size: 1.8rem;
            font-weight: 600;
            color: var(--primary); /* Primary color for section headings */
            margin-top: 0;
            margin-bottom: var(--spacing-md);
            text-align: center;
            position: relative;
            padding-bottom: var(--spacing-xs);
        }
        .section-card h3::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 70px; /* Longer underline for section headings */
            height: 4px;
            background: var(--primary-light);
            border-radius: var(--border-radius);
        }

        /* Form Group Styles */
        .form-group {
            margin-bottom: var(--spacing-sm);
            display: flex; /* Use flexbox for aligned labels and inputs */
            align-items: center;
            flex-wrap: wrap; /* Allow wrapping on small screens */
        }
        .form-group label {
            flex: 0 0 150px; /* Fixed width for labels */
            margin-right: var(--spacing-sm);
            font-weight: 600;
            color: var(--text-dark);
            font-size: 1rem;
            text-align: right; /* Align labels to the right */
        }
        .form-group input[type="text"],
        .form-group input[type="password"],
        .form-group select {
            flex: 1; /* Allow input to grow and take available space */
            padding: 10px 15px;
            background: var(--background);
            border: 2px solid var(--primary-light);
            border-radius: var(--border-radius);
            font-size: 1rem;
            color: var(--text-dark);
            transition: var(--transition);
            box-sizing: border-box; /* Include padding and border in element's total width */
            min-width: 200px; /* Ensure a minimum width for inputs on larger screens */
        }
        .form-group input:focus,
        .form-group select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(107, 127, 215, 0.2); /* Focus glow */
            outline: none;
        }
        /* Specific adjustment for role/department select to prevent excessive width */
        .form-group select {
            max-width: calc(100% - 150px - var(--spacing-sm)); /* Adjust based on label width */
        }

        .form-buttons {
            text-align: center;
            margin-top: var(--spacing-md);
        }
        .btn-action {
            padding: 12px 25px;
            border: none;
            outline: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-size: 1em;
            color: var(--text-light);
            font-weight: 600;
            transition: var(--transition);
            margin: 0 var(--spacing-xs);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            letter-spacing: 0.5px;
        }
        .btn-success { background: #28a745; } /* Green for success */
        .btn-success:hover { background: #218838; transform: translateY(-2px); box-shadow: 0 6px 15px rgba(0, 128, 0, 0.3); }

        /* Table Styles */
        .table-responsive {
            overflow-x: auto; /* Enable horizontal scrolling on small screens */
            -webkit-overflow-scrolling: touch; /* Smooth scrolling for iOS */
            margin-top: var(--spacing-md);
        }

        table {
            border-collapse: separate; /* Use separate for rounded corners */
            border-spacing: 0; /* Remove spacing between cells */
            width: 100%;
            min-width: 700px; /* Ensure table doesn't get too narrow, allowing overflow-x */
            box-shadow: var(--card-shadow);
            border-radius: var(--border-radius);
            overflow: hidden; /* Ensures rounded corners are applied */
            background-color: var(--background);
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid var(--accent); /* Bottom border for rows */
        }
        th {
            background-color: var(--primary);
            color: var(--text-light);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.9em;
        }
        tbody tr:nth-child(even) {
            background-color: #f0f2f5; /* Light stripe for zebra effect */
        }
        tbody tr:hover {
            background-color: var(--accent); /* Highlight row on hover */
            transition: var(--transition);
        }
        td:last-child {
            text-align: center; /* Center actions column content */
            display: flex; /* Make buttons side-by-side */
            justify-content: center; /* Center them horizontally */
            gap: 10px; /* Space between buttons */
            flex-wrap: wrap; /* Allow wrapping on small screens */
        }

        .btn-danger { background-color: #dc3545; } /* Red for danger actions */
        .btn-danger:hover { background-color: #c82333; transform: translateY(-2px); box-shadow: 0 6px 15px rgba(255, 0, 0, 0.3); }
        .btn-verify { background-color: #007bff; } /* Blue for verification */
        .btn-verify:hover { background-color: #0056b3; transform: translateY(-2px); box-shadow: 0 6px 15px rgba(0, 0, 255, 0.3); }

        /* Status text within table */
        .status-verified { color: #28a745; font-weight: 600; } /* Green for verified */
        .status-unverified { color: #dc3545; font-weight: 600; } /* Red for unverified */

        /* Message Alerts */
        .message-alert {
            padding: var(--spacing-sm);
            border-radius: var(--border-radius);
            text-align: center;
            margin: var(--spacing-md) auto;
            max-width: 600px;
            font-size: 1em;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .message-alert.error {
            background-color: #f8d7da; /* Light red */
            color: #721c24; /* Dark red */
            border: 1px solid #f5c6cb;
        }
        .message-alert.success {
            background-color: #d4edda; /* Light green */
            color: #155724; /* Dark green */
            border: 1px solid #c3e6cb;
        }

        /* Back to Dashboard Button Container */
        .dashboard-actions {
            display: flex;
            justify-content: center; /* Center the buttons horizontally */
            gap: var(--spacing-sm); /* Space between buttons */
            margin-top: var(--spacing-lg);
            flex-wrap: wrap; /* Allow wrapping on smaller screens */
        }

        /* Common Button Style for Dashboard and Logout */
        .btn-common {
            display: inline-block;
            padding: 12px 25px;
            border: none;
            outline: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-size: 1em;
            color: var(--text-light);
            font-weight: 600;
            transition: var(--transition);
            text-decoration: none;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        /* Back to Dashboard Button Specific Styles */
        .btn-back-to-dashboard {
            background: var(--secondary);
        }
        .btn-back-to-dashboard:hover {
            background: #7B85C4;
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.15);
        }

        /* Logout Button Specific Styles */
        .btn-logout {
            background: #ffc107; /* A distinct color for logout, e.g., yellow/orange */
            color: var(--text-dark); /* Dark text for better contrast */
        }
        .btn-logout:hover {
            background: #e0a800;
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.15);
        }


        /* Responsive adjustments */
        @media (max-width: 768px) {
            .admin-container {
                padding: var(--spacing-md);
                margin: var(--spacing-sm) auto;
            }
            .admin-container h2 {
                font-size: 2rem;
                margin-bottom: var(--spacing-md);
            }
            .section-card {
                padding: var(--spacing-sm);
                margin-bottom: var(--spacing-md);
            }
            .section-card h3 {
                font-size: 1.5rem;
                margin-bottom: var(--spacing-sm);
            }
            .form-group {
                flex-direction: column; /* Stack label and input vertically */
                align-items: flex-start;
            }
            .form-group label {
                width: 100%; /* Full width label */
                text-align: left;
                margin-right: 0;
                margin-bottom: var(--spacing-xs);
            }
            .form-group input,
            .form-group select {
                width: 100%;
                max-width: 100%; /* Override max-width for selects */
            }
            .btn-action {
                display: block; /* Make buttons stack vertically */
                width: calc(100% - (2 * var(--spacing-xs)));
                max-width: 200px; /* Keep button width reasonable */
                margin: var(--spacing-xs) auto;
            }

            /* Table specific responsive styles */
            table {
                min-width: unset; /* Remove fixed min-width for mobile stacking */
            }
            .table-responsive {
                overflow-x: auto; /* Keep horizontal scroll for larger tables if needed */
            }
            table, thead, tbody, th, td, tr {
                display: block; /* Make table elements stack */
            }
            thead tr {
                position: absolute;
                top: -9999px;
                left: -9999px; /* Hide table headers */
            }
            tr {
                border: 1px solid var(--accent);
                margin-bottom: var(--spacing-sm);
                border-radius: var(--border-radius);
                overflow: hidden;
            }
            td {
                border: none;
                border-bottom: 1px solid var(--accent);
                position: relative;
                padding-left: 50%; /* Space for data-label */
                text-align: right;
            }
            td:before {
                position: absolute;
                top: 12px;
                left: 12px;
                width: 45%;
                padding-right: 10px;
                white-space: nowrap;
                content: attr(data-label); /* Use data-label for content */
                font-weight: 600;
                color: var(--primary-light);
                text-align: left;
            }
            td:last-child {
                border-bottom: none;
                text-align: center;
                display: flex; /* Use flex for action buttons */
                flex-wrap: wrap;
                justify-content: center;
                padding-left: var(--spacing-sm); /* Adjust padding for action buttons */
            }
            td:last-child .btn-action {
                margin: 5px; /* Adjust button margin within cell */
            }

            .dashboard-actions {
                flex-direction: column; /* Stack buttons vertically on small screens */
                align-items: center; /* Center them */
            }
            .btn-common {
                width: calc(100% - (2 * var(--spacing-sm))); /* Adjust width for stacked buttons */
                max-width: 250px; /* Limit max width */
            }
        }

        @media (max-width: 480px) {
            .admin-container {
                padding: var(--spacing-sm);
            }
            .admin-container h2 {
                font-size: 1.8rem;
            }
            .section-card h3 {
                font-size: 1.3rem;
            }
            .message-alert {
                font-size: 0.9em;
            }
        }
    </style>
</head>
<body>

<div class="admin-container">
    <h2>Super Admin Panel - Manage Users</h2>

    <?php
    // Display any accumulated messages at the top
    foreach ($messages as $message) {
        echo "<div class='message-alert " . htmlspecialchars($message['type']) . "'>" . htmlspecialchars($message['text']) . "</div>";
    }
    ?>

    <div class="section-card">
        <h3>Add New User</h3>
        <form method="POST">
            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" placeholder="Full Name" required>
            </div>
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" placeholder="Email" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" placeholder="Password" required>
            </div>
            <div class="form-group">
                <label for="role">Role:</label>
                <select id="role" name="role" required onchange="toggleDepartmentField(this.value)">
                    <option value="">Select Role</option>
                    <option value="department">Department Admin</option>
                    <option value="accountant">Accountant</option>
                    <option value="librarian">Librarian</option>
                    <option value="admin">Admin</option>
                    <option value="sports_committee">Sports Committee Convenor</option>
                    <option value="cultural_committee">Cultural Committee Convenor</option>
                    <option value="tech_committee">Technical Committee Convenor</option>
                    <option value="iic_committee">IIC Convenor</option>
                    <option value="samaritans_committee">Samaritans Convenor</option>
                    <option value="samarth_committee">Samarth Convenor</option>
                    <option value="eclectica_committee">Eclectica Convenor</option>
                </select>
            </div>
            <div class="form-group" id="department-group" style="display:none;">
                <label for="department">Department:</label>
                <select id="department" name="department">
                    <option value="">Select Department</option>
                    <option value="AIML">Artificial Intelligence And Machine Learning</option>
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
            <div class="form-buttons">
                <button type="submit" class="btn-action btn-success" name="add_user">Add User</button>
            </div>
        </form>
    </div>

    <script>
    function toggleDepartmentField(role) {
        const departmentGroup = document.getElementById('department-group');
        const departmentSelect = document.getElementById('department');

        if (!departmentGroup || !departmentSelect) {
            console.error("Error: Department elements not found!");
            return;
        }

        if (role === 'department') {
            departmentGroup.style.display = 'flex'; // Use 'flex' to match .form-group CSS
            departmentSelect.setAttribute('required', 'required'); // Make department required
        } else {
            departmentGroup.style.display = 'none';
            departmentSelect.removeAttribute('required'); // Remove required
            departmentSelect.value = ''; // Clear selection
        }
    }

    // Ensure the function runs when the admin_management.php page itself loads
    document.addEventListener('DOMContentLoaded', function() {
        const roleSelect = document.getElementById('role');
        if (roleSelect) {
            toggleDepartmentField(roleSelect.value); // Set initial state
        }
    });
    </script>

    ---

    <div class="section-card">
        <h3>All Users</h3>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Department</th>
                        <th>Verified?</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                // Fetch all users from the database for display
                $user_sql = "SELECT * FROM users ORDER BY role, name";
                $users = $conn->query($user_sql);
                if ($users && $users->num_rows > 0):
                    while ($user = $users->fetch_assoc()):
                ?>
                        <tr>
                            <td data-label="ID"><?= $user['user_id'] ?></td>
                            <td data-label="Name"><?= htmlspecialchars($user['name']) ?></td>
                            <td data-label="Username"><?= htmlspecialchars($user['username']) ?></td>
                            <td data-label="Role">
                                <?php
                                    $displayRole = ucfirst(str_replace('_', ' ', $user['role']));
                                    if ($user['role'] == 'department') {
                                        $displayRole = 'Department Admin';
                                    }
                                    echo $displayRole;
                                ?>
                            </td>
                            <td data-label="Department"><?= htmlspecialchars($user['department'] ?? '-') ?></td>
                            <td data-label="Verified?">
                                <?php if ($user['is_verified']): ?>
                                    <span class="status-verified">Yes</span>
                                <?php else: ?>
                                    <span class="status-unverified">No</span>
                                <?php endif; ?>
                            </td>
                            <td data-label="Actions">
                                <?php if (!$user['is_verified'] && $user['role'] != 'admin'): ?>
                                    <a class="btn-action btn-verify" href="?verify=<?= $user['user_id'] ?>" onclick="return confirm('Verify this user?')">Verify</a>
                                <?php endif; ?>
                                <?php if ($user['user_id'] != $_SESSION['user_id']): // Prevent admin from deleting self ?>
                                    <a class="btn-action btn-danger" href="?delete=<?= $user['user_id'] ?>" onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                <?php
                    endwhile;
                else:
                ?>
                    <tr>
                        <td colspan="7" style="text-align: center;">No users found in the system.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="dashboard-actions">
        <a href='dashboard.php' class="btn-common btn-back-to-dashboard">← Back to Dashboard</a>
        <a href='logout.php' class="btn-common btn-logout" onclick="return confirm('Are you sure you want to log out?')">Logout</a>
    </div>
</div>

</body>
</html>