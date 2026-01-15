<?php
// session_start(); // Start the session
include 'db.php'; // Include your database connection

$overall_status = ''; // Initialize overall_status variable

// Redirect if user is not logged in or is not a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    // For AJAX includes, simply output an error message instead of a full redirect
    echo '<div class="status-message error-message">Access Denied. Please log in as a student to view your clearance status.</div>';
    exit();
}

$student_id = $_SESSION['user_id'];

// Get all clearance forms for this student, ordered by most recent
$query = "SELECT cf.form_id, cf.submitted_at
          FROM clearance_forms cf
          WHERE cf.student_id = ?
          ORDER BY cf.submitted_at DESC";

$stmt = $conn->prepare($query);
if (!$stmt) {
    echo '<div class="status-message error-message">Database error: Could not prepare statement for clearance forms. ' . htmlspecialchars($conn->error) . '</div>';
    exit();
}
$stmt->bind_param("i", $student_id);
$stmt->execute();
$forms_result = $stmt->get_result();

/**
 * Formats the clearance status into a styled HTML badge with an icon.
 * @param int|string $status_val The numerical status (1, 0, -1) or string status ('APPROVED', 'PENDING', etc.).
 * @return string HTML string for the status badge.
 */
function format_status_html($status_val) {
    $class = '';
    $display_text = '';
    $icon = '';

    // Determine class, display text, and icon based on status value
    if ($status_val == 1 || $status_val === 'APPROVED') {
        $class = 'status-approved';
        $display_text = 'APPROVED';
        $icon = '<i class="fas fa-check-circle"></i>';
    } elseif ($status_val == 0 || $status_val === 'PENDING') {
        $class = 'status-pending';
        $display_text = 'PENDING';
        $icon = '<i class="fas fa-hourglass-half"></i>';
    } elseif ($status_val == -1 || $status_val === 'REJECTED') {
        $class = 'status-rejected';
        $display_text = 'REJECTED';
        $icon = '<i class="fas fa-times-circle"></i>';
    } else { // Default for any other unexpected value or 'UNDER REVIEW'
        $class = 'status-review';
        $display_text = 'UNDER REVIEW';
        $icon = '<i class="fas fa-sync-alt fa-spin"></i>'; // Spinning icon for 'under review'
    }
    // Return a styled span with an icon
    return "<span class='status-badge $class'>$icon $display_text</span>";
}
?>

