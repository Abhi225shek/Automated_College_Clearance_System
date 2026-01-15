<?php
require('tcpdf/tcpdf.php'); // Include the TCPDF library
include('db.php'); // Include your database connection file
session_start(); // Start the session

// Redirect to login if user is not logged in or is not a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: login.php");
    exit();
}

// Check if form_id is provided in the URL
if (!isset($_GET['form_id'])) {
    die("Form ID missing.");
}

$form_id = intval($_GET['form_id']); // Sanitize form_id
$student_id = $_SESSION['user_id'];

// Verify form ownership: ensure the logged-in student owns this form
$verify_query = "SELECT student_id FROM clearance_forms WHERE form_id = ?";
$verify_stmt = $conn->prepare($verify_query);
$verify_stmt->bind_param("i", $form_id);
$verify_stmt->execute();
$verify_result = $verify_stmt->get_result();
$verify_data = $verify_result->fetch_assoc();

if (!$verify_data || $verify_data['student_id'] != $student_id) {
    die("You are not authorized to access this form.");
}

// Fetch student and form data for the PDF content
$query = "SELECT s.*, cf.submitted_at 
          FROM students s
          JOIN clearance_forms cf ON s.student_id = cf.student_id
          WHERE cf.form_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $form_id);
$stmt->execute();
$result = $stmt->get_result();
$form = $result->fetch_assoc();

if (!$form) {
    die("Form not found.");
}

// Fetch the latest status records for each section of this form
$status_query = "SELECT cs1.* FROM clearance_status cs1
                 INNER JOIN (
                     SELECT section, MAX(updated_at) as max_updated
                     FROM clearance_status WHERE form_id = ?
                     GROUP BY section
                 ) cs2 ON cs1.section = cs2.section AND cs1.updated_at = cs2.max_updated
                 WHERE cs1.form_id = ?";
$status_stmt = $conn->prepare($status_query);
$status_stmt->bind_param("ii", $form_id, $form_id);
$status_stmt->execute();
$status_result = $status_stmt->get_result();

$statuses = []; // Array to store the latest status for each section
while ($status = $status_result->fetch_assoc()) {
    $statuses[$status['section']] = $status;
}

// Define all possible clearance sections for consistent display in the PDF.
$all_clearance_sections = [
    'accounts', 'library', 'department',
    'sports_committee', 'cultural_committee', 'tech_committee',
    'iic_committee', 'samaritans_committee', 'samarth_committee', 'eclectica_committee'
];

// Create new TCPDF object
$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false); // Portrait, millimeters, A4 size
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor(htmlspecialchars($form['college_name'] ?? 'Techno Main Salt Lake')); // Use college name from DB or default
$pdf->SetTitle('Clearance Certificate - ' . htmlspecialchars($form['name']));
$pdf->SetSubject('Student Clearance Form');
$pdf->SetKeywords('Clearance, Certificate, College, Student');

// --- IMPORTANT: Disable default header and footer to remove lines ---
$pdf->SetPrintHeader(false);
$pdf->SetPrintFooter(false);
// --- End IMPORTANT ---

// Set document margins (left, top, right)
$pdf->SetMargins(15, 30, 15); // Adjust top margin to accommodate the detailed header
$pdf->SetAutoPageBreak(TRUE, 25); // Set auto page break with a bottom margin
$pdf->AddPage(); // Add the first page

// --- Header Section ---
// College Logo
// Assuming 'Tmslblack.jpg' is in the same directory as this PHP file.
// If this file is not found, a placeholder image can be used, or the Image call can be skipped.
$logo_path = 'Tmslblack.jpg';
if (file_exists($logo_path)) {
    $pdf->Image($logo_path, 15, 12, 30); // x, y, width - Placed on the left side
} else {
    // Optionally, draw a text placeholder or log an error if the logo file is missing
    // $pdf->SetFont('helvetica', 'B', 10);
    // $pdf->Text(15, 12, 'LOGO MISSING');
}

// College Name and Details (right aligned)
$pdf->SetFont('helvetica', 'B', 25);
$pdf->SetXY(70, 10); // Set position to start writing from
$pdf->Cell(0, 7, 'Techno Main Salt Lake', 0, 1, 'R'); // College Name

