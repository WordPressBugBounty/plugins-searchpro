<?php
if (!defined('ABSPATH')) exit;

use BerqWP\Cache;
use voku\helper\HtmlDomParser;

class berqPageOptimizer {
    public $page_slug = null;
    public $page_url = null;
    public $settings = null;

    function __construct() {

        if ($this->settings === null) {
            $this->settings = berqwp_get_page_params(home_url());
        }

        add_filter('berqwp_photon_before_closing_body', [$this, 'img_lazy_load_js']);
        add_filter('berqwp_photon_before_closing_body', [$this, 'video_lazy_load_js']);
        add_filter('berqwp_photon_before_closing_body', [$this, 'iframe_lazy_load_js']);
        add_filter('berqwp_photon_before_closing_body', [$this, 'dynamic_js_loading_script']);
        add_filter('berqwp_photon_before_closing_body', [$this, 'dynamic_css_loading_script']);
        add_filter('berqwp_photon_before_closing_body', [$this, 'prerender_on_hover']);

    }

    function start_cache() {
        add_action('template_redirect', [$this, 'buffer_start'], 2);
    }

    function set_slug($slug) {
        $this->page_slug = $slug;
    }

    function set_page($page_url) {
        $this->page_url = $page_url;
    }


    function buffer_start() {
        ob_start([$this, 'buffer_end']);
    }

    function prerender_on_hover($script) {

        if ($this->settings['prerender_link']) {
            $script .= "
            <script id='prefetch-links' defer>

                function berq_prerender(url) {
                const s = document.createElement('script');
                s.type = 'speculationrules';
                s.textContent = JSON.stringify({
                    prerender: [{ urls: [url] }]
                });
                document.head.appendChild(s);
                }

                // Set to keep track of prefetched links
                const prefetchedLinks = new Set();

                // Get all anchor tags on the page
                const links = document.querySelectorAll('a:not([data-price-key])');

                // Loop through each anchor tag
                links.forEach(link => {
                    // Add mouseover event listener
                    link.addEventListener('mouseover', () => {
                        const excludes = ['tel:', 'mailto:', 'sms:', 'geo:'];

                        const isExcluded = excludes.some(prefix =>
                            link.href.startsWith(prefix)
                        );

                        // Check if the link has already been prefetched
                        if (!prefetchedLinks.has(link.href) && !isExcluded) {
                            berq_prerender(link.href);

                            // Add the link to the set of prefetched links
                            prefetchedLinks.add(link.href);
                        }
                    });
                });

            </script>";
        }

        return $script;
    }

    function video_lazy_load_js($script) {

        if ($this->settings['lazy_load_videos']) {

            $script .= "
            <script async>
            (function(){
                // window.addEventListener('load', function() {
                window.addEventListener('DOMContentLoaded', function() {
                    // Function to load video and its sources
                    function loadLazyVideo(video) {
                        // Set video src from data attribute if exists
                        const videoSrc = video.getAttribute('data-berqwpsrc');
                        if (videoSrc) {
                            video.setAttribute('src', videoSrc);
                        }

                        // Find all <source> tags inside the video and apply their data-berqwpsrc as src
                        const sources = video.querySelectorAll('source');
                        sources.forEach(source => {
                            const sourceSrc = source.getAttribute('data-berqwpsrc');
                            if (sourceSrc) {
                                source.setAttribute('src', sourceSrc);
                            }
                        });

                        // Load the video once the sources have been set
                        video.load();
                    }

                    // Use IntersectionObserver to detect when the video is about to enter the viewport
                    let lazyVideos = [].slice.call(document.querySelectorAll('video.berqwp-lazy-video'));

                    if ('IntersectionObserver' in window) {
                        let lazyVideoObserver = new IntersectionObserver(function(entries, observer) {
                            entries.forEach(function(entry) {
                                if (entry.isIntersecting) {
                                    let video = entry.target;
                                    loadLazyVideo(video);
                                    // Unobserve the video once it's loaded
                                    lazyVideoObserver.unobserve(video);
                                }
                            });
                        });

                        // Observe each lazy video
                        lazyVideos.forEach(function(video) {
                            lazyVideoObserver.observe(video);
                        });
                    } else {
                        // Fallback for browsers that don't support IntersectionObserver
                        lazyVideos.forEach(function(video) {
                            loadLazyVideo(video);
                        });
                    }
                });

            })()
            </script>
            ";
        }

        return $script;
    }

    function iframe_lazy_load_js($script) {

        if ($this->settings['youtube_lazyloading']) {

            $script .= "
            <script defer>
                // document.addEventListener('DOMContentLoaded', function () {

                (function(){

                    var options = {
                        root: null, // null means the viewport
                        rootMargin: '0px', // adjust as needed
                        threshold: 0.1 // adjust as needed
                    };

                    var bwp_iframe_observer = new IntersectionObserver(function (entries, observer) {
                        entries.forEach(function (entry) {
                        if (entry.isIntersecting) {
                            let item = entry.target;
                            let iframe = item.getAttribute('data-embed');

                            let wrapper = document.createElement('div');
                            wrapper.innerHTML = iframe;
                            let iframeElement = wrapper.firstChild;

                            // Insert the iframe next to the item
                            item.insertAdjacentElement('afterend', iframeElement);

                            // Remove the original item
                            item.remove();

                            bwp_iframe_observer.unobserve(item);
                        }
                    });
                }, options);

                    let yt_em = document.querySelectorAll('.berqwp-lazy-youtube');
                    yt_em.forEach(function (item) {
                        bwp_iframe_observer.observe(item);
                    });

                })()
                // });
            </script>
            ";
        }

        return $script;
    }

