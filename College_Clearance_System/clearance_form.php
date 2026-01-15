<?php
// This file is intended to be included by dashboard.php via AJAX.
// Therefore, session_start() is not strictly needed here if dashboard.php always includes it,
// but it's kept for robustness if accessed directly for testing.
//session_start();
include 'db.php'; // Include your database connection

$user_id = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? null;

// Ensure only logged-in students can access this form
if (!$user_id || $role !== 'student') {
    // If accessed directly and not logged in as a student, redirect to login.
    // If included via AJAX, this echo will be displayed in the dynamic content area.
    echo '<div class="alert alert-danger" style="color: red; padding: 15px; border: 1px solid red; border-radius: 5px;">Access Denied. Please log in as a student to view this form.</div>';
    exit();
}

// Fetch student details from the database
$sql = "SELECT s.name, s.student_id, s.session, s.roll_number, s.stream ,s.college_id
        FROM students s
        JOIN users u ON s.student_id = u.user_id 
        WHERE u.user_id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo '<div class="alert alert-danger" style="color: red; padding: 15px; border: 1px solid red; border-radius: 5px;">Database error: Could not prepare statement.</div>';
    exit();
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $student = $result->fetch_assoc();
    $name = htmlspecialchars($student['name']);
    $student_college_id = htmlspecialchars($student['college_id']); // Renamed to avoid confusion with internal student_id
    $session = htmlspecialchars($student['session']);
    $roll_number = htmlspecialchars($student['roll_number']);
    $stream = htmlspecialchars($student['stream']);
} else {
    echo '<div class="alert alert-warning" style="color: orange; padding: 15px; border: 1px solid orange; border-radius: 5px;">Student record not found. Please contact an administrator.</div>';
    exit();
}

// Initialize remarks with empty strings. These fields are for departments/committees to fill in.
// When a student *creates* a form, these should generally be empty.
// If this form is used for *viewing* an existing form, these would be populated from the database.
// For this 'create' context, they start empty.
$accounts_remark = '';
$library_remark = '';
$department_remark = '';
$tech_committee_remark = '';
$cultural_committee_remark = '';
$sports_committee_remark = '';
$iic_committee_remark = '';
$samaritans_committee_remark = '';
$samarth_committee_remark = '';
$eclectica_committee_remark = '';

// The form will submit to 'submit_clearance_form.php' as per your original structure.
?>

<!--
    This style block is designed to be self-contained for the AJAX loaded content.
    It re-uses the CSS variables defined in the main dashboard.php file.
-->
<style>
    .clearance-form-content {
        width: 100%;
        max-width: 800px; /* Max width for the form within the dashboard card */
        margin: 0 auto; /* Center the form content */
        /* Padding adjusted: top padding is handled by the .card in dashboard.php */
        padding: 0 var(--spacing-lg) var(--spacing-lg) var(--spacing-lg);
        text-align: center; /* Center the heading */
    }

    .clearance-form-content h2 {
        font-size: 2.2rem;
        font-weight: 700;
        text-align: center;
        color: var(--primary);
        margin-bottom: var(--spacing-sm); /* Adjusted margin-bottom */
        position: relative;
        padding-bottom: var(--spacing-xs);
    }

    .clearance-form-content h2::after {
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

    /* Custom Form Group - for each input row */
    .form-group-custom {
        position: relative;
        margin-bottom: var(--spacing-md);
        text-align: left; /* Align labels/inputs to the left */
    }

    .form-group-custom label {
        display: block;
        margin-bottom: var(--spacing-xs);
        font-weight: 600;
        color: var(--text-dark);
        font-size: 1rem;
    }

    .form-group-custom input[type="text"],
    .form-group-custom textarea {
        width: 100%;
        padding: 12px 15px;
        background: var(--background);
        border: 2px solid var(--accent);
        border-radius: var(--border-radius);
        font-size: 1rem;
        color: var(--text-dark);
        transition: var(--transition);
        box-sizing: border-box; /* Ensures padding and border are included in the element's total width and height */
    }

    .form-group-custom input[type="text"]:focus,
    .form-group-custom textarea:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(107, 127, 215, 0.2); /* Primary blue with transparency */
        outline: none;
    }

    .form-group-custom input[readonly] {
        background-color: var(--accent); /* Visually indicate read-only fields */
        border-color: var(--secondary);
        cursor: not-allowed;
        color: var(--text-dark);
        opacity: 0.9;
    }

    .form-group-custom textarea {
        min-height: 80px;
        resize: vertical; /* Allow vertical resizing */
    }

    /* Clearance Section Styling */
    .clearance-section {
        background-color: var(--background); /* A slightly different background for sections */
        border: 1px solid var(--accent);
        border-radius: var(--border-radius);
        padding: var(--spacing-md);
        margin-top: var(--spacing-md);
        margin-bottom: var(--spacing-md); /* Added margin-bottom for spacing between sections */
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05); /* Subtle shadow for sections */
    }

    .clearance-section > label { /* Direct child label of section */
        font-size: 1.1rem;
        color: var(--primary);
        margin-bottom: var(--spacing-sm);
        display: block;
        font-weight: 700;
        text-align: center;
        width: 100%;
        position: relative;
        padding-bottom: var(--spacing-xs);
    }

    .clearance-section > label::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 60px;
        height: 3px;
        background: var(--primary-light);
        border-radius: var(--border-radius);
    }

    /* Submit Button */
    .btn-submit {
        width: 100%;
        padding: 15px 20px;
        background: var(--primary);
        border: none;
        outline: none;
        border-radius: var(--border-radius);
        cursor: pointer;
        font-size: 1.1em;
        color: var(--text-light);
        font-weight: 600;
        transition: var(--transition);
        margin-top: var(--spacing-lg); /* More space before button */
        box-shadow: var(--card-shadow);
        letter-spacing: 0.5px;
    }

    .btn-submit:hover {
        background: var(--primary-light);
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .clearance-form-content {
            padding: 0 var(--spacing-md) var(--spacing-md) var(--spacing-md);
        }
        .clearance-form-content h2 {
            font-size: 1.8rem;
        }
        .clearance-section {
            padding: var(--spacing-sm);
            margin-top: var(--spacing-sm);
            margin-bottom: var(--spacing-sm);
        }
        .btn-submit {
            padding: 12px 15px;
            font-size: 1em;
        }
    }

    @media (max-width: 480px) {
        .clearance-form-content {
            padding: 0 var(--spacing-sm) var(--spacing-sm) var(--spacing-sm);
        }
        .form-group-custom label,
        .form-group-custom input[type="text"],
        .form-group-custom textarea {
            font-size: 0.9rem;
        }
    }
