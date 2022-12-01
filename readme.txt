=== HandyPlugins PaddlePress - Paddle Integration for WordPress ===
Contributors: handyplugins, m_uysl
Tags: paddle, paddlepress, payment, software-licensing, membership
Requires at least: 5.0
Tested up to: 6.0
Requires PHP: 5.6
Stable tag: 1.6
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Easily accept credit card payments on your WordPress site using Paddle.

== Description ==

HandyPlugins PaddlePress is a standalone payments plugin that connects Paddle with WordPress. Simply set up a Paddle account and start accepting credit cards on your WordPress site.

__Plugin Website__: [https://handyplugins.co/paddlepress-pro/](https://handyplugins.co/paddlepress-pro/)

= HandyPlugins PaddlePress PRO Features =

HandyPlugins PaddlePress PRO provides additional functionalities to integrate Paddle with WordPress. PRO features:

- Customer Dashboard: Let your members easily view and manage their account details.
- Membership Levels: Create an unlimited number of membership packages and map with your Paddle products or plans.
- Restrict Contents: Restrict your contents to particular membership levels easily.
- Downloads: Downloadable items are available under the customer’s account page. You can limit access to files based on the plans that customers have.
- Website License Management: If you decide to sell domain based licensing keys. You can let your users register their domains.
- Subscription Upgrades and Downgrades: Customers can move between subscription levels and only pay the difference.
- Emails: Send welcome emails to new members, email payment receipts, and remind members before their account expires automatically.

By upgrading to HandyPlugins PaddlePress PRO you also get access to one-on-one help from our knowledgeable support team and our extensive documentation site.

**[Learn more about PaddlePress Pro](https://handyplugins.co/paddlepress-pro/)**

== WHO IS HANDYPLUGINS PADDLEPRESS PRO FOR? ==

- Website owners who want to sell "members-only" digital content or provide SaaS functionality on their WordPress website. [Learn how to do that](https://handyplugins.co/paddlepress-pro/docs/how-to-set-up-a-membership-website/)
- WordPress developers who want to sell WordPress [plugins](https://handyplugins.co/paddlepress-pro/docs/updater-implementation-for-wordpress-plugins/) or [themes](https://handyplugins.co/paddlepress-pro/docs/updater-implementation-for-wordpress-themes/).
[Checkout](https://handyplugins.co/paddlepress-pro/docs/release-a-new-wordpress-product/) how easy to manage releases with HandyPlugins PaddlePress Pro.


= Contributing & Bug Report =
Bug reports and pull requests are welcome on [Github](https://github.com/HandyPlugins/handyplugins-paddlepress). Some of our features are pro only, please consider before sending PR.

= Documentation =
Our documentation can be found on [https://handyplugins.co/paddlepress-pro/docs/](https://handyplugins.co/paddlepress-pro/docs/)

== Installation ==

= Manual Installation =

1. Upload the entire `/handyplugins-paddlepress` directory to the `/wp-content/plugins/` directory.
2. Activate HandyPlugins PaddlePress through the 'Plugins' menu in WordPress.

== Frequently Asked Questions ==

= Can I accept payments on mobile? =

Yes! We use Paddle [Overlay Checkout](https://developer.paddle.com/guides/how-tos/checkout/paddle-checkout) for a seamless user experience and it works perfectly with any website.

= Which payment methods does Paddle support? =

Since Paddle is the merchant of record for your transactions. It supports almost all popular payment methods. [Learn More](https://paddle.com/support/which-payment-methods-do-you-support/)

= What am I not allowed to sell on Paddle? =

Please read the [Paddle's AUP](https://paddle.com/support/aup/) guide.

= Can I offer coupon codes to my site visitors? =

Absolutely. You just need to setup a coupon code in your Paddle dashboard.

= How can I test Paddle? =

You can test the integration by using [Paddle Sandbox](https://developer.paddle.com/getting-started/sandbox)

== Screenshots ==

1. Plugin Settings
2. Paddle Products
3. Paddle Subscription Plans

== Changelog ==

= 1.6 (December 1, 2022) =
* Update deprecated JS code.
* Tested with WP 6.1
* Small tweaks and improvements

= 1.5 (July 26, 2022) =
* Shortcode improvements. Supports most of the Paddle checkout parameters now.
* Added `paddlepress_button_shortcode` filter.

= 1.4 (May 21, 2022) =
* Add event callback options.
* Small tweaks & improvements.
* Tested with WP 6.0

= 1.3 =
* tested with WP 6.0
* `data-success` attribute added to shortcode for custom redirection upon completion of checkout.
* Small tweaks.

= 1.3 =
* tested with WP 5.9
* `data-success` attribute added to shortcode for custom redirection upon completion of checkout.
* Small tweaks.

= 1.2 =
* tested with WP 5.8
* New Hook: fire `paddlepress_plan_changed` on plan updates
* Small tweaks

= 1.1 =
* Settings UI improvements
* Added: Paddle Sandbox support.
* Fix: Shortcode. Pass user_id and email to Paddle, for logged-in users.

= 1.0 =
* First release

== Upgrade Notice ==

= 1.0 =
* First release
