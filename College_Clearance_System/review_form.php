<?php
session_start(); // Start the session if not already started by dashboard.php
include 'db.php'; // Include your database connection

// Redirect if user is not logged in or doesn't have an authorized role
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], [
    'department', 'accountant', 'librarian', 
    'sports_committee', 'cultural_committee', 'tech_committee',
    'iic_committee','samaritans_committee','samarth_committee','eclectica_committee'
])) {
    header("Location: login.php");
    exit();
}

$role = $_SESSION['role'];
$admin_id = $_SESSION['user_id'];

// Check if form_id and section are provided in the URL
if (!isset($_GET['form_id']) || !isset($_GET['section'])) {
    echo "<div class='message-alert error'>No form selected or section not specified.</div>";
    exit();
}

$form_id = intval($_GET['form_id']);
$section = $_GET['section'];

// Map roles to their corresponding clearance sections
$role_section_map = [
    'accountant' => 'accounts',
    'librarian' => 'library',
    'department' => 'department',
    'sports_committee' => 'sports_committee',
    'cultural_committee' => 'cultural_committee',
    'tech_committee' => 'tech_committee',
    'iic_committee' => 'iic_committee',
    'samaritans_committee' => 'samaritans_committee',
    'samarth_committee' => 'samarth_committee',
    'eclectica_committee' => 'eclectica_committee'
];

// Validate if the selected section is appropriate for the user's role
if (!isset($role_section_map[$role]) || $role_section_map[$role] !== $section) {
    echo "<div class='message-alert error'><h3>Invalid section for your role.</h3></div>";
    exit();
}

// Fetch admin's verification status and department (for 'department' role)
$sql = "SELECT is_verified, department FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$admin_data = $result->fetch_assoc();

// Check if admin is verified
if (!$admin_data || $admin_data['is_verified'] != 1) {
    echo "<div class='message-alert error'><h3>Access denied. You are either not verified or not found.</h3></div>";
    exit();
}

$admin_department = $admin_data['department'];

// Fetch form data along with student details
$query = "SELECT cf.form_id, cf.submitted_at, s.* FROM clearance_forms cf
          JOIN students s ON cf.student_id = s.student_id
          WHERE cf.form_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $form_id);
$stmt->execute();
$result = $stmt->get_result();
$form = $result->fetch_assoc();

// Check if the form exists
if (!$form) {
    echo "<div class='message-alert info'>Clearance form not found.</div>";
    exit();
}

// Restrict 'department' role to reviewing forms only for students in their assigned department
if ($role == 'department' && strcasecmp($form['stream'], $admin_department) !== 0) {
    echo "<div class='message-alert error'><h3>Access denied. You are not authorized to review forms of other departments.</h3></div>";
    exit();
}

// Fetch all remarks associated with this form
$query = "SELECT section, remark FROM clearance_remarks WHERE form_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $form_id);
$stmt->execute();
$result = $stmt->get_result();
$remarks = [];
while ($row = $result->fetch_assoc()) {
    $remarks[$row['section']] = $row['remark'];
}

// Fetch the most recent status for the current section of this form
$query = "SELECT approved, signature, comments FROM clearance_status
          WHERE form_id = ? AND section = ?
          ORDER BY updated_at DESC LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bind_param("is", $form_id, $section);
$stmt->execute();
$result = $stmt->get_result();
$current_status = $result->fetch_assoc(); // This will be null if no status exists for this section/form