<style>
    /* Page Heading */
    .clearance-status-header {
        max-width: 1100px;
        margin: var(--spacing-xs) auto var(--spacing-lg) auto; /* Adjusted margin-top to var(--spacing-xs) */
        text-align: center;
        padding: 0 var(--spacing-md);
        animation: fadeIn 0.7s ease-out forwards;
    }

    .clearance-status-header h2 {
        font-size: 2.8rem;
        font-weight: 800;
        color: var(--text-dark);
        margin-bottom: var(--spacing-md);
        position: relative;
        letter-spacing: 0.8px;
    }

    .clearance-status-header h2::after {
        content: '';
        position: absolute;
        bottom: -10px;
        left: 50%;
        transform: translateX(-50%);
        width: 120px;
        height: 6px;
        background: var(--primary);
        border-radius: var(--border-radius);
    }

    /* --- Forms Grid --- */
    .forms-grid {
        display: grid;
        /* Adjusted min-width to accommodate content, allows 1 or 2 columns based on space */
        grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
        gap: var(--spacing-md);
        max-width: 1100px; /* Increased max width for the grid */
        margin: 0 auto;
        padding: 0 var(--spacing-md);
    }

    .form-status-card {
        background-color: var(--text-light);
        border-radius: var(--border-radius);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        padding: var(--spacing-md);
        text-align: left;
        border: 1px solid var(--accent);
        transition: transform 0.4s ease, box-shadow 0.4s ease;
        overflow: hidden;
        position: relative;
        transform: translateY(30px); /* Initial state for animation */
        opacity: 0; /* Initial state for animation */
        animation: cardSlideUpFadeIn 0.6s ease-out forwards;
    }

    /* Staggered animation for cards */
    .form-status-card:nth-child(1) { animation-delay: 0.1s; }
    .form-status-card:nth-child(2) { animation-delay: 0.2s; }
    .form-status-card:nth-child(3) { animation-delay: 0.3s; }
    .form-status-card:nth-child(4) { animation-delay: 0.4s; }
    /* You can add more :nth-child rules if you expect many cards and want more stagger */

    @keyframes cardSlideUpFadeIn {
        from { opacity: 0; transform: translateY(50px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .form-status-card:hover {
        transform: translateY(-8px) scale(1.01);
        box-shadow: 0 18px 40px rgba(0, 0, 0, 0.2);
    }

    .form-status-card .form-summary {
        padding-bottom: var(--spacing-xs);
        margin-bottom: var(--spacing-sm);
        border-bottom: 1px solid var(--accent);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap; /* Allow items to wrap on small screens */
        gap: 10px; /* Space between flex items */
    }

    .form-status-card .form-summary p {
        font-size: 1.1rem;
        color: var(--text-dark);
        margin: 0;
    }
    .form-status-card .form-summary strong {
        color: var(--primary);
        font-weight: 700;
        margin-right: 5px;
    }

    /* Sectional Status Heading */
    .form-status-card h4 {
        font-size: 1.4rem;
        color: var(--secondary);
        margin-top: var(--spacing-sm);
        margin-bottom: var(--spacing-sm);
        position: relative;
        padding-bottom: var(--spacing-xs);
        border-bottom: 2px solid var(--accent);
        text-align: left;
    }
    .form-status-card h4::after {
        content: '';
        position: absolute;
        left: 0;
        bottom: -2px;
        width: 60px;
        height: 3px;
        background-color: var(--primary-light);
        border-radius: var(--border-radius);
    }

    /* Status Items Grid (for approval sections) */
    .status-columns {
        display: grid;
        grid-template-columns: 1fr 1fr; /* Two columns by default */
        gap: var(--spacing-sm);
        margin-bottom: var(--spacing-md);
    }

    .status-item {
        padding: 10px;
        background-color: var(--background);
        border-radius: var(--border-radius);
        border: 1px solid var(--accent);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .status-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 10px rgba(0, 0, 0, 0.05);
    }

    .status-item .section-name {
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 5px;
        display: block;
    }

    .status-item .signature-info {
        font-size: 0.9em;
        color: #555;
        margin-top: 5px;
    }

    .status-item .comments {
        font-size: 0.85em;
        font-style: italic;
        color: #777;
        margin-top: 5px;
        padding-top: 5px;
        border-top: 1px dashed var(--accent);
    }

    /* Status Badges */
    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 6px 12px;
        border-radius: 20px; /* Pill shape */
        font-weight: 700;
        font-size: 0.9rem;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        gap: 5px; /* Space between icon and text */
    }

    .status-badge .fas {
        font-size: 0.8em;
    }

    /* Specific badge colors */
    .status-approved {
        background-color: #e6ffe6; /* Light green */
        color: #28a745; /* Green */
        border: 1px solid #28a745;
        animation: pulseApproved 2s infinite alternate; /* Pulse for approved */
    }
    .status-pending {
        background-color: #fff8e6; /* Light orange */
        color: #ffc107; /* Orange */
        border: 1px solid #ffc107;
        animation: pulsePending 2s infinite alternate; /* Pulse for pending */
    }
    .status-rejected {
        background-color: #ffe6e6; /* Light red */
        color: #dc3545; /* Red */
        border: 1px solid #dc3545;
        animation: shakeRejected 0.6s ease-in-out infinite alternate; /* Shake for rejected */
    }
    .status-review {
        background-color: var(--accent);
        color: var(--primary);
        border: 1px solid var(--primary);
        /* No specific animation for review, but you could add one if desired */
    }

    /* Badge Animations */
    @keyframes pulseApproved {
        0% { transform: scale(1); box-shadow: 0 0 0 rgba(40, 167, 69, 0.4); }
        50% { transform: scale(1.02); box-shadow: 0 0 8px rgba(40, 167, 69, 0.7); }
        100% { transform: scale(1); box-shadow: 0 0 0 rgba(40, 167, 69, 0.4); }
    }
    @keyframes pulsePending {
        0% { transform: scale(1); box-shadow: 0 0 0 rgba(255, 193, 7, 0.4); }
        50% { transform: scale(1.02); box-shadow: 0 0 8px rgba(255, 193, 7, 0.7); }
        100% { transform: scale(1); box-shadow: 0 0 0 rgba(255, 193, 7, 0.4); }
    }
    @keyframes shakeRejected {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-5px); }
        50% { transform: translateX(5px); }
        75% { transform: translateX(-5px); }
    }

    /* Overall Status Display */
    .overall-status {
        margin-top: var(--spacing-md);
        padding: var(--spacing-sm) var(--spacing-md);
        border-top: 2px solid var(--primary-light);
        font-size: 1.4rem;
        font-weight: 800;
        text-align: center;
        color: var(--text-dark);
        background-color: var(--background);
        border-radius: var(--border-radius);
        box-shadow: inset 0 0 10px rgba(0, 0, 0, 0.05);
        animation: overallGlow 2s infinite alternate; /* Subtle glow for overall status */
    }

    @keyframes overallGlow {
        from { text-shadow: 0 0 5px rgba(107, 127, 215, 0.3); }
        to { text-shadow: 0 0 10px rgba(107, 127, 215, 0.6); }
    }

    /* Download button */
    .download-btn {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        margin-top: var(--spacing-md);
        padding: 14px 30px;
        background: linear-gradient(to right, var(--primary), var(--primary-light));
        color: var(--text-light);
        text-decoration: none;
        border-radius: var(--border-radius);
        transition: all 0.4s ease;
        font-weight: 700;
        letter-spacing: 0.5px;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        border: none;
        position: relative;
        overflow: hidden;
        cursor: pointer;
    }

    .download-btn:hover {
        transform: translateY(-5px) scale(1.02);
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.25);
        background: linear-gradient(to right, var(--primary-light), var(--primary)); /* Reverse gradient on hover */
    }
    .download-btn .fas {
        font-size: 1.2em;
    }
    .download-btn::before { /* Ripple effect */
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 10px;
        height: 10px;
        background: rgba(255, 255, 255, 0.3);
        border-radius: 50%;
        transform: translate(-50%, -50%) scale(0);
        opacity: 0;
        transition: transform 0.6s ease-out, opacity 0.6s ease-out;
    }
    .download-btn:active::before {
        transform: translate(-50%, -50%) scale(25);
        opacity: 1;
        transition: 0s; /* No transition on active state */
    }

    /* Remarks Table */
    .remarks-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: var(--spacing-sm);
        margin-bottom: var(--spacing-sm);
        font-size: 0.95rem;
    }

    .remarks-table th, .remarks-table td {
        border: 1px solid var(--accent);
        padding: 10px 12px;
        text-align: left;
        color: var(--text-dark);
    }

    .remarks-table th {
        background-color: var(--secondary);
        color: var(--text-light);
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .remarks-table tr:nth-child(even) {
        background-color: var(--background);
    }
    .remarks-table tr:hover {
        background-color: var(--accent);
    }
    .remarks-table td strong {
        color: var(--primary);
    }

    /* No forms message */
    .no-forms-message {
        background-color: var(--text-light);
        color: var(--text-dark);
        padding: var(--spacing-md);
        border-radius: var(--border-radius);
        margin: var(--spacing-md) auto;
        font-size: 1.3rem;
        font-weight: 600;
        animation: fadeIn 0.8s ease-out;
        border: 1px solid var(--secondary);
        max-width: 1100px; /* Increased max width */
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    }

    /* Back to Dashboard button */
    .back-to-dashboard-btn {
        display: block;
        margin: var(--spacing-lg) auto var(--spacing-lg) auto;
        padding: 15px 35px;
        background: var(--text-dark);
        color: var(--text-light);
        text-decoration: none;
        border-radius: var(--border-radius);
        transition: all 0.4s ease;
        font-weight: 700;
        letter-spacing: 0.5px;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        width: fit-content; /* Make button width fit its content */
    }

    .back-to-dashboard-btn:hover {
        background: #444;
        transform: translateY(-5px) scale(1.01);
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.25);
    }

    /* General messages (e.g., access denied, database error) */
    .status-message {
        padding: var(--spacing-sm);
        border-radius: var(--border-radius);
        margin: var(--spacing-md) auto;
        text-align: center;
        font-weight: 600;
        animation: fadeIn 0.5s ease-out;
        font-size: 1.1rem;
        max-width: 1100px; /* Increased max width */
    }

    .status-message.error-message {
        background-color: #f8d7da; /* Light red */
        color: #721c24; /* Dark red */
        border: 1px solid #f5c6cb;
    }
    .status-message.warning-message {
        background-color: #fff3cd; /* Light yellow */
        color: #664d03; /* Dark yellow */
        border: 1px solid #ffecb5;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Responsive adjustments */
    @media (max-width: 1200px) {
        .forms-grid {
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
        }
    }
    @media (max-width: 992px) {
        .forms-grid {
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
        }
    }
    @media (max-width: 768px) {
        .clearance-status-header h2 {
            font-size: 2.2rem;
            padding-left: var(--spacing-sm);
            padding-right: var(--spacing-sm);
        }
        .forms-grid {
            grid-template-columns: 1fr; /* Single column on small screens */
            padding-left: var(--spacing-sm);
            padding-right: var(--spacing-sm);
        }
        .form-status-card {
            padding: var(--spacing-sm);
        }
        .form-status-card h4 {
            font-size: 1.2rem;
        }
        .status-columns {
            grid-template-columns: 1fr; /* Single column for status items on smaller screens */
            gap: var(--spacing-xs);
        }
        .download-btn, .back-to-dashboard-btn {
            padding: 12px 25px;
            font-size: 0.95em;
        }
        .remarks-table th, .remarks-table td {
            padding: 8px;
            font-size: 0.9em;
        }
        .no-forms-message {
            padding-left: var(--spacing-sm);
            padding-right: var(--spacing-sm);
        }
    }
    @media (max-width: 480px) {
        .clearance-status-header h2 {
            font-size: 1.8rem;
        }
        .form-status-card .form-summary p,
        .status-item {
            font-size: 0.9em;
        }
        .overall-status {
            font-size: 1.2rem;
        }
    }
</style>

<div class="clearance-status-header">
    <h2>My Clearance Forms Status</h2>
</div>

<?php if ($forms_result->num_rows > 0): ?>
    <div class="forms-grid">
        <?php while ($form = $forms_result->fetch_assoc()): ?>
            <div class="form-status-card">
                <div class="form-summary">
                    <p><strong>Form ID:</strong> #<?= htmlspecialchars($form['form_id']) ?></p>
                    <p><strong>Submitted At:</strong> <?= htmlspecialchars($form['submitted_at']) ?></p>
                </div>

                <h4>Sectional Status:</h4>
                <div class="status-columns">
                    <?php
                    // Get all *latest* status records for this form
                    $status_query = "SELECT cs1.section, cs1.approved, cs1.signature, cs1.comments
                                     FROM clearance_status cs1
                                     INNER JOIN (
                                         SELECT section, MAX(updated_at) as max_updated
                                         FROM clearance_status
                                         WHERE form_id = ?
                                         GROUP BY section
                                     ) cs2 ON cs1.section = cs2.section AND cs1.updated_at = cs2.max_updated
                                     WHERE cs1.form_id = ?";

                    $status_stmt = $conn->prepare($status_query);
                    if (!$status_stmt) {
                        echo '<div class="status-item"><div class="status-message error-message">DB Error preparing status statement.</div></div>';
                        continue; // Skip to next form card if statement fails
                    }
                    $status_stmt->bind_param("ii", $form['form_id'], $form['form_id']);
                    $status_stmt->execute();
                    $status_result = $status_stmt->get_result();

                    // Initialize approval status for each section (including new committees)
                    // Default all to 0 (PENDING) if no specific status found
                    $approvals = [
                        'department' => ['status' => 0, 'signature' => '', 'comments' => ''],
                        'accounts' => ['status' => 0, 'signature' => '', 'comments' => ''],
                        'library' => ['status' => 0, 'signature' => '', 'comments' => ''],
                        'sports_committee' => ['status' => 0, 'signature' => '', 'comments' => ''],
                        'cultural_committee' => ['status' => 0, 'signature' => '', 'comments' => ''],
                        'tech_committee' => ['status' => 0, 'signature' => '', 'comments' => ''],
                        'iic_committee' => ['status' => 0, 'signature' => '', 'comments' => ''],
                        'samaritans_committee' => ['status' => 0, 'signature' => '', 'comments' => ''],
                        'samarth_committee' => ['status' => 0, 'signature' => '', 'comments' => ''],
                        'eclectica_committee' => ['status' => 0, 'signature' => '', 'comments' => ''],
                    ];

                    // Populate the $approvals array with actual statuses from the database
                    if ($status_result->num_rows > 0) {
                        while ($status_row = $status_result->fetch_assoc()) {
                            $section = $status_row['section'];
                            if (array_key_exists($section, $approvals)) {
                                $approvals[$section]['status'] = $status_row['approved'];
                                $approvals[$section]['signature'] = $status_row['signature'];
                                $approvals[$section]['comments'] = $status_row['comments'];
                            }
                        }
                    }
                    $status_stmt->close(); // Close the status statement

                    // NOW, iterate through the $approvals array to display ALL sections
                    foreach ($approvals as $section => $data):
                        $current_status_val = $data['status'];
                        $signature = $data['signature'];
                        $comments = $data['comments'];
                        ?>
                        <div class="status-item">
                            <span class="section-name"><?= ucfirst(str_replace('_', ' ', $section)) ?></span>
                            <?= format_status_html($current_status_val) ?>

                            <?php if (!empty($signature)): ?>
                                <p class="signature-info">Signed by: <?= htmlspecialchars($signature) ?></p>
                            <?php endif; ?>
                            <?php if (!empty($comments)): ?>
                                <p class="comments">Comments: <?= htmlspecialchars($comments) ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; // End of foreach $approvals ?>
                </div>

                <?php
                // Get all remarks for this form
                $remarks_query = "SELECT section, remark FROM clearance_remarks WHERE form_id = ?";
                $remarks_stmt = $conn->prepare($remarks_query);
                if (!$remarks_stmt) {
                    echo '<p class="status-message error-message">Database error: Could not prepare statement for remarks. ' . htmlspecialchars($conn->error) . '</p>';
                } else {
                    $remarks_stmt->bind_param("i", $form['form_id']);
                    $remarks_stmt->execute();
                    $remarks_result = $remarks_stmt->get_result();

                    if ($remarks_result->num_rows > 0) {
                        echo "<h4>Remarks:</h4>";
                        echo "<table class='remarks-table'>";
                        echo "<thead><tr><th>Section</th><th>Remark</th></tr></thead>";
                        echo "<tbody>";
                        while ($remark = $remarks_result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td><strong>" . ucfirst(str_replace('_', ' ', $remark['section'])) . "</strong></td>";
                            echo "<td>" . htmlspecialchars($remark['remark']) . "</td>";
                            echo "</tr>";
                        }
                        echo "</tbody>";
                        echo "</table>";
                    }
                    $remarks_stmt->close();
                }

                // Determine overall status based on the 'approvals' array
                $all_approved = true;
                $any_pending = false;
                $any_rejected = false;

                foreach ($approvals as $section => $data) { // Loop through the $approvals array, not $status_result
                    $status = $data['status'];
                    if ($status == 0) $any_pending = true;
                    if ($status == -1) $any_rejected = true;
                    if ($status != 1) $all_approved = false; // If any section is not approved (0 or -1), then overall is not approved
                }

                if ($all_approved) {
                    $overall_form_status_text = 'APPROVED';
                } elseif ($any_rejected) {
                    $overall_form_status_text = 'REJECTED';
                } elseif ($any_pending) {
                    $overall_form_status_text = 'PENDING';
                } else {
                    // This case handles when no statuses are recorded yet or all are '0' (pending)
                    $overall_form_status_text = 'UNDER REVIEW';
                }
                ?>

                <div class="overall-status">
                    Overall Form Status: <?= format_status_html($overall_form_status_text) ?>
                </div>

                <?php if ($overall_form_status_text == 'APPROVED'): ?>
                    <a href='download_pdf.php?form_id=<?= htmlspecialchars($form['form_id']) ?>' target='_blank' class='download-btn'>
                        <i class="fas fa-cloud-download-alt"></i> Download Clearance Certificate
                    </a>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    </div>
<?php else: ?>
    <div class="no-forms-message">
        <p>You haven't submitted any clearance forms yet.</p>
        <p>Ready to get started? Click "Create Clearance Form" in the sidebar!</p>
    </div>
<?php endif; ?>

<a href='dashboard.php' class='back-to-dashboard-btn'>Back to Dashboard</a>