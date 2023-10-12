<?php
// Enqueue scripts and styles
function sp_enqueue_scripts() {
    // Add your scripts and styles here
}
add_action('wp_enqueue_scripts', 'sp_enqueue_scripts');

// Shortcode function
function sp_post_submission_shortcode() {
    ob_start();

    // Process form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && current_user_can('publish_posts')) {
        $post_title = sanitize_text_field($_POST['post_title']);
        $post_content = wp_kses_post($_POST['post_content']);
        $post_category = isset($_POST['post_category']) ? array_map('intval', $_POST['post_category']) : array();

        // Additional validation and processing logic
        // ...

        // Create post
        $new_post = array(
            'post_title'   => $post_title,
            'post_content' => $post_content,
            'post_status'  => 'publish',
            'post_author'  => get_current_user_id(),
            'post_type'    => 'post',
            'post_category' => $post_category,
        );

        $post_id = wp_insert_post($new_post);

        if (!is_wp_error($post_id)) {
            // Set featured image
            if (!empty($_FILES['featured_image']['name'])) {
                $attachment_id = sp_upload_featured_image($post_id);
                set_post_thumbnail($post_id, $attachment_id);
            }

            echo '<p class="success-message">Post submitted successfully!</p>';
        } else {
            echo '<p class="error-message">Error submitting post. Please try again.</p>';
        }
    }

    // Display the form
    ?>
    <div class="container posts-form-container">
        <div class="row posts-form-row">
            <div class="col-sm-12">
        <form id="post-submission-form" method="post" enctype="multipart/form-data">
        <label class="titles" for="post-title">Post Title:</label>
        <input type="text" name="post_title" id="post-title" required>

        <label class="titles" for="post-content">Post Content:</label>
        <textarea name="post_content" id="post-content" required></textarea>

        <label class="titles" for="post-category">Post Category:</label><br>
        <?php
        $categories = get_categories(array('hide_empty' => false));
        foreach ($categories as $category) {
            echo '<label><input type="checkbox" name="post_category[]" value="' . $category->term_id . '"> ' . $category->name . '</label><br>';
        }
        ?>
       
        <label class="titles" for="featured-image">Featured Image:</label><br>
        <input type="file" name="featured_image" id="featured-image" required>

        <input class="submit-post-btn" type="submit" value="Submit Post">
    </form>
            </div>
        </div>
    </div>

    <style scoped>
.posts-form-container{
display: flex;
align-items: center;
align-content: center;
justify-content: center;
flex-direction: column;
}

.titles{
    font-size: 1rem;
    font-weight: 500;
}

.submit-post-btn{
    background-color: #48b217;
}
.submit-post-btn:hover{
    background-color: #48b217;
}
    </style>

    <?php

    return ob_get_clean();
}
add_shortcode('create_posts', 'sp_post_submission_shortcode');

// Function to handle featured image upload
function sp_upload_featured_image($post_id) {
    if (!function_exists('wp_handle_upload')) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
    }

    $uploaded_file = $_FILES['featured_image'];
    $upload_overrides = array('test_form' => false);

    $movefile = wp_handle_upload($uploaded_file, $upload_overrides);

    if ($movefile && empty($movefile['error'])) {
        $wp_filetype = wp_check_filetype(basename($movefile['file']), null);
        $attachment = array(
            'post_mime_type' => $wp_filetype['type'],
            'post_title' => sanitize_file_name($uploaded_file['name']),
            'post_content' => '',
            'post_status' => 'inherit'
        );

        $attachment_id = wp_insert_attachment($attachment, $movefile['file'], $post_id);
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $attachment_data = wp_generate_attachment_metadata($attachment_id, $movefile['file']);
        wp_update_attachment_metadata($attachment_id, $attachment_data);

        return $attachment_id;
    } else {
        return false;
    }
}
?>
