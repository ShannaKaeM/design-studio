<?php
/**
 * Villa Capriani Owner CRM System
 * 
 * Comprehensive owner management system that integrates with existing JSON data
 * Handles registration, verification, user accounts, and owner portal access
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Villa Owner CRM Class
 */
class Villa_Owner_CRM {
    
    private $json_file;
    private $data;
    
    public function __construct() {
        $this->json_file = get_stylesheet_directory() . '/villa-data/migration/final-ownership-analysis.json';
        $this->load_data();
        
        // WordPress hooks
        add_action('init', [$this, 'init']);
        add_shortcode('villa_owner_registration', [$this, 'registration_form_shortcode']);
        add_shortcode('villa_owner_portal', [$this, 'owner_portal_shortcode']);
        add_action('wp_ajax_villa_register_owner', [$this, 'handle_registration']);
        add_action('wp_ajax_nopriv_villa_register_owner', [$this, 'handle_registration']);
    }
    
    /**
     * Load owner data from JSON file
     */
    private function load_data() {
        if (!file_exists($this->json_file)) {
            $this->data = ['property_list' => []];
            return;
        }
        
        $json_content = file_get_contents($this->json_file);
        $this->data = json_decode($json_content, true);
        
        if (!$this->data || !isset($this->data['property_list'])) {
            $this->data = ['property_list' => []];
        }
    }
    
    /**
     * Save data back to JSON file
     */
    private function save_data() {
        $this->data['last_updated'] = current_time('c');
        return file_put_contents($this->json_file, json_encode($this->data, JSON_PRETTY_PRINT));
    }
    
    /**
     * Initialize the CRM system
     */
    public function init() {
        // Create custom user role for owners
        if (!get_role('villa_owner')) {
            add_role('villa_owner', 'Villa Owner', [
                'read' => true,
                'villa_owner_access' => true
            ]);
        }
    }
    
    /**
     * Find owner by unit number
     */
    public function find_owner_by_unit($unit_number) {
        foreach ($this->data['property_list'] as $index => $property) {
            if (strtolower($property['unit']) === strtolower($unit_number)) {
                $property['_index'] = $index; // Store index for updates
                return $property;
            }
        }
        return null;
    }
    
    /**
     * Update owner data
     */
    public function update_owner($unit_number, $updates) {
        foreach ($this->data['property_list'] as $index => $property) {
            if (strtolower($property['unit']) === strtolower($unit_number)) {
                $this->data['property_list'][$index] = array_merge($property, $updates);
                return $this->save_data();
            }
        }
        return false;
    }
    
    /**
     * Verify owner credentials
     */
    public function verify_owner($unit_number, $first_name, $last_name, $email) {
        $owner = $this->find_owner_by_unit($unit_number);
        
        if (!$owner) {
            return ['success' => false, 'message' => 'Unit not found in our records.'];
        }
        
        if ($owner['status'] === 'MISSING_DATA' || $owner['primary_owner'] === 'NO_DATA') {
            return ['success' => false, 'message' => 'Incomplete owner data. Please contact HOA office.'];
        }
        
        // Parse owner name from data
        $full_name = trim($owner['primary_owner']);
        $name_parts = explode(' ', $full_name);
        $owner_first = $name_parts[0] ?? '';
        $owner_last = count($name_parts) > 1 ? end($name_parts) : '';
        
        // Get owner email
        $owner_email = '';
        if (!empty($owner['all_emails']) && is_array($owner['all_emails'])) {
            $owner_email = $owner['all_emails'][0];
        } elseif (!empty($owner['primary_email'])) {
            $owner_email = $owner['primary_email'];
        }
        
        // Verify name
        if (strtolower($owner_first) !== strtolower($first_name) || 
            strtolower($owner_last) !== strtolower($last_name)) {
            return ['success' => false, 'message' => 'Name does not match our records.'];
        }
        
        // Verify email (optional - if provided in our records)
        if (!empty($owner_email) && strtolower($owner_email) !== strtolower($email)) {
            return ['success' => false, 'message' => 'Email does not match our records.'];
        }
        
        // Check if already registered
        if (isset($owner['wp_user_id']) && $owner['wp_user_id']) {
            return ['success' => false, 'message' => 'This unit is already registered.'];
        }
        
        return ['success' => true, 'owner' => $owner];
    }
    