$pdf->SetFont('helvetica', '', 8);
$pdf->SetX(50); // Adjust X to align subsequent lines
$pdf->Cell(0, 5, '(An AICTE approved Engineering, Technology & Management College affiliated to MAKAUT)', 0, 1, 'R');
$pdf->SetX(50);
$pdf->Cell(0, 5, 'Maulana Abul Kalam Azad University of Technology, West Bengal, NBA Accrediated', 0, 1, 'R');
$pdf->SetX(50);
$pdf->Cell(0, 5, 'EM 4/1, Sector-V, Saltlake, Kolkata - 700 091', 0, 1, 'R');
$pdf->SetX(50);
$pdf->Cell(0, 5, 'Phone: (91) 33-2357-5683/84, Mob.: (91) 9831175306', 0, 1, 'R');
$pdf->SetX(50);
$pdf->Cell(0, 5, 'E-mail: info@ticollege.ac.in, Web: www.ticollege.ac.in', 0, 1, 'R');
$pdf->Ln(8); // Line break after header

// --- Certificate Title Section ---
$pdf->SetFont('helvetica', 'B', 13);
$x_rect = 40; // X position for the rectangle
$y_rect = $pdf->GetY(); // Current Y position for the rectangle
$w_rect = 125; // Width of the rectangle
$h_rect = 9; // Height of the rectangle

// Draw bold rounded rectangle
$pdf->SetLineWidth(0.5); // Set line width for bold border
$pdf->RoundedRect($x_rect, $y_rect, $w_rect, $h_rect, 2, '1111', ''); // radius = 2 mm, all corners rounded, no fill

// Write the text inside the rectangle
$pdf->SetXY($x_rect, $y_rect + 1); // Adjust Y to vertically center the text
$pdf->Cell($w_rect, 8, 'COLLEGE CLEARANCE / LEAVING CERTIFICATE', 0, 1, 'C');
$pdf->Ln(1); // Small line break after the main title

$pdf->SetFont('helvetica', '', 9);
$pdf->Cell(0, 6, '(To be completed by every final year student before collecting the final semester Grade Card & Certificate)', 0, 1, 'C');
$pdf->Ln(3); // Line break after the explanatory note

// --- Student Information Section ---
// Student Name
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(70, 7, 'Name of the Student (BLOCK Letters) :', 0, 0);
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(100, 8, strtoupper(htmlspecialchars($form['name'])), 'B', 1); // 'B' adds a bottom border
$pdf->Ln(2); // Small line break for spacing

// Student ID with individual boxes
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(30, 7, 'Student ID:', 0, 0);
$pdf->SetFont('helvetica', '', 10);
$college_id_chars = str_split($form['college_id']);
$num_id_boxes = 10; // As seen in the image
for ($i = 0; $i < $num_id_boxes; $i++) {
    $char_to_display = isset($college_id_chars[$i]) ? htmlspecialchars($college_id_chars[$i]) : '-'; // Use '-' for empty boxes as per image implication
    $pdf->Cell(8, 7, $char_to_display, 1, 0, 'C'); // Each character in a box
}
$pdf->Ln(9); // Line break after Student ID boxes

// Academic Session in individual boxes
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(40, 7, 'Academic Session:', 0, 0);
$pdf->SetFont('helvetica', '', 10);
$session_chars = str_split($form['session']);
$num_session_boxes = 9; // Approx 9 boxes in image for session
for ($i = 0; $i < $num_session_boxes; $i++) {
    $char_to_display = isset($session_chars[$i]) ? htmlspecialchars($session_chars[$i]) : '-';
    $pdf->Cell(8, 7, $char_to_display, 1, 0, 'C');
}
$pdf->Ln(9); // Line break after Academic Session boxes

// MAKAUT Roll Number in individual boxes
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(70, 7, 'MAKAUT Examination Roll Number:', 0, 0);
$pdf->SetFont('helvetica', '', 10);
$roll_chars = str_split($form['roll_number']);
$num_roll_boxes = 11; // Approx 11 boxes in image for roll number
for ($i = 0; $i < $num_roll_boxes; $i++) {
    $char_to_display = isset($roll_chars[$i]) ? htmlspecialchars($roll_chars[$i]) : '-';
    $pdf->Cell(8, 7, $char_to_display, 1, 0, 'C');
}
$pdf->Ln(9); // Line break after Roll Number boxes

// College: (Fixed value, as seen in image)
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(20, 7, 'College:', 0, 0);
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 7, 'Techno Main Salt Lake', 0, 1); // No border, as seen in image
$pdf->Ln(2); // Small line break for spacing

// Degree (Stream) - no box, simple text
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(110, 7, 'Student was studying in this Institute for Degree in (Stream):', 0, 0);
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(20, 7, htmlspecialchars($form['stream']), 0, 1); // No border or underline
$pdf->Ln(3); // Line break before clearance sections

