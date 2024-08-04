<?php
/*
Plugin Name: Custom Contact Form
Description: A custom contact form plugin with validation and submission management.
Version: 1.0
Author: Dita
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Enqueue necessary scripts and styles
function ccf_enqueue_scripts() {
    wp_enqueue_style('ccf-style', plugins_url('style.css', __FILE__));
    wp_enqueue_script('ccf-script', plugins_url('script.js', __FILE__), array('jquery'), null, true);
    wp_localize_script('ccf-script', 'ccf_ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
}
add_action('wp_enqueue_scripts', 'ccf_enqueue_scripts');

// Create the contact form shortcode
// Create the contact form shortcode
function ccf_contact_form_shortcode() {
    ob_start();
    ?>
    <form id="ccf-contact-form">
        <?php wp_nonce_field('ccf_nonce', 'ccf_nonce'); ?>
        <label for="ccf-name">Name:</label>
        <input type="text" id="ccf-name" name="ccf-name" placeholder="Enter your name" required>
        <label for="ccf-email">Email:</label>
        <input type="email" id="ccf-email" name="ccf-email" placeholder="Enter your email address" required>
        <label for="ccf-subject">Subject:</label>
        <input type="text" id="ccf-subject" name="ccf-subject" placeholder="“I need a Pro design!”">
        <label for="ccf-message">Message:</label>
        <textarea id="ccf-message" name="ccf-message" placeholder="We're happy to help! Describe your inquiry and we will reach out soon." required></textarea>
        <button type="submit" class="bounce-arrow">Send Message <svg xmlns="http://www.w3.org/2000/svg" width="14" height="11" viewBox="0 0 14 11" fill="none"><path d="M8.95001 0.530029L8.95001 4.53003L0.030012 4.53003L1.11814e-05 6.54003L8.95001 6.54003L8.95001 10.53L13.95 5.53003L8.95001 0.530029Z" fill="#FF4848"></path></svg></button>
    </form>
    <div id="ccf-response"></div>
    <?php
    return ob_get_clean();
}

add_shortcode('ccf_contact_form', 'ccf_contact_form_shortcode');

// Handle form submission via AJAX
function ccf_handle_form_submission() {
    if (!wp_verify_nonce($_POST['ccf_nonce'], 'ccf_nonce')) {
        wp_send_json_error('Invalid nonce');
    }

    $name = sanitize_text_field($_POST['name']);
    $email = sanitize_email($_POST['email']);
    $message = sanitize_textarea_field($_POST['message']);

    if (empty($name) || empty($email) || empty($message)) {
        wp_send_json_error('Please fill in all fields');
    }

    // Save the submission to the database
    global $wpdb;
    $table_name = $wpdb->prefix . 'ccf_submissions';
    $wpdb->insert($table_name, array(
        'name' => $name,
        'email' => $email,
        'message' => $message,
        'submitted_at' => current_time('mysql')
    ));

    wp_send_json_success('Thank you for your message!');
}
add_action('wp_ajax_ccf_handle_form_submission', 'ccf_handle_form_submission');
add_action('wp_ajax_nopriv_ccf_handle_form_submission', 'ccf_handle_form_submission');

// Create database table for submissions on plugin activation
function ccf_create_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'ccf_submissions';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name tinytext NOT NULL,
        email varchar(100) NOT NULL,
        message text NOT NULL,
        submitted_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'ccf_create_table');

/**
 * ADMIN
 */
// Add admin menu for viewing submissions
function ccf_add_admin_menu() {
    add_menu_page(
        'Contact Form Submissions',
        'Contact Form Submissions',
        'manage_options',
        'ccf-submissions',
        'ccf_submissions_page',
        'dashicons-email',
        20
    );
}
add_action('admin_menu', 'ccf_add_admin_menu');

// Display submissions page
function ccf_submissions_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'ccf_submissions';
    $submissions = $wpdb->get_results("SELECT * FROM $table_name ORDER BY submitted_at DESC");
    ?>
    <div class="wrap">
        <h1>Contact Form Submissions</h1>
        <table class="wp-list-table widefat fixed striped table-view-list">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Message</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($submissions as $submission) : ?>
                    <tr>
                        <td><?php echo esc_html($submission->name); ?></td>
                        <td><?php echo esc_html($submission->email); ?></td>
                        <td><?php echo esc_html($submission->message); ?></td>
                        <td><?php echo esc_html($submission->submitted_at); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
}
?>