</style>

<div class="clearance-form-content">
    <h2>College Clearance / Leaving Certificate</h2>

    <form method="POST" action="submit_clearance_form.php">
        <!-- Student Details Section -->
        <div class="form-group-custom">
            <label>Name of the Student:</label>
            <input type="text" name="student_name" value="<?= $name ?>" readonly>
        </div>

        <div class="form-group-custom">
            <label>Student ID:</label>
            <input type="text" name="student_id" value="<?= $student_college_id ?>" readonly>
        </div>

        <div class="form-group-custom">
            <label>Academic Session:</label>
            <input type="text" name="session" value="<?= $session ?>" readonly>
        </div>

        <div class="form-group-custom">
            <label>MAKAUT Examination Roll Number:</label>
            <input type="text" name="roll_number" value="<?= $roll_number ?>" readonly>
        </div>

        <div class="form-group-custom">
            <label>College:</label>
            <input type="text" name="college" value="Techno Main Salt Lake" readonly>
        </div>

        <div class="form-group-custom">
            <label>Degree (Stream):</label>
            <input type="text" name="stream" value="<?= $stream ?>" readonly>
        </div>

        <!-- Clearance Sections for Remarks -->
        <div class="clearance-section">
            <label>Accounts Clearance</label>
            <div class="form-group-custom">
                <textarea name="accounts_remark" placeholder="Remarks or 'No Dues' from Accounts Department" required><?= htmlspecialchars($accounts_remark) ?></textarea>
            </div>
        </div>

        <div class="clearance-section">
            <label>Library Clearance</label>
            <div class="form-group-custom">
                <textarea name="library_remark" placeholder="Remarks or 'No Dues' from Library" required><?= htmlspecialchars($library_remark) ?></textarea>
            </div>
        </div>

        <div class="clearance-section">
            <label>Department Clearance</label>
            <div class="form-group-custom">
                <textarea name="department_remark" placeholder="Remarks or 'No Dues' from Department" required><?= htmlspecialchars($department_remark) ?></textarea>
            </div>
        </div>

        <div class="clearance-section">
            <label>Committee Clearances</label>
            <div class="form-group-custom">
                <label for="tech_remark">Technical Committee:</label>
                <textarea id="tech_remark" name="tech_committee_remark" placeholder="Remarks or 'No Dues' from Technical Committee" required><?= htmlspecialchars($tech_committee_remark) ?></textarea>
            </div>
            <div class="form-group-custom">
                <label for="cultural_remark">Cultural Committee:</label>
                <textarea id="cultural_remark" name="cultural_committee_remark" placeholder="Remarks or 'No Dues' from Cultural Committee" required><?= htmlspecialchars($cultural_committee_remark) ?></textarea>
            </div>
            <div class="form-group-custom">
                <label for="sports_remark">Sports Committee:</label>
                <textarea id="sports_remark" name="sports_committee_remark" placeholder="Remarks or 'No Dues' from Sports Committee" required><?= htmlspecialchars($sports_committee_remark) ?></textarea>
            </div>
            <div class="form-group-custom">
                <label for="iic_remark">IIC Committee:</label>
                <textarea id="iic_remark" name="iic_committee_remark" placeholder="Remarks or 'No Dues' from IIC Committee" required><?= htmlspecialchars($iic_committee_remark) ?></textarea>
            </div>
            <div class="form-group-custom">
                <label for="samaritans_remark">Samaritans Committee:</label>
                <textarea id="samaritans_remark" name="samaritans_committee_remark" placeholder="Remarks or 'No Dues' from Samaritans Committee" required><?= htmlspecialchars($samaritans_committee_remark) ?></textarea>
            </div>
            <div class="form-group-custom">
                <label for="samarth_remark">Samarth Committee:</label>
                <textarea id="samarth_remark" name="samarth_committee_remark" placeholder="Remarks or 'No Dues' from Samarth Committee" required><?= htmlspecialchars($samarth_committee_remark) ?></textarea>
            </div>
            <div class="form-group-custom">
                <label for="eclectica_remark">Eclectica Committee:</label>
                <textarea id="eclectica_remark" name="eclectica_committee_remark" placeholder="Remarks or 'No Dues' from Eclectica Committee" required><?= htmlspecialchars($eclectica_committee_remark) ?></textarea>
            </div>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn-submit">Submit Clearance Form</button>
    </form>
</div>