// Instruction for Committees (as seen in image)
$pdf->SetFont('helvetica', 'I', 9);
$pdf->Cell(0, 6, '(Please get the following sections signed and stamped by respective departmental authorities)', 0, 1, 'C');
$pdf->Ln(2); // Line break after instruction

// --- Helper Function to Draw Each Section ---
function drawSection($pdf, $title, $description, $signature_label, $signature_val, $updated_at_val) {
    // Set font for section title
    $pdf->SetFont('helvetica', 'B', 13);
    // Print section title with an underline
    $pdf->Write(0, $title, '', 0, 'L', true, 0, false, false, 0);
    $pdf->Ln(1); // Small line break

    // Set font for description
    $pdf->SetFont('helvetica', '', 10);
    // Print description
    $pdf->MultiCell(0, 6, $description, 0, 'L', 0, 1); // 0, 1 for next line

    // Format date to show only the date part, or 'N/A' if no valid date
    $formatted_date = ($updated_at_val && strtotime($updated_at_val)) ? date('d-m-Y', strtotime($updated_at_val)) : 'N/A';
    
    // Store current Y before printing the signature label
    $current_y_before_label = $pdf->GetY();

    // Print signature label (left aligned)
    $pdf->SetFont('helvetica', '', 10); // Reset font for label
    $pdf->Cell(130, 6, $signature_label, 0, 0); 
    
    // Now, GetX() is after the label. This is where the signature line and text block will align horizontally.
    $start_x_for_sig_block = $pdf->GetX();

    // Define heights for text elements
    $sig_text_font_size = 9;
    $date_text_font_size = 8;
    $sig_cell_height = 5; // A reasonable height for font size 9
    $date_cell_height = 4; // A reasonable height for font size 8
    $padding_between_sig_and_date = 1; // Padding between signature and date text
    $padding_below_date_to_line = 1; // Padding from date text to the line

    // Calculate total height needed for signature, date, and padding
    $total_text_block_height = $sig_cell_height + $date_cell_height + $padding_between_sig_and_date;

    // Calculate the Y position for the line. This will be the bottom of the text block.
    $line_y_position = $current_y_before_label + $total_text_block_height + $padding_below_date_to_line;

    // Calculate Y position for signature text (top-most element in this block)
    $sig_text_y = $line_y_position - $date_cell_height - $padding_between_sig_and_date - $sig_cell_height;

    // Set X and Y for signature text
    $pdf->SetXY($start_x_for_sig_block, $sig_text_y); 
    
    // Print signature text
    $pdf->SetFont('helvetica', 'B', $sig_text_font_size); // Bold and slightly smaller for signature
    $pdf->Cell(60, $sig_cell_height, htmlspecialchars($signature_val), 0, 0, 'C'); // Cell width fixed at 60mm, no line break

    // Set X and Y for date text (next line after signature, aligned horizontally)
    $date_text_y = $line_y_position - $date_cell_height - $padding_below_date_to_line;
    $pdf->SetXY($start_x_for_sig_block, $date_text_y); 
    $pdf->SetFont('helvetica', 'I', $date_text_font_size); // Italic and even smaller for date
    $pdf->Cell(60, $date_cell_height, 'Date: ' . $formatted_date, 0, 0, 'C'); // Cell width fixed at 60mm, no line break

    // Draw the line for signature
    $pdf->Line($start_x_for_sig_block, $line_y_position, $start_x_for_sig_block + 60, $line_y_position, array('width' => 0.3)); // Line under the text

    // Reset current Y to be below the entire signature block for the next section
    $pdf->SetY($line_y_position + 4); // Move Y cursor below the line with a small margin

    $pdf->SetFont('helvetica', '', 10); // Reset font for next section
}