    static function url_to_path($url)
    {

        if (strpos($url, '?') !== false) {
            $url = explode('?', $url)[0];
        }

        if (strpos($url, '#') !== false) {
            $url = explode('#', $url)[0];
        }

        // Handle relative URLs
        if (strpos($url, '//') === 0) {
            return realpath(ABSPATH . ltrim($url, '/'));
        }

        $content_url = content_url();
        $content_dir = WP_CONTENT_DIR;

        if (strpos($url, $content_url) === 0) {
            //  var_dump(realpath(
            //     str_replace($content_url, $content_dir, $url)
            // ));
            return str_replace($content_url, $content_dir, $url);
        }

        // Fallback for site root files
        $site_url = get_site_url('/');

        if (strpos($url, $site_url) === 0) {
            return realpath(
                str_replace($site_url, ABSPATH, $url)
            );
        }

        return false;
    }

    function img_lazy_load_js($script) {

        if ($this->settings['img_lazyloading']) {

            $script .= "
            <script async>
            (function(){

            document.addEventListener('DOMContentLoaded', function () {
                var berq_img_lazy_options = {
                    root: null, // null means the viewport
                    rootMargin: '200px',
                    threshold: 0
                };

                var img_observer = new IntersectionObserver(function (entries, observer) {
                    entries.forEach(function (entry) {
                        if (entry.isIntersecting) {
                            let img = entry.target;
                            let imgSrc = img.getAttribute('data-berqwpsrc');
                            let imgSrcset = img.getAttribute('data-berqwp-srcset');

                            // Set the actual image source from data-berqwpsrc
                            if (imgSrc !== null) {
                                img.src = imgSrc;
                            }

                            if (imgSrcset !== null) {
                                img.srcset = imgSrcset;
                            }

                            if (img.getAttribute('data-srcset') !== null) {
                                img.srcset = img.getAttribute('data-srcset');
                            }

                            // You might want to remove the data-src attribute after loading
                            img.removeAttribute('data-berqwpsrc');
                            img.removeAttribute('data-berqwp-srcset');

                            img_observer.unobserve(img);
                        }
                    });
                }, berq_img_lazy_options);

                function berqwp_lazyload_images() {
                    let lazyImages = document.querySelectorAll('img[data-berqwpsrc]');
                    lazy_img_int = 1000;

                    lazyImages.forEach(function (img) {
                        img_observer.observe(img);
                    });
                }

                function berqwp_lazyload_source() {
                    let lazyImages = document.querySelectorAll('source[data-berqwp-srcset]');
                    lazy_img_int = 1000;

                    lazyImages.forEach(function (img) {
                        img_observer.observe(img);
                    });
                }

                berqwp_lazyload_images();
                setInterval(berqwp_lazyload_images, 1000);
                setInterval(berqwp_lazyload_source, 1000);

            });

            })()
            </script>
            ";
        }

        return $script;
    }

    function dynamic_css_loading_script($script) {

        if ($this->settings['css_optimization'] == 'asynchronous' || $this->settings['css_optimization'] == 'delay') {

            $script .= "
            <script defer>
            function berqwp_init_css() {
                // Get all link tags containing data-berqwp-style-href
                let berqwp_linkTags = document.querySelectorAll('link[data-berqwp-style-href]');

                // Iterate through each link tag
                berqwp_linkTags.forEach(function (linkTag, index) {
                    // Set the href attribute of each link tag
                    linkTag.setAttribute('href', linkTag.getAttribute('data-berqwp-style-href'));

                });

                document
                .querySelectorAll('style[type=\"text/berqwp-style\"]')
                .forEach(style => {
                    style.type = 'text/css';
                });


            }
            </script>
            ";

            if ($this->settings['css_optimization'] == 'asynchronous') {
                $script .= "
                <script defer>
                requestIdleCallback(() => {
                  berqwp_init_css();
                });
                </script>
                ";
            }

            if ($this->settings['css_optimization'] == 'delay') {
                $script .= "
                <script defer>
                (function() {

                function berqwp_css_handleUserInteraction(event) {


                    berqwp_init_css();


                    // After running the function, remove all event listeners to ensure it runs only once
                    for (let eventType of berqwp_js_interactionEventTypes) {
                        window.removeEventListener(eventType, berqwp_css_handleUserInteraction);
                    }
                }

                let berqwp_js_interactionEventTypes = ['click', 'mousemove', 'keydown', 'touchstart', 'scroll', 'berqwpLoadJS', 'berqwp_interaction_event'];

                for (let eventType of berqwp_js_interactionEventTypes) {
                    window.addEventListener(eventType, berqwp_css_handleUserInteraction, { passive: false });
                }

                })()
                </script>
                ";
            }
        }

        return $script;
    }