    /**
     * Generate unique username from first and last name (WooCommerce style)
     */
    private function generate_username($first_name, $last_name) {
        // Clean and format names
        $first = sanitize_user(strtolower(trim($first_name)));
        $last = sanitize_user(strtolower(trim($last_name)));
        
        // Create base username
        $base_username = $first . '-' . $last;
        
        // Remove any invalid characters
        $base_username = preg_replace('/[^a-z0-9\-_]/', '', $base_username);
        
        // Check if username exists, if so add number
        $username = $base_username;
        $counter = 1;
        
        while (username_exists($username)) {
            $username = $base_username . '-' . $counter;
            $counter++;
        }
        
        return $username;
    }
    
    /**
     * Register new owner account
     */
    public function register_owner($unit_number, $first_name, $last_name, $email, $password, $billing_address, $owner_data) {
        // Auto-generate username
        $username = $this->generate_username($first_name, $last_name);
        
        // Create WordPress user
        $user_id = wp_create_user($username, $password, $email);
        
        if (is_wp_error($user_id)) {
            return ['success' => false, 'message' => $user_id->get_error_message()];
        }
        
        // Set user role
        $user = new WP_User($user_id);
        $user->set_role('villa_owner');
        
        // Update user profile with names
        wp_update_user([
            'ID' => $user_id,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'display_name' => $first_name . ' ' . $last_name
        ]);
        
        // Update user meta with villa-specific data
        update_user_meta($user_id, 'villa_unit_number', $unit_number);
        update_user_meta($user_id, 'villa_entity_type', $owner_data['entity_type']);
        update_user_meta($user_id, 'villa_company', $owner_data['company'] ?? '');
        update_user_meta($user_id, 'villa_secondary_owner', $owner_data['secondary_owner'] ?? '');
        
        // Store billing address
        update_user_meta($user_id, 'villa_billing_address', $billing_address);
        
        // Update JSON data with WordPress user ID and registration info
        $updates = [
            'wp_user_id' => $user_id,
            'wp_username' => $username,
            'registration_date' => current_time('c'),
            'account_status' => 'active',
            'billing_address' => $billing_address
        ];
        
        $this->update_owner($unit_number, $updates);
        
        // Send welcome email (no verification needed)
        $this->send_welcome_email($user_id, $owner_data);
        
        return ['success' => true, 'user_id' => $user_id, 'username' => $username];
    }
    
    /**
     * Send welcome email to new owner
     */
    private function send_welcome_email($user_id, $owner_data) {
        $user = get_user_by('id', $user_id);
        $unit_number = get_user_meta($user_id, 'villa_unit_number', true);
        
        $subject = 'Welcome to Villa Capriani Owner Portal';
        $message = $this->get_welcome_email_template($user->display_name, $unit_number, $user->user_login);
        
        wp_mail($user->user_email, $subject, $message, [
            'Content-Type: text/html; charset=UTF-8',
            'From: Villa Capriani HOA <noreply@villacariani.com>'
        ]);
    }
    
    /**
     * Get welcome email template
     */
    private function get_welcome_email_template($name, $unit, $username) {
        return "
        <html>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                <div style='background: #5a7b7c; color: white; padding: 20px; text-align: center;'>
                    <h1 style='margin: 0;'>Villa Capriani</h1>
                    <p style='margin: 10px 0 0 0;'>Owner Portal Access</p>
                </div>
                
                <div style='padding: 30px 20px;'>
                    <h2 style='color: #5a7b7c; margin-top: 0;'>Welcome, {$name}!</h2>
                    
                    <p>Your Villa Capriani owner portal account has been successfully created for <strong>Unit {$unit}</strong>.</p>
                    
                    <div style='background: #f8f9fa; padding: 20px; margin: 20px 0; border-left: 4px solid #5a7b7c;'>
                        <h3 style='margin-top: 0; color: #5a7b7c;'>Your Login Information:</h3>
                        <p><strong>Username:</strong> {$username}</p>
                        <p><strong>Email:</strong> Your registered email address</p>
                        <p><em>Your username was automatically generated from your name for consistency.</em></p>
                    </div>
                    
                    <p>Through the owner portal, you can access:</p>
                    <ul style='padding-left: 20px;'>
                        <li>Committee information and updates</li>
                        <li>Important HOA documents and forms</li>
                        <li>Community announcements</li>
                        <li>Document library</li>
                        <li>Contact information for board members</li>
                        <li>Community updates and news</li>
                    </ul>
                    
                    <div style='background: #f8f9fa; padding: 20px; margin: 20px 0; border-left: 4px solid #5a7b7c;'>
                        <h3 style='margin-top: 0; color: #5a7b7c;'>Next Steps:</h3>
                        <p>Log in to your account to explore the owner portal and stay connected with your Villa Capriani community.</p>
                    </div>
                    
                    <p>If you have any questions, please don't hesitate to contact the HOA office.</p>
                    
                    <p>Best regards,<br>
                    <strong>Villa Capriani HOA</strong></p>
                </div>
                
                <div style='background: #f8f9fa; padding: 15px; text-align: center; font-size: 12px; color: #666;'>
                    <p>This is an automated message from Villa Capriani HOA.</p>
                </div>
            </div>
        </body>
        </html>";
    }
    
