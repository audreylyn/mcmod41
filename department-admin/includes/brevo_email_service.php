<?php
/**
 * Brevo (Sendinblue) Email Service for Room Reservation Notifications
 */

class BrevoEmailService {
    private $apiKey;
    private $fromEmail;
    private $fromName;
    
    public function __construct($apiKey, $fromEmail = 'mcismartspace@gmail.com', $fromName = 'MCiSmartSpace') {
        $this->apiKey = $apiKey;
        $this->fromEmail = $fromEmail;
        $this->fromName = $fromName;
    }
    
    /**
     * Send room reservation approval email
     */
    public function sendApprovalEmail($recipientEmail, $recipientName, $reservationDetails) {
        $subject = "Room Reservation Approved - " . $reservationDetails['activity_name'];
        
        $htmlContent = $this->getApprovalEmailTemplate($recipientName, $reservationDetails);
        $textContent = $this->getApprovalEmailTextTemplate($recipientName, $reservationDetails);
        
        return $this->sendEmail($recipientEmail, $recipientName, $subject, $htmlContent, $textContent);
    }
    
    /**
     * Send room reservation rejection email
     */
    public function sendRejectionEmail($recipientEmail, $recipientName, $reservationDetails) {
        $subject = "Room Reservation Update - " . $reservationDetails['activity_name'];
        
        $htmlContent = $this->getRejectionEmailTemplate($recipientName, $reservationDetails);
        $textContent = $this->getRejectionEmailTextTemplate($recipientName, $reservationDetails);
        
        return $this->sendEmail($recipientEmail, $recipientName, $subject, $htmlContent, $textContent);
    }
    
