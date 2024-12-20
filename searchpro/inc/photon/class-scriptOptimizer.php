<?php
if (!defined('ABSPATH')) exit;

class berqScriptOptimizer {
    public $loading = 'delay';
    public $js_mode = '';

    function set_loading($loading)
    {
        $this->loading = $loading;
    }

    function run_optimization($photonClass, $buffer) {

        /* if ($this->loading == 'default') { */
        /*     return $buffer; */
        /* } */

        $this->js_mode = $photonClass->js_mode;

        // Combine patterns to extract script src attributes and inline script contents
        $pattern = '/<script\b[^>]*?(?:src=["\'](.*?)["\']|>(.*?)<\/script>)/is';
        preg_match_all($pattern, $buffer, $matches, PREG_SET_ORDER);
    
        // Initialize arrays for script sources and inline scripts
        $scripts = [];
    
        // Process the matches
        foreach ($matches as $match) {
            if (!empty($match[1])) {
                // Matched script src attribute
                if (!str_contains($match[0], 'data-berqwp')) {
                    $src = $match[1];
                    $scripts[] = $src;
                }
            } elseif (!empty($match[2])) {
                // Matched inline script content
                if (!str_contains($match[0], 'data-berqwp')) {
                    $script = $match[2];
                    $scripts[] = $script;
                }
            }

            unset($match);
        }
    
        // Define the regular expression pattern for inline and external script tags
        $pattern = '/(<script\b[^>]*>(.*?)<\/script>)/is';
        $deffer_js = '';
    
        // Replace with cdn script urls when they're ready
        $script_tags_to_replace = [];
    
        // Remove JavaScript using preg_replace_callback
        $buffer = preg_replace_callback($pattern, function ($matches) use (&$photonClass, &$script_tags_to_replace) {
            $tag = $matches[0];

            if (strpos($tag, 'document.write(') !== false) {
                return $tag; // Keep the script tag with id="berqWP"
            }

            if (strpos($tag, '$zoho.salesiq = ') !== false) {
                return $tag;
            }

            // Create a Simple HTML DOM object
            $html = str_get_html($tag);

            // Find all script tags
            foreach ($html->find('script') as $scriptTag) {
                if (!empty($scriptTag->type) && $scriptTag->type !== 'text/javascript' && $scriptTag->type !== 'application/javascript' && $scriptTag->type !== 'module') {

                    return $tag;
                }

            }

            $tag = $html->save();

            // Clear Simple HTML DOM object
            $html->clear();
            unset($html);

            
            if ($photonClass->use_cdn) {

                // Create a Simple HTML DOM object
                $html = str_get_html($tag);

                // Find all script tags
                foreach ($html->find('script') as $scriptTag) {
                    $src = $scriptTag->src;

                    $kw_found = false;

                    foreach ($photonClass->external_js_excluded_keywords as $keyword) {
                        if (stripos($src, $keyword) !== false) {
                            $kw_found = true;
                        }
                    }

                    // Check if the script source contains any excluded keywords
                    if (!$kw_found) {
                        // Use wp_remote_get to fetch the script content
                        $response = wp_remote_get($src);

                        if (!is_wp_error($response)) {
                            $scriptContent = wp_remote_retrieve_body($response);

                            // Send the file URL to CDN as GET parameters
                            /* $cdnUrl = 'https://cdn.berqwp.com/'; */

                            global $berqCDN;
                            $berqCDN->add_file_in_queue($src);

                            // $cdnUrl = 'https://boost.berqwp.com/photon/cdn/';
                            // $cdnUrl .= '?url=' . urlencode($src);
                            // $cdnUrl .= '&domain=' . $photonClass->domain;

                            // $photonClass->add_into_cdn_queue($cdnUrl, $src);

                        }
                    }
                }

                $tag = $html->save();

                // Clear Simple HTML DOM object
                $html->clear();
                unset($html);

            }

            // Return the script tag after optimizing with CDN
            if (strpos($tag, 'data-berqwp') !== false) {
                return $tag; // Keep the script tag with id="berqWP"
            }

            if ($this->loading == 'default') {
                return $tag;
            }

            // If tag has a match for exclude url list
            if (!empty($photonClass->js_css_exclude_urls)) {
                foreach ($photonClass->js_css_exclude_urls as $js_exclude_keyword) {
                    if (!empty($js_exclude_keyword)) {
                        if (strpos($tag, trim($js_exclude_keyword)) !== false) {
                            return $tag;
                        }
                    }
                }
            }

            if ($photonClass->js_mode == 1 || $photonClass->js_mode == 3) {
                $html = str_get_html($tag);
    
                // Find all script tags
                foreach ($html->find('script') as $scriptTag) {

                    $shouldSkipOnLoad = false;
                    $scriptsrc = $scriptTag->src;

                    // if (str_contains($scriptsrc, 'http://')) {
                    //     $scriptsrc = str_replace('http://', 'https://', $scriptsrc);
                    // }

                    if (!empty($scriptsrc)) {
                        foreach ($photonClass->skipOnLoadJS as $pattern) {
                            if (strpos($scriptsrc, $pattern) !== false) {
                                $shouldSkipOnLoad = true;
                                break;
                            }
                        }

                        $scriptTag->src = $scriptsrc;
                    }
                    
                    if ($shouldSkipOnLoad) {
                        $scriptTag->setAttribute('data-berqwpSkipOnLoad', '1');
                    }
                    
                    if (empty($scriptTag->type) || $scriptTag->type == 'text/javascript') {
                        $scriptTag->setAttribute('data-type', 'text/javascript');
                        $scriptTag->type = 'text/bwp-script';
                    } else {
                        $scriptTag->setAttribute('data-type', esc_attr($scriptTag->type));
                        $scriptTag->type = 'text/bwp-script';
                    }

                }
                
                
                // Clear Simple HTML DOM object
                $tag = $html->save();
                $html->clear();
                unset($html);

                return $tag;
            }
            
            if ($photonClass->js_mode == 0) {

                if ($photonClass->cache_js && !$photonClass->use_cdn) {
                    $tag = $photonClass->optimize_external_js($tag);
                }

                $tag_src = $photonClass->get_src_from_script($tag);

                
                if (!empty($tag_src) && $photonClass->use_cdn) {
                    $script_tags_to_replace[] = $tag;
                }

                $tag = base64_encode($tag);
                


                return '<script data-berqwp-js="' . esc_attr($tag) . '"></script>'; // Remove other script tags

            } elseif ($photonClass->js_mode == 2) {

                if ($photonClass->cache_js && !$photonClass->use_cdn) {
                    return $photonClass->optimize_external_js($tag);
                } else {
                    return $tag;
                }

            }
        }, $buffer);
    
        if ($this->loading !== 'default') {
            add_filter('berqwp_buffer_before_closing_body', [$this, 'script']);
            
        }

        unset($photonClass);

        return $buffer;
    }

