<?php

function PREFIX_remove_scripts() {
    wp_dequeue_style( 'twenty-twenty-one-style' );
    wp_deregister_style( 'twenty-twenty-one-style' );
    wp_dequeue_style( 'twenty-twenty-one-print-style' );
   wp_deregister_style( 'twenty-twenty-one-print-style' );
 
    // Now register your styles and scripts here
 }
 add_action( 'wp_enqueue_scripts', 'PREFIX_remove_scripts', 20 );

 function styleAndScripts() {
    wp_enqueue_script('jquery-google-cdn', 'https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js', false, null, true);
    wp_enqueue_style( 'slick', 'https://cdn.jsdelivr.net/npm/slick-carousel/slick/slick.css' );
    wp_enqueue_style( 'slick-theme', 'https://cdn.jsdelivr.net/npm/slick-carousel/slick/slick-theme.css' );
    wp_enqueue_script( 'slickjs', 'https://cdn.jsdelivr.net/npm/slick-carousel/slick/slick.min.js', array('jquery'), null, true ); 
    wp_enqueue_script( 'mainjs', get_stylesheet_directory_uri() . '/js/custom-ajax.js', array('jquery'), time(), true );
}
add_action( 'wp_enqueue_scripts', 'styleAndScripts' );


function custom_mime_types($mimes) {
    // Add new MIME types
    $mimes['svg'] = 'image/svg+xml';
    $mimes['woff'] = 'font/woff';
    $mimes['woff2'] = 'font/woff2';
    
    return $mimes;
}
add_filter('upload_mimes', 'custom_mime_types');




function hide_admin_bar_for_subscribers() {
    if (current_user_can('subscriber')) {
        add_filter('show_admin_bar', '__return_false');
    }
}

add_action('init', 'hide_admin_bar_for_subscribers');


// login 



function custom_login_form() {
    if (is_user_logged_in()) {
        $logout_url = wp_logout_url(home_url()); // Redirect to home page after logout
        return 'You are already logged in. <a href="' . esc_url($logout_url) . '">Logout</a>';
    }



    // Display the login form inside a div
    $form = '<div class="custom-login-form">';
    if ( isset( $_GET['action'] ) && $_GET['action'] == 'lostpassword' ) {
        // Show the password reset form
        $form .= '<form action="' . esc_url( $_SERVER['REQUEST_URI'] ) . '" method="post">';
        $form .= '<label for="user_login">Email Address</label>';
        $form .= '<input type="text" name="user_login" id="user_login" required>';
        $form .= '<input type="submit" value="Get Password Reset Link">';
        $form .= '</form>';

        // Handle password reset request
        if ( isset( $_POST['user_login'] ) ) {
            $user_data = get_user_by( 'email', sanitize_email( $_POST['user_login'] ) );

            if ( $user_data ) {
                $reset_key = get_password_reset_key( $user_data );
                $reset_url = home_url( '/login-form/?action=rp&key=' . $reset_key . '&login=' . rawurlencode( $user_data->user_login ) );
                $form .= '<p>If the email exists in our system</p>';
                // $form .= '<p>If the email exists in our system, you will receive a password reset link. <a href="' . esc_url( $reset_url ) . '">Click here to reset your password</a>.</p>';
            } else {
                $form .= '<p class="error-msg">This email address is not registered.</p>';
            }
        }

    }
    elseif ( isset( $_GET['action'] ) && $_GET['action'] == 'rp' && isset( $_GET['key'] ) && isset( $_GET['login'] ) ) {
        // Handle the password reset form submission when the user has the reset link
        $key = sanitize_text_field( $_GET['key'] );
        $login = sanitize_text_field( $_GET['login'] );

        // Check if the reset key is valid
        $user = check_password_reset_key( $key, $login );

        if ( ! is_wp_error( $user ) ) {
            // Show the password reset form
            $form .= '<form action="' . esc_url( $_SERVER['REQUEST_URI'] ) . '" method="post">';
            $form .= '<label for="new_password">New Password</label>';
            $form .= '<input type="password" name="new_password" id="new_password" required>';

            // Add Show/Hide password functionality
            $form .= '<span id="toggle-password" class="dashicons dashicons-visibility" style="cursor: pointer;"></span>';
            $form .= '<input type="submit" value="Reset Password">';
            $form .= '</form>';

            // Handle password reset
            if ( isset( $_POST['new_password'] ) ) {
                $new_password = sanitize_text_field( $_POST['new_password'] );
                reset_password( $user, $new_password );
                $form .= '<p>Your password has been reset successfully. You can <a href="' . esc_url( home_url( '/login-form/' ) ) . '">log in</a> now.</p>';
            }
        } else {
            $form .= '<p class="error-msg">Invalid reset key.</p>';
        }
    }
    
    else {
    $form .= '<form method="post" action="' . esc_url($_SERVER['REQUEST_URI']) . '">';
    $form .= '<label for="username">Username</label>';
    $form .= '<input type="text" name="username" id="username" value="" required />';
    $form .= '<label for="password">Password</label>';
    $form .= '<input type="password" name="password" id="password" value="" required />';
     // Add Show/Hide password toggle for login form
     $form .= '<span id="toggle-password-login" class="dashicons dashicons-visibility" style="cursor: pointer;"></span>';

     $form .= '<p class="forgot-password"><a href="' . esc_url( home_url( '/login-form/?action=lostpassword' ) ) . '">Forgot Password?</a></p>';
    $form .= '<input type="submit" name="submit_login" value="Login" />';
    $form .= '</form>';

    }


    $form .= '</div>';

    return $form;
}