    /**
     * Send email using Brevo API v3
     */
    private function sendEmail($toEmail, $toName, $subject, $htmlContent, $textContent) {
        $data = [
            'sender' => [
                'email' => $this->fromEmail,
                'name' => $this->fromName
            ],
            'to' => [
                [
                    'email' => $toEmail,
                    'name' => $toName
                ]
            ],
            'subject' => $subject,
            'htmlContent' => $htmlContent,
            'textContent' => $textContent
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.brevo.com/v3/smtp/email');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'api-key: ' . $this->apiKey,
            'Content-Type: application/json',
            'accept: application/json'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return [
            'success' => $httpCode >= 200 && $httpCode < 300,
            'http_code' => $httpCode,
            'response' => $response
        ];
    }
    
    /**
     * HTML template for approval email
     */
    private function getApprovalEmailTemplate($recipientName, $details) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Room Reservation Approved</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 20px; background-color: #f4f4f4; }
                .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
                .header { background: #28a745; color: white; padding: 20px; text-align: center; border-radius: 5px; margin-bottom: 30px; }
                .header h1 { font-size: 20px; margin: 0; }
                .content { padding: 0 10px; }
                .details { background: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0; }
                .detail-item { margin: 10px 0; padding: 8px 0; border-bottom: 1px solid #e9ecef; }
                .detail-item:last-child { border-bottom: none; }
                .label { font-weight: bold; color: #495057; }
                .footer { text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e9ecef; color: #6c757d; font-size: 14px; }
                .success-badge { background: #28a745; color: white; padding: 3px 10px; border-radius: 15px; font-size: 12px; font-weight: bold; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Room Reservation Approved</h1>
                </div>
                
                <div class='content'>
                    <p>Dear {$recipientName},</p>
                    
                    <p>Great news! Your room reservation request has been <span class='success-badge'>APPROVED</span>.</p>
                    
                    <div class='details'>
                        <h3>Reservation Details:</h3>
                        <div class='detail-item'>
                            <span class='label'>Activity:</span> {$details['activity_name']}
                        </div>
                        <div class='detail-item'>
                            <span class='label'>Room:</span> {$details['room_name']}, {$details['building_name']}
                        </div>
                        <div class='detail-item'>
                            <span class='label'>Date:</span> {$details['reservation_date']}
                        </div>
                        <div class='detail-item'>
                            <span class='label'>Time:</span> {$details['start_time']} - {$details['end_time']}
                        </div>
                        <div class='detail-item'>
                            <span class='label'>Participants:</span> {$details['participants']}
                        </div>
                        <div class='detail-item'>
                            <span class='label'>Approved by:</span> {$details['approver_name']}
                        </div>
                    </div>
                    
                    <p><strong>Important Notes:</strong></p>
                    <ul>
                        <li>Please arrive on time for your scheduled activity</li>
                        <li>Ensure the room is clean and properly arranged after use</li>
                        <li>Contact the department office if you need to make any changes</li>
                        <li>Keep this email as confirmation of your approved reservation</li>
                    </ul>
                    
                    <p>If you have any questions or need to make changes to your reservation, please contact the department office immediately.</p>
                    
                    <p>Thank you for using our room reservation system!</p>
                </div>
                
                <div class='footer'>
                    <p>This is an automated message from the Room Reservation System.<br>
                    Please do not reply to this email.</p>
                </div>
            </div>
        </body>
        </html>";
    }
    
    /**
     * Plain text template for approval email
     */
    private function getApprovalEmailTextTemplate($recipientName, $details) {
        return "
ROOM RESERVATION APPROVED

Dear {$recipientName},

Great news! Your room reservation request has been APPROVED.

Reservation Details:
- Activity: {$details['activity_name']}
- Room: {$details['room_name']}, {$details['building_name']}
- Date: {$details['reservation_date']}
- Time: {$details['start_time']} - {$details['end_time']}
- Participants: {$details['participants']}
- Approved by: {$details['approver_name']}

Important Notes:
- Please arrive on time for your scheduled activity
- Ensure the room is clean and properly arranged after use
- Contact the department office if you need to make any changes
- Keep this email as confirmation of your approved reservation

If you have any questions or need to make changes to your reservation, please contact the department office immediately.

Thank you for using our room reservation system!

---
This is an automated message from the Room Reservation System.
Please do not reply to this email.
        ";
    }
    
    /**
     * HTML template for rejection email
     */
    private function getRejectionEmailTemplate($recipientName, $details) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Room Reservation Update</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 20px; background-color: #f4f4f4; }
                .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
                .header { background: #dc3545; color: white; padding: 20px; text-align: center; border-radius: 5px; margin-bottom: 30px; }
                .header h1 { font-size: 20px; margin: 0; }
                .content { padding: 0 10px; }
                .details { background: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0; }
                .detail-item { margin: 10px 0; padding: 8px 0; border-bottom: 1px solid #e9ecef; }
                .detail-item:last-child { border-bottom: none; }
                .label { font-weight: bold; color: #495057; }
                .reason-box { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 15px 0; }
                .footer { text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e9ecef; color: #6c757d; font-size: 14px; }
                .rejected-badge { background: #dc3545; color: white; padding: 3px 10px; border-radius: 15px; font-size: 12px; font-weight: bold; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Room Reservation Update</h1>
                </div>
                
                <div class='content'>
                    <p>Dear {$recipientName},</p>
                    
                    <p>We regret to inform you that your room reservation request has been <span class='rejected-badge'>NOT APPROVED</span>.</p>
                    
                    <div class='details'>
                        <h3>Reservation Details:</h3>
                        <div class='detail-item'>
                            <span class='label'>Activity:</span> {$details['activity_name']}
                        </div>
                        <div class='detail-item'>
                            <span class='label'>Room:</span> {$details['room_name']}, {$details['building_name']}
                        </div>
                        <div class='detail-item'>
                            <span class='label'>Date:</span> {$details['reservation_date']}
                        </div>
                        <div class='detail-item'>
                            <span class='label'>Time:</span> {$details['start_time']} - {$details['end_time']}
                        </div>
                        <div class='detail-item'>
                            <span class='label'>Participants:</span> {$details['participants']}
                        </div>
                        <div class='detail-item'>
                            <span class='label'>Reviewed by:</span> {$details['reviewer_name']}
                        </div>
                    </div>
                    
                    <div class='reason-box'>
                        <h4>Reason for Rejection:</h4>
                        <p>{$details['rejection_reason']}</p>
                    </div>
                    
                    <p><strong>Next Steps:</strong></p>
                    <ul>
                        <li>You may submit a new request for a different date/time</li>
                        <li>Contact the department office to discuss alternative options</li>
                        <li>Check room availability for other suitable times</li>
                    </ul>
                    
                    <p>If you have any questions about this decision or need assistance with finding alternative arrangements, please contact the department office.</p>
                    
                    <p>Thank you for your understanding.</p>
                </div>
                
                <div class='footer'>
                    <p>This is an automated message from the Room Reservation System.<br>
                    Please do not reply to this email.</p>
                </div>
            </div>
        </body>
        </html>";
    }
    
    /**
     * Plain text template for rejection email
     */
    private function getRejectionEmailTextTemplate($recipientName, $details) {
        return "
ROOM RESERVATION UPDATE

Dear {$recipientName},

We regret to inform you that your room reservation request has been NOT APPROVED.

Reservation Details:
- Activity: {$details['activity_name']}
- Room: {$details['room_name']}, {$details['building_name']}
- Date: {$details['reservation_date']}
- Time: {$details['start_time']} - {$details['end_time']}
- Participants: {$details['participants']}
- Reviewed by: {$details['reviewer_name']}

Reason for Rejection:
{$details['rejection_reason']}

Next Steps:
- You may submit a new request for a different date/time
- Contact the department office to discuss alternative options
- Check room availability for other suitable times

If you have any questions about this decision or need assistance with finding alternative arrangements, please contact the department office.

Thank you for your understanding.

---
This is an automated message from the Room Reservation System.
Please do not reply to this email.
        ";
    }
}
?>