    function script($script_html) {
        $script_html .= "
        <script id='create-blob'>
            // Get all script tags in the document
            var scriptTags = document.getElementsByTagName('script');

            // Loop through each script tag
            for (let i = 0; i < scriptTags.length; i++) {
                const scriptTag = scriptTags[i];

                // Check if the script tag contains the data-berqwp-js attribute
                const berqwpAttribute = scriptTag.getAttribute('data-berqwp-js');
                if (berqwpAttribute) {
                    // Decode the base64 encoded string
                    const decodedString = atob(berqwpAttribute);

                    // Extract the inner content of the decoded string
                    const match = decodedString.match(/<script[^>]*>([\s\S]+)<\/script>/i);
                    if (match && match.length > 1) {
                        const innerContent = match[1];

                        // Convert the inner content into a Blob
                        const blob = new Blob([innerContent], { type: 'application/javascript' });

                        // Create a new script tag with the blob content
                        const newScriptTag = document.createElement('script');
                        newScriptTag.src = URL.createObjectURL(blob);
                        console.log(URL.createObjectURL(blob))

                        // Convert the newScriptTag HTML string to base64
                        const newScriptTagHtml = newScriptTag.outerHTML;
                        const base64Encoded = btoa(newScriptTagHtml);

                        // Add the base64 encoded string back to the data-berqwp-js attribute of the original script tag
                        scriptTag.setAttribute('data-berqwp-js', base64Encoded);
                    }
                }
            }

        </script>
        ";

        $script_html .= "
        <script type='text/javascript' async>
                            // Function to cache and load scripts
                            async function cache_file(url) {
                                const cache = await caches.open('berqwp-cache');

                                // Check if the resource is already cached
                                const cachedResponse = await cache.match(url);
                                if (cachedResponse) {
                                    console.log('Script is already cached:', url);
                                } else {
                                    // Fetch and cache the resource
                                    const response = await fetch(url, { cache: 'reload' });
                                    if (response.ok) {
                                        await cache.put(url, response.clone());
                                        console.log('Fetched and cached script:', url);
                                    } else {
                                        console.error('Failed to fetch script:', response.statusText);
                                    }
                                }
                            }
            (function() {
                var berqwpScriptsPreloaded = false;
                window.addEventListener('berqwpLCPLoaded', function() {
                    if (berqwpScriptsPreloaded) {
                        return;
                    }
                    
                    berqwpScriptsPreloaded = true;

                    // Function to preload scripts
                    function preloadScripts() {
                        const scripts = document.querySelectorAll('script[type=\"text/bwp-script\"]');
                        
                        scripts.forEach(script => {
                            if (script.src) {
                                const link = document.createElement('link');
                                link.rel = 'preload';
                                link.href = script.src;
                                link.as = 'script';
                                document.head.appendChild(link);
                            }
                        });
                    }

                    // Function to load scripts from the cache
                    function loadScripts() {
                        const scripts = document.querySelectorAll('script[type=\"text/bwp-script\"]');

                        scripts.forEach((script, index) => {
                            const newScript = document.createElement('script');
                            newScript.src = script.src;
                            newScript.type = script.getAttribute('data-type') || 'text/javascript';
                            newScript.async = false; // To maintain order of execution
                            
                            // Copy other attributes
                            Array.from(script.attributes).forEach(attr => {
                                if (attr.name !== 'type') {
                                    newScript.setAttribute(attr.name, attr.value);
                                }
                            });

                            // Replace the original script
                            script.parentNode.replaceChild(newScript, script);
                        });
                    }

                    // Preload scripts as early as possible
                    preloadScripts();

                });
            })();

        </script>
    ";


        $script_html .= "
        <script id='optimize-js' defer>
                    let js_execution_mode = '" . $this->js_mode . "';
                    let js_loading = '" . $this->loading . "';
                    let berq_content;
                    let berq_click = null;
                    var total_berq_scripts = 0;
                    var loaded_berq_scripts = 0;
                    let assets_to_cache = [];

                    var scriptTags = document.querySelectorAll('script[data-berqwp-js]');
                    scriptTags.forEach(div => {
                        const scriptData = atob(div.getAttribute('data-berqwp-js'));
                        const scriptElement = document.createRange().createContextualFragment(scriptData).children[0];

                        if (scriptElement.src) {
                            total_berq_scripts++;
                        }

                    });

                    let lcpElement = null;
                    const lcp_observer = new PerformanceObserver((list) => {
                        const entries = list.getEntries();
                        for (const entry of entries) {
                            if (entry.entryType === 'largest-contentful-paint') {
                                lcpElement = entry.element;

                                // Handle <img> elements
                                if (lcpElement.tagName === 'IMG') {
                                    // Check if the image has loaded
                                    if (lcpElement.complete && !lcpElement.src.includes('data:')) {
                                        // The image is already loaded
                                        console.log('LCP image loaded:', lcpElement.src);
                                        let berqwp_lcp_event = new CustomEvent('berqwpLCPLoaded');
                                        window.dispatchEvent(berqwp_lcp_event);
                                    } else {
                                        // Listen for the image load event
                                        lcpElement.onload = function() {
                                            console.log('LCP image loaded:', lcpElement.src);
                                            let berqwp_lcp_event = new CustomEvent('berqwpLCPLoaded');
                                            window.dispatchEvent(berqwp_lcp_event);
                                        };

                                        lcpElement.onerror = function() {
                                            console.error('Failed to load LCP image:', lcpElement.src);
                                        };
                                    }
                                }

                                // If the LCP element has a background image
                                const backgroundImage = window.getComputedStyle(lcpElement).backgroundImage;

                                if (backgroundImage && backgroundImage !== 'none') {
                                    // Extract the URL from the background-image CSS property
                                    const imageUrl = backgroundImage.slice(5, -2);
                                    
                                    // Create a new Image object to check if the background image is loaded
                                    const img = new Image();
                                    img.src = imageUrl;
                                    
                                    img.onload = function() {
                                        console.log('Background image loaded:', imageUrl);
                                        let berqwp_lcp_event = new CustomEvent('berqwpLCPLoaded');
                                        window.dispatchEvent(berqwp_lcp_event);
                                    };
                                    
                                    img.onerror = function() {
                                        console.error('Failed to load background image:', imageUrl);
                                    };
                                } else {
                                    // If there's no background image or it's already loaded
                                    let berqwp_lcp_event = new CustomEvent('berqwpLCPLoaded');
                                    window.dispatchEvent(berqwp_lcp_event);
                                }
                            }
                        }
                    });

                    lcp_observer.observe({ type: 'largest-contentful-paint', buffered: true });
                

    
                    function berqwp_js_handleUserInteraction(event) {
                        
                        if (event.type === 'click' || event.type === 'touchstart') {
                            event.preventDefault();
                            berq_click = event.target;
                        }

                        if (js_execution_mode == 0) {
                            // Get all script tags with data-berqwp-js attribute
                            var scriptTags = document.querySelectorAll('script[data-berqwp-js]');

                            // Add all external scripts into browser cache
                            scriptTags.forEach(div => {
                                const scriptData = atob(div.getAttribute('data-berqwp-js'));
                                const scriptElement = document.createRange().createContextualFragment(scriptData).children[0];
                        
                                if (scriptElement && scriptElement.src) {
                                    assets_to_cache.push(scriptElement.src);
                                }
                            });
                            
                            (async () => {
                                // Call the function to start fetching all scripts
                                await berqwp_add_assets_browser_cache(assets_to_cache);

                                // Function to execute scripts
                                function executeScriptsSequentially(scripts, index) {
                                    if (index < scripts.length) {
                                        var scriptTag = scripts[index];
                                        var berqwpJsCode = scriptTag.getAttribute('data-berqwp-js');
                                        berqwpJsCode = atob(berqwpJsCode);
                                        var parser = new DOMParser();
                                        var parsedHTML = parser.parseFromString(berqwpJsCode, 'text/html');
                                        var scriptContent = parsedHTML.querySelector('script');
                                
                                        if (scriptContent) {
                                            if (scriptContent.src) {
                                                // External script, append to the body
                                                var newScript = document.createElement('script');
                                                newScript.onload = function () {
                                                    // Execute the next script in the sequence
                                                    executeScriptsSequentially(scripts, index + 1);
                                                    loaded_berq_scripts++;
                                                };
                                                newScript.src = scriptContent.src;
    
                                                // console.log(newScript.src)
                                                // document.body.appendChild(newScript);
                                                scriptTag.parentNode.insertBefore(newScript, scriptTag.nextSibling);
    
                                            } else {
                                                var newScript = document.createElement('script');
                                                newScript.innerHTML = scriptContent.innerHTML;
    
                                                // console.log(newScript);
                                                // document.body.appendChild(newScript);
                                                scriptTag.parentNode.insertBefore(newScript, scriptTag.nextSibling);
                                                
                                                // Inline script, execute immediately
                                                // eval(scriptContent.innerHTML);
                                                // Execute the next script in the sequence
                                                executeScriptsSequentially(scripts, index + 1);
                                            }
                                        }
                                    }
                                }
                                
                                // Start executing scripts sequentially
                                executeScriptsSequentially(scriptTags, 0);

                                berq_content = true;

                            })();
                            


                        } else if (js_execution_mode == 1) {
                         
                                var scripts = document.querySelectorAll('script[type=\"text/bwp-script\"]');

                                // Function to dynamically load scripts
                                async function loadScript(index) {
                                    if (index >= scripts.length) {

                                        // setTimeout(function() {
                                            // After all scripts are loaded, dispatch events
                                            let event = new Event('DOMContentLoaded', {
                                                bubbles: true,
                                                cancelable: true
                                            });
                                            window.dispatchEvent(event);
                                            document.dispatchEvent(event);

                                            window.dispatchEvent(new Event('load'));
                                            document.dispatchEvent(new Event('load'));

                                            triggerReadyStateChange('complete');
        
                                            // Create a new resize event
                                            var resizeEvent = new Event('resize');
        
                                            // Dispatch the resize event
                                            window.dispatchEvent(resizeEvent);
                                            document.dispatchEvent(resizeEvent);
        
                                            console.log('scripts loaded.')

                                            window.dispatchEvent(new Event('berqwp_after_delay_js_loaded'));
                                        
                                        // }, 1000)
                                        return;
                                    }

                                    // Create a new script element
                                    var script = scripts[index];
                                    var newScript = document.createElement('script');
                                    // newScript.type = 'text/javascript';
                                    newScript.type = script.getAttribute('data-type');

                                    // Copy the content or src of the original script
                                    if (script.src) {
                                        newScript.src = script.src;

                                        if (script.hasAttribute('data-berqwpSkipOnLoad')) {
                                            loadScript(index + 1);
                                        } else {

                                            // Set a timeout to proceed even if onload doesn't fire
                                            var scriptTimeout = setTimeout(function() {
                                                console.warn('Script load timeout:', script.src);
                                                loadScript(index + 1);
                                            }, 5000); // 5 seconds timeout
        
                                            
                                            newScript.onload = function() {
                                                clearTimeout(scriptTimeout); // Clear timeout if script loads successfully
                                                loadScript(index + 1);
                                            };
        
                                            newScript.onerror = function() {
                                                clearTimeout(scriptTimeout); // Clear timeout if there's an error loading the script
                                                console.warn('Error loading script:', script.src);
                                                loadScript(index + 1); // Proceed to the next script
                                            };
                                        }
                                        

                                    } else {
                                        newScript.text = script.textContent;
                                        setTimeout(function() {
                                            loadScript(index + 1);
                                        }, 0); // Delay to simulate async load
                                    }

                                    // Copy other attributes if necessary
                                    Array.from(script.attributes).forEach(function(attr) {
                                        if (attr.name !== 'type') {
                                            newScript.setAttribute(attr.name, attr.value);
                                        }
                                    });

                                    // Replace the old script with the new script
                                    script.parentNode.replaceChild(newScript, script);
                                }


                                (async () => {

                                    triggerReadyStateChange('interactive');

                                    let berqwp_lcp_event = new CustomEvent('berqwpLCPLoaded');
                                    window.dispatchEvent(berqwp_lcp_event);

                                    // Start loading scripts from the first one
                                    loadScript(0);
                                    
                                })();


                            
                        } else if (js_execution_mode == 3) {

                            (function(){
                                // Select all inline script tags with type=\"text/bwp-script\"
                                const inlineScripts = document.querySelectorAll('script[type=\"text/bwp-script\"]');
                                
                                inlineScripts.forEach((script) => {
                                    // Get the content of the inline script
                                    const scriptContent = script.innerHTML;

                                    if (!scriptContent) {
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

                                    // Replace the original inline script with the new external script tag
                                    script.parentNode.replaceChild(newScript, script);
                                });
                            })();


                            (function () {
                                const scriptQueue = [];
                                let b = 0;

                                console.log('Starting script processing...');

                                triggerReadyStateChange('interactive');

                                const berqwp_lcp_event = new CustomEvent('berqwpLCPLoaded');
                                window.dispatchEvent(berqwp_lcp_event);

                                const scripts = Array.from(document.querySelectorAll('script[type=\"text/bwp-script\"], script[type=\"application/javascript\"]'));

                                if (scripts.length === 0) {
                                    console.warn('No scripts found. Exiting...');
                                    return;
                                }

                                const preloadScript = (src, id, type) => {
                                    console.log(`Preloading script: \${src}`);
                                    return new Promise((resolve) => {
                                        fetch(src)
                                            .then(response => {
                                                if (!response.ok) {
                                                    throw new Error(`Failed to preload script \${id}: \${response.status}`);
                                                }
                                                return response.text();
                                            })
                                            .then(code => {
                                                console.log(`Script \${id} preloaded successfully.`);
                                                resolve({ id, src, code });
                                            })
                                            .catch(error => {
                                                console.warn(`Error preloading script \${id}:`, error);
                                                resolve(null);  // Resolve with null to skip this script and continue execution
                                            });
                                    });
                                };

                                const preloadInlineScript = (code, id, type) => {
                                    console.log(`Preloading inline script: \${id}`);
                                    return Promise.resolve({ id, code, type });
                                };

                                const executeScriptsSequentially = async (scriptsToExecute) => {
                                    console.log('Starting sequential script execution...');
                                    for (let i = 0; i < scriptsToExecute.length; i++) {
                                        const scriptData = scriptsToExecute[i];
                                        if (!scriptData) {
                                            continue;  // Skip this script if it was skipped during preloading
                                        }

                                        try {
                                            console.log(`Executing script: \${scriptData.id}`);
                                            const scriptElement = document.createElement('script');
                                            scriptElement.id = scriptData.id;
                                            scriptElement.type = scriptData.type;

                                            if (scriptData.src) {
                                                scriptElement.src = scriptData.src;
                                            } else {
                                                scriptElement.textContent = scriptData.code;
                                            }

                                            // Adding a 1ms delay before executing each script
                                            setTimeout(() => {
                                                document.head.appendChild(scriptElement);
                                                console.log(`Script \${scriptData.id} executed.`);

                                                // Dispatch lifecycle events only after the very last script
                                                if (i === scriptsToExecute.length - 1) {
                                                    // Dispatching lifecycle events
                                                    const event = new Event('DOMContentLoaded', { bubbles: true, cancelable: true });
                                                    window.dispatchEvent(event);
                                                    document.dispatchEvent(event);

                                                    triggerReadyStateChange('complete');

                                                    window.dispatchEvent(new Event('load'));
                                                    document.dispatchEvent(new Event('load'));

                                                    const resizeEvent = new Event('resize');
                                                    window.dispatchEvent(resizeEvent);
                                                    document.dispatchEvent(resizeEvent);

                                                    console.log('Scripts fully loaded.');
                                                    window.dispatchEvent(new Event('berqwp_after_delay_js_loaded'));
                                                }
                                            }, 1);  // 1ms delay

                                        } catch (error) {
                                            console.error(`Error executing script \${scriptData.id}:`, error);
                                        }
                                    }
                                    console.log('All scripts executed.');
                                };

                                // Step 1: Preload all scripts
                                for (let i = 0; i < scripts.length; i++) {
                                    const script = scripts[i];
                                    const id = script.id || `script-\${b++}`;
                                    const type = script.getAttribute('data-type');

                                    if (script.src) {
                                        scriptQueue.push(preloadScript(script.src, id, type));
                                    } else {
                                        const code = script.textContent || script.innerText;
                                        scriptQueue.push(preloadInlineScript(code, id, type));
                                    }
                                }

                                // Step 2: Execute scripts as soon as they're preloaded
                                Promise.all(scriptQueue)
                                    .then(preloadedScripts => executeScriptsSequentially(preloadedScripts))
                                    .catch(error => {
                                        console.error('Error during script processing:', error);
                                    });
                            })();

                        }

                        
                    
                        // After running the function, remove all event listeners to ensure it runs only once
                        for (let eventType of berqwp_js_interactionEventTypes) {
                            window.removeEventListener(eventType, berqwp_js_handleUserInteraction);
                        }
                    }
                    
                    let berqwp_js_interactionEventTypes = ['click', 'mousemove', 'keydown', 'touchstart', 'scroll', 'berqwpLoadJS', 'berqwp_interaction_event'];
                    
                    if (js_loading == 'preload') {
                        berqwp_js_interactionEventTypes = ['load', 'berqwpStylesLoaded'];
                        
                        // berqwp_js_interactionEventTypes.push('berqwpStylesLoaded');
                    }
                    
                    for (let eventType of berqwp_js_interactionEventTypes) {
                        window.addEventListener(eventType, berqwp_js_handleUserInteraction, { passive: false });
                    }

                    if (js_loading == 'preload' && !document.getElementById('preload-styles')) {
                        // Trigger event to load JavaScript
                        let berqwp_load_js_event = new CustomEvent('berqwpLoadJS');
            
                        // Dispatch the custom event
                        window.dispatchEvent(berqwp_load_js_event);
                    }
                    
                    setInterval(function () {

                        if (berq_content == true) {
                            berq_content = false;
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
                            


                        }

                        if (berq_click && total_berq_scripts == loaded_berq_scripts) {
                            setTimeout(function() {
                                console.log(berq_click);
                                const clickEvent = new MouseEvent('click', {
                                    bubbles: true,
                                    cancelable: true,
                                    view: window
                                });
                                berq_click.dispatchEvent(clickEvent);
                                berq_click = null;
                            }, 500)
                        }
    
                    }, 2000);
    
                    var berq_timeo;
                    if (window.screen.width <= 999) {
                        berq_timeo = 3000;
                    } else {
                        berq_timeo = 4000;
                    }
    
        </script>
        ";

        return $script_html;
    }
}
