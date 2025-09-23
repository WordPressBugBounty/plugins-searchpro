=== BerqWP - Automated All-In-One Page Speed Optimization for Core Web Vitals, Cache, CDN, Images, CSS, and JavaScript ===
Contributors: berqwp, thevisionofhamza, berqier
Tags: core web vitals, cache, cdn, critical css, speed
Requires at least: 5.3
Tested up to: 6.8
Stable tag: 2.2.55
Requires PHP: 7.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Automatically boost your PageSpeed score to 90+ for both mobile & desktop and pass Core Web Vitals for WordPress website without any technical skills.

== Description ==

**Eliminate the Need for Multiple Plugins and Automate Performance Optimization Effortlessly.**

[BerqWP](https://berqwp.com/?utm_source=wordpress-repo) is a 100% automatic **All-In-One speed optimization plugin** that ensures your website passes the core web vitals assessment and boosts your website speed score to 90+ for mobile and desktop devices.

BerqWP automatically applies modern speed optimization techniques recommended by [Google (web.dev)](https://web.dev/performance), so your customers and visitors can have the best hassle-free experience.

Since all popular page speed testing tools use similar methods, you'll get the same results across tools like [Google PageSpeed Insights](https://pagespeed.web.dev/), [GTmetrix](https://gtmetrix.com/), [Pingdom](https://tools.pingdom.com/), and others.

## 💙 Why do people love BerqWP?

- **Automatically applies Google ([web.dev](https://web.dev/performance)) recommended optimizations to your website.**
- **100% automatic (no configuration needed)**
- **100% SEO friendly, no black hat**
- **Drastically improve Core Web Vitals metrices**
- **90+ speed score for mobile & desktop**
- **Comes with CDN integrated**
- **Monitor Core Web Vitals in real-time with Web Vitals Analytics**
- **Works with all popular WordPress themes and plugins** 
- **Doesn’t break your website**
- **Built for non-techies**
- **Get instant support with BerqWP's AI assistant**

## 🎯 Real World Results
- **88.6% of BerqWP-optimized websites have an average loading time below 3 seconds.**
- **BerqWP-optimized websites have an average bounce rate of 4.78% on mobile and 6.18% on desktop.**

## 🔥 Features:
- **Zero Configuration:** BerqWP comes with optimal settings that work perfectly for 99% of users. It requires no configurations.

- **Automatic Cache Warmup:** Keep your cache always primed. Automatically generate new cache to ensure every user experiences fast loading times.

- **Fluid Images:** Automatically delivers container-sized, retina-ready WebP images via BerqWP CDN to boost your site speed and improve user experience across all devices.

- **Instant Cache:** BerqWP instantly creates a basic optimized cache, while the fully optimized cache is generated in the background on our cloud servers.

- **Cache Invalidation:** When you make a change on your website, BerqWP automatically detects the change and recreates the cache for that page.

- **Speculative API:** BerqWP makes returning visitors' experience instantly fast by using the browser's Speculative API, loading the webpage literally within milliseconds.

- **Next-Gen Image Optimization:** Convert images to the efficient WebP format. WebP conversion can reduce image file sizes by up to 85%.

- **Sandbox Optimization:** Activate sandbox mode to test BerqWP optimizations without impacting actual website visitors.

- **Lazy Load Images:** Lazy Loading for Images: Load images only when they're in the viewport, allowing other assets to download faster.

- **Lazy Load Embeds:** Load Google Maps, YouTube videos, and other embeds only when a user scrolls to them.

- **Preload Cookie Banner:** Preload the cookie banner as early as possible during page load, even when JavaScript is delayed. Currently supports CookieYes, Real Cookie Banner, CYTRIO, and My Agile Privacy.

- **Critical CSS:** BerqWP generates critical CSS for each page individually, ensuring a faster web experience by loading inline critical CSS.

- **CSS Optimization:** BerqWP prioritizes CSS files without modifying them, preventing the website from breaking.

- **Fonts Optimization:** BerqWP prioritizes your website's fonts, freeing up bandwidth to download other important assets on a slow network connection.

- **BerqWP CDN**: Deliver static files such as images, CSS, JavaScript, and web fonts at lightning speed from our 300 global points of presence (PoPs) for all websites.

- **JavaScript Optimization**: BerqWP offers specialized JavaScript optimization features, including JS delay and JS defer. It provides three distinct JavaScript optimization modes to address Core Web Vitals issues, such as render blocking. BerqWP ensures that your website not only passes Core Web Vitals but also achieves a speed score of 90+ for both mobile and desktop.

- **Web Vitals Analytics:** With BerqWP, you gain access to our Web Vitals Analytics on the BerqWP website. It enables you to track and monitor core web vitals and the website performance experienced by actual visitors.

- **Supports Cloudflare Edge Cache:** BerqWP automatically configures the correct cache rules for your website, delivering page cache through Cloudflare's global network, significantly reducing server response time. Simply connect your Cloudflare account in the plugin settings to get started.

- **Much more!**

**[Get a free license key](https://berqwp.com/?utm_source=wordpress-repo) with a BerqWP free account and access all BerqWP Premium features for up to 10 pages.**



## How Does BerqWP Work?
Unlike traditional speed optimization plugins, BerqWP optimizes your website using our proprietary optimization software called the **Photon Engine**. Instead of handling resource-intensive tasks on your web server, which can slow it down, BerqWP processes optimizations externally. This is crucial because heavy tasks like **critical CSS generation, WebP conversion, and JavaScript optimization** can overwhelm ordinary servers when performed at scale.

Here’s how it works:

* When a webpage is added to the **Photon Engine** queue, BerqWP fetches the page’s HTML.
* Various optimizations are applied, including HTML minification, image conversion, lazy loading, and JavaScript execution control.
* Once optimized, the Photon Engine stores the **fully optimized HTML** in your website as a form of cache.
* To handle traffic before optimization is complete, BerqWP creates an **instant cache**, a placeholder cache with minimal optimizations, ensuring fast page loads even before full optimization.

## ✅ Advanced Cache for Faster Server Response Time
BerqWP utilizes the WordPress **advanced-cache.php** file to deliver cached pages as fast as possible. By bypassing unnecessary database queries and reducing the number of files loaded on each request, BerqWP significantly improves **server response time (TTFB – Time to First Byte)**, ensuring that your pages load within milliseconds.

## ✅ Core Web Vitals Optimizations
BerqWP is designed to help websites achieve high scores on **Google’s Core Web Vitals**, improving user experience and SEO rankings.

**Largest Contentful Paint (LCP) Optimization** 

* **Image Preloading:** BerqWP detects the LCP element (usually an image or banner) and preloads it to ensure it loads as quickly as possible.
* **WebP Conversion:** All images are converted into **WebP format**, reducing file size by **70% – 85%** while maintaining quality.
* **Efficient Lazy Loading:** Images and background images only load when users scroll, reducing initial load time.

**Interaction to Next Paint (INP) & JavaScript Optimization**

* **JavaScript Delay:** BerqWP delays JavaScript execution until user interaction (mouse move, click, scroll, or tap), improving initial page load speed.
* **Multiple Execution Modes:** Choose between different JavaScript execution modes tailored to your website’s needs.

**Cumulative Layout Shift (CLS) Fixes**

* **Image Dimension Attributes:** BerqWP ensures all images have width and height attributes, preventing unexpected layout shifts.
* **Font Optimization:** Fonts are loaded efficiently with font-display: swap to eliminate render-blocking issues.

## ✅ Critical CSS for Instant Rendering
Loading external stylesheets can delay rendering, so BerqWP generates **Critical CSS** for each page separately.

* The Critical CSS contains only the styles needed for the visible portion of the page.
* The remaining CSS loads **asynchronously**, ensuring fast page rendering without affecting layout or design.


## ✅ BerqWP CDN – Global Performance Boost
BerqWP integrates with **Cloudflare CDN** to ensure static assets (images, CSS, JavaScript, web fonts) load from the nearest server to the visitor’s location. This results in:

* **Reduced latency** and improved website performance globally.
* Faster **Time to First Byte (TTFB)** for all users.

## ✅ Font Optimization for CLS Stability
* BerqWP **extracts and preloads** fonts used in above-the-fold content, ensuring faster font rendering.
* Uses font-display: swap to **prevent text from disappearing while fonts load**, improving user experience.

## ✅ Optimization Modes for Flexibility
BerqWP offers four optimization modes:

* **Basic** – Minimal optimizations, ensuring stability.
* **Medium** – A balance between performance and compatibility.
* **Blaze** – A balance between performance and User Experience.
* **Aggressive** – Best for high-speed results

For best results, we recommend using **Aggressive mode**, but switching to a different mode can help if compatibility issues arise.


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


## ⚙️ How to set up BerqWP in 3 Steps?
1. Ensure that you have deactivated any speed optimization plugins on your website. Then, install and activate the BerqWP plugin.
2. Activate the license key.
3. Relax and take it easy. Our automatic cache warm-up mechanism will handle the rest for you.

## 📌 Plugin Support:
We value both BerqWP Free and Premium users. If you encounter any issues, please enable **"Sandbox Mode"** and submit a support ticket. For **BerqWP Premium** users, we have a dedicated support center on our website.

**Get BerqWP today!**

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/searchpro` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Use the BerqWP settings page to configure the plugin.

== Frequently Asked Questions ==

= Why is the plugin slug searchpro when your product is called BerqWP? =
Early in development we registered our plugin under the searchpro namespace on WordPress.org. Unfortunately, WordPress.org doesn’t allow slugs to be renamed once created. Rest assured, this is only a directory name—every feature, every update, and every bit of our branding is still BerqWP. You won’t see “Search Pro” anywhere in the UI, URLs, or your dashboards after installation.

= How does BerqWP work? =
BerqWP sends your website’s pages to our Photon Engine, where we optimize them and store optimized copies on your website as cache. This means BerqWP doesn’t use your server’s resources for optimization.

= What are the requirements to run BerqWP? =
All you need is a WordPress website running on PHP 7.4 or above.

= What will I get in the free version? =
You'll get access to all BerqWP Premium features for up to 10 pages.

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

= I’m a free account user and need your help. =
Please create a support ticket via https://wordpress.org/support/plugin/searchpro/ so we can assist you.

== Screenshots ==
1. Plugin settings page.
2. Exceptional website performance results.
3. BerqWP speed optimization results comparison.
4. All modern speed optimization features.
5. Unnoticeable Image Optimization.
6. Monitor core web vitals in real time with Web Vitals Analytics.

== Changelog ==

= 2.2.55 =
* [Bug] Fixed false “Connection Blocked” warnings during server maintenance.

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