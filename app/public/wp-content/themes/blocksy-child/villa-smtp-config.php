<?php
/**
 * Villa Capriani SMTP Configuration
 * 
 * Admin interface for configuring email settings
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add SMTP configuration to admin menu
 */
function villa_add_smtp_admin_menu() {
    $hook = add_submenu_page(
        'villa-admin',
        'Email Settings',
        'Email Settings',
        'manage_options',
        'villa-smtp-settings',
        'villa_smtp_settings_page'
    );
    
    // Enqueue admin styles for this page
    add_action('load-' . $hook, 'villa_smtp_admin_styles');
}
add_action('admin_menu', 'villa_add_smtp_admin_menu');

/**
 * Enqueue admin styles for SMTP settings page
 */
function villa_smtp_admin_styles() {
    wp_enqueue_style(
        'villa-admin-crm',
        get_stylesheet_directory_uri() . '/assets/css/villa-admin-crm.css',
        [],
        '1.0.0'
    );
}

/**
 * SMTP settings page
 */
function villa_smtp_settings_page() {
    // Handle form submission
    if (isset($_POST['villa_save_smtp']) && wp_verify_nonce($_POST['villa_smtp_nonce'], 'villa_smtp_settings')) {
        update_option('villa_smtp_username', sanitize_email($_POST['smtp_username']));
        update_option('villa_smtp_password', sanitize_text_field($_POST['smtp_password']));
        update_option('villa_smtp_from_email', sanitize_email($_POST['smtp_from_email']));
        update_option('villa_smtp_from_name', sanitize_text_field($_POST['smtp_from_name']));
        update_option('villa_smtp_host', sanitize_text_field($_POST['smtp_host']));
        update_option('villa_smtp_port', intval($_POST['smtp_port']));
        
        echo '<div class="notice notice-success"><p>SMTP settings saved successfully!</p></div>';
    }
    
    // Get current settings
    $smtp_username = get_option('villa_smtp_username', '');
    $smtp_password = get_option('villa_smtp_password', '');
    $smtp_from_email = get_option('villa_smtp_from_email', 'noreply@villacapriani.com');
    $smtp_from_name = get_option('villa_smtp_from_name', 'Villa Capriani HOA');
    $smtp_host = get_option('villa_smtp_host', 'smtp.gmail.com');
    $smtp_port = get_option('villa_smtp_port', 587);
    ?>
    
    <div class="wrap">
        <h1>Villa Capriani Email Settings</h1>
        
        <div class="villa-admin-container">
            <div class="villa-admin-card">
                <h2>SMTP Configuration</h2>
                <p>Configure your email server settings for sending professional emails to owners.</p>
                
                <form method="post">
                    <?php wp_nonce_field('villa_smtp_settings', 'villa_smtp_nonce'); ?>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">SMTP Host</th>
                            <td>
                                <input type="text" name="smtp_host" value="<?php echo esc_attr($smtp_host); ?>" class="regular-text" />
                                <p class="description">For Gmail: smtp.gmail.com | For Cloudflare: Check your Cloudflare email settings</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">SMTP Port</th>
                            <td>
                                <input type="number" name="smtp_port" value="<?php echo esc_attr($smtp_port); ?>" class="small-text" />
                                <p class="description">Usually 587 for TLS or 465 for SSL</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Username</th>
                            <td>
                                <input type="email" name="smtp_username" value="<?php echo esc_attr($smtp_username); ?>" class="regular-text" />
                                <p class="description">Your email address (e.g., admin@villacapriani.com)</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Password</th>
                            <td>
                                <input type="password" name="smtp_password" value="<?php echo esc_attr($smtp_password); ?>" class="regular-text" />
                                <p class="description">For Gmail, use an App Password (not your regular password)</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">From Email</th>
                            <td>
                                <input type="email" name="smtp_from_email" value="<?php echo esc_attr($smtp_from_email); ?>" class="regular-text" />
                                <p class="description">Email address that appears in the "From" field</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">From Name</th>
                            <td>
                                <input type="text" name="smtp_from_name" value="<?php echo esc_attr($smtp_from_name); ?>" class="regular-text" />
                                <p class="description">Name that appears in the "From" field</p>
                            </td>
                        </tr>
                    </table>
                    
                    <p class="submit">
                        <input type="submit" name="villa_save_smtp" class="button-primary" value="Save Settings" />
                    </p>
                </form>
            </div>
            
            <div class="villa-admin-card">
                <h2>Gmail Setup Instructions</h2>
                <ol>
                    <li><strong>Enable 2-Factor Authentication</strong> on your Gmail account</li>
                    <li>Go to <strong>Google Account Settings</strong> → Security</li>
                    <li>Under "Signing in to Google", click <strong>App passwords</strong></li>
                    <li>Generate a new app password for "Mail"</li>
                    <li>Use this app password in the Password field above</li>
                </ol>
                
                <h3>Cloudflare Email Setup</h3>
                <p>If using Cloudflare Email Routing:</p>
                <ol>
                    <li>Check your Cloudflare dashboard for SMTP settings</li>
                    <li>Update the SMTP host and port accordingly</li>
                    <li>Use your Cloudflare email credentials</li>
                </ol>
            </div>
            
            <div class="villa-admin-card">
                <h2>Test Email</h2>
                <p>Send a test email to verify your SMTP configuration:</p>
                <form method="post" style="margin-top: 15px;">
                    <?php wp_nonce_field('villa_test_email', 'villa_test_nonce'); ?>
                    <input type="email" name="test_email" placeholder="Enter test email address" class="regular-text" required />
                    <input type="submit" name="villa_send_test" class="button" value="Send Test Email" />
                </form>
                
                <?php
                if (isset($_POST['villa_send_test']) && wp_verify_nonce($_POST['villa_test_nonce'], 'villa_test_email')) {
                    $test_email = sanitize_email($_POST['test_email']);
                    $subject = 'Villa Capriani SMTP Test';
                    $message = 'This is a test email from Villa Capriani. If you received this, your SMTP configuration is working correctly!';
                    
                    if (wp_mail($test_email, $subject, $message)) {
                        echo '<div class="notice notice-success"><p>Test email sent successfully to ' . esc_html($test_email) . '</p></div>';
                    } else {
                        echo '<div class="notice notice-error"><p>Failed to send test email. Please check your SMTP settings.</p></div>';
                    }
                }
                ?>
            </div>
        </div>
    </div>
    
    <?php
}

/**
 * Update SMTP configuration function
 */
function villa_smtp_config($phpmailer) {
    $smtp_host = get_option('villa_smtp_host', 'smtp.gmail.com');
    $smtp_port = get_option('villa_smtp_port', 587);
    $smtp_username = get_option('villa_smtp_username', '');
    $smtp_password = get_option('villa_smtp_password', '');
    $smtp_from_email = get_option('villa_smtp_from_email', 'noreply@villacapriani.com');
    $smtp_from_name = get_option('villa_smtp_from_name', 'Villa Capriani HOA');
    
    if (!empty($smtp_username) && !empty($smtp_password)) {
        $phpmailer->isSMTP();
        $phpmailer->Host = $smtp_host;
        $phpmailer->SMTPAuth = true;
        $phpmailer->Port = $smtp_port;
        $phpmailer->SMTPSecure = ($smtp_port == 465) ? 'ssl' : 'tls';
        $phpmailer->Username = $smtp_username;
        $phpmailer->Password = $smtp_password;
        $phpmailer->From = $smtp_from_email;
        $phpmailer->FromName = $smtp_from_name;
    }
}
add_action('phpmailer_init', 'villa_smtp_config');