add_shortcode('custom_login_form', 'custom_login_form');

function handle_custom_login() {
    // Check if the login form is submitted
    if (isset($_POST['submit_login'])) {
        $username = sanitize_text_field($_POST['username']);
        $password = sanitize_text_field($_POST['password']);

        // Login the user
        $creds = array(
            'user_login'    => $username,
            'user_password' => $password,
            'remember'      => true,
        );

        $user = wp_signon($creds, false);

        // Check for errors during login
        if (is_wp_error($user)) {
            echo '<p class="login-error">Error: ' . $user->get_error_message() . '</p>';
       
        } else {
            if (in_array('administrator', $user->roles) || in_array('author', $user->roles)) {
                wp_redirect(admin_url()); // Redirect to the WordPress dashboard
            } else {
                wp_redirect(home_url()); // Redirect to the homepage
            }
            exit;
        }
    }
}

add_action('template_redirect', 'handle_custom_login');




function custom_login_form_scripts() {
    ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Toggle visibility for password fields
            var togglePassword = document.getElementById('toggle-password');
            var togglePasswordLogin = document.getElementById('toggle-password-login');
            var passwordField = document.getElementById('new_password');
            var loginPasswordField = document.getElementById('password');

            // Toggle password visibility for password reset
            if (togglePassword) {
                togglePassword.addEventListener('click', function () {
                    if (passwordField.type === 'password') {
                        passwordField.type = 'text';
                        togglePassword.classList.remove('dashicons-visibility');
                        togglePassword.classList.add('dashicons-hidden');
                    } else {
                        passwordField.type = 'password';
                        togglePassword.classList.remove('dashicons-hidden');
                        togglePassword.classList.add('dashicons-visibility');
                    }
                });
            }

            // Toggle password visibility for login form
            if (togglePasswordLogin) {
                togglePasswordLogin.addEventListener('click', function () {
                    if (loginPasswordField.type === 'password') {
                        loginPasswordField.type = 'text';
                        togglePasswordLogin.classList.remove('dashicons-visibility');
                        togglePasswordLogin.classList.add('dashicons-hidden');
                    } else {
                        loginPasswordField.type = 'password';
                        togglePasswordLogin.classList.remove('dashicons-hidden');
                        togglePasswordLogin.classList.add('dashicons-visibility');
                    }
                });
            }
        });
    </script>
    <?php
}
add_action('wp_footer', 'custom_login_form_scripts');