    function dynamic_js_loading_script($script) {

        if ($this->settings['js_optimization'] == 'asynchronous' || $this->settings['js_optimization'] == 'delay') {

            $script .= "
            <script id='berqwp-preloader' defer>
            class berqwpPreloader {
                jobId = 0;
                pending = new Map();

                _preloadAssets(assets, asType) {
                    const seen = new Set();
                    const unique = assets.filter(({ url }) => {
                        if (seen.has(url)) return false;
                        seen.add(url);
                        return true;
                    });

                    const id = ++this.jobId;

                    return new Promise((resolve) => {
                        if (unique.length === 0) {
                            console.log('[berqwp] empty — resolving immediately');  // Is it bailing here?
                            resolve({ status: 'done', failed: [] });
                            return;
                        }

                        this.pending.set(id, resolve);

                        let completed = 0;
                        const failed = [];

                        const onSettle = () => {
                            completed++;
                            if (completed === unique.length) {
                                this.pending.delete(id);
                                resolve({ status: 'done', failed });
                            }
                        };

                        unique.forEach(({ url, crossOrigin }) => {
                            const alreadyPreloaded = document.querySelector(`link[rel=\"preload\"][as=\"\${asType}\"][href=\"\${url}\"]`);
                            const alreadyScript    = asType === 'script' && document.querySelector(`script[src=\"\${url}\"]:not([type=\"text/bwp-script\"])`);
                            const alreadyStyle     = asType === 'style'  && document.querySelector(`link[rel=\"stylesheet\"][href=\"\${url}\"]`);

                            if (alreadyPreloaded || alreadyScript || alreadyStyle) {
                                return onSettle();
                            }

                            const link = document.createElement('link');
                            link.rel  = 'preload';
                            link.as   = asType;
                            link.href = url;

                            if (crossOrigin) {
                                link.crossOrigin = crossOrigin;
                            }

                            link.onload  = () => {
                                console.log('[berqwp] preloaded:', url);  // Is onload firing?
                                onSettle();
                            };
                            link.onerror = () => {
                                console.warn('[berqwp] failed:', url);    // Or is it erroring?
                                failed.push(url);
                                onSettle();
                            };

                            document.head.appendChild(link);
                        });
                    });
                }

                preload(assets) {
                    const normalized = assets.map(a =>
                        typeof a === 'string' ? { url: a, crossOrigin: null } : a
                    );
                    return this._preloadAssets(normalized, 'script');
                }

                preloadScripts() {
                    const elements = [...document.querySelectorAll('script[type=\"text/bwp-script\"]')];
                    const assets = elements
                        .filter(el => el.getAttribute('src'))
                        .map(el => ({
                            url: el.getAttribute('src'),
                            crossOrigin: el.getAttribute('crossorigin') || null
                        }));

                    return this._preloadAssets(assets, 'script');
                }

                preloadStyles() {
                    const elements = [...document.querySelectorAll('link[data-berqwp-style-href]')];
                    const assets = elements
                        .filter(el => el.getAttribute('data-berqwp-style-href'))
                        .map(el => ({
                            url: el.getAttribute('data-berqwp-style-href'),
                            crossOrigin: el.getAttribute('crossorigin') || null
                        }));

                    return this._preloadAssets(assets, 'style');
                }

            }

            (function() {
                window.bwpPreloader = new berqwpPreloader();
            })()
            </script>
            <script defer>
            (function(){
                // Select all inline script tags with type=\"text/bwp-script\"
                const inlineScripts = document.querySelectorAll('script[type=\"text/bwp-script\"]');

                inlineScripts.forEach((script) => {
                    // Get the content of the inline script
                    const scriptContent = script.innerHTML;

                    if (!scriptContent) {
                        return;
                    }

                    if (script.getAttribute('data-type') == 'module') {
                        return;
                    }

                    // Create a Blob from the script content
                    const blob = new Blob([scriptContent], { type: 'application/javascript' });

                    // Create a URL for the Blob
                    const scriptURL = URL.createObjectURL(blob);

                    // Create a new external script tag
                    const newScript = document.createElement('script');
                    newScript.src = scriptURL;
                    newScript.type = 'text/bwp-script';  // Or 'text/bwp-script', but 'application/javascript' is more typical for JS
                    newScript.setAttribute('data-type', script.getAttribute('data-type'));

                    // Copy other attributes if necessary
                    Array.from(script.attributes).forEach(function(attr) {
                        if (attr.name !== 'type' && attr.name !== 'src') {
                            newScript.setAttribute(attr.name, attr.value);
                        }
                    });

                    // Replace the original inline script with the new external script tag
                    script.parentNode.replaceChild(newScript, script);
                });

            })();

            </script>
            <script data-mode='4' defer>
            var berq_click = null;
            function berqwpLoadJs() {

                (function () {
                    'use strict';
                    const VERSION = '3.0';
                    let activated = false;
                    const pendingScripts = new Map();
                    let completedCount = 0;

                    // Custom Event System
                    const createTrackerEvent = (name, detail = {}) =>
                        new CustomEvent(`bwp:\${name}`, {
                            detail: {
                                version: VERSION,
                                timestamp: performance.now(),
                                ...detail
                            }
                        });

                    // Event Polyfills
                    (function () {
                        const originalAddEventListener = EventTarget.prototype.addEventListener;
                        const readyState = document.readyState;
                        const simulatedEvents = new WeakMap();

                        function createEvent(type) {
                            const event = new Event(type, { bubbles: false, cancelable: false });
                            event.isSimulated = true;
                            return event;
                        }

                        EventTarget.prototype.addEventListener = function (type, listener, options) {

                            if (typeof listener === 'function') {
                                if (type === 'DOMContentLoaded') {
                                    if (readyState !== 'loading') {
                                        setTimeout(() => listener.call(this, createEvent(type)), 0);
                                        return;
                                    }
                                } else if ((this === window || this instanceof HTMLScriptElement) && type === 'load') {
                                    if (readyState === 'complete') {
                                        setTimeout(() => listener.call(this, createEvent(type)), 0);
                                        return;
                                    }
                                }
                            }


                            originalAddEventListener.call(this, type, listener, options);
                        };

                        ['onDOMContentLoaded', 'onload'].forEach(prop => {
                            const target = prop === 'onload' ? window : document;
                            const descriptor = Object.getOwnPropertyDescriptor(target.constructor.prototype, prop.toLowerCase()) || {};

                            Object.defineProperty(target, prop, {
                                set(fn) {
                                    if (typeof fn === 'function') {
                                        const eventType = prop.replace('on', '').toLowerCase();
                                        if ((eventType === 'domcontentloaded' && readyState !== 'loading') ||
                                            (eventType === 'load' && readyState === 'complete')) {
                                            setTimeout(() => fn.call(target, createEvent(eventType)), 0);
                                        } else {
                                            target.addEventListener(eventType, fn);
                                        }
                                    }
                                    return descriptor.set ? descriptor.set.call(this, fn) : undefined;
                                },
                                get() {
                                    return descriptor.get ? descriptor.get.call(this) : undefined;
                                },
                                configurable: true
                            });
                        });
                    })();

                    // Activation System
                    function activate() {
                        if (activated) return;
                        activated = true;
                        document.dispatchEvent(createTrackerEvent('activation'));

                        // Remove listeners
                        ['mousemove', 'click', 'scroll', 'keydown'].forEach(evt => {
                            window.removeEventListener(evt, activate, true);
                        });

                        processScripts();
                        setupObserver();
                    }

                    function processScripts() {
                        const scripts = Array.from(document.querySelectorAll('script[type=\"text/bwp-script\"]:not([data-earlyberqwp])'));
                        scripts.forEach(convertScript);
                    }

                    function setupObserver() {
                        new MutationObserver(mutations => {
                            mutations.forEach(mutation => {
                                mutation.addedNodes.forEach(node => {
                                    if (node.tagName === 'SCRIPT' && node.type === 'text/bwp-script') {
                                        convertScript(node);
                                    }
                                });
                            });
                        }).observe(document, { childList: true, subtree: true });
                    }

                    // Script Conversion
                    function convertScript(original) {
                        if (original.dataset.processed) return;
                        original.dataset.processed = 'true';

                        const script = document.createElement('script');
                        const attrs = Array.from(original.attributes).filter(a => a.name !== 'type');
                        const id = `script-\${performance.now()}-\${Math.random().toString(36).slice(2)}`;

                        script.type = original.getAttribute('data-type');
                        try {
                            attrs.forEach(attr => script.setAttribute(attr.name, attr.value));
                        } catch (e) {
                        }
                        if (original.textContent) script.textContent = original.textContent;

                        pendingScripts.set(id, {
                            element: script,
                            src: script.src,
                            isExternal: !!script.src
                        });

                        document.dispatchEvent(createTrackerEvent('script-loading', {
                            scriptId: id,
                            element: script
                        }));

                        script.addEventListener('load', () => handleCompletion(id, true));
                        script.addEventListener('error', () => handleCompletion(id, false));

                        if (original.textContent) {
                            handleCompletion(id, true);
                        }

                        // script.async = false;
                        original.replaceWith(script);
                    }

                    function handleCompletion(id, success) {
                        const record = pendingScripts.get(id);
                        if (!record) return;

                        pendingScripts.delete(id);
                        completedCount++;

                        document.dispatchEvent(createTrackerEvent('script-complete', {
                            scriptId: id,
                            success,
                            duration: performance.now() - record.element.startTime
                        }));

                        if (pendingScripts.size === 0) {
                            document.dispatchEvent(createTrackerEvent('scripts-loaded', {
                                total: completedCount,
                                failed: completedCount - Array.from(pendingScripts.values())
                                    .filter(r => r.success).length
                            }));
                        }
                    }

                    document.addEventListener('bwp:scripts-loaded', (e) => {
                        console.log('scripts loaded.')
                        window.dispatchEvent(new Event('berqwp_after_delay_js_loaded'));

                    });

                    // Initialization
                    activate();

                    window.addEventListener('berqwp_after_delay_js_loaded', function() {
                        let event = new Event('DOMContentLoaded', {
                            bubbles: true,
                            cancelable: true
                        });
                        document.dispatchEvent(event);
                        window.dispatchEvent(new Event('load'));


                        // Create a new resize event
                        var resizeEvent = new Event('resize');

                        // Dispatch the resize event
                        window.dispatchEvent(resizeEvent);


                        if (berq_click) {
                            setTimeout(function() {
                                console.log(berq_click);
                                const clickEvent = new MouseEvent('click', {
                                    bubbles: true,
                                    cancelable: false,
                                    view: window
                                });
                                berq_click.dispatchEvent(clickEvent);
                                berq_click = null;
                            }, 500)
                        }

                    });
                })();
            }
            </script>
            ";

            if ($this->settings['js_optimization'] == 'asynchronous') {
                $script .= "
                <script defer>
                requestIdleCallback(() => {
                  berqwpLoadJs();
                });
                </script>
                ";
            }

            if ($this->settings['js_optimization'] == 'delay') {
                $script .= "
                <script defer>
                (function() {

                let berqwpScriptTags = document.querySelectorAll('script[type=\"text/bwp-script\"]');
                let jsUrls = Array.from(berqwpScriptTags).map(scriptTag => scriptTag.getAttribute('src')).filter(url => url);

                function preloadJS(jsURLs) {
                    return window.bwpPreloader.preloadScripts();
                }

                let bwp_js_initialized = false;
                function bwp_js_init(jsUrls) {
                    if (bwp_js_initialized) {
                        return;
                    }
                    bwp_js_initialized = true;

                    preloadJS(jsUrls)
                    .then(() => {
                        console.log('Preloading completed, invoking berqwpLoadJs...');
                        if (typeof berqwpLoadJs === 'function') {
                            berqwpLoadJs();
                        } else {
                            console.error('berqwpLoadJs is not defined or not a function.');
                        }
                    })
                    .catch(error => {
                        console.error('bwp_js_init failed during preloadJS:', error);
                    });
                }

                function berqwp_js_handleUserInteraction(event) {

                    if (event.type === 'click' || event.type === 'touchstart') {
                        berq_click = event.target;

                        // Traverse up the DOM to find the closest <a> ancestor
                        if (berq_click.closest('a')) {
                            berq_click = berq_click.closest('a');
                        }

                        // If click is done on a link
                        if (berq_click.tagName === 'A' && berq_click.href && /^https?:\/\//.test(berq_click.href)) {
                            console.log('Skipping JavaScript execution, a link was clicked.')
                            return;
                        }

                        console.log(event.type)

                    }

                    bwp_js_init(jsUrls);


                    // After running the function, remove all event listeners to ensure it runs only once
                    for (let eventType of berqwp_js_interactionEventTypes) {
                        window.removeEventListener(eventType, berqwp_js_handleUserInteraction);
                    }
                }

                let berqwp_js_interactionEventTypes = ['click', 'mousemove', 'keydown', 'touchstart', 'scroll', 'berqwpLoadJS', 'berqwp_interaction_event'];

                for (let eventType of berqwp_js_interactionEventTypes) {
                    window.addEventListener(eventType, berqwp_js_handleUserInteraction, { passive: false });
                }

                })()
                </script>
                ";
            }
        }

        return $script;
    }

