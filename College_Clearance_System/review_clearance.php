<?php
//session_start(); // Start the session if not already started by dashboard.php
include 'db.php'; // Include your database connection

// Redirect if user is not logged in or doesn't have an authorized role
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['department', 'accountant', 'librarian', 'sports_committee', 'cultural_committee', 'tech_committee', 'iic_committee', 'samaritans_committee', 'samarth_committee', 'eclectica_committee'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Check if the user is verified by the Super Admin
$sql = "SELECT is_verified, department FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user['is_verified'] != 1) {
    // Display a styled alert if the user is not verified
    echo "<div class='verification-alert'><h2>You are not verified by the Super Admin yet. You cannot review clearance forms.</h2><a href='dashboard.php' class='btn-back'>Back to Dashboard</a></div>";
    exit();
}

$admin_department = isset($user['department']) ? $user['department'] : null;

/**
 * Formats the approval status into a styled HTML span.
 * @param int $approved_val The approval status (1: APPROVED, 0: PENDING, -1: REJECTED).
 * @return string HTML span with appropriate class and text.
 */
function format_status($approved_val) {
    if ($approved_val == 1) return "<span class='status-approved'>APPROVED</span>";
    elseif ($approved_val == 0) return "<span class='status-pending'>PENDING</span>";
    else return "<span class='status-rejected'>REJECTED</span>";
}

// Role-to-section mapping for specific review sections
$role_section_map = [
    'accountant' => 'accounts',
    'librarian' => 'library',
    'sports_committee' => 'sports_committee',
    'cultural_committee' => 'cultural_committee',
    'tech_committee' => 'tech_committee',
    'iic_committee' => 'iic_committee',
    'samaritans_committee' => 'samaritans_committee',
    'samarth_committee' => 'samarth_committee',
    'eclectica_committee' => 'eclectica_committee'
];

$query = ""; // Initialize query string
$stmt = null; // Initialize statement object

// Build query based on user role to fetch relevant forms
if ($role == 'department') {
    if (!$admin_department) {
        echo "<div class='info-message'><p>No department assigned to your account. Please contact admin.</p><a href='dashboard.php' class='btn-back'>Back to Dashboard</a></div>";
        exit();
    }
    // Department admins see forms for students in their assigned department, specifically their 'department' status
    $query = "
        SELECT cf.form_id, s.name AS student_name, s.roll_number, s.stream, cf.submitted_at,
               cs.approved, cs.updated_at
        FROM clearance_forms cf
        JOIN students s ON cf.student_id = s.student_id
        JOIN clearance_status cs ON cf.form_id = cs.form_id
        WHERE s.stream = ? 
        AND cs.section = 'department'
        AND cs.updated_at = (
            SELECT MAX(updated_at) 
            FROM clearance_status 
            WHERE form_id = cf.form_id AND section = 'department'
        )
        ORDER BY cf.submitted_at DESC
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $admin_department);
} elseif (in_array($role, ['sports_committee', 'cultural_committee', 'tech_committee', 'iic_committee', 'samaritans_committee', 'samarth_committee', 'eclectica_committee'])) {
    // For committee roles, show all clearance forms as they might review forms from any stream
    // and their section check will happen when the 'review_form.php' loads the specific status.
    $query = "
        SELECT cf.form_id, s.name AS student_name, s.roll_number, s.stream, cf.submitted_at
        FROM clearance_forms cf
        JOIN students s ON cf.student_id = s.student_id
        ORDER BY cf.submitted_at DESC
    ";
    $stmt = $conn->prepare($query);
} else {
    // For other specific roles (accountant, librarian), filter by their specific section status
    $section = $role_section_map[$role];
    $query = "
        SELECT cf.form_id, s.name AS student_name, s.roll_number, s.stream, cf.submitted_at,
               cs.approved, cs.updated_at
        FROM clearance_forms cf
        JOIN students s ON cf.student_id = s.student_id
        JOIN clearance_status cs ON cf.form_id = cs.form_id
        WHERE cs.section = ?
        AND cs.updated_at = (
            SELECT MAX(updated_at) 
            FROM clearance_status 
            WHERE form_id = cf.form_id AND section = ?
        )
        ORDER BY cf.submitted_at DESC
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $section, $section);
}

// Execute the query
if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    // Fallback if no query was prepared (should not happen with current logic, but good practice)
    $result = null;
}
?>

<!--
    This style block is designed to be embedded or included directly
    within the dynamic content area of dashboard.php.
    It re-uses the CSS variables defined in dashboard.php's :root.
