<?php
/**
 * Chatbot Layout Component
 * Includes the Botpress chatbot widget in user pages
 */

// Include chatbot configuration
require_once __DIR__ . '/../components/chatbot/chatbot-config.php';
?>

<!-- Include Botpress Chatbot Widget -->
<?php renderChatbotWidget(); ?>

<!-- Chatbot integration without additional help buttons -->

<!-- Mobile responsiveness for chatbot -->
<style>
@media (max-width: 768px) {
    .chatbot-help-trigger {
        display: none !important;
    }
    
    #bp-widget {
        bottom: 80px !important;
        right: 15px !important;
    }
}

@media (max-width: 480px) {
    #bp-widget {
        bottom: 70px !important;
        right: 10px !important;
        transform: scale(0.9);
    }
}
</style>
