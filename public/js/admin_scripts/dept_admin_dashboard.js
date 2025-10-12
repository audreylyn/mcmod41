// Initialize charts once the DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize sidebar dropdowns
    const dropdowns = document.querySelectorAll('a.dropdown');
    dropdowns.forEach(function(dropdown) {
        const submenu = dropdown.nextElementSibling;
        if (submenu) {
            submenu.style.display = "none";
        }
    });

    // Room Status Chart - Updated colors to match site theme
    const roomStatusData = {
        pending: window.roomStats?.pending || 0,
        approved: window.roomStats?.approved || 0,
        rejected: window.roomStats?.rejected || 0
    };

    const roomStatusChart = new Chart(
        document.getElementById('roomStatusChart'), {
            type: 'pie',
            data: {
                labels: ['Pending', 'Approved', 'Rejected'],
                datasets: [{
                    data: [roomStatusData.pending, roomStatusData.approved, roomStatusData.rejected],
                    backgroundColor: ['#d4af37', '#1e5631', '#c62828'], // Gold, Dark Green, Deep Red
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            font: {
                                size: 12
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = Math.round((value / total) * 100);
                                return `${label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        }
    );

    // Equipment Issues Chart - Updated colors to match site theme
    const issueStatusData = {
        pending: window.issueStats?.pending || 0,
        in_progress: window.issueStats?.in_progress || 0,
        resolved: window.issueStats?.resolved || 0,
        rejected: window.issueStats?.rejected || 0
    };

    const issuesStatusChart = new Chart(
        document.getElementById('issuesStatusChart'), {
            type: 'pie',
            data: {
                labels: ['Pending', 'In Progress', 'Resolved', 'Rejected'],
                datasets: [{
                    data: [
                        issueStatusData.pending,
                        issueStatusData.in_progress,
                        issueStatusData.resolved,
                        issueStatusData.rejected
                    ],
                    backgroundColor: [
                        '#d4af37', // Gold for pending
                        '#3e7650', // Medium green for in progress
                        '#1e5631', // Dark green for resolved
                        '#c62828' // Deep red for rejected
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            font: {
                                size: 12
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = Math.round((value / total) * 100);
                                return `${label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        }
    );

    // Monthly Trends Chart removed - replaced with Quick Actions section

    // Reports functionality
    initializeReports();
});

function initializeReports() {
    let selectedReport = null;
    let startDate = null;
    let endDate = null;

    // Don't set default dates - require user to select them first
    const startDateInput = document.getElementById('startDate');
    const endDateInput = document.getElementById('endDate');
    
    console.log('Date inputs found:', { startDateInput, endDateInput });
    
    // Initialize all buttons as disabled
    updateReportButtons(false);
    updateExportButtons();

    // Quick filter buttons removed

    // Date input change handlers
    if (startDateInput) {
        startDateInput.addEventListener('change', function() {
            startDate = this.value;
            checkDateRangeAndEnableReports();
        });
    }
    
    if (endDateInput) {
        endDateInput.addEventListener('change', function() {
            endDate = this.value;
            checkDateRangeAndEnableReports();
        });
    }

    function checkDateRangeAndEnableReports() {
        console.log('Checking dates:', { startDate, endDate });
        const hasValidDates = startDate && endDate && validateDateRange();
        console.log('Has valid dates:', hasValidDates);
        
        updateReportButtons(hasValidDates);
        updateStatusMessage(hasValidDates);
        
        // Reset selected report if dates become invalid
        if (!hasValidDates) {
            selectedReport = null;
            const reportBtns = document.querySelectorAll('.report-btn');
            reportBtns.forEach(btn => btn.classList.remove('selected'));
        }
        
        updateExportButtons();
    }

    function updateReportButtons(enabled) {
        console.log('Updating report buttons, enabled:', enabled);
        const reportBtns = document.querySelectorAll('.report-btn');
        console.log('Found report buttons:', reportBtns.length);
        
        reportBtns.forEach(btn => {
            btn.disabled = !enabled;
            if (!enabled) {
                btn.classList.remove('selected');
            }
            console.log('Button disabled state:', btn.disabled);
        });
    }

    function updateStatusMessage(hasValidDates) {
        const statusMessage = document.getElementById('reportStatusMessage');
        if (statusMessage) {
            if (hasValidDates) {
                statusMessage.innerHTML = '<i class="mdi mdi-check-circle"></i><span>Date range selected. Choose a report type below.</span>';
                statusMessage.className = 'report-status-message success';
            } else {
                statusMessage.innerHTML = '<i class="mdi mdi-information-outline"></i><span>Select a date range above to enable report options</span>';
                statusMessage.className = 'report-status-message';
            }
        }
    }

    // Report selection buttons
    const reportBtns = document.querySelectorAll('.report-btn');
    reportBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            // Don't allow selection if button is disabled
            if (this.disabled) {
                return;
            }
            
            // Update selected state
            reportBtns.forEach(b => b.classList.remove('selected'));
            this.classList.add('selected');
            selectedReport = this.dataset.report;
            
            // Enable export buttons
            updateExportButtons();
        });
    });

    // Export buttons
    const exportBtns = document.querySelectorAll('.export-btn');
    exportBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const format = this.dataset.format;
            if (selectedReport && startDate && endDate) {
                handleExport(selectedReport, format, startDate, endDate);
            } else {
                showAlert('Please select a report and date range first.', 'warning');
            }
        });
    });

    function validateDateRange() {
        if (!startDate || !endDate) {
            return false;
        }
        if (new Date(startDate) > new Date(endDate)) {
            showAlert('Start date cannot be after end date.', 'error');
            return false;
        }
        return true;
    }

    function updateExportButtons() {
        const exportBtns = document.querySelectorAll('.export-btn');
        const hasSelection = selectedReport && startDate && endDate;
        
        exportBtns.forEach(btn => {
            btn.disabled = !hasSelection;
        });
    }

    function handleExport(reportType, format, startDate, endDate) {
        // Show loading state
        const exportBtn = document.querySelector(`[data-format="${format}"]`);
        const originalText = exportBtn.innerHTML;
        exportBtn.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> Processing...';
        exportBtn.disabled = true;

        if (format === 'preview') {
            // Handle preview with POST request
            const formData = new FormData();
            formData.append('report_type', reportType);
            formData.append('start_date', startDate);
            formData.append('end_date', endDate);

            fetch('includes/report_preview.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                showReportPreview(data);
            })
            .catch(error => {
                console.error('Preview error:', error);
                showAlert('Error generating preview. Please try again.', 'error');
            })
            .finally(() => {
                exportBtn.innerHTML = originalText;
                exportBtn.disabled = false;
            });
        } else if (format === 'csv') {
            // Handle CSV export with AJAX (similar to building export)
            const params = new URLSearchParams({
                report_type: reportType,
                start_date: startDate,
                end_date: endDate
            });

            fetch(`includes/report_export_ajax.php?${params}`, {
                method: 'GET'
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Create temporary link to download the file
                    const a = document.createElement('a');
                    a.href = data.fileUrl;
                    a.download = data.fileName;
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    
                    showAlert('Report exported successfully!', 'success');
                } else {
                    showAlert(data.message || 'Export failed', 'error');
                }
            })
            .catch(error => {
                console.error('Export error:', error);
                showAlert('Error generating CSV export. Please try again.', 'error');
            })
            .finally(() => {
                exportBtn.innerHTML = originalText;
                exportBtn.disabled = false;
            });
        } 
    }

    function showReportPreview(htmlContent) {
        // Create and show modal with report preview
        const modal = document.createElement('div');
        modal.className = 'modal is-active';
        modal.innerHTML = `
            <div class="modal-background"></div>
            <div class="modal-card" style="width: 90%; max-width: 1200px;">
                <header class="modal-card-head">
                    <p class="modal-card-title">Report Preview</p>
                    <button class="delete" type="button"></button>
                </header>
                <section class="modal-card-body" style="max-height: 70vh; overflow-y: auto;">
                    ${htmlContent}
                </section>
                <footer class="modal-card-foot">
                    <button class="button" type="button">Close</button>
                </footer>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        // Close modal handlers
        const closeButtons = modal.querySelectorAll('.delete, .button');
        closeButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                document.body.removeChild(modal);
            });
        });
        
        modal.querySelector('.modal-background').addEventListener('click', () => {
            document.body.removeChild(modal);
        });
    }

    function showAlert(message, type) {
        // Create alert notification
        const alert = document.createElement('div');
        alert.className = `notification is-${type} is-light`;
        alert.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 1000; max-width: 400px;';
        alert.innerHTML = `
            <button class="delete"></button>
            ${message}
        `;
        
        document.body.appendChild(alert);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (document.body.contains(alert)) {
                document.body.removeChild(alert);
            }
        }, 5000);
        
        // Manual close
        alert.querySelector('.delete').addEventListener('click', () => {
            document.body.removeChild(alert);
        });
    }

    // Initialize export buttons as disabled
    updateExportButtons();
    
    // Test function - you can call this in console to test
    window.testReportButtons = function() {
        console.log('Testing report buttons...');
        console.log('Current dates:', { startDate, endDate });
        checkDateRangeAndEnableReports();
    };
}