// --- Clearance Sections Data and Loop ---
$sections_data = [
    'accounts' => [
        'description' => 'The above student has no dues regarding Semester Fees.',
        'signature_label' => 'Signature (with date) of the Account Officer / In-charge:',
    ],
    'library' => [
        'description' => 'The above student has no dues in the Library.',
        'signature_label' => 'Signature (with date) of the Librarian / In-charge:',
    ],
    'department' => [
        'description' => 'The above student has no dues in the Department.',
        'signature_label' => 'Signature (with date) of the HOD / In-charge:',
    ],
    'tech_committee' => [
        'description' => 'The above student has no dues with the Technical Committee.',
        'signature_label' => 'Signature (with date) of the Convenor/Co-convenor of the Technical Committee:',
    ],
    'cultural_committee' => [
        'description' => 'The above student has no dues with the Cultural & Organizing (Anakhronos) Committee.',
        'signature_label' => 'Signature (with date) of the Convenor/Co-convenor of the Cultural Committee:',
    ],
    'sports_committee' => [
        'description' => 'The above student has no dues with the Sports Committee.',
        'signature_label' => 'Signature (with date) of the Convenor/Co-convenor of the Sports Committee:',
    ],
    'iic_committee' => [
        'description' => 'The above student has no dues with the IIC Committee.',
        'signature_label' => 'Signature (with date) of the Convenor/Co-convenor of the IIC Committee:',
    ],
    'samaritans_committee' => [
        'description' => 'The above student has no dues with the Samaritans Committee.',
        'signature_label' => 'Signature (with date) of the Convenor/Co-convenor of the Samaritans Committee:',
    ],
    'samarth_committee' => [
        'description' => 'The above student has no dues with the Samarth Committee.',
        'signature_label' => 'Signature (with date) of the Convenor/Co-convenor of the Samarth Committee:',
    ],
    'eclectica_committee' => [
        'description' => 'The above student has no dues with the Eclectica Committee.',
        'signature_label' => 'Signature (with date) of the Convenor/Co-convenor of the Eclectica Committee:',
    ],
];

// Loop through each section and draw it if status data is available and approved
foreach ($all_clearance_sections as $section_key) {
    // Check if the section data exists before trying to access it
    if (!isset($sections_data[$section_key])) {
        // This could log an error or skip the section as needed. For now, we'll skip it.
        continue; 
    }

    $section_info = $sections_data[$section_key];
    $status_info = $statuses[$section_key] ?? null; // Get status for the current section, or null if not found

    // Only display signature and date if the section is explicitly APPROVED (status = 1)
    // Otherwise, it implies 'N/A' or not yet signed for the signature and empty for the date.
    $signature_to_display = 'N/A';
    $date_to_display = '';

    if ($status_info && $status_info['approved'] == 1) {
        $signature_to_display = $status_info['signature'] ?? '';
        $date_to_display = $status_info['updated_at'] ?? '';
    }

    drawSection(
        $pdf,
        ucfirst(str_replace('_', ' ', $section_key)) . ':', // Title for the section
        $section_info['description'], // Description
        $section_info['signature_label'], // Signature label
        $signature_to_display, // Signature value (will be 'N/A' if not approved)
        $date_to_display // Date value (will be empty if not approved)
    );
}


// --- Final Note Section ---
$pdf->Ln(3); // Line break

// Define the note box dimensions and position
$note_box_padding = 3; // Padding inside the note box
$note_text_content = "NOTE:\n1. After completing this certificate in all respects, please make 2 (two) photocopies. One copy will be required at the time of collecting the final semester Grade Card. Another copy will be required to collect the Degree Certificate some months later.\n2. Keep the ORIGINAL Certificate with you and show it only for verification.";
$pdf->SetFont('helvetica', 'I', 9); // Italic font for note

// Define margins to ensure accurate box drawing
$left_margin = 13;
$right_margin = 13;

$note_box_width_content = $pdf->GetPageWidth() - $left_margin - $right_margin;
$note_box_width_cell = $note_box_width_content - (2 * $note_box_padding); // Width for MultiCell inside the box

// To calculate approximate height for MultiCell before drawing the box,
// we can use MultiCellHeight (though it's usually for content that has *already* been drawn)
// or estimate based on number of lines and font size.
$line_height_estimate = 7; // Approximate line height for font size 9
$num_lines_estimate = substr_count($note_text_content, "\n") + 1;
$note_box_calculated_height = ($num_lines_estimate * $line_height_estimate) + (2 * $note_box_padding);

$current_x_note = $pdf->GetX();
$current_y_note = $pdf->GetY();

// Draw a curved rectangular box around the note
$pdf->SetDrawColor(100, 100, 100); // Darker grey border
$pdf->SetLineWidth(0.2);
$pdf->RoundedRect(
    $current_x_note, 
    $current_y_note, 
    $note_box_width_content, // Use the calculated content width for the box
    $note_box_calculated_height, 
    3, // Radius for rounded corners (3mm)
    '1111', // All corners rounded
    '' // No fill
);

// Position for the MultiCell content inside the drawn box
$pdf->SetXY($current_x_note + $note_box_padding, $current_y_note + $note_box_padding);
$pdf->MultiCell($note_box_width_cell, $line_height_estimate, $note_text_content, 0, 'L', 0, 1); // MultiCell for multi-line text

// Reset Y position for subsequent content outside this block
$pdf->SetY($current_y_note + $note_box_calculated_height + 5); // Move Y cursor below the note box with some spacing

// Output the PDF to the browser for download/display
$pdf->Output('Clearance_Certificate_' . $form_id . '.pdf', 'I');
?>
