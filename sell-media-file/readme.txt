=== Sell Media File with Stripe ===
Contributors: naa986
Donate link: https://noorsplugin.com/
Tags: sell, selling, audio, music, video, media, commerce, sell downloads, sell media, digital downloads, download, downloads, e-commerce, e-downloads, e-store, ecommerce, eshop, stripe
Requires at least: 3.0
Tested up to: 4.8
Stable tag: 1.0.6
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Sell digital download such as music, video, audio, e-book, PDF in WordPress with Stripe

== Description ==

[Sell Media](https://noorsplugin.com/sell-media-file-plugin-for-wordpress/) plugin allows you to sell media or download with Stripe. You can add shortcode anywhere on your site to create pay button. When a user clicks on the payment button, a window pops up where they can enter their credit card information to purchase your product. Digital downloads become available instantly upon completion of the payment.

= Features =

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

= Plugin Setup =

After you have activated the plugin, you need to configure some settings related to your Stripe merchant account. It's located under "Sell Media File -> Settings".

* Stripe Test Secret Key
* Stripe Test Publishable Key
* Stripe Live Secret Key
* Stripe Live Publishable Key
* Currency Code

In order to create a Buy button you can add the following shortcode to a post/page:

`[sell_media_file item_name="My video" description="My cool video" amount="2.00" label="Buy Now" download_link="https://example.com/wp-content/uploads/videos/my-cool-video.mp4"]`

= Button Parameters =

You can add additional parameters in the shortcode to customize your payment buttons.

* **name** - The name of your company or website.
* **image** - A URL pointing to a square image of your brand or product(128x128px recommended). The recommended image types are .gif, .jpeg, and .png.
* **locale**- Specify auto to display Checkout in the user's preferred language, if available. English will be used by default.
* **currency** - The currency of the item (e.g. currency="USD"). If not specified it will take it from the settings.
* **billingAddress** - Specify whether Checkout should collect the user's billing address (e.g. billingAddress="true"). The default is false.
* **shippingAddress** - Specify whether Checkout should collect the user's shipping address (e.g. shippingAddress="true"). The default is false.
* **panelLabel** - The label of the payment button in the Checkout form (e.g. panelLabel="Pay $2.00"). Checkout does not translate custom labels to the user's preferred language.

For setup instructions please visit the [Sell Media](https://noorsplugin.com/sell-media-file-plugin-for-wordpress/) plugin page.

= Recommended Reading =

* [Sell Media Documentation](https://noorsplugin.com/sell-media-file-plugin-for-wordpress/)
* My Other [Free WordPress Plugins](https://noorsplugin.com/wordpress-plugins/)

== Installation ==

1. Go to the Add New plugins screen in your WordPress Dashboard
1. Click the upload tab
1. Browse for the plugin file (sell-media-file.zip) on your computer
1. Click "Install Now" and then hit the activate button

== Frequently Asked Questions ==

= Can this plugin be used to sell media files? =

Yes.

= Can this plugin be used to sell videos? =

Yes.

= Can this plugin be used to sell music? =

Yes.

== Screenshots ==

For screenshots please visit the [Sell Media](https://noorsplugin.com/sell-media-file-plugin-for-wordpress/) plugin page

== Upgrade Notice ==
none

== Changelog ==

= 1.0.6 =
* Fixed a bug where queries could be performed on orders on the front end.
* Sell Media orders are now also excluded from search.

= 1.0.5 =
* Sell Media plugin can now be used to track affiliate commission through the Affiliates Manager plugin.

= 1.0.4 =
* Changed the "price" parameter to "amount" in the shortcode.

= 1.0.3 =
* Sell Media File plugin has been redesigned. It now accepts payments via Stripe.

= 1.0.2 =
* Sell Media File is now compatible with WordPress 4.3

= 1.0.1 =
* First commit
