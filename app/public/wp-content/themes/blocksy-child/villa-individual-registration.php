<?php
/**
 * Villa Individual Owner Registration
 * Allows each owner to create their own account
 */

// Include the individual owners class
require_once get_stylesheet_directory() . '/villa-individual-owners.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['villa_register_individual'])) {
    // Verify nonce
    if (!wp_verify_nonce($_POST['villa_register_nonce'], 'villa_register_individual')) {
        wp_die('Security check failed');
    }
    
    global $villa_individual_owners;
    
    // Get form data
    $unit_number = sanitize_text_field($_POST['unit_number']);
    $first_name = sanitize_text_field($_POST['first_name']);
    $last_name = sanitize_text_field($_POST['last_name']);
    $email = sanitize_email($_POST['email']);
    $password = $_POST['password'];
    $billing_address = sanitize_textarea_field($_POST['billing_address']);
    
    // Find owner
    $result = $villa_individual_owners->find_owner($first_name, $last_name, $unit_number);
    
    if ($result['success']) {
        // Register the owner
        $registration = $villa_individual_owners->register_owner(
            $result['owner_key'],
            $email,
            $password,
            $billing_address
        );
        
        if ($registration['success']) {
            // Auto-login
            wp_set_current_user($registration['user_id']);
            wp_set_auth_cookie($registration['user_id']);
            
            // Redirect to success page or dashboard
            wp_redirect(home_url('/owner-dashboard'));
            exit;
        } else {
            $error_message = $registration['message'];
        }
    } else {
        $error_message = $result['message'];
    }
}

// Shortcode for registration form
function villa_individual_registration_form() {
    global $villa_individual_owners;
    
    // If already logged in, redirect
    if (is_user_logged_in()) {
        return '<p>You are already logged in. <a href="' . home_url('/owner-dashboard') . '">Go to Dashboard</a></p>';
    }
    
    ob_start();
    ?>
    <div class="villa-individual-registration">
        <div class="registration-header">
            <h2>Villa Capriani Owner Registration</h2>
            <p>Create your individual owner account. Each owner needs their own account, even if you share ownership of a property.</p>
        </div>
        
        <?php if (isset($error_message)): ?>
            <div class="villa-notice villa-error">
                <?php echo esc_html($error_message); ?>
            </div>
        <?php endif; ?>
        
        <form method="post" class="villa-registration-form">
            <?php wp_nonce_field('villa_register_individual', 'villa_register_nonce'); ?>
            
            <div class="form-section">
                <h3>Property Information</h3>
                <div class="form-group">
                    <label for="unit_number">Unit Number *</label>
                    <input type="text" id="unit_number" name="unit_number" required 
                           placeholder="e.g., 101A, 205B" 
                           value="<?php echo isset($_POST['unit_number']) ? esc_attr($_POST['unit_number']) : ''; ?>">
                    <small>Enter the unit number you own or co-own</small>
                </div>
            </div>
            
            <div class="form-section">
                <h3>Owner Information</h3>
                <div class="form-row">
                    <div class="form-group half">
                        <label for="first_name">First Name *</label>
                        <input type="text" id="first_name" name="first_name" required 
                               value="<?php echo isset($_POST['first_name']) ? esc_attr($_POST['first_name']) : ''; ?>">
                    </div>
                    <div class="form-group half">
                        <label for="last_name">Last Name *</label>
                        <input type="text" id="last_name" name="last_name" required 
                               value="<?php echo isset($_POST['last_name']) ? esc_attr($_POST['last_name']) : ''; ?>">
                    </div>
                </div>
                <small>Your name must match our HOA records exactly</small>
            </div>
            
            <div class="form-section">
                <h3>Account Information</h3>
                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input type="email" id="email" name="email" required 
                           value="<?php echo isset($_POST['email']) ? esc_attr($_POST['email']) : ''; ?>">
                    <small>This will be your login email</small>
                </div>
                
                <div class="form-group">
                    <label for="password">Password *</label>
                    <input type="password" id="password" name="password" required 
                           minlength="8" 
                           pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}">
                    <small>At least 8 characters with uppercase, lowercase, and number</small>
                </div>
            </div>
            
            <div class="form-section">
                <h3>Billing Information</h3>
                <div class="form-group">
                    <label for="billing_address">Billing Address *</label>
                    <textarea id="billing_address" name="billing_address" rows="3" required
                              placeholder="Street Address&#10;City, State ZIP"><?php echo isset($_POST['billing_address']) ? esc_textarea($_POST['billing_address']) : ''; ?></textarea>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" name="villa_register_individual" class="villa-button primary">
                    Create My Account
                </button>
            </div>
        </form>
        
        <div class="registration-footer">
            <p>Already have an account? <a href="<?php echo wp_login_url(); ?>">Log In</a></p>
            <p class="help-text">
                <strong>Important:</strong> Each owner must create their own account. 
                If you co-own a property with someone else, they will need to register separately.
            </p>
        </div>
    </div>
    
    <style>
    .villa-individual-registration {
        max-width: 600px;
        margin: 0 auto;
        padding: 20px;
    }
    
    .registration-header {
        text-align: center;
        margin-bottom: 30px;
    }
    
    .registration-header h2 {
        color: var(--wp--preset--color--primary);
        margin-bottom: 10px;
    }
    
    .villa-registration-form {
        background: #fff;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .form-section {
        margin-bottom: 30px;
    }
    
    .form-section h3 {
        color: var(--wp--preset--color--primary);
        font-size: 1.2em;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 2px solid #eee;
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-group label {
        display: block;
        font-weight: 600;
        margin-bottom: 5px;
        color: #333;
    }
    
    .form-group input,
    .form-group textarea {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 16px;
    }
    
    .form-group input:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: var(--wp--preset--color--primary);
    }
    
    .form-group small {
        display: block;
        margin-top: 5px;
        color: #666;
        font-size: 0.9em;
    }
    
    .form-row {
        display: flex;
        gap: 20px;
        margin-bottom: 10px;
    }
    
    .form-group.half {
        flex: 1;
        margin-bottom: 0;
    }
    
    .villa-notice {
        padding: 15px;
        border-radius: 4px;
        margin-bottom: 20px;
    }
    
    .villa-error {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    
    .villa-button {
        padding: 12px 30px;
        border: none;
        border-radius: 4px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .villa-button.primary {
        background: var(--wp--preset--color--primary);
        color: white;
        width: 100%;
    }
    
    .villa-button.primary:hover {
        background: var(--wp--preset--color--primary-dark);
    }
    
    .registration-footer {
        text-align: center;
        margin-top: 30px;
    }
    
    .registration-footer p {
        margin: 10px 0;
    }
    
    .help-text {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 4px;
        font-size: 0.9em;
        color: #666;
    }
    
    @media (max-width: 600px) {
        .form-row {
            flex-direction: column;
        }
        
        .villa-registration-form {
            padding: 20px;
        }
    }
    </style>
    <?php
    return ob_get_clean();
}

// Register shortcode
add_shortcode('villa_individual_registration', 'villa_individual_registration_form');