    function optimize_js($buffer) {
        $dom = HtmlDomParser::str_get_html($buffer);
        $js_excludes = $this->settings['exclude_js'];
        $js_excludes = array_map(function ($kw) {
            return sanitize_text_field(trim($kw));
        }, $js_excludes);

        foreach ($dom->find('script') as $element) {
            $script_src = $element->src;
            $outerhtml = $element->outertext;

            if (strpos($outerhtml, 'document.write(') !== false) {
                continue;
            }

            if (strpos($outerhtml, '$zoho.salesiq = ') !== false) {
                continue;
            }

            if (strpos($outerhtml, 'CRLeadStar.init') !== false) {
                continue;
            }

            if (!empty($element->type) && $element->type !== 'text/javascript' && $element->type !== 'application/javascript' && $element->type !== 'module') {
                continue;
            }

            if ($element->hasAttribute('data-berqwp-exclude')) {
                continue;
            }

            // exclude script tags
            if (!empty($js_excludes)) {
                foreach ($js_excludes as $exclude_kw) {
                    if (strpos($outerhtml, $exclude_kw) !== false) {

                        if ($this->settings['defer_excluded_js'] && !$element->hasAttribute('async')) {
                            $element->setAttribute('defer', 'defer');
                        }

                        continue 2;
                    }
                }
            }

            if ($this->settings['js_optimization'] == 'defer') {

                if (!$element->hasAttribute('async')) {
                    $element->setAttribute('defer', 'defer');
                }

                continue;
            }

            if (empty($element->type) || $element->type == 'text/javascript') {
                $element->setAttribute('data-type', 'text/javascript');
                $element->type = 'text/bwp-script';
            } else {
                $element->setAttribute('data-type', esc_attr($element->type));
                $element->type = 'text/bwp-script';
            }

        }

        return (string) $dom;
    }

