<?php
/**
 * Botpress Chatbot Configuration
 * MCiSmartSpace System Integration
 */

class ChatbotConfig {
    
    // Botpress Configuration Constants
    const BOT_ID = 'wkspace_01K4RC203AXAS1VYMVRTAG3C4N'; // Replace with your actual Bot ID from Botpress dashboard
    const CLIENT_ID = 'a4184183-8a95-4bf3-ad03-8c6cf53f1032'; // Replace with your actual Client ID
    const WEBHOOK_URL = 'YOUR_WEBHOOK_URL'; // Replace with your webhook URL if using server-side integration
    
    // System Context Configuration
    public static function getSystemContext() {
        return [
            'system_name' => 'MCiSmartSpace',
            'system_type' => 'Room Reservation & Equipment Management',
            'features' => [
                'room_browsing' => 'Browse and view available rooms',
                'room_reservation' => 'Make room reservations',
                'reservation_history' => 'View reservation history and status',
                'equipment_reporting' => 'Report equipment issues',
                'profile_management' => 'Edit user profile and change password',
                'qr_scanning' => 'QR code scanning for equipment validation'
            ],
            'user_roles' => ['Student', 'Teacher'],
            'support_topics' => [
                'How to make a room reservation',
                'How to cancel a reservation', 
                'How to report equipment issues',
                'How to change password',
                'How to view reservation history',
                'QR code scanning help',
                'Account management',
                'Technical issues'
            ]
        ];
    }
    
    // Get user context for personalized support
    public static function getUserContext() {
        $context = [];
        
        if (isset($_SESSION['user_id'])) {
            $context['user_id'] = $_SESSION['user_id'];
            $context['name'] = $_SESSION['name'] ?? '';
            $context['role'] = $_SESSION['role'] ?? '';
            $context['email'] = $_SESSION['email'] ?? '';
            $context['is_authenticated'] = true;
        } else {
            $context['is_authenticated'] = false;
        }
        
        return $context;
    }
    
    // Get current page context
    public static function getPageContext() {
        $currentPage = basename($_SERVER['PHP_SELF']);
        
        $pageContexts = [
            'users_browse_room.php' => [
                'section' => 'room_browsing',
                'help_topics' => [
                    'How to search for rooms',
                    'Understanding room availability',
                    'Room features and equipment',
                    'Making a reservation'
                ]
            ],
            'users_reservation_history.php' => [
                'section' => 'reservation_history',
                'help_topics' => [
                    'Understanding reservation status',
                    'How to cancel a reservation',
                    'Viewing reservation details',
                    'Reservation approval process'
                ]
            ],
            'equipment_report_status.php' => [
                'section' => 'equipment_reporting',
                'help_topics' => [
                    'How to report equipment issues',
                    'Tracking report status',
                    'Adding photos to reports',
                    'Understanding report priorities'
                ]
            ],
            'edit_profile.php' => [
                'section' => 'profile_management',
                'help_topics' => [
                    'How to update profile information',
                    'Changing password',
                    'Account security',
                    'Profile photo upload'
                ]
            ],
            'qr-scan.php' => [
                'section' => 'qr_scanning',
                'help_topics' => [
                    'How to scan QR codes',
                    'Equipment validation process',
                    'Camera permissions',
                    'QR code not working'
                ]
            ]
        ];
        
        return [
            'current_page' => $currentPage,
            'context' => $pageContexts[$currentPage] ?? [
                'section' => 'general',
                'help_topics' => ['General system help', 'Navigation assistance']
            ]
        ];
    }
    
    // Generate chatbot initialization data
    public static function getChatbotInitData() {
        return [
            'system_context' => self::getSystemContext(),
            'user_context' => self::getUserContext(),
            'page_context' => self::getPageContext(),
            'timestamp' => date('c'),
            'session_id' => session_id()
        ];
    }
}

// Helper function to render chatbot widget with proper configuration
function renderChatbotWidget() {
    $config = ChatbotConfig::getChatbotInitData();
    
    // Include the widget component
    include_once 'botpress-widget.php';
    
    // Generate a unique session identifier for this user
    $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : "guest-" . session_id();
    
    // Add page-specific context script
    echo "<script>
        // Send page context when chatbot is ready
        document.addEventListener('DOMContentLoaded', function() {
            if (window.botpressWebChat) {
                setTimeout(function() {
                    // Set user ID to localStorage before initializing chatbot
                    localStorage.setItem('bp-user-id', '" . $userId . "');
                    
                    // Configure chatbot with user-specific ID
                    if (window.botpressWebChat.mergeConfig) {
                        window.botpressWebChat.mergeConfig({
                            userId: '" . $userId . "',
                            conversationId: '" . $userId . "-conversation'
                        });
                    }
                    
                    // Send context to chatbot
                    sendChatbotContext('page_load', " . json_encode($config) . ");
                }, 1000);
            }
        });
    </script>";
}
?>