-->
<style>
    /* Review Forms Specific Styles */
    .review-forms-container {
        width: 100%;
        max-width: 1000px; /* Max width for the container */
        margin: var(--spacing-md) auto; /* Top/bottom margin, auto left/right to center */
        padding: var(--spacing-lg); /* Inner padding */
        background-color: var(--background); /* Light background */
        border-radius: var(--border-radius); /* Rounded corners */
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05); /* Subtle shadow */
        color: var(--text-dark); /* Dark text color */
    }

    .review-forms-container h2 {
        font-size: 2.2rem;
        font-weight: 700;
        text-align: center;
        color: var(--primary); /* Primary blue color */
        margin-bottom: var(--spacing-md);
        position: relative;
        padding-bottom: var(--spacing-xs); /* Space for the underline */
    }

    .review-forms-container h2::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 100px;
        height: 4px;
        background: var(--secondary); /* Secondary color for the underline */
        border-radius: var(--border-radius);
    }

    /* Table Styles */
    .review-forms-table {
        width: 100%;
        border-collapse: collapse; /* Collapse table borders */
        margin-top: var(--spacing-md);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05); /* Table shadow */
        border-radius: var(--border-radius);
        overflow: hidden; /* Ensures border-radius applies to table content */
    }

    .review-forms-table th,
    .review-forms-table td {
        padding: 12px 15px;
        text-align: left;
        border-bottom: 1px solid var(--accent); /* Bottom border for rows */
    }

    .review-forms-table th {
        background-color: var(--primary-light); /* Header background */
        color: var(--text-light); /* White text for headers */
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.9em;
    }

    .review-forms-table tr:nth-child(even) {
        background-color: #eef1f6; /* Slightly lighter row for zebra striping */
    }

    .review-forms-table tr:hover {
        background-color: var(--accent); /* Highlight row on hover */
        transition: var(--transition);
    }

    .review-forms-table td {
        background-color: var(--background); /* Cell background */
        font-size: 0.95em;
    }

    /* Status Badges - these styles are available for format_status function */
    .status-approved {
        color: var(--text-light);
        background-color: #28a745; /* Green */
        padding: 5px 10px;
        border-radius: var(--border-radius);
        font-weight: 600;
        font-size: 0.85em;
    }

    .status-pending {
        color: var(--text-light);
        background-color: #ffc107; /* Yellow/Orange */
        padding: 5px 10px;
        border-radius: var(--border-radius);
        font-weight: 600;
        font-size: 0.85em;
    }

    .status-rejected {
        color: var(--text-light);
        background-color: #dc3545; /* Red */
        padding: 5px 10px;
        border-radius: var(--border-radius);
        font-weight: 600;
        font-size: 0.85em;
    }

    .status-not-reviewed {
        color: var(--text-light);
        background-color: #007bff; /* Blue for 'Not Reviewed' */
        padding: 5px 10px;
        border-radius: var(--border-radius);
        font-weight: 600;
        font-size: 0.85em;
    }


    /* Buttons */
    .btn-action, .btn-back {
        display: inline-block;
        padding: 8px 15px;
        border-radius: var(--border-radius);
        text-decoration: none;
        font-weight: 600;
        transition: var(--transition);
        font-size: 0.9em;
        text-align: center;
        white-space: nowrap; /* Prevent text wrapping */
        border: 1px solid transparent; /* Consistent border for hover effects */
    }

    .btn-action {
        background-color: var(--primary);
        color: var(--text-light);
        border-color: var(--primary);
    }

    .btn-action:hover {
        background-color: var(--primary-light);
        border-color: var(--primary-light);
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .btn-back {
        background-color: var(--secondary);
        color: var(--text-light);
        border-color: var(--secondary);
        margin-top: var(--spacing-md);
        padding: 10px 20px; /* Slightly larger for main back button */
    }

    .btn-back:hover {
        background-color: #7B85C4; /* Slightly darker secondary for hover */
        border-color: #7B85C4;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    /* Message Alerts (Verification and Info) */
    .verification-alert, .info-message {
        padding: var(--spacing-md);
        border-radius: var(--border-radius);
        text-align: center;
        margin: var(--spacing-md) auto;
        max-width: 600px;
        background-color: var(--accent); /* Light background for alerts */
        border: 1px solid var(--secondary); /* Border color matching theme */
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        color: var(--text-dark); /* Ensure text is readable */
    }

    .verification-alert h2 {
        color: var(--text-dark); /* Use theme dark text for better contrast */
        margin-bottom: var(--spacing-sm);
    }
    .info-message p {
        color: var(--text-dark);
        margin-bottom: var(--spacing-sm);
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .review-forms-container {
            padding: var(--spacing-md);
            margin: var(--spacing-sm) auto;
        }
        .review-forms-container h2 {
            font-size: 1.8rem;
        }
        .review-forms-table th,
        .review-forms-table td {
            padding: 8px 10px;
            font-size: 0.85em;
        }
        .btn-action, .btn-back {
            padding: 6px 10px;
            font-size: 0.8em;
        }
    }

    @media (max-width: 480px) {
        .review-forms-container {
            padding: var(--spacing-sm);
        }
        .review-forms-table thead {
            display: none; /* Hide table headers on small screens */
        }
        .review-forms-table,
        .review-forms-table tbody,
        .review-forms-table tr,
        .review-forms-table td {
            display: block; /* Make table elements stack */
            width: 100%; /* Take full width */
        }
        .review-forms-table tr {
            margin-bottom: var(--spacing-xs);
            border: 1px solid var(--accent);
            border-radius: var(--border-radius);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.03);
            background-color: var(--background);
        }
        .review-forms-table td {
            text-align: right; /* Align cell content to the right */
            padding-left: 50%; /* Make space for data-label */
            position: relative;
            border-bottom: 1px dashed var(--accent); /* Dotted separator between cells */
        }
        .review-forms-table td:last-child {
            border-bottom: none; /* No border for the last cell in a stacked row */
        }
        .review-forms-table td::before {
            content: attr(data-label); /* Use data-label for pseudo-elements */
            position: absolute;
            left: 0;
            width: 50%;
            padding-left: 15px;
            font-weight: 600;
            text-align: left;
            color: var(--primary-light);
        }
        /* Define data-label content for each column */
        .review-forms-table td:nth-of-type(1)::before { content: "Form ID:"; }
        .review-forms-table td:nth-of-type(2)::before { content: "Student Name:"; }
        .review-forms-table td:nth-of-type(3)::before { content: "Roll Number:"; }
        .review-forms-table td:nth-of-type(4)::before { content: "Stream:"; }
        .review-forms-table td:nth-of-type(5)::before { content: "Submitted At:"; }
        .review-forms-table td:nth-of-type(6)::before { content: "Action:"; }
    }
</style>

<div class="review-forms-container">
    <h2>Clearance Forms Pending for Review</h2>

    <?php
    if ($result && $result->num_rows > 0) {
        echo "<table class='review-forms-table'>";
        echo "<thead><tr>
                <th>Form ID</th>
                <th>Student Name</th>
                <th>Roll Number</th>
                <th>Stream</th>
                <th>Submitted At</th>
                <th>Action</th>
              </tr></thead>";
        echo "<tbody>";

        while ($row = $result->fetch_assoc()) {
            $form_id = $row['form_id'];
            // Determine the correct section for the 'Review' link based on the current user's role
            $link_section = '';
            if ($role == 'department') {
                $link_section = 'department';
            } elseif ($role == 'accountant') {
                $link_section = 'accounts';
            } elseif ($role == 'librarian') {
                $link_section = 'library';
            } elseif ($role == 'sports_committee') {
                $link_section = 'sports_committee';
            } elseif ($role == 'cultural_committee') {
                $link_section = 'cultural_committee';
            } elseif ($role == 'tech_committee') {
                $link_section = 'tech_committee';
            } elseif ($role == 'iic_committee') {
                $link_section = 'iic_committee';
            } elseif ($role == 'samaritans_committee') {
                $link_section = 'samaritans_committee';
            } elseif ($role == 'samarth_committee') {
                $link_section = 'samarth_committee';
            } elseif ($role == 'eclectica_committee') {
                $link_section = 'eclectica_committee';
            }

            echo "<tr>
                    <td data-label='Form ID'>{$form_id}</td>
                    <td data-label='Student Name'>" . htmlspecialchars($row['student_name']) . "</td>
                    <td data-label='Roll Number'>" . htmlspecialchars($row['roll_number']) . "</td>
                    <td data-label='Stream'>" . htmlspecialchars($row['stream']) . "</td>
                    <td data-label='Submitted At'>" . htmlspecialchars($row['submitted_at']) . "</td>
                    <td data-label='Action'><a href='review_form.php?form_id={$form_id}&section={$link_section}' class='btn-action'>Review</a></td>
                </tr>";
        }

        echo "</tbody></table>";
    } else {
        echo "<p class='info-message'>No clearance forms found for review.</p>";
    }

    echo "<div style='text-align: center; margin-top: var(--spacing-md);'><a href='dashboard.php' class='btn-back'>Back to Dashboard</a></div>";

    // Close statement and connection
    if ($stmt) {
        $stmt->close();
    }
    if ($conn) {
        $conn->close();
    }
    ?>
</div>
