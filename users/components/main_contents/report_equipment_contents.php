<!-- Page content -->
<div class="right_col" role="main">
    <div class="issue-report-container">
        <h3 class="title">Report Equipment Issue</h3>
        <p class="subtitle">Submit a report for any malfunctioning equipment</p>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">Equipment Information</div>
            <div class="card-body">
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Equipment Unit ID</div>
                        <div class="info-value"><?php echo $unitId ?: 'N/A'; ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Equipment Type</div>
                        <div class="info-value"><?php echo $equipmentType ?: 'N/A'; ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Location</div>
                        <div class="info-value"><?php echo $roomName ?: 'N/A'; ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Building</div>
                        <div class="info-value"><?php echo $buildingName ?: 'N/A'; ?></div>
                    </div>
                </div>
            </div>
        </div>

        <form method="POST" action="" enctype="multipart/form-data">
            <div class="card">
                <div class="card-header">Issue Details</div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="issue_type" class="form-label">Issue Type</label>
                        <select id="issue_type" name="issue_type" class="form-select" required>
                            <option value="">Select issue type</option>
                            <option value="Hardware Failure">Hardware Failure</option>
                            <option value="Software Problem">Software Problem</option>
                            <option value="Connectivity Issue">Connectivity Issue</option>
                            <option value="Power Problem">Power Problem</option>
                            <option value="Display Issue">Display Issue</option>
                            <option value="Audio Problem">Audio Problem</option>
                            <option value="Peripheral Not Working">Peripheral Not Working</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="condition" class="form-label">Equipment Condition</label>
                        <select id="condition" name="condition" class="form-select" required>
                            <option value="needs_repair">Needs Repair</option>
                            <option value="maintenance">Maintenance</option>
                            <option value="missing">Missing</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="description" class="form-label">Description</label>
                        <textarea id="description" name="description" class="form-control" placeholder="Provide details about the issue..." required></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Attach Image (Optional)</label>
                        <div class="upload-container">
                            <label for="image_upload" class="upload-label">
                                <i class="fa fa-cloud-upload upload-icon"></i>
                                <div class="upload-text">Click to upload an image</div>
                                <div class="upload-subtext">JPG, PNG or GIF (max. 5MB)</div>
                                <input type="file" id="image_upload" name="image" class="upload-input" accept="image/*">
                            </label>
                        </div>
                        <div id="image-preview" style="display: none; margin-top: 1rem;">
                            <img id="preview-img" src="" alt="Preview" style="max-width: 100%; max-height: 200px; border-radius: 0.5rem;">
                        </div>
                    </div>
                </div>
            </div>

            <div class="btn-container">
                <a href="qr-scan.php" class="btn btn-secondary">Cancel</a>
                <button type="submit" name="submit_report" class="btn btn-primary">Submit Report</button>
            </div>
        </form>
    </div>
</div>