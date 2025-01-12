<?php

// function to post the post
function create_post($post_title, $post_image_upload, $post_category, $post_location, $post_description, $post_delivery, $post_colour, $post_fuel, $post_mileage, $post_price, $post_negotiable)
{
    global $conn;

    $message = [];

    // random id for post
    $post_id = rand(time(), 10000000);

    $file_array = reorganize_files_array($post_image_upload);

    $file_data = move_uploaded_post_images($file_array);

    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare('INSERT INTO re_posts(post_id, post_user, post_title, post_image, post_category, post_location, post_description, post_delivery, post_color, post_fuel_type, post_mileage, post_price, post_negotiable) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)');
    $stmt->bind_param('iisssssssssss', $post_id, $user_id, $post_title, $file_data, $post_category, $post_location, $post_description, $post_delivery, $post_colour, $post_fuel, $post_mileage, $post_price, $post_negotiable);
    if ($stmt->execute()) {
        $message['success'] = 'The ads post was successfully requested.';
    } else {
        $message['error'] = 'There is some error to post ads posts request.';
    }
    return $message;
}

// Helper function to reorganize $_FILES array
function reorganize_files_array($files)
{
    $file_array = array();
    $file_count = count($files['name']);
    $file_keys = array_keys($files);

    for ($i = 0; $i < $file_count; $i++) {
        foreach ($file_keys as $key) {
            $file_array[$i][$key] = $files[$key][$i];
        }
    }

    return $file_array;
}

// helper function to upload files and get json format data
function move_uploaded_post_images($file_array)
{
    $file_data_array = array(); // Array to store file data

    foreach ($file_array as $file) {
        // File details
        $file_name = rand(1000, 10000) . '-' . $file['name'];
        $file_tmp = $file['tmp_name'];
        $file_size = $file['size'];
        $file_type = $file['type'];

        // Move uploaded file to desired location (optional)
        $upload_dir = "frontend/uploads/";
        $file_path = $upload_dir . $file_name;
        move_uploaded_file($file_tmp, $file_path);

        // Store file details in array
        $file_data = array("name" => $file_name, "path" => $file_path);
        $file_data_array[] = $file_data;
    }

    // Encode file data array as JSON
    return $json_file_data = json_encode($file_data_array);
}

function get_pending_posts()
{
    global $conn;

    $data = [];

    $stmt = $conn->prepare("SELECT * FROM re_posts WHERE post_status = 'pending'");
    if ($stmt->execute()) :
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
    else :
        $data['error'] = 'Something went wrong while fetching posts.';
    endif;

    return $data;
}

function get_published_posts()
{
    global $conn;

    $data = [];

    $stmt = $conn->prepare("SELECT * FROM re_posts WHERE post_status != 'pending'");
    if ($stmt->execute()) :
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
    else :
        $data['error'] = 'Something went wrong while fetching posts.';
    endif;

    return $data;
}

function get_posts()
{
    global $conn;

    $data = [];

    $stmt = $conn->prepare("SELECT * FROM re_posts");
    if ($stmt->execute()) :
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
    else :
        $data['error'] = 'Something went wrong while fetching posts.';
    endif;

    return $data;
}

// function to update post status
function update_post_status($post_id, $post_status)
{
    global $conn;

    $message = [];

    $stmt = $conn->prepare('UPDATE re_posts SET post_status = ? WHERE post_id = ?');
    $stmt->bind_param('si', $post_status, $post_id);

    if ($stmt->execute()) {
        $message['success'] = 'The post status was successfully updated.';
    } else {
        $message['error'] = 'There is some error to update post status.';
    }
    return $message;
}

// function to display post inside the loop
function display_post($post)
{
?>

    <li>
        <form method="post">
            <h2 class="h6 mb-1">
                <a href="<?php echo get_root_directory_uri() . '/post?id=' . urldecode($post['post_id']); ?>" target="_blank">
                    <?php echo $post['post_title']; ?>
                </a>
            </h2>
            <div class="post-status mb-1">
                <label for="postStatus">Status : </label>
                <input type="hidden" name="_post_id" value="<?php echo $post['post_id']; ?>">
                <select name="postStatus" id="postStatus" class="form-select">
                    <option value="pending" <?php echo $post['post_status'] == 'pending' ? 'selected' : null; ?>>Pending</option>
                    <option value="published" <?php echo $post['post_status'] == 'published' ? 'selected' : null; ?>>Published</option>
                </select>
            </div>
            <button type="submit" name="change_status" class="btn btn-dark btn-submit">Save</button>
        </form>
    </li>

<?php
}

// function to update views
function update_views($post_id)
{
    global $conn;

    $stmt = $conn->prepare('UPDATE re_posts SET post_views = post_views + 1 WHERE post_id = ?');
    $stmt->bind_param('i', $post_id);

    $stmt->execute();
}