    function lazy_load_videos($buffer) {
        $dom = HtmlDomParser::str_get_html($buffer);

        foreach ($dom->find('video') as $element) {
            $outerhtml = $element->outertext;
            $parent = $element->parent();

            if (strpos($outerhtml, 'video-js') !== false) {
                continue;
            }

            if (strpos($outerhtml, 'mg_self-hosted-video') !== false) {
                continue;
            }

            if (!empty($element->src)) {
                $original_src = $element->src;
                // $video->attr['data-berqwpsrc'] = $original_src;
                $element->setAttribute('data-berqwpsrc', $original_src);
                $element->removeAttribute('src');
            }

            // Handle each <source> element within the video tag
            foreach ($element->find('source') as $source) {
                if (!empty($source->src)) {
                    $original_src = $source->src;
                    // $source->attr['data-berqwpsrc'] = $original_src;
                    $source->setAttribute('data-berqwpsrc', $original_src);
                    $source->removeAttribute('src');
                }
                unset($source);
            }

            $class = $element->getAttribute('class');
            $class .= ' berqwp-lazy-video';
            $element->setAttribute('class', $class);
            $element->setAttribute('preload', 'none');
            // $element->attr[' preload'] = 'none';
        }

        return (string) $dom;
    }

    function lazy_load_images($buffer) {
        $dom = HtmlDomParser::str_get_html($buffer);
        $image_excludes = $this->settings['exclude_img_lazy_load'];
        $image_excludes = array_map(function ($kw) {
            return sanitize_text_field(trim($kw));
        }, $image_excludes);

        foreach ($dom->find('img') as $element) {

            if (!$element->hasAttribute('src') || empty($element->src)) {
                continue;
            }

            $img_src = $element->src;
            $outerhtml = $element->outertext;
            $img_path = self::url_to_path($img_src);

            // exclude lazy load
            if (!empty($image_excludes)) {
                foreach ($image_excludes as $exclude_kw) {
                    if (strpos($outerhtml, $exclude_kw) !== false) {
                        continue 2;
                    }
                }
            }

            if (strpos($img_src, 'data:') === 0) {
                continue;
            }

            if (strpos($img_src, ';base64,') !== false) {
                continue;
            }

            if (strpos($outerhtml, 'rs-lazyload') !== false) {
                continue;
            }

            if (strpos($outerhtml, 'mfn-lazy') !== false) {
                continue;
            }

            if (strpos($outerhtml, 'data-dbsrc=') !== false) {
                continue;
            }

            if (strpos($outerhtml, 'data-orig-src=') !== false) {
                continue;
            }

            if (strpos($outerhtml, 'facebook.com') !== false) {
                continue;
            }

            if (strpos($img_src, '${') !== false) {
                continue;
            }

            // Skip smush lazy loading
            if (strpos($img_src, '--smush-placeholder-width') !== false) {
                continue;
            }

            // wpbackery background image
            if (strpos($img_src, 'class="background-image"') !== false) {
                continue;
            }

            // lscache lazy load
            if (strpos($img_src, 'class="lazyload"') !== false) {
                continue;
            }

            // salient lazy load
            if (strpos($img_src, 'data-nectar-img-src') !== false) {
                continue;
            }

            $width = $element->width;
            $height = $element->height;

            if (!empty($img_path) && file_exists($img_path) && !$element->hasAttribute('width') && !$element->hasAttribute('height')) {
                list($width, $height) = getimagesize($img_path);
            }

            $element->removeAttribute('src');
            $element->setAttribute('data-berqwpsrc', $img_src);

            if ($element->hasAttribute('srcset')) {
                $srcset = $element->srcset;
                $element->removeAttribute('srcset');
                $element->setAttribute('data-berqwp-srcset', $srcset);
            }

            if (!empty($width) && !empty($height)) {
                $svg = '<svg width="' . $width . '" height="' . $height . '" xmlns="http://www.w3.org/2000/svg" version="1.1">';
                $svg .= '<rect width="100%" height="100%" fill="none" />';
                $svg .= '</svg>';

                $base64Svg = base64_encode($svg);

                // Create data URI
                $ph_webp_url = 'data:image/svg+xml;base64,' . $base64Svg;
            } else {
                $ph_webp_url = 'data:image/gif;placeholder=MjQxOjM1NQ==-1;base64,R0lGODlhAQABAIABAAAAAP///yH5BAEAAAEALAAAAAABAAEAAAICTAEAOw==';
            }

            $element->setAttribute('src', $ph_webp_url);
            $element->setAttribute('decoding', 'async');

        }

        return (string) $dom;
    }

