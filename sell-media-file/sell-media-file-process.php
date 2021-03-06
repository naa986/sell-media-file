<?php

function sell_media_file_process_ipn() {
    if (!isset($_POST['stripeToken']) && !isset($_POST['stripeTokenType'])) {
        return;
    }
    $nonce = $_REQUEST['_wpnonce'];
    if ( !wp_verify_nonce($nonce, 'sell_media_file')){
        wp_die('Error! Nonce Security Check Failed!');
    }
    $stripeToken = sanitize_text_field($_POST['stripeToken']);
    if (empty($stripeToken)) {
        $error_msg = __('Please make sure your card details have been entered correctly and that your browser supports JavaScript.', 'sell-media-file');
        $error_msg .= ' ' . __('Please also make sure that you are including jQuery and there are no JavaScript errors on the page.', 'sell-media-file');
        wp_die($error_msg);
    }
    if (!isset($_POST['item_name']) || empty($_POST['item_name'])) {
        $error_msg = __('Item name could not be found.', 'sell-media-file');
        wp_die($error_msg);
    }
    $item_name = sanitize_text_field($_POST['item_name']);
    $transient_name = 'sellmediafile-amount-' . sanitize_title_with_dashes($item_name);
    $price = get_transient($transient_name);
    if(!isset($price) || !is_numeric($price)){
        $error_msg = __('Item price amount could not be found.', 'sell-media-file');
        wp_die($error_msg);
    }
    $transient_name = 'sellmediafile-currency-' . sanitize_title_with_dashes($item_name);
    $currency = get_transient($transient_name);
    if(!isset($currency) || empty($currency)){
        $error_msg = __('Item currency could not be found.', 'sell-media-file');
        wp_die($error_msg);
    }
    $download_link = '';
    $transient_name = 'sellmediafile-link-' . sanitize_title_with_dashes($item_name);
    $dl_link = get_transient($transient_name);
    if(isset($dl_link) && !empty($dl_link)){
        $download_link = $dl_link;
    }
    $description = '';
    if(isset($_POST['item_description']) && !empty($_POST['item_description'])){
        $description = sanitize_text_field($_POST['item_description']);
    }
    
    $BillingName = isset($_POST['stripeBillingName']) && !empty($_POST['stripeBillingName']) ? sanitize_text_field($_POST['stripeBillingName']) : '';
    $BillingAddressLine1 = isset($_POST['stripeBillingAddressLine1']) && !empty($_POST['stripeBillingAddressLine1']) ? sanitize_text_field($_POST['stripeBillingAddressLine1']) : '';
    $BillingAddressZip = isset($_POST['stripeBillingAddressZip']) && !empty($_POST['stripeBillingAddressZip']) ? sanitize_text_field($_POST['stripeBillingAddressZip']) : '';
    $BillingAddressState = isset($_POST['stripeBillingAddressState']) && !empty($_POST['stripeBillingAddressState']) ? sanitize_text_field($_POST['stripeBillingAddressState']) : '';
    $BillingAddressCity = isset($_POST['stripeBillingAddressCity']) && !empty($_POST['stripeBillingAddressCity']) ? sanitize_text_field($_POST['stripeBillingAddressCity']) : '';
    $BillingAddressCountry = isset($_POST['stripeBillingAddressCountry']) && !empty($_POST['stripeBillingAddressCountry']) ? sanitize_text_field($_POST['stripeBillingAddressCountry']) : '';
    $ShippingName = isset($_POST['stripeShippingName']) && !empty($_POST['stripeShippingName']) ? sanitize_text_field($_POST['stripeShippingName']) : '';
    $ShippingAddressLine1 = isset($_POST['stripeShippingAddressLine1']) && !empty($_POST['stripeShippingAddressLine1']) ? sanitize_text_field($_POST['stripeShippingAddressLine1']) : '';
    $ShippingAddressZip = isset($_POST['stripeShippingAddressZip']) && !empty($_POST['stripeShippingAddressZip']) ? sanitize_text_field($_POST['stripeShippingAddressZip']) : '';
    $ShippingAddressState = isset($_POST['stripeShippingAddressState']) && !empty($_POST['stripeShippingAddressState']) ? sanitize_text_field($_POST['stripeShippingAddressState']) : '';
    $ShippingAddressCity = isset($_POST['stripeShippingAddressCity']) && !empty($_POST['stripeShippingAddressCity']) ? sanitize_text_field($_POST['stripeShippingAddressCity']) : '';
    $ShippingAddressCountry= isset($_POST['stripeShippingAddressCountry']) && !empty($_POST['stripeShippingAddressCountry']) ? sanitize_text_field($_POST['stripeShippingAddressCountry']) : '';
    sell_media_file_debug_log("Post Data", true);
    sell_media_file_debug_log_array($_POST, true);
    // Other charge data
    $post_data['source'] = $stripeToken;
    $post_data['currency'] = strtolower($currency);
    $post_data['amount'] = $price * 100;
    $post_data['description'] = $description;
    $post_data['capture'] = 'true';
    $email = '';
    if (isset($_POST['stripeEmail'])) {
        $email = sanitize_email($_POST['stripeEmail']);
        $post_data['receipt_email'] = $email;
    }

    $post_data['expand[]'] = 'balance_transaction';

    // Make the request
    $response = sell_media_file_stripe_request($post_data);

    if (is_wp_error($response)) {
        throw new Exception($response->get_error_message());
    }
    sell_media_file_debug_log("Response Data", true);
    sell_media_file_debug_log_array($response, true);
    //process data
    $txn_id = $response->id;
    $args = array(
        'post_type' => 'sellmediafile_order',
        'meta_query' => array(
            array(
                'key' => '_txn_id',
                'value' => $txn_id,
                'compare' => '=',
            ),
        ),
    );
    $query = new WP_Query($args);
    if ($query->have_posts()) {  //a record already exists
        sell_media_file_debug_log("An order with this transaction ID already exists. This payment will not be processed.", false);
        return;
    }
    $content = '';
    $content .= '<strong>Transaction ID:</strong> '.$txn_id.'<br />';
    $content .= '<strong>Item name:</strong> '.$item_name.'<br />';
    $amount = $price;
    $content .= '<strong>Amount:</strong> '.$amount.'<br />';
    $content .= '<strong>Currency:</strong> '.$currency.'<br />';
    $name = $BillingName;
    if(!empty($name)){
        $content .= '<strong>Billing Name:</strong> '.$name.'<br />';
    }
    if(!empty($email)){
        $content .= '<strong>Email:</strong> '.$email.'<br />'; 
    }
    if(!empty($BillingAddressLine1)){
        $content .= '<strong>Billing Address:</strong> '.$BillingAddressLine1;
        if(!empty($BillingAddressCity)){
            $content .= ', '.$BillingAddressCity;
        }
        if(!empty($BillingAddressState)){
            $content .= ', '.$BillingAddressState;
        }
        if(!empty($BillingAddressZip)){
            $content .= ', '.$BillingAddressZip;
        }
        if(!empty($BillingAddressCountry)){
            $content .= ', '.$BillingAddressCountry;
        }
        $content .= '<br />';
    }
    if(!empty($ShippingAddressLine1)){
        $content .= '<strong>Shipping Address:</strong> '.$ShippingAddressLine1;
        if(!empty($ShippingAddressCity)){
            $content .= ', '.$ShippingAddressCity;
        }
        if(!empty($ShippingAddressState)){
            $content .= ', '.$ShippingAddressState;
        }
        if(!empty($ShippingAddressZip)){
            $content .= ', '.$ShippingAddressZip;
        }
        if(!empty($ShippingAddressCountry)){
            $content .= ', '.$ShippingAddressCountry;
        }
        $content .= '<br />';
    }
    $sell_media_file_order = array(
        'post_title' => 'order',
        'post_type' => 'sellmediafile_order',
        'post_content' => '',
        'post_status' => 'publish',
    );
    sell_media_file_debug_log("Updating order information", true);
    $post_id = wp_insert_post($sell_media_file_order);  //insert a new order
    $post_updated = false;
    if ($post_id > 0) {
        $post_content = $content;
        $updated_post = array(
            'ID' => $post_id,
            'post_title' => $post_id,
            'post_type' => 'sellmediafile_order',
            'post_content' => $post_content
        );
        $updated_post_id = wp_update_post($updated_post);  //update the order
        if ($updated_post_id > 0) {  //successfully updated
            $post_updated = true;
        }
    }
    //save order information
    if ($post_updated) {
        update_post_meta($post_id, '_txn_id', $txn_id);
        update_post_meta($post_id, '_name', $name);
        update_post_meta($post_id, '_amount', $amount);
        update_post_meta($post_id, '_email', $email);
        sell_media_file_debug_log("Order information updated", true);
        do_action('smf_order_processed', $post_id);
    } else {
        sell_media_file_debug_log("Order information could not be updated", false);
        return;
    }
    sell_media_file_debug_log("Oder processing completed", true, true);
    $success_message = __('Thank you! Your order has been completed. ', 'sell-media-file');
    if(!empty($download_link)){
        $success_message .= sprintf(__('<a target="_blank" href="%s">Click here</a> to download the file.', 'sell-media-file'), esc_url($download_link));
    }
    wp_die($success_message);
}

