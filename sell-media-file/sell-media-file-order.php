<?php

function sell_media_file_order_page() {
    $labels = array(
        'name' => __('Orders', 'sell-media-file'),
        'singular_name' => __('Order', 'sell-media-file'),
        'menu_name' => __('Sell Media File', 'sell-media-file'),
        'name_admin_bar' => __('Order', 'sell-media-file'),
        'add_new' => __('Add New', 'sell-media-file'),
        'add_new_item' => __('Add New Order', 'sell-media-file'),
        'new_item' => __('New Order', 'sell-media-file'),
        'edit_item' => __('Edit Order', 'sell-media-file'),
        'view_item' => __('View Order', 'sell-media-file'),
        'all_items' => __('All Orders', 'sell-media-file'),
        'search_items' => __('Search Orders', 'sell-media-file'),
        'parent_item_colon' => __('Parent Orders:', 'sell-media-file'),
        'not_found' => __('No Orders found.', 'sell-media-file'),
        'not_found_in_trash' => __('No orders found in Trash.', 'sell-media-file')
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'exclude_from_search' => true,
        'publicly_queryable' => false,
        'show_ui' => true,
        'show_in_menu' => true,
        'query_var' => true,
        'rewrite' => false,
        'capability_type' => 'post',
        'has_archive' => true,
        'hierarchical' => false,
        'menu_position' => null,
        'supports' => 'editor'
    );

    register_post_type('sellmediafile_order', $args);
}

function sell_media_file_order_columns($columns) {
    unset($columns['title']);
    unset($columns['date']);
    $edited_columns = array(
        'title' => __('Order', 'sell-media-file'),
        'txn_id' => __('Transaction ID', 'sell-media-file'),
        'name' => __('Name', 'sell-media-file'),
        'email' => __('Email', 'sell-media-file'),
        'amount' => __('Total', 'sell-media-file'),
        'date' => __('Date', 'sell-media-file')
    );
    return array_merge($columns, $edited_columns);
}

//meta boxes
function sell_media_file_order_meta_box($post) {
    $payment_status = get_post_meta($post->ID, '_payment_status', true);
    $payment_type = get_post_meta($post->ID, '_payment_type', true);
    $txn_id = get_post_meta($post->ID, '_txn_id', true);
    // Add an nonce field so we can check for it later.
    wp_nonce_field('sellmediafile_meta_box', 'sellmediafile_meta_box_nonce');
    ?>
    <table class="form-table">
        <tbody>
            <tr valign="top">
                <th scope="row"><label for="_payment_status"><?php _e('Payment Status', 'sell-media-file'); ?></label></th>
                <td><input name="_payment_status" type="text" id="_payment_status" value="<?php echo $payment_status; ?>" class="regular-text">
                    <p class="description">Item Name</p></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="_payment_type"><?php _e('Payment Type', 'sell-media-file'); ?></label></th>
                <td><input name="_payment_type" type="text" id="_payment_type" value="<?php echo $payment_type; ?>" class="regular-text">
                    <p class="description">Item Name</p></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="_txn_id"><?php _e('Transaction ID', 'sell-media-file'); ?></label></th>
                <td><input name="_txn_id" type="text" id="_txn_id" value="<?php echo $txn_id; ?>" class="regular-text">
                    <p class="description">Item Name</p></td>
            </tr>
        </tbody>

    </table>

    <?php
}

function sell_media_file_custom_column($column, $post_id) {
    switch ($column) {
        case 'title' :
            echo $post_id;
            break;
        case 'txn_id' :
            echo get_post_meta($post_id, '_txn_id', true);
            break;
        case 'name' :
            echo get_post_meta($post_id, '_name', true);
            break;
        case 'email' :
            echo get_post_meta($post_id, '_email', true);
            break;
        case 'amount' :
            echo get_post_meta($post_id, '_amount', true);
            break;
    }
}

function sell_media_file_save_meta_box_data($post_id) {
    /*
     * We need to verify this came from our screen and with proper authorization,
     * because the save_post action can be triggered at other times.
     */
    // Check if our nonce is set.
    if (!isset($_POST['sellmediafile_meta_box_nonce'])) {
        return;
    }
    // Verify that the nonce is valid.
    if (!wp_verify_nonce($_POST['sellmediafile_meta_box_nonce'], 'sellmediafile_meta_box')) {
        return;
    }
    // If this is an autosave, our form has not been submitted, so we don't want to do anything.
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    // Check the user's permissions.
    if (isset($_POST['post_type']) && 'page' == $_POST['post_type']) {
        if (!current_user_can('edit_page', $post_id)) {
            return;
        }
    } else {
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
    }
    /* OK, it's safe for us to save the data now. */
    // Make sure that it is set.
    /*
    if (isset($_POST['_email'])) {
        $email = sanitize_text_field($_POST['_email']);
        update_post_meta($post_id, '_email', $email);
    }
    */
}

add_action('save_post', 'sell_media_file_save_meta_box_data');