// Add some basic styles for the login form
function custom_login_form_styles() {
    echo '<style>
        .custom-login-form {
            max-width: 400px;
            margin: 0 auto;
            padding: 20px;
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        .custom-login-form label {
            display: block;
            font-size: 14px;
            margin-bottom: 5px;
        }
        .custom-login-form input[type="text"], .custom-login-form input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            font-size: 14px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .custom-login-form input[type="submit"] {
            width: 100%;
            padding: 10px;
            background-color: #0073aa;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
        }
        .custom-login-form input[type="submit"]:hover {
            background-color: #005b8d;
        }
        .custom-login-form .forgot-password {
            text-align: right;
            margin-bottom: 10px;
        }
        .custom-login-form .forgot-password a {
            font-size: 12px;
            color: #0073aa;
            text-decoration: none;
        }
        .custom-login-form .forgot-password a:hover {
            text-decoration: underline;
        }
        .custom-login-form .error-msg {
            color: red;
            font-size: 14px;
            margin-top: 10px;
        }
        .dashicons-visibility:before {
            content: "\f177";
        }
        .dashicons-hidden:before {
            content: "\f530";
        }

        .my-account-page {
            padding: 20px;
            text-align: center;
        }

        .my-account-page .user-image {
            border-radius: 50%;
            width: 100px;
            height: 100px;
            object-fit: cover;
            margin: 10px 0;
        }

        .my-account-page .logout-btn {
            background-color: #f00;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }

        .my-account-page .logout-btn:hover {
            background-color: #d00;
        }
            .my-account-page .user-initial {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background-color: #ddd;
            color: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 40px;
            font-weight: bold;
            margin: 10px auto;
            
        }
   
     


    </style>';
}
add_action('wp_head', 'custom_login_form_styles');


// Register the custom registration form
function custom_registration_form() {
    ?>
    <div class="custom-login-form">
    <form action="" method="post">
        <input type="text" name="name" placeholder="Full Name" required />
        <input type="email" name="email" placeholder="Email Address" required />
        <input type="text" name="phone" placeholder="Phone Number" required />
        <select name="role">
            <option value="subscriber">Subscriber</option>
            <option value="author">Author</option>
        </select>
        <input type="password" name="password" placeholder="Password" required />
        <input type="submit" name="submit_registration" value="Register" />
    </form>
</div>
    <?php
}
add_shortcode('custom_registration', 'custom_registration_form');

// Handle registration form submission
function handle_user_registration() {
    if (isset($_POST['submit_registration'])) {
        // Sanitize form input data
        $name = sanitize_text_field($_POST['name']);
        $email = sanitize_email($_POST['email']);
        $phone = sanitize_text_field($_POST['phone']);
        $role = $_POST['role']; // Subscriber or Author
        $password = sanitize_text_field($_POST['password']);

        // If author, temporarily set role as 'pending_author'
        if ($role == 'author') {
            $role = 'pending_author'; // Temporary role before approval
        }

        // Create the user
        $userdata = array(
            'user_login' => $email,
            'user_pass'  => $password,
            'user_email' => $email,
            'first_name' => $name,
            'role'       => $role, // Use 'pending_author' for authors
        );

        $user_id = wp_insert_user($userdata);

        // Check for successful user creation
        if (!is_wp_error($user_id)) {
            // Email headers for HTML emails
            $headers = array('Content-Type: text/html; charset=UTF-8');

            // Send email based on user role
            if ($role == 'subscriber') {
                wp_mail($email, 'Account Verification', 'Please verify your email to complete the registration.', $headers);
            } elseif ($role == 'pending_author') {
                $admin_email = get_option('admin_email');

                // Approval link for admin
                $approve_link = add_query_arg(
                    array(
                        'action' => 'approve_author',
                        'user_id' => $user_id,
                    ),
                    admin_url('admin-post.php')
                );

                // Email content for admin approval
                $message = "<h3>New Author Registration Pending Approval</h3>";
                $message .= "<p><strong>Name:</strong> " . $name . "</p>";
                $message .= "<p><strong>Email:</strong> " . $email . "</p>";
                $message .= "<p><strong>Phone:</strong> " . $phone . "</p>";
                $message .= "<p><strong>Role:</strong> Pending Author</p>";
                $message .= "<p><a href='" . $approve_link . "'>Click here to approve this Author</a></p>";

                // Send email to admin
                wp_mail($admin_email, 'New Author Registration Pending Approval', $message, $headers);

                // Email to author about pending approval
                wp_mail($email, 'Account Pending', 'Your account is pending approval. Once approved, you will receive a confirmation email.', $headers);
            }
        }
    }
}
add_action('init', 'handle_user_registration');



function approve_author() {
    // Check if the current user is an admin
    if (!current_user_can('administrator')) {
        wp_die('You are not allowed to access this page');
    }

    if (isset($_GET['action']) && $_GET['action'] == 'approve_author' && isset($_GET['user_id'])) {
        $user_id = intval($_GET['user_id']);
        $user = get_user_by('id', $user_id);
   
        // If user exists and role is 'pending_author', approve the account
        if ($user && in_array('pending_author', $user->roles)) {
            
            // Update the user role to 'author' upon admin approval
            wp_update_user(array(
                'ID' => $user_id,
                'role' => 'author', // Change role to 'author'
            ));

            // Send email to author confirming approval
            wp_mail($user->user_email, 'Account Verified', 'Your author account has been approved. You can now log in.', 'Content-Type: text/html; charset=UTF-8');

            // Redirect to the users page in admin dashboard
            wp_redirect(admin_url('users.php'));
            exit;
        }
    }
}

// Hook to approve author
add_action('admin_post_approve_author', 'approve_author');
add_action('admin_post_nopriv_approve_author', 'approve_author'); // Allow non-privileged users to access




function register_pending_author_role() {
    // Register the 'pending_author' role if it doesn't already exist
    if (!get_role('pending_author')) {
        add_role(
            'pending_author', // Role slug
            'Pending Author', // Role name
            array(
                'read' => true, // Allow read permission
                'edit_posts' => false, // Do not allow editing posts
                'publish_posts' => false, // Do not allow publishing posts
            )
        );
    }
}

add_action('init', 'register_pending_author_role');





function custom_my_account_shortcode() {
    // Check if the user is logged in
    if ( is_user_logged_in() ) {
        $current_user = wp_get_current_user();
        $user_image = get_avatar_url($current_user->ID); // Get user image (if any)
        $user_name = explode(" ", $current_user->display_name)[0]; // Get the first word of the name

        ob_start(); // Start output buffering

        ?>
        <div class="my-account-page">
            <h2>Welcome, <?php echo $user_name; ?>!</h2>
            
            <div class="user-initial">
                <span><?php echo strtoupper($user_name[0]); // Show first letter of name ?></span>
            </div>
            
            <div class="user-details">
                <p><strong>Username:</strong> <?php echo $current_user->user_login; ?></p>
                <p><strong>Email:</strong> <?php echo $current_user->user_email; ?></p>
            </div>
            
            <!-- Logout Button -->
             <?php
            $logout_url = wp_logout_url(home_url()); // Redirect to home page after logout
            return '<a href="' . esc_url($logout_url) . '" class="logout-btn">Logout</a>';
            ?>
        </div>
        <?php
        
        return ob_get_clean(); // Return the content from output buffering
    } else {
        $custom_login_url = site_url('/login-form'); // Replace with your custom login page slug
        wp_redirect($custom_login_url);
        exit; // Ensure no further code is executed
    }
}
add_shortcode('custom_my_account', 'custom_my_account_shortcode');



// Redirect based on user login status
function custom_redirect_based_on_login_status() {
    // Skip if in admin area
    if (is_admin()) {
        return;
    }

    // Get current user
    $current_user = wp_get_current_user();
    
    // Skip redirection for admin users
    if (in_array('administrator', $current_user->roles)) {
        return;
    }

    $login_page = home_url('/login-form/');
    $form_page = home_url('/form/');
    $my_account_page = home_url('/my-account/');

    // Check if user is logged in
    if (is_user_logged_in()) {
        // Redirect to My Account if the user is logged in and tries to access the login or form page
        if (is_page('login-form') || is_page('form')) {
            wp_redirect($my_account_page);
            exit;
        }
    } else {
        // Redirect to Login page if the user is not logged in and tries to access My Account page
        if (is_page('my-account')) {
            wp_redirect($login_page);
            exit;
        }
    }
}
add_action('template_redirect', 'custom_redirect_based_on_login_status');






















































// Add admin menu for Authors (Visible only to Admins)
function register_author_menu() {
    if (current_user_can('manage_options')) { // Check if the user is an Admin
        add_menu_page(
            'Authors Management',        // Page title
            'Authors',                   // Menu title
            'manage_options',            // Capability
            'author_dashboard',          // Menu slug
            'author_dashboard_page',     // Callback function
            'dashicons-admin-users',     // Icon
            6                            // Position
        );
    }
}
add_action('admin_menu', 'register_author_menu');

// Add Job Dashboard menu for Authors (Visible only to Authors)
function add_job_menu_for_author() {
    if (current_user_can('author')) { // Check if the user is an Author
        add_menu_page(
            'Job Dashboard',             // Page title
            'Job',                       // Menu title
            'read',                      // Capability (Authors have this capability)
            'job_page',                  // Menu slug
            'job_dashboard_page',        // Callback function
            'dashicons-businessperson',  // Icon
            6                            // Position
        );
    }
}
add_action('admin_menu', 'add_job_menu_for_author');

// Callback function for Authors Management page (Admin Dashboard)
function author_dashboard_page() {
    // Fetch all authors
    $args = [
        'role' => 'author',
    ];
    $authors = get_users($args); // Get all users with 'author' role

    ?>
    <div class="wrap">
        <h1>Authors Management</h1>
        <table class="widefat fixed">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Action</th>
                    <th>Write Gmail</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($authors as $author): ?>
                    <tr>
                        <td><?php echo esc_html($author->display_name); ?></td>
                        <td><?php echo esc_html($author->user_email); ?></td>
                        <td>
                            <input 
                                type="checkbox" 
                                class="author-status" 
                                data-author-id="<?php echo esc_attr($author->ID); ?>" 
                                <?php echo get_user_meta($author->ID, 'author_status', true) == '1' ? 'checked' : ''; ?>
                                disabled
                            >
                        </td>
                        <td>
                            <button 
                                class="send-email" 
                                data-author-id="<?php echo esc_attr($author->ID); ?>" 
                                data-author-email="<?php echo esc_attr($author->user_email); ?>"
                                <?php echo get_user_meta($author->ID, 'author_status', true) == '1' ? '' : 'disabled'; ?>
                            >
                               Auto Send Email
                            </button>
                        </td>
                        <td>
                        <button class="open-email-popup" data-author-id="<?php echo esc_attr($author->ID); ?>" data-author-email="<?php echo esc_attr($author->user_email); ?>"
                        <?php echo get_user_meta($author->ID, 'author_status', true) == '1' ? '' : 'disabled'; ?>
                        >Send Gmail</button>
                       </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
       

        <div id="email-popup" style="display: none; position: fixed; top: 20%; left: 30%; width: 40%; background: #fff; padding: 20px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); z-index: 1000;">
    <button class="close-email-popup" style="float: right;">&times;</button>
    <h2>Send Custom Email</h2>
    <input type="hidden" id="email-author-id" value="">
    <input type="hidden" id="email-author-email" value="">
    <div>
        <label for="email-subject">Subject:</label>
        <input type="text" id="email-subject" style="width: 100%; padding: 8px; margin-bottom: 10px;">
    </div>
    <div>
        <label for="email-body">Body:</label>
        <!-- <textarea id="email-body" rows="8" style="width: 100%; padding: 8px;"></textarea> -->
        <div id="email-body" style="height: 200px; background: #fff;"></div>
    </div>
    <button id="send-email" style="margin-top: 10px; background: #0073aa; color: #fff; padding: 10px 20px; border: none; cursor: pointer;">
        Send Email
    </button>
</div>

 </div>
    <?php
}

// Callback function for Job Dashboard (Author Dashboard)
function job_dashboard_page() {
    $user_id = get_current_user_id();
    $checked = get_user_meta($user_id, 'author_status', true) == '1' ? 'checked' : '';
    ?>
    <div class="wrap">
        <h1>Job Dashboard</h1>
        <label>
            <input type="checkbox" id="job-availability" data-author-id="<?php echo $user_id; ?>" <?php echo $checked; ?>>
            Available for Jobs
        </label>
    </div>
    <?php
}


// Send Email via Gmail when Admin clicks the button and update status to false (unchecked)
function send_admin_email_to_author() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized access.');
    }

    $author_id = intval($_POST['author_id']);
    $author_email = sanitize_email($_POST['author_email']);
    $subject = 'Important Message from Admin';
    $message = 'This is an important message from the Admin to inform you about your author status.';

    // Send email to Author
    $headers = array('Content-Type: text/html; charset=UTF-8');
    $mail_sent = wp_mail($author_email, $subject, $message, $headers);

    // If email is sent successfully
    if ($mail_sent) {
        // Automatically update author status to 'false' (unchecked)
        update_user_meta($author_id, 'author_status', '0');
        
        wp_send_json_success('Email sent successfully and author status updated to false.');
    } else {
        wp_send_json_error('Failed to send email.');
    }
}
add_action('wp_ajax_send_admin_email_to_author', 'send_admin_email_to_author');

