<?php
/**
 * Quick Chatbot Integration Helper
 * Add this to any user page that doesn't have the chatbot yet
 */

// Simple function to add chatbot to any page
function addChatbotToPage() {
    echo '
    <!-- Chatbot Widget -->
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        // Check if chatbot is already loaded
        if (!document.getElementById("chatbot-integration-loaded")) {
            // Create script element for Botpress
            const script = document.createElement("script");
            script.src = "https://cdn.botpress.cloud/webchat/v1/inject.js";
            script.onload = function() {
                // Initialize chatbot
                window.botpressWebChat.init({
                    botId: "YOUR_BOT_ID", // Replace with actual Bot ID
                    clientId: "YOUR_CLIENT_ID", // Replace with actual Client ID
                    hostUrl: "https://cdn.botpress.cloud/webchat/v1",
                    messagingUrl: "https://messaging.botpress.cloud",
                    botName: "MCiSmartSpace Support",
                    botAvatarUrl: "../public/assets/logo.webp",
                    theme: "prism",
                    themeColor: "#0f4228",
                    showWidget: true,
                    disableAnimations: false,
                    enableConversationDeletion: true,
                    showConversationsButton: false
                });
                
                // Mark as loaded
                const marker = document.createElement("div");
                marker.id = "chatbot-integration-loaded";
                marker.style.display = "none";
                document.body.appendChild(marker);
            };
            document.head.appendChild(script);
        }
    });
    </script>
    
    <style>
    /* Chatbot styling */
    #bp-widget {
        --bp-color-primary: #0f4228 !important;
        --bp-color-secondary: #1a5d3a !important;
    }
    
    @media (max-width: 768px) {
        #bp-widget {
            bottom: 70px !important;
        }
    }
    
    @media print {
        #bp-widget {
            display: none !important;
        }
    }
    </style>';
}
?>