// Handle form submission (Approve/Reject)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action']; // 'approve' or 'reject'
    $signature = mysqli_real_escape_string($conn, $_POST['signature']); // Sanitize input
    $comments = mysqli_real_escape_string($conn, $_POST['comments'] ?? ''); // Sanitize input, default to empty string if not set
    $approval_value = ($action === 'approve') ? 1 : -1; // 1 for approved, -1 for rejected

    // Insert a new status record to maintain history
    $insert_query = "INSERT INTO clearance_status 
                     (form_id, section, approved, signature, comments, updated_at)
                     VALUES (?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("isiss", $form_id, $section, $approval_value, $signature, $comments);

    if ($stmt->execute()) {
        echo "<div class='message-alert success'>Form has been successfully " . strtoupper($action) . "D.</div>";
        // Refresh the current status data after successful submission to update the display
        $query = "SELECT approved, signature, comments FROM clearance_status
                  WHERE form_id = ? AND section = ?
                  ORDER BY updated_at DESC LIMIT 1";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("is", $form_id, $section);
        $stmt->execute();
        $result = $stmt->get_result();
        $current_status = $result->fetch_assoc(); // Update $current_status with the new data

    } else {
        echo "<div class='message-alert error'>Error: " . $stmt->error . "</div>";
    }
}
?>

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
        --card-shadow: 0 4px 10px rgba(0, 0, 0, 0.05); /* Added for clarity in card styling */
    }

    /* Main form container and heading styles - consistent with College Clearance form */
    .form-container {
        width: 100%;
        max-width: 900px; /* Wider for more content */
        margin: var(--spacing-md) auto;
        padding: var(--spacing-lg);
        background-color: var(--background);
        border-radius: var(--border-radius);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
        color: var(--text-dark);
        text-align: left;
    }

    .form-container h2 {
        font-size: 2.5rem; /* Larger main heading */
        font-weight: 700;
        text-align: center;
        color: var(--primary);
        margin-bottom: var(--spacing-lg); /* More space below heading */
        position: relative;
        padding-bottom: var(--spacing-sm);
    }

    .form-container h2::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 120px; /* Longer underline */
        height: 5px; /* Thicker underline */
        background: var(--secondary);
        border-radius: var(--border-radius);
    }

    /* Card-based sections */
    .info-card {
        background-color: var(--background);
        border: 1px solid var(--accent);
        border-radius: var(--border-radius);
        padding: var(--spacing-md);
        margin-bottom: var(--spacing-lg); /* Spacing between cards */
        box-shadow: var(--card-shadow); /* Subtle shadow for cards */
    }

    .info-card h3 {
        font-size: 1.6rem; /* Section heading size */
        font-weight: 600;
        color: var(--primary-light);
        margin-top: 0; /* Remove default top margin */
        margin-bottom: var(--spacing-sm);
        text-align: center;
        position: relative;
        padding-bottom: var(--spacing-xs);
    }
    .info-card h3::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 50px;
        height: 3px;
        background: var(--accent);
        border-radius: var(--border-radius);
    }

    /* Key-value pairs inside cards for student info */
    .detail-item {
        margin-bottom: var(--spacing-xs); /* Tighter spacing for detail items */
        line-height: 1.6;
        font-size: 1.05em;
        display: flex; /* Use flexbox for alignment */
        align-items: baseline;
    }
    .detail-item strong {
        color: var(--text-dark);
        flex-shrink: 0; /* Prevent label from shrinking */
        width: 180px; /* Fixed width for labels for alignment */
        margin-right: var(--spacing-sm);
    }
    .detail-item span {
        flex-grow: 1; /* Allow value to take remaining space */
    }

    /* Remarks section specific */
    .remarks-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); /* Responsive grid for remarks */
        gap: var(--spacing-md);
        margin-top: var(--spacing-md);
    }
    .remark-item {
        background-color: var(--background); /* Use background for remark items too */
        border: 1px solid var(--primary-light);
        border-radius: var(--border-radius);
        padding: var(--spacing-sm);
        box-shadow: 0 2px 5px rgba(0,0,0,0.02);
    }
    .remark-item strong {
        display: block;
        color: var(--primary);
        margin-bottom: var(--spacing-xs);
        font-size: 1em;
    }
    .remark-item p {
        font-size: 0.9em;
        line-height: 1.4;
        margin: 0;
    }

    /* Current status section specific (also an info-card) */
    .status-section-card {
        background-color: var(--accent); /* Accent background for this section */
        padding: var(--spacing-md);
        border-radius: var(--border-radius);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08); /* More prominent shadow */
        margin-bottom: var(--spacing-lg); /* Spacing below the status card */
        text-align: center;
    }

    .status-section-card h3 {
        color: var(--primary); /* More prominent heading */
    }
    .status-section-card h3::after {
        background: var(--primary-light);
    }

    .status-display {
        font-size: 1.2em; /* Larger status text */
        font-weight: 700;
        display: inline-block;
        padding: 10px 20px;
        border-radius: var(--border-radius);
        margin-top: var(--spacing-sm);
        margin-bottom: var(--spacing-sm);
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    /* Status colors remain the same */
    .status-approved { background-color: #28a745; color: var(--text-light); }
    .status-pending { background-color: #ffc107; color: var(--text-dark); }
    .status-rejected { background-color: #dc3545; color: var(--text-light); }
    .status-default { background-color: var(--secondary); color: var(--text-light); }

    /* Review Action Form (also an info-card) */
    .review-action-form-card {
        background-color: var(--accent); /* Accent background for this section */
        padding: var(--spacing-lg);
        border-radius: var(--border-radius);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08); /* More shadow for interaction section */
        margin-top: var(--spacing-lg);
    }
    .review-action-form-card h3 {
        color: var(--primary); /* Consistent heading style */
        margin-bottom: var(--spacing-md);
    }
    .review-action-form-card h3::after {
        background: var(--primary-light);
    }

    .form-group-custom { /* Reusing the generic form group for consistent input styling */
        margin-bottom: var(--spacing-md);
    }
    .form-group-custom label {
        display: block;
        margin-bottom: var(--spacing-xs);
        font-weight: 600;
        color: var(--text-dark);
        font-size: 1rem;
    }
    .form-group-custom textarea {
        width: 100%;
        padding: 12px 15px;
        background: var(--background);
        border: 2px solid var(--primary-light);
        border-radius: var(--border-radius);
        font-size: 1rem;
        color: var(--text-dark);
        transition: var(--transition);
        box-sizing: border-box;
        min-height: 80px;
        resize: vertical;
    }
    .form-group-custom textarea:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(107, 127, 215, 0.2);
        outline: none;
    }

    .form-buttons {
        text-align: center;
        margin-top: var(--spacing-md);
    }
    .btn-action {
        padding: 14px 30px; /* Larger buttons */
        border: none;
        outline: none;
        border-radius: var(--border-radius);
        cursor: pointer;
        font-size: 1.1em;
        color: var(--text-light);
        font-weight: 600;
        transition: var(--transition);
        margin: 0 var(--spacing-sm); /* Increased space between buttons */
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        letter-spacing: 0.8px;
        min-width: 150px;
    }
    .btn-approve {
        background: #28a745;
    }
    .btn-approve:hover {
        background: #218838;
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0, 128, 0, 0.3);
    }
    .btn-reject {
        background: #dc3545;
    }
    .btn-reject:hover {
        background: #c82333;
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(255, 0, 0, 0.3);
    }

    /* Back Link Button - Consistent with other forms */
    .btn-back-to-list {
        display: inline-block;
        padding: 12px 25px;
        background: var(--secondary);
        border: none;
        outline: none;
        border-radius: var(--border-radius);
        cursor: pointer;
        font-size: 1em;
        color: var(--text-light);
        font-weight: 600;
        transition: var(--transition);
        margin-top: var(--spacing-lg);
        text-decoration: none;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }
    .btn-back-to-list:hover {
        background: #7B85C4;
        transform: translateY(-3px);
        box-shadow: 0 8px 15px rgba(0, 0, 0, 0.15);
    }

    /* Message Alerts - Reusing existing styles */
    .message-alert {
        padding: var(--spacing-md);
        border-radius: var(--border-radius);
        text-align: center;
        margin: var(--spacing-md) auto;
        max-width: 600px;
        font-size: 1.1em;
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
    .message-alert.info {
        background-color: #d1ecf1; /* Light blue */
        color: #0c5460; /* Dark blue */
        border: 1px solid #bee5eb;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .form-container {
            padding: var(--spacing-md);
            margin: var(--spacing-sm) auto;
        }
        .form-container h2 {
            font-size: 2rem;
            margin-bottom: var(--spacing-md);
        }
        .info-card, .status-section-card, .review-action-form-card {
            padding: var(--spacing-sm);
            margin-bottom: var(--spacing-md);
        }
        .info-card h3, .status-section-card h3, .review-action-form-card h3 {
            font-size: 1.4rem;
        }
        .detail-item {
            flex-direction: column; /* Stack label and value on small screens */
            align-items: flex-start;
        }
        .detail-item strong {
            width: auto; /* Remove fixed width */
            margin-right: 0;
            margin-bottom: 5px; /* Space between label and value */
        }
        .remarks-grid {
            grid-template-columns: 1fr; /* Single column on smaller screens */
            gap: var(--spacing-sm);
        }
        .btn-action, .btn-back-to-list {
            padding: 10px 20px;
            font-size: 0.9em;
            margin: var(--spacing-xs); /* Adjust margin for stacking */
            width: calc(100% - (2 * var(--spacing-xs))); /* Full width minus margin */
            max-width: 250px; /* Limit width */
            display: block; /* Make them block to stack */
        }
        .form-buttons {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
    }

    @media (max-width: 480px) {
        .form-container {
            padding: var(--spacing-sm);
        }
        .form-container h2 {
            font-size: 1.6rem;
        }
        .info-card h3, .status-section-card h3, .review-action-form-card h3 {
            font-size: 1.2rem;
        }
        .detail-item, .remark-item p {
            font-size: 0.9em;
        }
    }
</style>

<div class="form-container">
    <h2>Review Clearance Form</h2>

    <div class="info-card">
        <h3>Student Information</h3>
        <div class="detail-item"><strong>Student Name:</strong> <span><?= htmlspecialchars($form['name']) ?></span></div>
        <div class="detail-item"><strong>Roll Number:</strong> <span><?= htmlspecialchars($form['roll_number']) ?></span></div>
        <div class="detail-item"><strong>Stream:</strong> <span><?= htmlspecialchars($form['stream']) ?></span></div>
        <div class="detail-item"><strong>Submitted At:</strong> <span><?= htmlspecialchars($form['submitted_at']) ?></span></div>
    </div>

    <div class="info-card">
        <h3>All Submitted Remarks</h3>
        <div class="remarks-grid">
            <?php
            // Define all possible sections to display remarks for
            $all_sections_for_remarks = [
                'accounts', 'library', 'department', 
                'tech_committee', 'cultural_committee', 'sports_committee',
                'iic_committee', 'samaritans_committee', 'samarth_committee', 'eclectica_committee'
            ];
            $has_remarks = false; // Flag to check if any remarks are displayed
            foreach ($all_sections_for_remarks as $sect):
                if (isset($remarks[$sect])):
                    $has_remarks = true;
            ?>
                    <div class="remark-item">
                        <strong><?= ucfirst(str_replace('_', ' ', $sect)) ?>:</strong>
                        <p><?= htmlspecialchars($remarks[$sect]) ?></p>
                    </div>
            <?php
                endif;
            endforeach;
            if (!$has_remarks):
            ?>
                <p style="text-align: center; color: var(--text-dark); grid-column: 1 / -1;">No remarks have been submitted yet for this form.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="status-section-card">
        <h3>Current Status for <?= ucfirst(str_replace('_', ' ', $section)) ?></h3>
        <p>Status:
            <span class="status-display
                <?php
                    if ($current_status) {
                        if ($current_status['approved'] == 1) echo 'status-approved';
                        elseif ($current_status['approved'] == 0) echo 'status-pending';
                        else echo 'status-rejected';
                    } else {
                        echo 'status-default'; // If no status found for this section (i.e., not reviewed yet)
                    }
                ?>
            ">
                <?php
                    if ($current_status) {
                        if ($current_status['approved'] == 1) echo 'APPROVED';
                        elseif ($current_status['approved'] == 0) echo 'PENDING';
                        else echo 'REJECTED';
                    } else {
                        echo 'NOT REVIEWED YET';
                    }
                ?>
            </span>
        </p>
        <?php if ($current_status && !empty($current_status['signature'])): ?>
            <p><strong>Last Reviewed by Signature:</strong> <span><?= htmlspecialchars($current_status['signature']) ?></span></p>
        <?php endif; ?>
        <?php if ($current_status && !empty($current_status['comments'])): ?>
            <p><strong>Last Comments:</strong> <span><?= htmlspecialchars($current_status['comments']) ?></span></p>
        <?php endif; ?>
    </div>

    <div class="review-action-form-card">
        <h3>Your Review Action</h3>
        <form method="post">
            <div class="form-group-custom">
                <label for="signature">Your Digital Signature (Name or Initials):</label>
                <textarea id="signature" name="signature" required rows="2" placeholder="Enter your name or initials"></textarea>
            </div>

            <div class="form-group-custom">
                <label for="comments">Add Comments (Optional):</label>
                <textarea id="comments" name="comments" rows="3" placeholder="Add any relevant comments about this review..."></textarea>
            </div>

            <div class="form-buttons">
                <button type="submit" name="action" value="approve" class="btn-action btn-approve">Approve</button>
                <button type="submit" name="action" value="reject" class="btn-action btn-reject">Reject</button>
            </div>
        </form>
    </div>

    <div style="text-align: center;">
        <a href="dashboard.php" class="btn-back-to-list">← Back to Form List</a>
    </div>
</div>
