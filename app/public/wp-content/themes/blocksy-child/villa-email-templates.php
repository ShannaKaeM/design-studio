<?php
/**
 * Villa Capriani Email Template System
 * 
 * Professional email templates for owner communications
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get welcome email template
 */
function villa_get_welcome_email_template($data) {
    $template = '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Welcome to Villa Capriani</title>
        <style>
            body {
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
                line-height: 1.6;
                color: #333333;
                margin: 0;
                padding: 0;
                background-color: #f8f9fa;
            }
            .email-container {
                max-width: 600px;
                margin: 0 auto;
                background-color: #ffffff;
                border-radius: 8px;
                overflow: hidden;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            }
            .email-header {
                background: linear-gradient(135deg, #5a7b7c 0%, #4a6b6c 100%);
                color: white;
                padding: 40px 30px;
                text-align: center;
            }
            .email-header h1 {
                margin: 0;
                font-size: 28px;
                font-weight: 300;
            }
            .email-header .subtitle {
                margin: 10px 0 0 0;
                font-size: 16px;
                opacity: 0.9;
            }
            .email-body {
                padding: 40px 30px;
            }
            .welcome-message {
                font-size: 18px;
                color: #2c3e50;
                margin-bottom: 30px;
            }
            .info-box {
                background-color: #f8f9fa;
                border-left: 4px solid #5a7b7c;
                padding: 20px;
                margin: 25px 0;
                border-radius: 4px;
            }
            .info-box h3 {
                margin: 0 0 15px 0;
                color: #2c3e50;
                font-size: 16px;
            }
            .info-item {
                display: flex;
                justify-content: space-between;
                margin-bottom: 8px;
                padding-bottom: 8px;
                border-bottom: 1px solid #e9ecef;
            }
            .info-item:last-child {
                border-bottom: none;
                margin-bottom: 0;
                padding-bottom: 0;
            }
            .info-label {
                font-weight: 600;
                color: #495057;
            }
            .info-value {
                color: #6c757d;
            }
            .cta-button {
                display: inline-block;
                background-color: #5a7b7c;
                color: white;
                padding: 15px 30px;
                text-decoration: none;
                border-radius: 6px;
                font-weight: 600;
                margin: 20px 0;
                transition: background-color 0.3s ease;
            }
            .cta-button:hover {
                background-color: #4a6b6c;
            }
            .features-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 20px;
                margin: 30px 0;
            }
            .feature-card {
                background-color: #f8f9fa;
                padding: 20px;
                border-radius: 6px;
                text-align: center;
            }
            .feature-icon {
                font-size: 24px;
                margin-bottom: 10px;
            }
            .feature-title {
                font-weight: 600;
                color: #2c3e50;
                margin-bottom: 8px;
            }
            .feature-description {
                font-size: 14px;
                color: #6c757d;
            }
            .email-footer {
                background-color: #2c3e50;
                color: white;
                padding: 30px;
                text-align: center;
            }
            .email-footer p {
                margin: 0 0 10px 0;
                font-size: 14px;
            }
            .contact-info {
                margin-top: 20px;
                padding-top: 20px;
                border-top: 1px solid #495057;
            }
            .social-links {
                margin-top: 15px;
            }
            .social-links a {
                color: #adb5bd;
                text-decoration: none;
                margin: 0 10px;
            }
        </style>
    </head>
    <body>
        <div class="email-container">
            <div class="email-header">
                <h1>Villa Capriani</h1>
                <p class="subtitle">Owner Portal</p>
            </div>
            
            <div class="email-body">
                <div class="welcome-message">
                    <strong>Welcome to your new home, ' . esc_html($data['first_name']) . '!</strong>
                </div>
                
                <p>Congratulations on becoming a verified owner at Villa Capriani! Your account has been successfully created and you now have access to our exclusive owner portal.</p>
                
                <div class="info-box">
                    <h3>Your Account Information</h3>
                    <div class="info-item">
                        <span class="info-label">Name:</span>
                        <span class="info-value">' . esc_html($data['first_name'] . ' ' . $data['last_name']) . '</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Unit Number:</span>
                        <span class="info-value">' . esc_html($data['unit_number']) . '</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Username:</span>
                        <span class="info-value">' . esc_html($data['username']) . '</span>
                    </div>
                </div>
                
                <div style="text-align: center;">
                    <a href="' . esc_url($data['login_url']) . '" class="cta-button">Access Your Portal</a>
                </div>
                
                <div class="features-grid">
                    <div class="feature-card">
                        <div class="feature-icon">📋</div>
                        <div class="feature-title">Committee Information</div>
                        <div class="feature-description">View and join community committees</div>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">📊</div>
                        <div class="feature-title">Financial Reports</div>
                        <div class="feature-description">Access budgets and financial statements</div>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">📄</div>
                        <div class="feature-title">Important Documents</div>
                        <div class="feature-description">Download bylaws, policies, and forms</div>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">🗳️</div>
                        <div class="feature-title">Community Voting</div>
                        <div class="feature-description">Participate in HOA decisions</div>
                    </div>
                </div>
                
                <p><strong>What\'s Next?</strong></p>
                <ul>
                    <li>Log in to your portal using the button above</li>
                    <li>Complete your profile information</li>
                    <li>Explore available committees and consider joining</li>
                    <li>Review important community documents</li>
                    <li>Set your communication preferences</li>
                </ul>
                
                <p>If you have any questions or need assistance, please don\'t hesitate to contact our HOA management team.</p>
            </div>
            
            <div class="email-footer">
                <p><strong>Villa Capriani Homeowners Association</strong></p>
                <div class="contact-info">
                    <p>📧 info@villacapriani.com | 📞 (555) 123-4567</p>
                    <p>🏠 123 Villa Capriani Drive, Your City, ST 12345</p>
                </div>
                <div class="social-links">
                    <a href="#">Facebook</a> |
                    <a href="#">Newsletter</a> |
                    <a href="#">Website</a>
                </div>
                <p style="margin-top: 20px; font-size: 12px; color: #adb5bd;">
                    This email was sent to a verified Villa Capriani property owner. 
                    If you believe you received this in error, please contact us immediately.
                </p>
            </div>
        </div>
    </body>
    </html>';
    
    return $template;
}

