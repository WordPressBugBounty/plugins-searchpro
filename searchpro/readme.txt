=== BerqWP - Automatic WordPress Website Speed Optimization ===
Contributors: berqwp, thevisionofhamza
Tags: seo, cache, pagespeed, performance, speed
Requires at least: 5.3
Tested up to: 7.0
Stable tag: 4.0.29
Requires PHP: 7.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

All-in-one performance plugin to speed up your website automatically for SEO and Core Web Vitals.

== Description ==

[BerqWP](https://berqwp.com/?utm_source=wordpress-repo) is an **all-in-one WordPress performance plugin** that automatically optimizes your website for 90+ PageSpeed scores on mobile and desktop, passing Core Web Vitals to improve your search engine rankings.

It handles everything including full-page caching, image optimization, LCP preloading and CSS/JS minification so you get a fast website without touching a single line of code.

**No more juggling multiple plugins for caching, image optimization, minification and CDN. BerqWP replaces them all.**

BerqWP offers two optimization methods:

- **Local Optimization (Free):** All optimization runs on your server. No account or license key required.
- **Cloud Optimization (Paid):** Offloads page processing to BerqWP’s servers for additional features like WebP conversion, Fluid Images, Critical CSS, CDN, and Web Vitals Analytics.

## Why Cloud Optimization
On high traffic websites, cache becomes stale fast. Every new post, comment or content edit triggers a cache rebuild which puts heavy load on your server and can slow your site down or take it offline entirely. With Cloud Optimization, all processing happens on BerqWP's servers so your server stays free to do what it does best: serve your visitors.

## Compatible Themes & Plugins

BerqWP works with all WordPress themes and plugins. Here are just a few popular ones:

**Themes:** Astra, GeneratePress, Divi, OceanWP, Kadence, Neve, Blocksy, Hello Elementor, and more.

**Plugins:** Yoast SEO, Rank Math, All in One SEO, WooCommerce, Elementor, Divi Builder, Polylang, WPML, TranslatePress, Cloudflare, Complianz, CookieYes, and more.

## Features

- Works with all WordPress themes and plugins
- All hostings are supported
- SEO-friendly, no changes to your content
- Supports Apache, Nginx, and LiteSpeed web servers
- Sandbox mode to test optimizations without affecting real visitors

## 🔥 Local Optimization (Free):

- **Self-Hosted:** All the page optimization happens on your server

- **Cache Invalidation:** When you make a change on your website, BerqWP automatically detects the change and recreates the cache for that page.

- **Preload LCP:** Automatically detects and preload LCP images.

- **Server-side Cache:** Delivers static cache files directly from your web server without hitting PHP & database, making loading lightning fast. Automatically configures cache rules for Apache, supports Nginx cache as well.

- **Used CSS:** Extracts and inlines only the CSS used on each page, then loads remaining CSS based on your CSS Delivery Method setting. Can be enabled in the CSS & JavaScript tab when using Local Optimization.

- **Speculative API:** BerqWP makes returning visitors' experience instantly fast by using the browser's Speculative API, loading the webpage literally within milliseconds.

- **Sandbox Optimization:** Activate sandbox mode to test BerqWP optimizations without impacting actual website visitors.

- **Lazy Load Images:** Lazy Loading for Images: Load images only when they're in the viewport, allowing other assets to download faster.

- **Lazy Load Embeds:** Load Google Maps, YouTube videos, and other embeds only when a user scrolls to them.

- **JavaScript Optimization**: Load JavaScript after user interaction or make it load asynchronous for faster page rendering.

- **Supports Cloudflare Edge Cache:** BerqWP automatically configures the correct cache rules for your website, delivering page cache through Cloudflare's global network, significantly reducing server response time. Simply connect your Cloudflare account in the plugin settings to get started.

- **Page Cache Rules:** Define rules to automatically flush a page’s cache when a specific post type is updated.

[Learn more about Local Optimization]((https://berqwp.com/help-center/local-optimization/))

## 🔥 Cloud Optimization (Paid):

- **Offload Optimization:** Offload page optimization to BerqWP servers so your server can just chill.

- **Zero Configuration:** BerqWP comes with optimal settings that work perfectly for 99% of users. It requires no configurations.

- **Automatic Cache Warmup:** Keep your cache always primed. Automatically generate new cache to ensure every user experiences fast loading times. Available on BerqWP Cloud.

- **[Fluid Images](https://berqwp.com/fluid-images/):** Automatically serves each image at the right size for the visitor's screen in the best format their browser supports: AVIF, WebP, or original. Delivered via BerqWP's image CDN with full responsive srcset, lazy loading, and zero changes to your original files. Available on BerqWP Cloud.

- **Next-Gen Image Optimization:** Convert images to the efficient WebP format. WebP conversion can reduce image file sizes by up to 85%.

- **Preload Cookie Banner:** Preload the cookie banner as early as possible during page load, even when JavaScript is delayed. Currently supports CookieYes, Real Cookie Banner, CYTRIO, and My Agile Privacy.

- **Critical CSS:** BerqWP generates critical CSS for each page individually, ensuring a faster web experience by loading inline critical CSS.

- **CSS Optimization:** BerqWP prioritizes CSS files without modifying them, preventing the website from breaking.

- **Fonts Optimization:** BerqWP prioritizes your website's fonts, freeing up bandwidth to download other important assets on a slow network connection.

- **BerqWP CDN**: Deliver static files such as images, CSS, JavaScript, and web fonts at lightning speed from our 300 global points of presence (PoPs) for all websites.

- **Web Vitals Analytics:** With BerqWP, you gain access to our Web Vitals Analytics on the BerqWP website. It enables you to track and monitor core web vitals and the website performance experienced by actual visitors.

- **Better Compatibility**: Better compatibility with WordPress themes & plugins, new bug fixes get applied in realtime.

- **Much more!**

Cloud Optimization requires a license key. [Sign up for free plan](https://berqwp.com/free-account/?utm_source=wordpress-repo).

## 👩‍💻 Real User Success Stories
* **★★★★★ [BerqWP beat NitroPack, LiteSpeed Cache, WP Rocket, Perfmatters, FlyingPress….](https://wordpress.org/support/topic/berqwp-beat-nitropack-litespeed-cache-wp-rocket-perfmatters-flyingpress/)**
_"BerqWP beat NitroPack, LiteSpeed Cache, WP Rocket, Perfmatters, FlyingPress, W3 Total Cache, Super Cache and more. On their homepage you can test your site speed and they will show you the expected Page Speed Insights score. In my experience, once BerqWP plugin is installed, your PSI scores will be higher than their estimate. Connect your Cloudflare free account and get Edge Page Caching. Then set to Aggressive mode and enjoy higher scores without changing a single setting. BerqWP is Voodoo Magic!"_

* **★★★★★ [Consistent 100/100 score, Great support](https://wordpress.org/support/topic/consistent-100-100-score-great-support/)**
_"I have tried multiple caching software programs, but none of them were so easy to set up, and the results are incomparable! I have reached out several times to the support. They always respond, sometimes you have to wait (decent time), sometimes within a day they respond and solve your issue instantly. Keep up the good work!"_

* **★★★★★ [Working really great](https://wordpress.org/support/topic/working-really-great/)**
_"Just bought the pro plugin and started using it replacing the 10web io which was great but expensive for me, I was just a little worried whether this one would work ok without conflicting with my current plugins so i tested the free version first and it worked fine. Good customer support as well. I would recommend the pro plugin but try the free one first and then decide."_

* **★★★★★ [A must have](https://wordpress.org/support/topic/a-must-have-532/)**
_"It’s been a while without writing a review from my side, but BerqWP deserves my 5 stars.It’s incredible easy to use, seriously, a newbie could make it work easily. It improves loading times dramatically, to the point that I don’t really understand what’s the magic behind it (I’m not joking). I always thought that a bit more speed could be achieved at my websites, but not this much. I wasn’t aware that the servers that I use for my sites were capable of this speeds, and I always thought that part of the issue was using medium servers instead of top notch ones.Thank you so much guys! Keep doing your magic, I’ll spread the word about you as I really want you to succeed and last forever. Oh, and I really can’t believe how your plugin can exceed that much what other premium plugins achieve (I tried quite a few)."_

* **★★★★★ [Plugin is incredibly easy to use](https://wordpress.org/support/topic/plugin-is-incredibly-easy-to-use/)**
_"The BerqWP plugin is incredibly easy to use, even for those who are not optimization experts. The setup is intuitive, and it works flawlessly, even on websites built with Elementor Pro, without causing any issues. Additionally, their support team is active and consistently responds to inquiries promptly, instilling confidence in the service."_

**[See all reviews](https://wordpress.org/support/plugin/searchpro/reviews/)**

## ✅ Optimization Modes for Flexibility
BerqWP offers four [optimization modes](https://berqwp.com/help-center/berqwp-optimization-modes/):

* **Standard** – Core optimizations including image optimization, page cache, and URL prefetch. Maximum compatibility.
* **Smart** – Asynchronous JavaScript and CSS loading. Highly stable, great for complex sites.
* **Blaze** – Asynchronous CSS with delayed JavaScript. Design-first optimization for high performance with visual stability.
* **Turbo** – Delayed CSS and JavaScript for minimal initial requests and best possible performance.

For best results, we recommend starting with **Turbo mode**, but stepping down to Blaze or Smart can help if compatibility issues arise.


## ⚙️ How to set up BerqWP in 4 simple steps?
1. Ensure that you have deactivated any speed optimization plugins on your website. Then, install and activate the BerqWP plugin.
2. Whitelist our server IP 157.250.205.194 (Only for BerqWP Cloud)
3. Choose your optimization method. Select **Local** for a free, no-account-required setup, or activate your license key to enable **BerqWP Cloud** for the full optimization suite.
4. Relax and take it easy. BerqWP will handle the rest for you.

## 📌 Plugin Support:
We value both BerqWP Free and Premium users. If you encounter any issues, please enable **"Sandbox Mode"** and submit a support ticket. For **BerqWP Premium** users, we have a dedicated support center on our website.

**Get BerqWP today!**

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/searchpro` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Use the BerqWP settings page to configure the plugin.

== Frequently Asked Questions ==

= Why is the plugin slug searchpro when your product is called BerqWP? =
Early in development we registered our plugin under the searchpro namespace on WordPress.org. Unfortunately, WordPress.org doesn’t allow slugs to be renamed once created. Rest assured, this is only a directory name. Every feature, every update, and every bit of our branding is still BerqWP. You won’t see “Search Pro” anywhere in the UI, URLs, or your dashboards after installation.

= How does BerqWP work? =
BerqWP offers two optimization methods. With **Local**, the plugin runs core optimizations directly on your server using PHP, with no account or license needed. With **BerqWP Cloud**, the plugin sends only your page URL and plugin settings to our servers. Our Photon Engine then fetches and processes everything directly using Cloudflare Workers, applies every optimization on our cloud infrastructure, and stores the fully optimized HTML back on your site as cache. Nothing else leaves your server.

= What are the requirements to run BerqWP? =
All you need is a WordPress website running on PHP 7.4 or above.

= Which web servers are supported? =
You can use BerqWP on a WordPress website running on Apache, Nginx, and LiteSpeed web servers.

= Do I get BerqWP CDN on all plans? =
Yes, you can access BerqWP CDN with all of our plans. You can enable or disable the BerqWP CDN from the plugin settings page.

= Does BerqWP CDN support background images? =
Absolutely, BerqWP CDN supports background images and lazy loading as well.

= Can I use any external CDN if I want to? =
Yes, you can switch to any CDN you want. CDN plugins are also supported by BerqWP.

= Does BerqWP work with Cloudflare? =
Absolutely, BerqWP seamlessly works with Cloudflare.

= Will my plan be renewed automatically? =
Yes, your plan will be renewed automatically.

= Can I use the Speculative Loading plugin with BerqWP? =
BerqWP already includes URL prefetching feature provided by WordPress’s Speculative Loading plugin, so there's no need to use Speculative Loading with BerqWP.

= Which hosting providers are supported? =
You can use BerqWP with any hosting provider. If your hosting provider has a built-in speed optimizer, make sure to disable it.

= Are multilingual and e-commerce websites supported? =
Yes, you can use BerqWP for e-commerce and multilingual websites.

= What if I need support? =
No worries, we have a dedicated support team ready to help you with any assistance you may need with BerqWP.

= I’m a free user and need your help. =
Please create a support ticket via https://wordpress.org/support/plugin/searchpro/ so we can assist you.

= Where do I report security bugs found in this plugin? =
Please report security bugs found in the source code of the BerqWP plugin through the [Patchstack Vulnerability Disclosure Program](https://patchstack.com/database/vdp/1f596c21-7f77-46cf-a109-eaf488b31eca). The Patchstack team will assist you with verification, CVE assignment, and notify the developers of this plugin.

== External Services ==

This plugin connects to BerqWP's cloud servers to provide optimization services. These connections only occur when Cloud Optimization mode is enabled with a valid license key.

= BerqWP Cloud (boost.berqwp.com) =
Used for: Cloud-based page rendering and optimization, delivery of optimized HTML cache, CDN, critical CSS generation, and WebP image conversion.
Data sent: Your site URL, page URLs, and a license key hash for authentication.

= BerqWP API (berqwp.com) =
Used for: License key verification and addon synchronization.
Data sent: Your license key and site URL, only when activating or refreshing a license.

- [Terms and Conditions](https://berqwp.com/terms-conditions/)
- [Privacy Policy](https://berqwp.com/privacy-policy/)

== Compatible Themes & Plugins ==

**Themes:** Astra, GeneratePress, Divi, OceanWP, Kadence, Neve, Blocksy, Hello Elementor

**Plugins:** Yoast SEO, Rank Math, All in One SEO, WooCommerce, Elementor, Divi Builder, Polylang, WPML, TranslatePress, Cloudflare, Complianz, CookieYes

== Screenshots ==
1. Hero image
2. Dashboard
3. Cache Management
4. CSS & JavaScript
5. Media Optimization
6. CDN
7. Integration

== Changelog ==

= 4.0.29 =
* [Enhancement] UX improvements.

= 4.0.28 =
* [Enhancement] Added "Flora v2" JavaScript execution mode.

= 4.0.27 =
* [Enhancement] Add cache header for locally hosted static files
* [Bug] Fixed raw gzip being delivered on Apache.

= 4.0.26 =
* [Enhancement] Used CSS is now available for local optimization mode
* [Enhancement] Review banner now shows for all users
* [Enhancement] Email field on intro page is now optional
* [Security] Added nonce sanitization across all admin actions
* [Security] Added capability check to optimized pages AJAX handler
* [Security] Fixed unsanitized $_SERVER, $_GET, and $_POST inputs
* [Security] Replaced json_encode() with wp_json_encode() in API endpoints
* [Bug] Fixed empty endpoint in batch cache request handler

= 4.0.25 =
* [Enhancement] UI improvements
* [Bug] Fixed license activation bug on multi site network

= 4.0.23 =
* [Bug] Fixed too few arguments error on menu update.

= 4.0.22 =
* [Enhancement] Better CDN assets handling
* [Enhancement] Flush site cache on update menus
* [Enhancement] Flush CDN and critical css on flush elementor cache

= 4.0.21 =
* [Bug] Fixed Composer version conflict with other plugins.

= 4.0.19 =
* [Bug] Fixed undefined function wp_mkdir_p() error for Hostinger.

= 4.0.18 =
* [Enhancement] Added "Recently Optimized Pages" to the plugin dashboard.
* [Enhancement] Improved custom post type detection.
* [Bug] Scoped Composer packages to avoid conflicts with other plugins.

= 4.0.17 =
* [Bug] Fixed all page cache files getting deleted when attempt to flush a single page.

= 4.0.16 =
* [Bug] Prevent adding htaccess cache rules for LiteSpeed.

= 4.0.15 =
* [New Feature] Introduced Local Optimization method: run BerqWP entirely on your own server for free, with no account or license key required. Includes full-page caching, lazy loading, LCP preloading, image dimension attributes, URL prefetching, prerender on hover, and font optimization.
* [New Feature] Added BerqWP Cloud optimization method for the full premium feature suite including critical CSS, WebP conversion, Fluid Images, BerqWP CDN, JavaScript execution modes, and Web Vitals Analytics.
* [New Feature] Added Prerender Page on Link Hover using the browser's Speculation Rules API.
* [Enhancement] Renamed optimization modes: Basic is now Standard, Medium is now Smart, Aggressive is now Turbo.
* [Enhancement] Server-side cache delivery on Apache via automatic .htaccess rewrite rules, serving cached pages before PHP loads.
* [Enhancement] New cache file path structure: host/path/index.html.gz for improved compatibility and clarity.

= 3.1.21 =
* [Enhancement] Reduced file reads.
* [Enhancement] Better error handling & error logging.

= 3.1.19 =
* [Enhancement] Added retry timeout for pages in the cache queue.
* [Bug] Fixed an issue where pages in the queue could get stuck.
* [Bug] Fixed missing array key warnings in PHP error logs.
* Code refactoring.

= 3.1.18 =
* [Enhancement] Added compatiblity with multisite network.
* [Bug] Fixed critical error on save settings.
* [Bug] Now uses file based locking for heartbeat.

= 3.1.17 =
* [Bug] Fixed pages not being added to the queue when the YoastSEO "Disable Query Parameters" feature is enabled.
* [Enhancement] Skip page cache for page URLs with redirections.
* [Enhancement] Added compatibility with StackCDN.

= 3.1.15 =
* [Bug] Fixed force cache not working when a page is already in queue list.

= 3.1.14 =
* [Enhancement] Skip external files when uploading assets to the BerqWP server.
* [Enhancement] Replaced gzopen with gzdecode to ensure maximum compatibility across different hosting environments.

= 3.1.13 =
* [Enhancement] Use static cache when reading the config file.
* [Bug] Fixed cache warmup not working with non-ASCII asset file paths.

= 3.1.12 =
* [Enhancement] Auto refresh home cache when a scheduled post is published.
* [Enhancement] Retry page assets uploading if it failes.
* [Enhancement] Use Guzzle for license key verification requests resolving verification failure on some web hosts.

= 3.1.11 =
* [Bug] Fixed partial uploads of static files by verifying uploads using file hashes.
* [Bug] Fixed broken images.
* [Bug] Fixed an issue where new content changes were not reflected.

= 3.0.19 =
* [Bug] Fixed an issue where cache was not being served without the advanced-cache drop-in plugin.

= 3.0.18 =
* [Bug] Fixed a CSS issue caused by Hide My WP URL rewrite.

= 3.0.17 =
* [Bug] Fixed an issue where CSS files were not being uploaded to the Photon Engine.

= 3.0.16 =
* [Bug] Fixed 403 cache being stored.

= 3.0.15 =
* [Enhancement] Replaced CURL with Guzzle for multipart file upload.

= 3.0.14 =
* [Enhancement] Reset uploaded assets cache when cache warmup is triggered.

= 3.0.12 =
* [Bug] Fixed pages getting failed during cache warmup.

= 3.0.11 =
* [Bug] Fixed HTML download failure during cache warmup.

= 3.0.05 =
* [Enhancement] Improved handling of locally stored external static files.

= 3.0.04 =
* [Bug] Fixed exclude CSS & JS not working.

= 3.0.03 =
* [Bug] Avoid optimization for error pages.

= 3.0.02 =
* [Bug] Fixed sandbox mode not working.

= 3.0.01 =
* [Enhancement] Introduced a new plugin-to-server request architecture to fully resolve server-related issues.
* [Enhancement] Added a dedicated CDN tab with a CDN exclusion option.
* [Bug] Fixed a fatal error caused by duplicate Guzzle instances.

= 2.2.63 =
* [Bug] Fixed issue where partial cached pages were going blank.
* [Bug] Corrected typo in initialize.php (site_url → site_id).
* [Bug] Improved prevention of BerqWP config file reset.

= 2.2.62 =
* [Bug] Fixed the plugin checking for simple_html_dom.php inside the plugin directory.

= 2.2.61 =
* [Bug] Fixed an issue where the config file could reset under stressful server conditions.
* [Bug] Resolved a conflict with the HTMLSimpleDOM library.

= 2.2.59 =
* [Enhancement] Added support for pages with the .htm extension.
* [Bug] Fixed empty cache being stored.

= 2.2.58 =
* [Enhancement] Added support for pages with the .html extension.
* [Bug] Fixed Fixed an issue where the Flush and Force Cache links were not appearing for post types other than "post".

= 2.2.57 =
* [Bug] Fixed cache storage failure on websites with a low PHP post data limit.

= 2.2.56 =
* [New Feature] Added cache rules for single posts to allow automatic cache flushing.
* [New Feature] Added option to force cache for any page.
* [New Feature] Added manual cache warmup via the admin bar.
* [Enhancement] Improved integration with Yoast SEO URL cleanup feature.
* [Bug] Fixed Polylang language switch issue.

= 2.2.55 =
* [Bug] Fixed false "Connection Blocked" warnings during server maintenance.

= 2.2.54 =
* [Enhancement] Added compatibility with the Complianz plugin.
* [Bug] Fixed an issue causing unwanted automatic WooCommerce product cache flushes.

= 2.2.53 =
* [Bug] Prevent flushing homepage cache when auto-saving post drafts.
* [Bug] Fixed false "connection block" warnings.

= 2.2.52 =
* [Bug] Fixed an issue where the page cache refresh was not triggering after a post update.

= 2.2.51 =
* [Enhancement] Increased license key cache lifespan to one month.
* [Bug] Prevented overwriting of advanced-cache.php file if it's not writable.

= 2.2.49 =
* [Bug] Fixed PHP filemtime warnings.

= 2.2.48 =
* [Bug] Disable Guzzle HTTP errors.

= 2.2.46 =
* [Bug] Fixed fluid images not working issue.

= 2.2.45 =
* [New Feature] Added Fluid Images.
* [Enhancement] Replaced custom HTTP library with Guzzle.
* [Enhancement] Improved compatibility with Cloudflare Edge Cache.
* [Enhancement] Cleared cache queue upon plugin/license deactivation to avoid unwanted requests.
* [Bug] Fixed a license key verification bug.

= 2.2.42 =
* [Enhancement] Added compatibility with the WooCommerce JTL Connector plugin.
* [Bug] Fixed unexpected error upon plugin activation.

= 2.2.41 =
* [Enhancement] Instant cache files are now delivered using advanced cache, drastically improving loading times.
* [Enhancement] Added compatibility with WP All Import automatic scheduling.
* [Bug] Resolved request timeout issue when updating menus.
* [Bug] Fixed compatibility issue with Pressable that prevented the creation of the advanced-cache.php file.
* [Other] Removed unnecessary files.

= 2.2.38 =
* [Bug] Hide the "wp-config.php not writable" notice if WP_CACHE is already defined.

= 2.2.37 =
* [Bug] Fixed deprecated PHP warning message.

= 2.2.35 =
* [Enhancement] Added wildcard cache exclusion using asterisk (*).
* [Enhancement] Flora is now the default JavaScript execution mode.
* [Bug] Fixed issue with unnecessarily large log files.

= 2.2.34 =
* [Bug] Fixed cache warmup issue for pages with redirects.
* [Bug] Fixed issue where Cloudflare Edge Cache rules were not deleted when the plugin was deactivated.

= 2.2.33 =
* [New Feature] Added "Max Cache Lifespan" setting.
* [New Feature] Added toggle setting for page compression.
* [New Feature] Introduced a new JavaScript execution mode called "Flora".
* [Enhancement] Implemented hierarchical category purge, now all parent categories are purged when a post is updated.
* [Enhancement] Author archive pages are now purged when their posts are updated.

= 2.2.31 =
* [Bug] Fixed JavaScript-related bugs with BerqWP Instant Cache by completely disabling JavaScript optimization.

= 2.2.29 =
* [Enhancement] Purge the homepage cache when a new WooCommerce product is published.

= 2.2.28 =
* [Enhancement] Increase nonce lifespan on for BerqWP cache requests.
* [Enhancement] Better image handling for webp images.

= 2.2.26 =
* [Bug] Fixed permission issue at post preview page.

= 2.2.25 =
* [Bug] Fixed an issue where the product category cache did not flush after adding a new product.

= 2.2.24 =
* [Bug] Fixed an issue in WooCommerce where the cache for all products was being flushed incorrectly after a product sale ends.

= 2.2.23 =
* [Bug] Fixed an issue where the wp-config.php file could become empty on Azure servers.

= 2.2.22 =
* [Enhancement] Completely disabled JavaScript optimization for instant cache to ensure maximum compatibility.
* [Bug] Resolved an issue with invalid page links during cache warmup.

= 2.2.21 =
* [Bug] Fixed a bug where the product cache does not flush after a product sale ends.

= 2.2.19 =
* [Enhancement] Increased cache lifespan to 30 days.

= 2.2.18 =
* [Enhancement] UI improvements.
* [Enhancement] Removed unwanted admin notices on the BerqWP settings page.
* [Enhancement] Automatically purge cache for taxonomies when a post is updated.
* [Enhancement] Automatically purge WooCommerce product cache when stock or sale status changes.
* [Enhancement] Automatically purge TranslatePress translation pages when the main page is purged.

= 2.2.17 =
* [Bug] Fixed license key revoke bug.

= 2.2.16 =
* [Bug] Fixed error caused by Cloudflare flush cache.

= 2.2.15 =
* [New Feature] Added Cloudflare Edge Cache support.
* [New Feature] Added preload video poster for YouTube embeds.
* [Enhancement] Auto purge homepage cache when a new post is published.
* [Enhancement] Added support for the Filter Everything plugin; disabled cache for all filter URLs.
* [Enhancement] Improved compatibility with reverse proxy cache.
* [Enhancement] Removed "Defer JavaScript" from the JavaScript execution mode setting.
* [Bug] Fixed issue with translated page URLs.
* [Bug] Disabled JavaScript and CSS optimization for partial cache to ensure maximum compatibility.
* [Bug] Fixed bug where sitemap was being cached by BerqWP.

= 2.2.14 =
* [Bug] Fixed a fatal error when updating the JS/CSS exclude value.

= 2.2.13 =
* [Bug] Fixed a fatal error caused by missing options during a fresh installation.

= 2.2.11 =
* [New Feature] Added a new JavaScript execution mode: "Parallel execution".
* [Enhancement] Improved cache refresh using a fetch request, resulting in a better cache hit rate.
* [Bug] Resolved an issue on multilingual sites where the wrong cache file was being delivered for specific languages.
* [Bug] Fixed an issue where optimizations were not reflected in WebPageTest.org reports.
* [Bug] Addressed high server resource usage by disabling cache generation for unknown query parameters.
* [Bug] Fixed an issue where logged-in users were served cached pages.

= 2.1.8 =
* [New Feature] Added a button to refresh license key details.
* [Enhancement] Replaced transients with options to avoid unnecessary license key verification requests.

= 2.1.7 =
* [Bug] Fixed issue with purge page not working on multilingual websites.
* [Bug] Removed unnecessary license verification requests.

= 2.1.6 =
* [Bug] Resolved an issue where the cache was not working after adding items to the cart.

= 2.1.5 =
* [New Feature] Added a setting to configure CSS loading.
* [New Feature] Added a setting to configure JavaScript loading.
* [New Feature] Added an option to preload the cookie banner.
* [New Feature] Users can now exclude specific cookie IDs to bypass cache for them.
* [Bug Fix] Resolved issues with license key verification.
* [Bug Fix] Fixed a compatibility issue with TranslatePress where optimized pages were not displaying on the plugin settings page.

= 2.1.4 =
* [Enhancement] Added an option to enable/disable Core Web Vitals tracking.
* [Enhancement] Improved compatibility with Polylang and TranslatePress.
* [Bug] Fixed an issue where the cache was not refreshing.

= 2.1.3 =
* [Bug] Fixed undefined function error with Instant Cache.
* [Bug] Fixed a fatal error that occurred when the total number of pages was zero.

= 2.1.2 =
* [Enhancement] More efficient cache warmup by offloading the process, removing the WP cron dependency.
* [Enhancement] BerqWP now stores cache using a webhook, eliminating the REST API dependency.
* [Enhancement] Automatic purge of critical CSS for a page when its content is updated.
* [Enhancement] Automatic purge of WooCommerce product cache when stock changes.
* [Enhancement] Integration with Elementor & Divi animations for initial page load.
* [Enhancement] Whitelabel capabilities for the plugin settings pages.
* [Enhancement] Request new cache for the page before it expires, ensuring visitors are always served fully optimized cache.
* [Enhancement] Added rate limiting for cache requests and license verification requests.
* [Enhancement] Multisite network integration.

= 1.9.91 =
* [Bug] Fixed weird characters appearing with LiteSpeed server.
* [Bug] Fixed JavaScript not loading with partial cache.


= 1.9.9 =
* [Enhancement] Offloaded cache pages list using AJAX.
* [Bug] Fixed critical error when accessing the BerqWP dashboard.
* [Bug] Fixed WooCommerce product gallery slider not working.
* [Bug] Fixed active PHP session warning in Site Health.
* [Bug] Fixed critical CSS not automatically invalidating cache.
* [Bug] Fixed improper image tag dimensions in the slider.
* [Bug] Fixed broken images on initial page load.

= 1.9.7 =
* [Enhancement] Improved compatibility with the WP Social Ninja plugin.
* [Enhancement] Improved compatibility with the Green Popups plugin.
* [Enhancement] Improved compatibility with the Salient theme.
* [Bug] Fixed an issue with unoptimized pages in Pingdom speed tests.

= 1.9.6 =
* [Enhancement] Better compatibility with Elementor.
* [Enhancement] Better compatibility with Hide My WP Ghost plugin.

= 1.9.5 =
* [New Feature] Users can now purge CDN and critical CSS directly from the admin bar.
* [Enhancement] Disabled cache for web crawlers.
* [Bug] Prevented pages with redirection from being cached.

= 1.9.4 =
* [Bug] Do not require the SimpleHTMLDom library if it is already loaded.

= 1.9.3 =
* [New Feature] Added Instant Cache, allowing BerqWP to generate partially optimized cache instantly.
* [Enhancement] Improved integration with Pagely, Pantheon, and Pressable caching systems.
* [Enhancement] Added an admin notice if the REST API is disabled.

= 1.9.2 =
* [Bug] Fixed high CPU usage caused by duplicate cache warmup requests.

= 1.9.1 =
* [Bug] Fixed issue where Varnish cache was not purging when flushing BerqWP cache.

= 1.8.9 =
* [Enhancement] Added support for reverse proxy cache.

= 1.8.8 =
* [Enhancement] Added compatibility for Hide My WP Ghost plugin.

= 1.8.7 =
* [Bug] Fixed license key verification bug.

= 1.8.6 =
* [Bug] Fixed slow cache generation issue.

= 1.8.5 =
* [Enhancement] Added a request cache link in the admin bar to force cache for the current page.
* [Improvement] Improved support for cache invalidation.

= 1.8.4 =
* [Bug] Fixed support for LiteSpeed web server.

= 1.8.3 =
* [Bug] Fixed memory exhausted error on the plugin settings page.

= 1.8.2 =
* [Enhancement] Added a progress bar showing the percentage of pages that have been cached, and a list of cached page URLs.
* [Enhancement] Added an error notification if the REST API is disabled using the Admin Site Enhancements plugin.
* [Improvement] UI improvements.
* [Improvement] Switched default optimization mode to "Medium".
* [Improvement] Replaced before & after PageSpeed score comparison with PageSpeed mobile & desktop scores.

= 1.8.1 =
* [Improvement] Enhanced optimization for core web vitals.
* [Improvement] Decreased cache lifespan to a maximum of 10 hours.
* [Improvement] Font preloading is now enabled by default.
* [Bug] Fixed broken CDN images.

= 1.7.8 =
* [Improvement] Up to 80% faster loading for cached pages.
* [Improvement] Better support for LiteSpeed hosting.

= 1.7.7 =
* [Improvement] Better security, secured API requests.

= 1.7.6 =
* [Improvement] Enhanced compatibility with built-in caching systems on SiteGround, Cloudways, and WPEngine.

= 1.7.5 =
* [Bug] Fixed issue causing blank page cache for gzip-compressed files.

= 1.7.4 =
* [Bug] Fixed blank cache delivery on LiteSpeed webserver.

= 1.7.2 =
* [Enhancement] Added GZip compression for cache files, reducing cache file size by up to 70% and improving server response time.
* [Enhancement] Added cache content types, allowing users to include/exclude post types and taxonomies from the cache.
* [Improvement] Added a fallback function in case the drop-in plugin isn't working or WP_CACHE isn't set to true.
* [Improvement] Added support for Varnish cache.
* Bug fixes.

= 1.7.1 =
* [Improvement] Updated cache batch size.

= 1.6.9 =
* [Bug] Fix conflict with ShortPixels plugin.

= 1.6.8 =
* [Enhancement] Added optimization modes.
* [Enhancement] Added compatibility with the Nginx Helper plugin.
* [Enhancement] Added logs.
* [Enhancement] Added webpage URL prefetch.
* [Improvement] Reduced the usage of the WordPress options table.
* [Improvement] Enhanced cache warmup functionality.
* [Improvement] Added support for the "data:" image URL scheme.
* [Improvement] Enhanced compatibility for browsers with JavaScript disabled via the <noscript> tag.
* [Improvement] Cleaned BerqWP options upon uninstallation.
* [Bug] Fixed background images not loading upon initial page load.
* [Bug] Fixed duplicate WP_Cache define function in wp-config.php.
* [Bug] Fixed broken WebP images when an image URL has a duplicate file extension in the filename.
* [Bug] Fixed issue where Ignore params were not working.

= 1.6.7 =
* Added user agent for cache warmup requests.
* Fixed license key deactivate bug.

= 1.6.6 =
* Minor improvements for cache warmup.

= 1.6.5 =
* Implemented parallel processing for cache warmup.
* Added a toggle for enabling/disabling BerqWP CDN.
* Added a toggle for WebP image generation.
* Users can now exclude any external JavaScript & CSS files from optimization.
* Added a toggle to preload font face upon the initial page load.

= 1.6.4 =
* Added flush cache support for object cache.

= 1.6.3 =
* BerqWP now caches external JavaScript files.
* Improved user interface.
* Added a Dropin plugin for delivering the cache.

= 1.6.2 =
* Premium users now have the ability to set a click and define the time to trigger the click after the page loads.
* Enhanced cache delivery speed for improved performance.
* Added browser cache for optimized user experience.

= 1.5.8 =
* Now you can activate the BerqWP license key on your multisite network by adding `define("BERQWP_LICENSE_KEY", "Enter your license key here");` in your wp-config.php file of the parent website. The license key will be activated on all sites in the network.
* Fixed duplicates for the Page Exclusion list.
* Automatically disable advanced-cache.php left by other speed optimization plugins.
* Made improvements for Oxygen builder.

= 1.5.7 =
* Added compatibility for subdirectory WordPress installations.
* Enabled JavaScript optimization exclusively for home pages in the free version.

= 1.5.6 =
* Made improvements in caching initialization so that BerqWP can now function even without WP cron.
* Added some more speed optimization plugins to BerqWP's plugin conflict list.

= 1.5.5 =
* Fixed compatibility issues with the Avada theme.
* Now, BerqWP can detect and optimize image URLs added as the attribute value of div, span, and section tags.

= 1.5.4 =
* Added some more speed optimization plugins to BerqWP's plugin conflict list.
* BerqWP can now lazy load image srcset as well.
* Improved CSS optimization method for more sustainable results.

= 1.5.3 =
* Changed the hook for delivering cache from the `template_redirect` hook to the `wp` hook.
* Added some more speed optimization plugins to BerqWP's plugin conflict list.

= 1.5.2 =
* BerqWP plugin is now translation ready.

= 1.5.1 =
* Removed unused code and files.

= 1.4.36 =
* Fixed Issue with WebP Image srcset: The WebP srcset was previously blocked by license key verification. Now, you get WebP URLs for image srcset even without activating the license key.

= 1.4.3 =
* Improvement In Cache Invalidation: BerqWP can now detect dynamic parts of HTML that change on every refresh and can ignore them, enhancing the cache invalidation process.
* Improvement In Image Lazy Loading: BerqWP no longer uses an old-school loader GIF as an image placeholder. Now, BerqWP generates a low-quality blurred image that serves as a placeholder, providing a better user experience.
* Improvement In CLS: BerqWP can now add width and height attributes for images that don’t have them, contributing to improved Cumulative Layout Shift (CLS).
* Added WooCommerce Cart and Checkout Page URLs to the Cache Excluded List: WooCommerce cart and checkout page URLs have been added to the cache excluded list, ensuring a smooth shopping experience.
* Added Trailing Slashes for Cache Excluded URLs: Trailing slashes have been added for cache excluded URLs, aligning with URL formatting best practices.
* Moved BerqWP Review Notification to BerqWP Admin Page Only: The BerqWP review notification has been relocated to the BerqWP admin page exclusively, reducing user interruptions.

= 1.4.2 =
* Added page exclusions for caching.
* Added option to ignore URL parameters for caching.
* Removed caching for logged-in users.
* Removed caching lifespan.
* Resolved the issue with the Gutenberg image block "Click to expand" feature.

= 1.4.1 =
* Combined BerqWP Lite & Premium.
* Integrated the Photon Engine for cloud based optimization.
* BerqWP has become a 100% automatic speed optimization tool.
* Automatically converts images into WebP.
* Fixed bugs.

= 1.3.58 =
* Fixed some bugs.

= 1.3.57 =
* Fixed some bugs.
* Prevented 404 pages from being cached.

= 1.3.56 =
* Added support for Jetpack plugin.

= 1.3.55 =
* Added WebP support for hosts or servers that previously lacked compatibility.

= 1.3.54 =
* Fixed bugs.

= 1.3.53 =
* Added interactions-based styles loading.

= 1.3.52 =
* Added automatic cleaning for BerqWP scheduled tasks.

= 1.3.51 =
* Enhanced JavaScript-based lazy loading for images.
* Added placeholder image for lazy loading.

= 1.3.5 =
* Added JavaScript-based lazy loading for images.
* Fixed bugs.

= 1.3.4 =
* Enhanced the LCP mechanism. Now, BerqWP preloads LCP separately for mobile and desktop.

= 1.3.3 =
* Enhance WebP Images
* Fixed bugs

= 1.3.2 =
* Updated layout
* Enhance WebP images
* Added review notification

= 1.3.1 =
* Fixed a bug regarding WebP images.

= 1.3 =
* BerqWP Lite initial release.
* SearchPro plugin temporarily switch with BerqWP Lite.