// Update Author Status via AJAX (for Authors)
function update_author_status() {
    // Ensure the user is logged in
    if (!is_user_logged_in()) {
        wp_send_json_error('User not logged in.');
    }

    $user_id = get_current_user_id();
    $status = sanitize_text_field($_POST['status']);

    if ($status == '1') {
        update_user_meta($user_id, 'author_status', '1'); // Set status to true
        send_status_update_email_to_admin($user_id); // Send email to admin
    } else {
        update_user_meta($user_id, 'author_status', '0'); // Set status to false
    }

    wp_send_json_success();
}
add_action('wp_ajax_update_author_status', 'update_author_status');

// Function to send email notification to Admin when author status is updated to true
function send_status_update_email_to_admin($author_id) {
    $author = get_userdata($author_id);
    $admin_email = get_option('admin_email');
    $subject = 'Author Status Updated';
    $message = "Author: " . $author->display_name . " has updated their status to 'Available for Jobs'.\n\n";
    
    wp_mail($admin_email, $subject, $message);
}

// Update Author Status to false after the email is sent
function update_author_status_gmail() {
    // Make sure the request is valid and secure
    if (!isset($_POST['status'])) {
        wp_send_json_error('Invalid request.');
    }

    $user_ids = get_current_user_id();
    $status_admin = sanitize_text_field($_POST['status']);

    // Update status to false (uncheck)
    if ($status_admin == '0') {
        update_user_meta($user_ids, 'author_status', '0');
    }

    wp_send_json_success();
}
add_action('wp_ajax_update_author_status_gmail', 'update_author_status_gmail');


