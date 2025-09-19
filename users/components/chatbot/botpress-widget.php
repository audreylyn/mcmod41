<?php
/**
 * Botpress Chatbot Widget Component
 * MCiSmartSpace Customer Support Chatbot
 */
?>

<!-- Botpress Chatbot Widget -->
<div id="botpress-chatbot-container">
    <!-- The chatbot will be injected here by Botpress -->
</div>

<!-- Botpress Webchat Scripts -->

<script src="https://cdn.botpress.cloud/webchat/v3.2/inject.js"></script>
<script src="https://files.bpcontent.cloud/2025/09/09/23/20250909231308-LH15MMY4.js" defer></script>
<style>
    .bpWebchat {
  box-shadow: none !important;
}
</style>

<!-- Initialize with user-specific ID -->
<script>
    // Set user-specific storage key for Botpress
    if (typeof localStorage !== 'undefined') {
        // Create a unique key for this user based on user_id from session
        const userID = '<?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : "guest-" . session_id(); ?>';
        localStorage.setItem('bp-user-id', userID);
    }
</script>
    

<script>
// Custom event handlers for better integration - wait for Botpress to load
function initBotpressEvents() {
    if (typeof window.botpressWebChat !== 'undefined' && window.botpressWebChat.onEvent) {
        window.botpressWebChat.onEvent(function(event) {
            if (event.type === 'webchat:ready') {
                console.log('MCiSmartSpace chatbot is ready');
                
                // Get user ID from localStorage or generate a new one
                const userId = localStorage.getItem('bp-user-id') || '<?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : "guest-" . session_id(); ?>';
                
                // Force user ID to ensure chat sessions are properly separated
                window.botpressWebChat.mergeConfig({
                    userId: userId,
                    conversationId: userId + '-conversation'
                });
                
                // Send initial context about the current page
                const currentPage = '<?php echo basename($_SERVER['PHP_SELF']); ?>';
                window.botpressWebChat.sendEvent({
                    type: 'trigger',
                    channel: 'web',
                    payload: {
                        type: 'page_context',
                        page: currentPage,
                        timestamp: new Date().toISOString(),
                        userId: userId
                    }
                });
            }
            
            if (event.type === 'message') {
                // Track chatbot interactions for analytics if needed
                console.log('Chatbot message:', event);
            }
        });
    } else {
        // Botpress not loaded yet, wait and try again
        setTimeout(initBotpressEvents, 100);
    }
}

// Start trying to initialize Botpress events after DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Wait a bit for Botpress script to load, then try to initialize
    setTimeout(initBotpressEvents, 500);
});

// Helper function to send custom context to chatbot
function sendChatbotContext(contextType, data) {
    if (typeof window.botpressWebChat !== 'undefined' && window.botpressWebChat.sendEvent) {
        // Get user ID from localStorage or generate a new one
        const userId = localStorage.getItem('bp-user-id') || '<?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : "guest-" . session_id(); ?>';
        
        window.botpressWebChat.sendEvent({
            type: 'trigger',
            channel: 'web',
            payload: {
                type: contextType,
                data: data,
                timestamp: new Date().toISOString(),
                userId: userId
            }
        });
    }
}

// Integration with existing system features
document.addEventListener('DOMContentLoaded', function() {
    // Wait a bit for Botpress script to load, then try to initialize
    setTimeout(initBotpressEvents, 500);
    
    // Add context-aware help buttons throughout the system
    const helpButtons = document.querySelectorAll('.chatbot-help-trigger');
    helpButtons.forEach(button => {
        button.addEventListener('click', function() {
            const context = this.getAttribute('data-context');
            const topic = this.getAttribute('data-topic');
            
            // Open chatbot and send context - with safety check
            if (typeof window.botpressWebChat !== 'undefined' && window.botpressWebChat.sendEvent) {
                window.botpressWebChat.sendEvent({
                    type: 'trigger',
                    channel: 'web', 
                    payload: {
                        type: 'help_request',
                        context: context,
                        topic: topic
                    }
                });
            }
        });
    });
});
</script>

<!-- Keep default Botpress appearance - no custom styling -->