/**
 * Get password reset email template
 */
function villa_get_password_reset_email_template($data) {
    $template = '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Password Reset - Villa Capriani</title>
        <style>
            body {
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
                line-height: 1.6;
                color: #333333;
                margin: 0;
                padding: 0;
                background-color: #f8f9fa;
            }
            .email-container {
                max-width: 600px;
                margin: 0 auto;
                background-color: #ffffff;
                border-radius: 8px;
                overflow: hidden;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            }
            .email-header {
                background: linear-gradient(135deg, #5a7b7c 0%, #4a6b6c 100%);
                color: white;
                padding: 40px 30px;
                text-align: center;
            }
            .email-body {
                padding: 40px 30px;
            }
            .security-notice {
                background-color: #fff3cd;
                border: 1px solid #ffeaa7;
                border-radius: 6px;
                padding: 20px;
                margin: 20px 0;
            }
            .cta-button {
                display: inline-block;
                background-color: #5a7b7c;
                color: white;
                padding: 15px 30px;
                text-decoration: none;
                border-radius: 6px;
                font-weight: 600;
                margin: 20px 0;
            }
            .email-footer {
                background-color: #2c3e50;
                color: white;
                padding: 30px;
                text-align: center;
                font-size: 14px;
            }
        </style>
    </head>
    <body>
        <div class="email-container">
            <div class="email-header">
                <h1>Password Reset Request</h1>
                <p>Villa Capriani Owner Portal</p>
            </div>
            
            <div class="email-body">
                <p>Hello ' . esc_html($data['first_name']) . ',</p>
                
                <p>We received a request to reset the password for your Villa Capriani owner account.</p>
                
                <div class="security-notice">
                    <strong>Security Notice:</strong> If you did not request this password reset, please ignore this email and contact our support team immediately.
                </div>
                
                <div style="text-align: center;">
                    <a href="' . esc_url($data['reset_url']) . '" class="cta-button">Reset Your Password</a>
                </div>
                
                <p><strong>Important:</strong></p>
                <ul>
                    <li>This link will expire in 24 hours</li>
                    <li>You can only use this link once</li>
                    <li>If the link doesn\'t work, copy and paste it into your browser</li>
                </ul>
                
                <p>If you continue to have trouble, please contact our support team.</p>
            </div>
            
            <div class="email-footer">
                <p><strong>Villa Capriani HOA</strong></p>
                <p>📧 support@villacapriani.com | 📞 (555) 123-4567</p>
            </div>
        </div>
    </body>
    </html>';
    
    return $template;
}

/**
 * Configure SMTP for email sending
 * SMTP configuration is handled by villa-smtp-config.php
 */
function villa_configure_smtp() {
    // SMTP configuration is handled by the dedicated villa-smtp-config.php file
    // This ensures emails are sent using the configured SMTP settings
}