    /**
     * Registration form shortcode
     */
    public function registration_form_shortcode($atts) {
        if (is_user_logged_in()) {
            return '<p>You are already logged in. <a href="' . wp_logout_url() . '">Logout</a></p>';
        }
        
        ob_start();
        ?>
        <div id="villa-owner-registration">
            <h2>Villa Capriani Owner Registration</h2>
            <p>Please verify your information to create your owner portal account. Your username will be automatically generated from your name.</p>
            
            <form id="villa-registration-form" method="post">
                <?php wp_nonce_field('villa_owner_registration', 'villa_nonce'); ?>
                
                <div class="form-group">
                    <label for="unit_number">Unit Number *</label>
                    <input type="text" id="unit_number" name="unit_number" required placeholder="e.g., 101, 2A, etc.">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">First Name *</label>
                        <input type="text" id="first_name" name="first_name" required>
                    </div>
                    <div class="form-group">
                        <label for="last_name">Last Name *</label>
                        <input type="text" id="last_name" name="last_name" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Choose Password *</label>
                        <input type="password" id="password" name="password" required>
                        <small>Password must be at least 8 characters long</small>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password *</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                </div>
                
                <fieldset class="billing-fieldset">
                    <legend>Billing Address</legend>
                    
                    <div class="form-group">
                        <label for="billing_street">Street Address *</label>
                        <input type="text" id="billing_street" name="billing_street" required placeholder="123 Main Street">
                    </div>
                    
                    <div class="form-row three-col">
                        <div class="form-group">
                            <label for="billing_city">City *</label>
                            <input type="text" id="billing_city" name="billing_city" required>
                        </div>
                        <div class="form-group">
                            <label for="billing_state">State *</label>
                            <input type="text" id="billing_state" name="billing_state" required maxlength="2" placeholder="FL">
                        </div>
                        <div class="form-group">
                            <label for="billing_zip">ZIP Code *</label>
                            <input type="text" id="billing_zip" name="billing_zip" required pattern="[0-9]{5}(-[0-9]{4})?" placeholder="12345">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="billing_country">Country *</label>
                        <select id="billing_country" name="billing_country" required>
                            <option value="US" selected>United States</option>
                            <option value="CA">Canada</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </fieldset>
                
                <button type="submit" class="villa-btn villa-btn-primary">Register Account</button>
            </form>
            
            <div id="villa-registration-message"></div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            $('#villa-registration-form').on('submit', function(e) {
                e.preventDefault();
                
                var formData = $(this).serialize();
                formData += '&action=villa_register_owner';
                
                $('#villa-registration-message').html('<p class="villa-loading">Processing registration...</p>');
                
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            $('#villa-registration-message').html('<p class="villa-success">' + response.data.message + '</p>');
                            if (response.data.redirect) {
                                setTimeout(function() {
                                    window.location.href = response.data.redirect;
                                }, 2000);
                            }
                        } else {
                            $('#villa-registration-message').html('<p class="villa-error">' + response.data.message + '</p>');
                        }
                    },
                    error: function() {
                        $('#villa-registration-message').html('<p class="villa-error">Registration failed. Please try again.</p>');
                    }
                });
            });
        });
        </script>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Handle AJAX registration
     */
    public function handle_registration() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['villa_nonce'], 'villa_owner_registration')) {
            wp_die('Security check failed');
        }
        
        // Sanitize input
        $unit_number = sanitize_text_field($_POST['unit_number']);
        $first_name = sanitize_text_field($_POST['first_name']);
        $last_name = sanitize_text_field($_POST['last_name']);
        $email = sanitize_email($_POST['email']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        $billing_street = sanitize_text_field($_POST['billing_street']);
        $billing_city = sanitize_text_field($_POST['billing_city']);
        $billing_state = sanitize_text_field($_POST['billing_state']);
        $billing_zip = sanitize_text_field($_POST['billing_zip']);
        $billing_country = sanitize_text_field($_POST['billing_country']);
        
        $billing_address = [
            'street' => $billing_street,
            'city' => $billing_city,
            'state' => $billing_state,
            'zip' => $billing_zip,
            'country' => $billing_country
        ];
        
        // Validate passwords
        if ($password !== $confirm_password) {
            wp_send_json_error(['message' => 'Passwords do not match.']);
        }
        
        if (strlen($password) < 8) {
            wp_send_json_error(['message' => 'Password must be at least 8 characters long.']);
        }
        
        // Verify owner
        $verification = $this->verify_owner($unit_number, $first_name, $last_name, $email);
        if (!$verification['success']) {
            wp_send_json_error(['message' => $verification['message']]);
        }
        
        // Register owner
        $registration = $this->register_owner($unit_number, $first_name, $last_name, $email, $password, $billing_address, $verification['owner']);
        if (!$registration['success']) {
            wp_send_json_error(['message' => $registration['message']]);
        }
        
        // Auto-login
        wp_set_current_user($registration['user_id']);
        wp_set_auth_cookie($registration['user_id']);
        
        wp_send_json_success([
            'message' => 'Registration successful! Welcome to Villa Capriani.',
            'redirect' => home_url('/owner-portal/')
        ]);
    }
    
    /**
     * Owner portal shortcode
     */
    public function owner_portal_shortcode($atts) {
        if (!is_user_logged_in()) {
            return '<p>Please <a href="' . wp_login_url() . '">login</a> to access the owner portal.</p>';
        }
        
        $user = wp_get_current_user();
        if (!in_array('villa_owner', $user->roles)) {
            return '<p>Access denied. Owner account required.</p>';
        }
        
        $unit_number = get_user_meta($user->ID, 'villa_unit_number', true);
        $owner_data = $this->find_owner_by_unit($unit_number);
        
        ob_start();
        ?>
        <div id="villa-owner-portal">
            <div class="portal-header">
                <h2>Welcome, <?php echo esc_html($user->display_name); ?></h2>
                <p class="unit-info">Unit <?php echo esc_html($unit_number); ?></p>
            </div>
            
            <div class="portal-content">
                <div class="portal-section">
                    <h3>Your Information</h3>
                    <div class="owner-info">
                        <p><strong>Primary Owner:</strong> <?php echo esc_html($owner_data['primary_owner'] ?? 'N/A'); ?></p>
                        <?php if (!empty($owner_data['secondary_owner'])): ?>
                        <p><strong>Secondary Owner:</strong> <?php echo esc_html($owner_data['secondary_owner']); ?></p>
                        <?php endif; ?>
                        <p><strong>Entity Type:</strong> <?php echo esc_html($owner_data['entity_type'] ?? 'N/A'); ?></p>
                        <?php if (!empty($owner_data['company'])): ?>
                        <p><strong>Company:</strong> <?php echo esc_html($owner_data['company']); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="portal-section">
                    <h3>Billing Address</h3>
                    <div class="billing-address">
                        <?php
                        $billing_address = get_user_meta($user->ID, 'villa_billing_address', true);
                        ?>
                        <p><?php echo esc_html($billing_address['street']); ?></p>
                        <p><?php echo esc_html($billing_address['city']); ?>, <?php echo esc_html($billing_address['state']); ?> <?php echo esc_html($billing_address['zip']); ?></p>
                        <p><?php echo esc_html($billing_address['country']); ?></p>
                    </div>
                </div>
                
                <div class="portal-section">
                    <h3>Quick Links</h3>
                    <div class="quick-links">
                        <a href="/committees/" class="portal-link">View Committees</a>
                        <a href="/documents/" class="portal-link">Document Library</a>
                        <a href="/contact/" class="portal-link">Contact HOA</a>
                        <a href="<?php echo wp_logout_url(); ?>" class="portal-link">Logout</a>
                    </div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Get owner statistics for admin
     */
    public function get_statistics() {
        $stats = [
            'total_units' => count($this->data['property_list']),
            'units_with_data' => 0,
            'registered_owners' => 0,
            'units_with_emails' => 0,
            'entities' => 0,
            'individuals' => 0
        ];
        
        foreach ($this->data['property_list'] as $property) {
            if ($property['status'] !== 'MISSING_DATA') {
                $stats['units_with_data']++;
            }
            
            if (isset($property['wp_user_id']) && $property['wp_user_id']) {
                $stats['registered_owners']++;
            }
            
            if (!empty($property['all_emails']) || !empty($property['primary_email'])) {
                $stats['units_with_emails']++;
            }
            
            if ($property['entity_type'] === 'Entity') {
                $stats['entities']++;
            } else {
                $stats['individuals']++;
            }
        }
        
        return $stats;
    }
}

// Initialize the CRM system
new Villa_Owner_CRM();
