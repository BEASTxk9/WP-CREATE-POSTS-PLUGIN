<?php
// Shortcode function
function sp_delete_post_shortcode()
{
    $output = '
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    ';

    // Display posts for the current user
    $current_user_id = get_current_user_id();
    $user_posts = get_posts(array('post_type' => 'post', 'author' => $current_user_id));

    if ($user_posts) {
        // Open container & row
        $output .= '
            <div class="container delete-post-container">
                <div class="row delete-post-row">
        ';

        foreach ($user_posts as $post) {
            $output .= '
<div class="card col-sm-12 col-md-3 mx-1 my-1">
    <div class="card-image display-flex justify-content-center align-content-center align-items-center"  style="background-image: url(' . get_the_post_thumbnail_url($post->ID, 'thumbnail') . ');"></div>


    <div class="category">  
        <p>Category: ' . get_the_category_list(', ', '', $post->ID) . '</p>
    </div>

    <div class="heading"> 
         <p>Title: <br>' . esc_html($post->post_title) . '</p>
        <div class="author">' . esc_html(get_the_date('F j, Y', $post->ID)) . '</div>
    </div>

    <!-- DELETE BTN -->
    <button class="delete-post-btn"  data-post-id="' . $post->ID . '">
    <span> Delete
    </span>
  </button>

</div>
            ';
        }

        $output .= '
            </div>
        </div>
        <br>
        ';
    }

?>
        <style scoped>
.delete-post-row{
    display: flex;
    justify-content: center;
}

            .delete-post-col {
                display: flex;
                flex-direction: column;
                justify-content: space-between;
                margin-bottom: 1rem;
                background-size: cover;
                border: 1px solid black;
                background-repeat: no-repeat;
            }

            .category, .category a{
                color: #48b217 !important;
            }

            .card {
  width: 160px;
  background: white;
  padding: .4em;
  border-radius: 6px;
}

.card-image {
  width: 100%;
  height: 150px;
  border-radius: 6px 6px 0 0;
  background-size: cover;
  background-repeat: no-repeat;
}

.card-image:hover {
  transform: scale(0.98);
}

.category {
  text-transform: uppercase;
  font-size: 0.7em;
  font-weight: 600;
  color: rgb(63, 121, 230);
  padding: 10px 7px 0;
}

.category:hover {
  cursor: pointer;
}

.heading {
  font-weight: 600;
  color: rgb(88, 87, 87);
  padding: 7px;
}

.heading:hover {
  cursor: pointer;
}

.author {
  color: gray;
  font-weight: 400;
  font-size: 11px;
  padding-top: 20px;
}

.name {
  font-weight: 600;
}

.name:hover {
  cursor: pointer;
}

            /* delete btn */
            button {
 position: relative;
 height: 50px;
 padding: 0 30px;
 border: 2px solid #000;
 background: #e8e8e8;
 user-select: none;
 white-space: nowrap;
 transition: all .05s linear;
 font-family: inherit;
}

button:before, button:after {
 content: "";
 position: absolute;
 background: #e8e8e8;
 transition: all .2s linear;
}

button:before {
 width: calc(100% + 6px);
 height: calc(100% - 16px);
 top: 8px;
 left: -3px;
}

button:after {
 width: calc(100% - 16px);
 height: calc(100% + 6px);
 top: -3px;
 left: 8px;
}

button:hover {
 cursor: crosshair;
}

button:active {
 transform: scale(0.95);
}

button:hover:before {
 height: calc(100% - 32px);
 top: 16px;
}

button:hover:after {
 width: calc(100% - 32px);
 left: 16px;
}

button span {
 font-size: 15px;
 z-index: 3;
 position: relative;
 font-weight: 600;
 display: flex;
 align-items: center;
 justify-content: center;
 align-content: center;

}
        </style>
<?php

    // Enqueue scripts
    $output .= '
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                var deleteButtons = document.querySelectorAll(".delete-post-btn");

                deleteButtons.forEach(function(button) {
                    button.addEventListener("click", function() {
                        var postId = this.getAttribute("data-post-id");
                        var confirmation = confirm("Are you sure you want to delete this post?");

                        if (confirmation) {
                            // AJAX request to delete the post
                            var xhr = new XMLHttpRequest();
                            xhr.open("POST", "' . admin_url('admin-ajax.php') . '");
                            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                            xhr.onload = function() {
                                var response = JSON.parse(xhr.responseText);
                                if (response.success) {
                                    // Reload the page or update the UI as needed
                                    location.reload();
                                } else {
                                    alert("Error deleting post: " + response.message);
                                }
                            };
                            xhr.send("action=sp_delete_post&post_id=" + postId);
                        }
                    });
                });
            });
        </script>
    ';

    return $output;
}
add_shortcode('delete_posts', 'sp_delete_post_shortcode');

// Ajax handler for post deletion
add_action('wp_ajax_sp_delete_post', 'sp_delete_post');
function sp_delete_post()
{
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;

    // Check if the user has the capability to delete the post
    if (current_user_can('delete_post', $post_id)) {
        wp_delete_post($post_id, true);
        echo json_encode(array('success' => true));
    } else {
        echo json_encode(array('success' => false, 'message' => 'Permission denied.'));
    }

    wp_die();
}
?>
