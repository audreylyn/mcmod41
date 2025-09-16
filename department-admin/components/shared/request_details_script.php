<?php
// Generate JavaScript for request details modal
$detailsScript = '';
foreach ($requests as $row) {
    $id = $row['RequestID'];
    $purpose = htmlspecialchars($row['Purpose'] ?? '', ENT_QUOTES);
    $statusClass = '';
    switch ($row['Status']) {
        case 'pending':
            $statusClass = 'status-pending';
            break;
        case 'approved':
            $statusClass = 'status-approved';
            break;
        case 'rejected':
            $statusClass = 'status-rejected';
            break;
    }

    $detailsScript .= "if(requestId === $id) {\n";
    $detailsScript .= "  let detailsHtml = `
        <p><strong>Request ID:</strong> <span>$id</span></p>
        <p><strong>Activity:</strong> <span>" . htmlspecialchars($row['ActivityName'], ENT_QUOTES) . "</span></p>
        <p><strong>Purpose:</strong> <span>$purpose</span></p>
        <p><strong>Room:</strong> <span>" . htmlspecialchars($row['room_name'], ENT_QUOTES) . ", " . htmlspecialchars($row['building_name'], ENT_QUOTES) . "</span></p>
        <p><strong>Date:</strong> <span>" . date('F j, Y', strtotime($row['ReservationDate'])) . "</span></p>
        <p><strong>Time:</strong> <span>" . date('g:i A', strtotime($row['StartTime'])) . " - " . date('g:i A', strtotime($row['EndTime'])) . "</span></p>
        <p><strong>Participants:</strong> <span>" . $row['NumberOfParticipants'] . "</span></p>
        <p><strong>Status:</strong> <span class=\"status-badge $statusClass\">" . ucfirst($row['Status']) . "</span></p>";

    // Add approved by information if the request is approved
    if ($row['Status'] === 'approved' && (!empty($row['ApproverFirstName']) || !empty($row['ApproverLastName']))) {
        $approverFullName = htmlspecialchars($row['ApproverFirstName'] . ' ' . $row['ApproverLastName'], ENT_QUOTES);
        $detailsScript .= "<p style=\"color: var(--success-color);\"><strong>Approved by:</strong> <span>" . $approverFullName . "</span></p>";
    }

    if ($row['Status'] === 'rejected') {
        if (!empty($row['RejectionReason'])) {
            $detailsScript .= "<p style=\"color: var(--danger-color);\"><strong>Rejection Reason:</strong> <span>" . htmlspecialchars($row['RejectionReason'], ENT_QUOTES) . "</span></p>";
        }

        if (!empty($row['RejecterFirstName']) || !empty($row['RejecterLastName'])) {
            $rejecterFullName = htmlspecialchars($row['RejecterFirstName'] . ' ' . $row['RejecterLastName'], ENT_QUOTES);
            $rejecterFullName = htmlspecialchars($row['RejecterFirstName'] . ' ' . $row['RejecterLastName'], ENT_QUOTES);
            $detailsScript .= "<p style=\"color: var(--danger-color);\"><strong>Rejected by:</strong> <span>" . $rejecterFullName . "</span></p>";
        }
    }

    $detailsScript .= "`;\n";
    $detailsScript .= "  document.getElementById('detailsModalContent').innerHTML = detailsHtml;\n";
    $detailsScript .= "}\n";
}
echo $detailsScript;
?>