// Enqueue Admin Scripts
function enqueue_admin_scripts($hook) {

    // wp_enqueue_script('wp-tinymce');
    $nonce = wp_create_nonce('send_email_nonce');

    if ($hook === 'toplevel_page_author_dashboard' || $hook === 'toplevel_page_job_page') {
        wp_enqueue_style('quill-css', 'https://cdn.quilljs.com/1.3.7/quill.snow.css', array(), '1.3.7');
    
        // Enqueue Quill JS
        wp_enqueue_script('quill-js', 'https://cdn.quilljs.com/1.3.7/quill.min.js', array(), '1.3.7', true);
        
        wp_enqueue_script('custom-admin-script', get_stylesheet_directory_uri() . '/js/checkbox-sync.js?'.strtotime(date('Y-m-d H:i:s')).'');
        wp_localize_script('custom-admin-script', 'ajax_object', [
            'ajax_url' => admin_url('admin-ajax.php'),
        ]);

       
    }

    wp_localize_script('checkbox-sync-script', 'my_email_nonce', array(
        'nonce' => $nonce
    ));
}
add_action('admin_enqueue_scripts', 'enqueue_admin_scripts');







// Send Email via Gmail when Admin clicks the button and update status to false (unchecked)
function send_custom_email_to_author() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized access.');
    }

    $author_id = intval($_POST['author_id']);
    $author_email = sanitize_email($_POST['author_email']);
    $subject = sanitize_text_field($_POST['subject']);
    $message = sanitize_textarea_field($_POST['message']);

    // Send email to Author
    $headers = array('Content-Type: text/html; charset=UTF-8');
    $mail_sent = wp_mail($author_email, $subject, $message, $headers);

    // If email is sent successfully
    if ($mail_sent) {
        // Automatically update author status to 'false' (unchecked)
        update_user_meta($author_id, 'author_status', '0');
        
        wp_send_json_success('Email sent successfully and author status updated to false.');
    } else {
        wp_send_json_error('Failed to send email.');
    }
}
add_action('wp_ajax_send_custom_email_to_author', 'send_custom_email_to_author');





