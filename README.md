# Sell Media Plugin for WordPress

## Description

[Sell Media](https://noorsplugin.com/sell-media-file-plugin-for-wordpress/) is an e-commmerce WordPress plugin that allows you to sell media files or downloads with the Stripe payment gateway. You can add shortcode anywhere on your site to create pay button. When a user clicks on the payment button, a window pops up where they can enter their credit card information to purchase your product. Digital downloads become available instantly upon completion of the payment. It was developed by [noorsplugin](https://noorsplugin.com/) and is currently being used on hundreds of websites.

## Sell Media Plugin Features

* Sell PDF files online (e-book, epub etc)
* Sell videos online (MP4, WebM, Ogv, FLV etc)
* Sell audio files, mp3, podcasts or songs
* Sell photos, photo prints or digital photos
* Sell any digital downloads with this easy digital downloads plugin
* Accept credit card payments from your users with a few clicks
* Responsive payment form which looks great on mobile and tablet devices
* Sell products, services, media files or downloads using Stripe payment gateway
* Create payment buttons on the fly using shortcodes
* Accept once off payments or donations from users with Stripe checkout
* Allow users to checkout without ever leaving your site
* View or Manage orders received from your WordPrss admin dashboard
* Quick settings configurations
* Enable debug to troubleshoot various issues (e.g. orders not getting updated)
* Sell items with different pricing options
* Switch your store to Stripe sandbox mode for testing
* Compatible with the latest version of WordPress
* Compatible with any WordPress theme
* Sell in any currency supported by Stripe

## Sell Media Plugin Setup

After you have activated the plugin, you need to configure some settings related to your Stripe merchant account. It's located under **Sell Media File > Settings**.

* Stripe Test Secret Key
* Stripe Test Publishable Key
* Stripe Live Secret Key
* Stripe Live Publishable Key
* Currency Code

In order to create a Buy button you can add the following shortcode to a post/page:
```
[sell_media_file item_name="My video" description="My cool video" amount="2.00" label="Buy Now" download_link="https://example.com/wp-content/uploads/videos/my-cool-video.mp4"]
```
## Sell Media Shortcode Parameters

You can add additional parameters in the shortcode to customize your payment buttons.

### name

The name of your company or website.

### image

A URL pointing to a square image of your brand or product(128x128px recommended). The recommended image types are .gif, .jpeg, and .png.

### locale

Specify auto to display Checkout in the user's preferred language, if available. English will be used by default.

### currency

The currency of the item (e.g. currency="USD"). If not specified it will take it from the settings.

### billingAddress

Specify whether Checkout should collect the user's billing address (e.g. **billingAddress="true"**). The default is false.

### shippingAddress

Specify whether Checkout should collect the user's shipping address (e.g. **shippingAddress="true"**). The default is false.

### panelLabel

The label of the payment button in the Checkout form (e.g. **panelLabel="Pay $2.00"**). Checkout does not translate custom labels to the user's preferred language.

## Documentation

For detailed documentation please visit the [WordPress Sell Media Plugin](https://noorsplugin.com/sell-media-file-plugin-for-wordpress/) page.