    private function extract_template_scripts( $buffer ) {
        $placeholders = [];

        $pattern = '/<script(?=[^>]*\btype=["\'](?:text\/template|text\/x-template|text\/x-handlebars-template|text\/x-handlebars|text\/ng-template)["\'])[^>]*>.*?<\/script>/is';

        $result = preg_match_all( $pattern, $buffer, $matches );

        if ( $result === false || $result === 0 ) {
            return [ $buffer, $placeholders ];
        }

        foreach ( $matches[0] as $index => $script_block ) {
            $placeholder                  = '<!--BERQWP_TEMPLATE_' . $index . '-->';
            $placeholders[ $placeholder ] = $script_block;
            $buffer                       = str_replace( $script_block, $placeholder, $buffer );
        }

        return [ $buffer, $placeholders ];
    }

    private function restore_template_scripts( $buffer, $placeholders ) {
        if ( empty( $placeholders ) ) {
            return $buffer;
        }

        foreach ( $placeholders as $placeholder => $original_block ) {
            $buffer = str_replace( $placeholder, $original_block, $buffer );
        }

        return $buffer;
    }

    function lazy_load_iframes($buffer) {
        $dom = HtmlDomParser::str_get_html($buffer);

        foreach ($dom->find('iframe') as $element) {
            $outerhtml = $element->outertext;
            $outerhtml = apply_filters('photon_before_iframe_optimization', $outerhtml);
            $isyoutube = false;

            if ($this->settings['preload_yt_poster']) {

                if (preg_match('/youtube\.com\/embed\/([a-zA-Z0-9_-]+)/', $element->src, $matches)) {
                    $videoId = $matches[1];
                    $isyoutube = true;

                    // Generate the thumbnail URL
                    $thumbnailUrl = "https://img.youtube.com/vi/{$videoId}/hqdefault.jpg";
                    $highresthumbnailUrl = "https://img.youtube.com/vi/{$videoId}/maxresdefault.jpg";


                    if (strpos($element->src, 'autoplay=1') !== false && strpos($element->src, 'mute=') === false) {
                        $element->setAttribute('src', $element->src . "&mute=1");
                    }

                    // Generate lazy-load HTML content for srcdoc
                    $srcdocContent = <<<HTML
            <style>
                *{padding:0;margin:0;overflow:hidden}
                .play-button {
                    width: 70px;
                    position: absolute;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%);
                    cursor: pointer;
                }
            </style>
            <a href='{$element->src}' style='position: absolute; width: 100%; height: 100%; background: url({$thumbnailUrl}) no-repeat center center; background-size: cover;'>
                <picture>
                    <source srcset='{$highresthumbnailUrl}' media='(min-width: 999px)'>
                    <img src='{$thumbnailUrl}' style='position: absolute; width: 100%; height: 100%;object-fit: cover;'>
                </picture>
                <div class='play-button'>
                <svg height='100%' version='1.1' viewBox='0 0 68 48' width='100%'><path class='ytp-large-play-button-bg' d='M66.52,7.74c-0.78-2.93-2.49-5.41-5.42-6.19C55.79,.13,34,0,34,0S12.21,.13,6.9,1.55 C3.97,2.33,2.27,4.81,1.48,7.74C0.06,13.05,0,24,0,24s0.06,10.95,1.48,16.26c0.78,2.93,2.49,5.41,5.42,6.19 C12.21,47.87,34,48,34,48s21.79-0.13,27.1-1.55c2.93-0.78,4.64-3.26,5.42-6.19C67.94,34.95,68,24,68,24S67.94,13.05,66.52,7.74z' fill='#f00'></path><path d='M 45,24 27,14 27,34' fill='#fff'></path></svg>
                </div>
            </a>
            HTML;

                    // Update the iframe attributes
                    $element->setAttribute('srcdoc', $srcdocContent);

                    $element->setAttribute('src', $element->src); // Remove immediate src loading
                }

                if ($isyoutube && $element->hasAttribute('data-berqWPexclude')) {
                    continue;
                }

            }

            if (strpos($outerhtml, 'youtube.com') !== false && !$element->hasAttribute('referrerpolicy')) {
                $element->setAttribute('referrerpolicy', 'strict-origin-when-cross-origin');
            }

            if ($element->hasAttribute('data-berqWPexclude')) {
                continue;
            }

            $class = $element->getAttribute('class');

            if (!empty($class)) {
                $classes = explode(' ', $class ?? '');
                $classes = array_filter($classes, fn($c) => $c !== 'lazyload');
                $class = trim(implode(' ', $classes));
                $element->setAttribute('class', $class);
            }

            if ($element->hasAttribute('data-src')) {
                $element->setAttribute('rm-src', $element->src);
                $element->removeAttribute('src');
            }

            $outerhtml = esc_attr($element->outertext);

            $element->outertext = '<div class="berqwp-lazy-youtube" data-embed="' . $outerhtml . '"></div>';

        }

