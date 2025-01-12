<?php
$post_id = $_GET['id'];
$user_id = get_user_id();

if (!isset($_GET['id']) || empty($_GET['id'])) :
    header('Location: ' . get_root_directory_uri() . '/');
endif;


if (!is_admin() && !is_published($post_id)) :
    header('Location: ' . get_root_directory_uri() . '/404');
endif;

if ($_SERVER['REQUEST_METHOD'] == 'POST') :
    if (isset($_POST['comment-submit'])) :
        $comment_content = $_POST['comment-field'];
        $reply_to = $_POST['reply-to'];
        post_comment($post_id, $user_id, $comment_content, $reply_to);
    endif;
endif;

if (is_published($post_id))
    update_views($post_id);

if (isset($_GET['booking'])) :
    switch ($_GET['booking']):
        case 'true':
            $booking_message = book_post($post_id, $user_id);
            break;
        case 'false':
            $booking_message = cancel_booked_post($post_id, $user_id);
            break;
        default:
            break;
    endswitch;

endif;

if (isset($_GET['action'])) :
    if ($_GET['action'] == 'delete') :
        $comment_id = $_GET['commentid'];
        delete_comment($comment_id);
    endif;
endif;

get_header();

$post_data = get_post_by_id($post_id);

?>

<section class="post-detail py-5">
    <div class="container">
        <div class="flex flex-wrap">
            <div class="col-md-5">
                <figure class="post-gallery">
                    <?php

                    $post_image_array = json_decode($post_data['post_image']);
                    if (count($post_image_array) > 0) :

                        foreach ($post_image_array as $post_image) :
                            echo '<img src="' . get_root_directory_uri() . '/' . $post_image->path . '" alt="' . $post_image->name . '" />';
                        endforeach;

                    else :
                    ?>
                        <img src="<?php echo get_theme_directory_uri(); ?>/assets/img/jpg/default-image.jpg" alt="Default Image">

                    <?php

                    endif;
                    ?>
                </figure>
            </div>
            <div class="col-md-7 ps-3">
                <?php
                if (isset($booking_message['success'])) :
                ?>
                    <div class="alert mb-2">
                        <p class="bg-success p-1">
                            <?php echo $booking_message['success']; ?>
                        </p>
                    </div>
                <?php
                endif;
                ?>
                <?php
                if (isset($booking_message['error'])) :
                ?>
                    <div class="alert mb-2">
                        <p class="bg-error p-1">
                            <?php echo $booking_message['error']; ?>
                        </p>
                    </div>
                <?php
                endif;
                ?>

                <h1 class="post-title h3"><?php echo $post_data['post_title']; ?></h1>

                <div class="post-author">
                    <?php
                    $user_info = get_user_info_by_id($post_data['post_user']);
                    $avatar = $user_info['user_profile'];
                    $user_link =  get_root_directory_uri() . '/user?id=' . urlencode($user_info['user_id']);
                    ?>

                    <div class="user-info">
                        <a href="<?php echo $user_link; ?>" target="_blank">
                            <?php if ($avatar != "") :
                                $img_url = get_image_url($avatar);
                            ?>
                                <img class="user-image" src="<?php echo $img_url; ?>" alt="<?php echo $name; ?>">
                            <?php else : ?>
                                <img class="user-image" src="<?php echo get_theme_directory_uri(); ?>/assets/img/png/default-user.png" alt="Profile Image">
                            <?php endif; ?>
                        </a>
                        <div class="user-detail">
                            <a href="<?php echo $user_link; ?>" target="_blank">
                                <span class="user-name"><?php echo $user_info['user_fullname']; ?></span>
                            </a>
                            <a href="tel:<?php echo $user_info['user_phone']; ?>" class="user-contact"><?php echo $user_info['user_phone']; ?></a>
                        </div>
                    </div>
                    <div class="flex gap-2 my-2">
                        <?php if (!is_booked($post_id, $user_id)) : ?>
                            <a href="post?id=<?php echo urlencode($post_id); ?>&booking=<?php echo urlencode('true'); ?>" class="btn btn-outline">Book Now</a>
                        <?php elseif (is_booked($post_id, $user_id)) : ?>
                            <a href="post?id=<?php echo urlencode($post_id); ?>&booking=<?php echo urlencode('false'); ?>" class="btn btn-outline">Cancel Booking</a>
                        <?php endif; ?>
                        <a href="#" class="btn btn-outline">Save Post</a>
                    </div>
                </div>

                <div class="tabs-container">
                    <ul class="tab-list">
                        <li><button class="tab-button active" data-target="#post-information">Description</button></li>
                        <li><button class="tab-button" data-target="#post-comments">Comments</button></li>
                        <li><button class="tab-button" data-target="#location-info">Location</button></li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane active" id="post-information" class="post-information">
                            <div class="post-description mb-2">
                                <?php echo $post_data['post_description']; ?>
                            </div>

                            <div class="other-details">
                                <h2 class="h4 mb-1">Other Details</h2>
                                <ul class="detail-list">
                                    <li>
                                        <span class="detail-title">Colour</span>
                                        <span class="detail-info">
                                            <span style="display:inline-block;width: 20px; height:20px; border-radius: 50%; background-color: <?php echo $post_data['post_color']; ?>"></span>
                                        </span>
                                    </li>
                                    <li>
                                        <span class="detail-title">Location</span>
                                        <span class="detail-info"><?php echo $post_data['post_location']; ?></span>
                                    </li>
                                    <li>
                                        <span class="detail-title">Delivery</span>
                                        <span class="detail-info"><?php echo $post_data['post_delivery'] ? 'Yes' : 'No'; ?></span>
                                    </li>
                                    <li>
                                        <span class="detail-title">Fuel Type</span>
                                        <span class="detail-info"><?php echo $post_data['post_fuel_type']; ?></span>
                                    </li>
                                    <li>
                                        <span class="detail-title">Mileage</span>
                                        <span class="detail-info"><?php echo $post_data['post_mileage']; ?></span>
                                    </li>
                                    <li>
                                        <span class="detail-title">Pricing</span>
                                        <span class="detail-info">Rs. <?php echo $post_data['post_price']; ?></span>
                                    </li>
                                    <!-- <li>
                                        <span class="detail-title">Rent Start Date</span>
                                        <span class="detail-info"><?php //echo $post_data['post_rent_start']; 
                                                                    ?></span>
                                    </li>
                                    <li>
                                        <span class="detail-title">Rent End Date</span>
                                        <span class="detail-info"><?php //echo $post_data['post_rent_end']; 
                                                                    ?></span>
                                    </li> -->
                                </ul>
                            </div>
                        </div>
                        <div class="tab-pane" id="post-comments">
                            <?php require_once 'includes/comments.php'; ?>
                        </div>
                        <div class="tab-pane" id="location-info">
                            <ul class="location-list">
                                <li>
                                    <img class="location-icon" src="<?php echo get_theme_directory_uri(); ?>/assets/img/png/location.png" alt="Location Icon">
                                    <span class="location"><?php echo $post_data['post_location']; ?></span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
get_footer();
