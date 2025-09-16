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

    // Monthly Trends Chart - Updated colors to match site theme
    const labels = window.monthlyLabels || [];
    const data = window.monthlyData || [];

    const monthlyTrendsChart = new Chart(
        document.getElementById('monthlyTrendsChart'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Room Requests',
                    data: data,
                    borderColor: '#1e5631', // Dark green for line
                    backgroundColor: 'rgba(30, 86, 49, 0.15)', // Transparent green for area
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#d4af37', // Gold for data points
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0,
                            font: {
                                size: 11
                            }
                        },
                        grid: {
                            display: true,
                            color: 'rgba(0,0,0,0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 11
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            boxWidth: 12,
                            font: {
                                size: 12
                            }
                        }
                    }
                }
            }
        }
    );
});