        return (string) $dom;
    }

    function store_cache($buffer) {
        // Define the cache directory
        $cache_directory = bwp_get_cache_dir();
        $url = $this->page_url;

        // Create the cache directory if it doesn't exist
        if (!file_exists($cache_directory)) {
            mkdir($cache_directory, 0755, true);
        }

        $cache = new Cache(null, bwp_get_cache_dir());
        $cache->store_cache($url, $buffer);


        do_action('berqwp_stored_page_cache', $this->page_slug);

        global $berq_log;
        $berq_log->info("Stored cache for $url from PageOptimizer class");
    }

    function preload_images($buffer) {
        $dom = HtmlDomParser::str_get_html($buffer);
        $count = 0;
        $candidates = [];

        foreach ($dom->find('img') as $element) {

            if ($count >= 9) {
                break;
            }

            if (!$element->hasAttribute('src') || empty($element->src)) {
                continue;
            }

            $img_src = $element->src;
            $outerhtml = $element->outertext;
            $img_path = self::url_to_path($img_src);

            // exclude lazy load
            // if (!empty($image_excludes)) {
            //     foreach ($image_excludes as $exclude_kw) {
            //         if (strpos($outerhtml, $exclude_kw) !== false) {
            //             continue 2;
            //         }
            //     }
            // }

            if (strpos($img_src, 'data:') === 0) {
                continue;
            }

            if (strpos($img_src, ';base64,') !== false) {
                continue;
            }

            if (strpos($outerhtml, 'rs-lazyload') !== false) {
                continue;
            }

            if (strpos($outerhtml, 'mfn-lazy') !== false) {
                continue;
            }

            if (strpos($outerhtml, 'data-dbsrc=') !== false) {
                continue;
            }

            if (strpos($outerhtml, 'data-orig-src=') !== false) {
                continue;
            }

            if (strpos($outerhtml, 'facebook.com') !== false) {
                continue;
            }

            $width = $element->width;
            $height = $element->height;

            if (!empty($img_path) && file_exists($img_path) && !$element->hasAttribute('width') && !$element->hasAttribute('height')) {
                list($width, $height) = getimagesize($img_path);
            }

            $candidate = [
                'width' => $width,
                'height' => $height,
                'area' => (int) $width * (int) $height,
                'src' => $img_src,
            ];

            if ($element->hasAttribute('loading')) {
                $candidate['loading'] = $element->loading;
            }

            if ($element->hasAttribute('srcset')) {
                $candidate['srcset'] = $element->srcset;
            }

            if ($element->hasAttribute('sizes')) {
                $candidate['sizes'] = $element->sizes;
            }

            $candidates[] = $candidate;
            $count++;


        }

        $buffer = (string) $dom;

        if (!empty($candidates)) {

            $processed = [];

            // unique src
            $candidates = array_filter($candidates, function ($item) use (&$processed) {
                if (in_array($item['src'], $processed)) {
                    return false;
                }

                $processed[] = $item['src'];

                return true;
            });

            usort($candidates, function ($a, $b) {
                return $b['area'] <=> $a['area']; // DESC
            });

            $candidates = array_slice($candidates, 0, 2);
            $prelaod_html = '';

            foreach ($candidates as $preload_img) {
                $prelaod_html .= '<link rel="preload" as="image" ';
                $prelaod_html .= ' fetchpriority="high" ';
                $prelaod_html .= 'href="'.$preload_img['src'].'" ';

                if (!empty($preload_img['srcset'])) {
                    $prelaod_html .= 'imagesrcset="'.$preload_img['srcset'].'" ';
                }

                if (!empty($preload_img['sizes'])) {
                    $prelaod_html .= 'imagesizes="'.$preload_img['sizes'].'" ';
                }

                $prelaod_html .= '>'.PHP_EOL;
            }

            $buffer = berqwp_prependHtmlToHead($buffer, $prelaod_html);

        }



        return $buffer;
    }

    function preload_stylesheet($buffer) {
        $dom = HtmlDomParser::str_get_html($buffer);

        foreach ($dom->find('link') as $element) {

            if ($element->rel == 'stylesheet'){
                $element->setAttribute('rel', 'preload');
                $element->setAttribute('as', 'style');
                $element->removeAttribute('media');
                // $element->setAttribute('media', 'print');
                // $element->setAttribute('onload', "this.media='all'");
                $element->setAttribute('onload', "this.rel='stylesheet'");
            }

        }

        return (string) $dom;

    }

    function delay_styles($buffer) {

        if ( !empty($this->settings['css_optimization']) && $this->settings['css_optimization'] !== 'delay' && $this->settings['css_optimization'] !== 'asynchronous' ) {
            return $buffer;
        }

        $dom = HtmlDomParser::str_get_html($buffer);

        $style_excludes = array_map(function ($kw) {
            return sanitize_text_field(trim($kw));
        }, $this->settings['exclude_css']);

        foreach ($dom->find('link') as $element) {

            $outerhtml = $element->outertext;

            if ($element->rel == 'stylesheet' || $element->as == 'style'){

                if (!$element->hasAttribute('href')) {
                    continue;
                }

                if ($element->hasAttribute('data-berqwp-exclude')) {
                    continue;
                }

                // exclude style
                if (!empty($style_excludes)) {
                    foreach ($style_excludes as $exclude_kw) {
                        if (!empty($exclude_kw) && strpos($outerhtml, $exclude_kw) !== false) {
                            continue;
                        }
                    }
                }

                $element->setAttribute('rel', 'stylesheet');
                $element->setAttribute('as', 'style');
                $element->setAttribute('data-berqwp-style-href', $element->href);
                $element->removeAttribute('href');
            
            }

        }

        foreach ($dom->find('style') as $element) {

            $outerhtml = $element->outertext;

            if ($element->hasAttribute('data-berqwp-exclude')) {
                continue;
            }

            // exclude style
            if (!empty($style_excludes)) {
                foreach ($style_excludes as $exclude_kw) {
                    if (!empty($exclude_kw) && strpos($outerhtml, $exclude_kw) !== false) {
                        continue;
                    }
                }
            }

            $element->setAttribute('type', 'text/berqwp-style');
        }

        return (string) $dom;

    }

    function buffer_end($buffer) {

        // $buffer = file_get_contents('/Users/hamzamairaj/Local Sites/plugin-berqwp/app/public/wp-content/plugins/searchpro/inc/photon/page.html');

        if (empty($buffer)) {
            return $buffer;
        }

        $buffer = $buffer.'<!-- Optimized with BerqWP\'s instant cache. --->';

        $script = "
            <script defer>
                var comment = document.createComment(' This website is optimized using the BerqWP plugin. @".time()." ');
                document.documentElement.insertBefore(comment, document.documentElement.firstChild);

                function isMobileDevice() {
                    return /Mobi|Android|iPhone|iPad|iPod|Opera Mini|IEMobile|WPDesktop/i.test(navigator.userAgent);
                }

                function astraHeaderClass() {
                    if (isMobileDevice() && window.screen.width <= 999) {
                        if ((document.body.classList.contains('theme-astra') || document.body.classList.contains('ast-page-builder-template')) && !document.body.classList.contains('ast-header-break-point')) {
                            document.body.classList.add('ast-header-break-point');
                        }
                    }
                }

                function divimobilemenu() {
                    const divimenuele = document.querySelector('#et_mobile_nav_menu .mobile_menu_bar.mobile_menu_bar_toggle');

                    if (isMobileDevice() && divimenuele) {
                        divimenuele.innerHTML = '<div class=\"dipi_hamburger hamburger hamburger--spring\">     <div class=\"hamburger-box\">         <div class=\"hamburger-inner\"></div>     </div> </div>';
                    }
                }

                divimobilemenu();
                astraHeaderClass();
                window.addEventListener('resize', function() {
                    astraHeaderClass();
                });

                window.dispatchEvent(new Event('berqwp_js_initialized'));
            </script>

        ";

        [ $buffer, $template_placeholders ] = $this->extract_template_scripts( $buffer );

        $buffer = $this->preload_images($buffer);
        // $buffer = $this->preload_stylesheet($buffer);

        if ($this->settings['img_lazyloading']) {
            $buffer = $this->lazy_load_images($buffer);
        }

        if ($this->settings['js_optimization'] == 'auto') {
            if ($this->settings['opt_mode'] == 'basic') {
                $this->settings['js_optimization'] = 'defer';
            }

            if ($this->settings['opt_mode'] == 'medium') {
                $this->settings['js_optimization'] = 'asynchronous';
            }

            if ($this->settings['opt_mode'] == 'blaze') {
                $this->settings['js_optimization'] = 'delay';
            }

            if ($this->settings['opt_mode'] == 'aggressive') {
                $this->settings['js_optimization'] = 'delay';
            }
        }

        if ($this->settings['js_optimization'] !== 'disable') {
            $buffer = $this->optimize_js($buffer);
        }

        if ($this->settings['lazy_load_videos']) {
            $buffer = $this->lazy_load_videos($buffer);
        }

        if ($this->settings['youtube_lazyloading']) {
            $buffer = $this->lazy_load_iframes($buffer);
        }

        if ($this->settings['css_optimization'] == 'auto') {
            if ($this->settings['opt_mode'] == 'basic') {
                $this->settings['css_optimization'] = 'disable';
            }

            if ($this->settings['opt_mode'] == 'medium') {
                $this->settings['css_optimization'] = 'asynchronous';
            }

            if ($this->settings['opt_mode'] == 'blaze') {
                $this->settings['css_optimization'] = 'asynchronous';
            }

            if ($this->settings['opt_mode'] == 'aggressive') {
                $this->settings['css_optimization'] = 'delay';
            }
        }

        $enable_critical_css = (bool) apply_filters('berqwp_local_critical_css', false);

        if ($enable_critical_css && $this->settings['css_optimization'] !== 'disable') {
            $critical_css = new berqUsedCSS(get_option('home'));
            $critical_css->forceInclude($this->settings['force_include_critical_css']);
            $critical_css = $critical_css->process_css($buffer);
            $critical_css = sprintf('<style data-berqwp-exclude id="berqwp-critical-css">%s</style>', $critical_css);
    
            $buffer = berqwp_prependHtmlToHead($buffer, $critical_css);
            $buffer = $this->delay_styles($buffer);

        }


        $buffer = $this->restore_template_scripts( $buffer, $template_placeholders );

        $script = apply_filters('berqwp_photon_before_closing_body', $script);
        $buffer = berqwp_appendHtmlToBody($buffer, $script);

        $buffer = apply_filters( 'berqwp_cache_buffer', $buffer );
        $this->store_cache($buffer);

        return $buffer;
    }
}
