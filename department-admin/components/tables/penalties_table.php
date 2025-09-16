<div class="table-container">
    <div class="card">
        <header class="card-header">
            <div class="new-title-container">
                <p class="new-title">MANAGE STUDENT PENALTIES</p>
            </div>
        </header>
        <div class="card-content">
            <table id="studentsTable" class="student-table display is-fullwidth">
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Email</th>
                        <th>Program</th>
                        <th>Year & Section</th>
                        <th>Status</th>
                        <th>Penalty Details</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($studentsResult->num_rows > 0): ?>
                        <?php while ($student = $studentsResult->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($student['FirstName'] . ' ' . $student['LastName']); ?></strong>
                                </td>
                                <td><?php echo htmlspecialchars($student['Email']); ?></td>
                                <td><?php echo htmlspecialchars($student['Program']); ?></td>
                                <td><?php echo htmlspecialchars($student['YearSection']); ?></td>
                                <td>
                                    <?php if ($student['PenaltyStatus'] === 'banned'): ?>
                                        <span class="status-badge banned">
                                            <i class="mdi mdi-cancel"></i> BANNED
                                        </span>
                                    <?php else: ?>
                                        <span class="status-badge active">
                                            <i class="mdi mdi-check-circle"></i> ACTIVE
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($student['penalty_reason']): ?>
                                        <div class="penalty-info">
                                            <strong>Reason:</strong> <?php echo htmlspecialchars($student['penalty_reason']); ?><br>
                                            <small class="text-muted">
                                                Issued: <?php echo date('M d, Y h:i A', strtotime($student['penalty_issued'])); ?>
                                                <?php if ($student['penalty_expires']): ?>
                                                    <br>Expires: <?php echo date('M d, Y h:i A', strtotime($student['penalty_expires'])); ?>
                                                <?php else: ?>
                                                    <br><span class="text-danger">Permanent</span>
                                                <?php endif; ?>
                                            </small>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">No active penalties</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <?php if ($student['PenaltyStatus'] === 'banned'): ?>
                                            <button type="button" class="btn-edit" 
                                                    onclick="unbanStudent(<?php echo $student['StudentID']; ?>, '<?php echo htmlspecialchars($student['FirstName'] . ' ' . $student['LastName']); ?>')">
                                                <i class="mdi mdi-check-circle"></i> Unban
                                            </button>
                                        <?php else: ?>
                                            <button type="button" class="btn-delete" 
                                                    onclick="banStudent(<?php echo $student['StudentID']; ?>, '<?php echo htmlspecialchars($student['FirstName'] . ' ' . $student['LastName']); ?>')">
                                                <i class="mdi mdi-cancel"></i> Ban
                                            </button>
                                        <?php endif; ?>
                                        <button type="button" class="btn-view" 
                                                onclick="viewPenaltyHistory(<?php echo $student['StudentID']; ?>, '<?php echo htmlspecialchars($student['FirstName'] . ' ' . $student['LastName']); ?>')">
                                            <i class="mdi mdi-history"></i> History
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>