function sell_media_file_stripe_request($request, $api = 'charges', $method = 'POST') {

    $stripe_options = sell_media_file_get_option();
    $secret_key = $stripe_options['stripe_secret_key'];
    if (SELL_MEDIA_FILE_STRIPE_TESTMODE) {
        $secret_key = $stripe_options['stripe_test_secret_key'];
    }
    $response = wp_safe_remote_post(
            'https://api.stripe.com/v1/' . $api, array(
        'method' => $method,
        'headers' => array(
            'Authorization' => 'Basic ' . base64_encode($secret_key . ':'),
            'Stripe-Version' => '2016-02-29'
        ),
        'body' => $request,
        'timeout' => 70,
        'user-agent' => 'SellMediaFile'
            )
    );

    if (is_wp_error($response)) {
        wp_die(__('There was a problem connecting to the payment gateway.', 'sell-media-file'));
    }

    if (empty($response['body'])) {
        wp_die(__('Empty response.', 'sell-media-file'));
    }

    $parsed_response = json_decode($response['body']);

    // Handle response
    if (!empty($parsed_response->error)) {
        $error_msg = (!empty($parsed_response->error->code)) ? $parsed_response->error->code : 'stripe_error: ' . $parsed_response->error->message;
        wp_die($error_msg);
    } else {
        return $parsed_response;
    }
}