// function to get post by views descending order
function get_post_by_views()
{
    global $conn;

    $status = 'pending';

    $stmt = $conn->prepare("SELECT * FROM re_posts WHERE post_status != ? ORDER BY post_views DESC");
    $stmt->bind_param('s', $status);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
    }
    return $data;
}

// function to check if post is published or not
function is_published($post_id)
{
    global $conn;

    $status = 'pending';

    $stmt = $conn->prepare("SELECT * FROM re_posts WHERE post_id = ? and post_status != ?");
    $stmt->bind_param('is', $post_id, $status);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result->num_rows > 0)
            return true;
        return false;
    }
    return false;
}

// function to get post data by id
function get_post_by_id($post_id)
{
    global $conn;

    $stmt = $conn->prepare("SELECT * FROM re_posts WHERE post_id = ?");
    $stmt->bind_param('i', $post_id);

    $stmt->execute();

    $result = $stmt->get_result();

    return $result->fetch_array(MYSQLI_ASSOC);
}

function delete_comment($comment_id)
{
    if (is_login()) {
        global $conn;
        $query = $conn->prepare("SELECT * FROM re_comments WHERE comment_id = ?");
        $query->bind_param("i", $comment_id);
        if ($query->execute()) {
            $result = $query->get_result();
            if ($result->num_rows > 0) {
                $query = $conn->prepare("DELETE FROM re_comments WHERE comment_id = ?");
                $query->bind_param("i", $comment_id);
                $query->execute();
            }
        }
    }
}

// funciton to get post by user id
function get_post_by_user_id($user_id, $status = 'pending')
{
    global $conn;

    $stmt = $conn->prepare("SELECT * FROM re_posts WHERE post_user = ? AND post_status != ?");
    $stmt->bind_param('is', $user_id, $status);

    $stmt->execute();

    $result = $stmt->get_result();

    return $result->fetch_all(MYSQLI_ASSOC);
}

// function to get user bookings
function get_bookings_by_user($user_id)
{
    global $conn;

    $status = 'booked';

    $stmt = $conn->prepare('SELECT post_id FROM re_bookings WHERE user_id = ? AND booking_status = ?');
    $stmt->bind_param('is', $user_id, $status);

    $stmt->execute();
    $result = $stmt->get_result();

    return $result->fetch_all(MYSQLI_ASSOC);
}


// function nto update the post status
function update_post_status_by_user($post_id, $user_id, $post_status)
{
    global $conn;

    $message = [];

    $stmt = $conn->prepare("UPDATE re_posts SET post_status = ? WHERE post_id = ? AND post_user = ?");
    $stmt->bind_param('sii', $post_status, $post_id, $user_id);

    if ($stmt->execute()) :
        $message['success'] = 'The post is successfully ' . $post_status;
    else :
        $message['success'] = 'There is an error while updating the post status. Please try again later.';
    endif;
    return $message;
}

// function to check if post is rented or not
function is_rented($post_id)
{
    global $conn;

    $status = 'rented';

    $stmt = $conn->prepare('SELECT * FROM re_posts WHERE post_id = ? and post_status = ?');
    $stmt->bind_param('is', $post_id, $status);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result->num_rows > 0)
            return true;
        return false;
    }
    return false;
}

// function to check if the post is current user's or not
function is_my_post($post_id, $user_id)
{
    global $conn;

    $stmt = $conn->prepare('SELECT * FROM re_posts WHERE post_id = ? AND post_user = ?');
    $stmt->bind_param('is', $post_id, $user_id);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result->num_rows > 0)
            return true;
        return false;
    }
    return false;
}

// function to update post
function update_post($post_id, $post_title, $post_image_upload, $post_category, $post_location, $post_description, $post_delivery, $post_colour, $post_fuel, $post_mileage, $post_price, $post_negotiable)
{
    global $conn;
    $message = [];

    // random id for post

    $file_array = reorganize_files_array($post_image_upload);

    $file_data = move_uploaded_post_images($file_array);


    $stmt = $conn->prepare('UPDATE re_posts SET post_title = ?, post_image = ?, post_category = ?, post_location = ?, post_description = ?, post_delivery = ?, post_color = ?, post_fuel_type = ?, post_mileage = ?, post_price = ?, post_negotiable = ? WHERE post_id = ?');
    $stmt->bind_param('sssssssssssi', $post_title, $file_data, $post_category, $post_location, $post_description, $post_delivery, $post_colour, $post_fuel, $post_mileage, $post_price, $post_negotiable, $post_id);
    if ($stmt->execute()) {
        $message['success'] = 'The ads post was successfully updated.';
    } else {
        $message['error'] = 'There is some error to update post.';
    }
    return $message;
}

// function to delte post
function delete_post_by_id($post_id)
{
    global $conn;
    $message = [];
    $stmt = $conn->prepare("DELETE FROM re_posts WHERE post_id = ?");
    $stmt->bind_param('i', $post_id);
    if ($stmt->execute()) {
        $message['success'] = 'The ads post was successfully deleted.';
    } else {
        $message['error'] = 'There is some error to delete post.';
    }
    return $message;
}
