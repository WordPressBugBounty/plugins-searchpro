<?php
if (!defined('ABSPATH')) exit;

use voku\helper\HtmlDomParser;

class berqUsedCSS {

    private static $html = null;

    public $combine_css = '';
    public $domain = '';
    public $site_url = '';
    public $js_classes = [];

    static $forceExclude = [
        '/:focus[^)]/',
        '/:hover[^)]/',
    ];
    static $forceInclude = [
        '/\[.*\$.*=.*\]/',
        // '/:has\(.*?\)/',
        // '/:not\(.*?\)/',
        // '/:is\(.*?\)/',
        // '/:where\(.*?\)/',
        // '/^\:is/',
        // '/^\:where/',
        '/\.elementor-motion-effects-element-type-background/',
        '/\.swiper-wrapper/',
        '/\.woocommerce-js/',
        '/\.et_pb_menu__logo img/',
        '/\.tns-/',
        '/\.woocommerce-js/',
        '/\.dtb-hamburger/',
        '/\.elementor-social-icon/',
        '/\.visible/',
        '/^\.td-js-loaded/',
        '/^\.lte-navwrapper-mobile/',
        '/^\.lte-navbar-items.navbar-mobile/',
        '/^\.navbar-mobile/',
        '/^\.theme-menu-mode-responsive/',
        '/^\.theme-menu-responsive-button/',
        '/^\.stk-video-background/',
        '/^\.is-menu.is-dropdown/',
        '/^\.animated/',
        '/^\.et_pb_animation/',
        '/^\.et_animated/',
        '/^\.et_mobile_menu/',
        '/^\.awb-menu.collapse-enabled/',
        '/^\.collapse-enabled/',
        '/^\.do-animate/',
        '/^\.ast-header-break-point/',
        '/^\.plus-canvas-content-wrap/',
        '/^\.elementor-element:where/',
        '/^\.bounce/',
        '/^\.bounceIn/',
        '/^\.bounceInDown/',
        '/^\.bounceInLeft/',
        '/^\.bounceInRight/',
        '/^\.bounceInUp/',
        '/^\.e-animation-bob/',
        '/^\.e-animation-bounce-in/',
        '/^\.e-animation-bounce-out/',
        '/^\.e-animation-buzz-out/',
        '/^\.e-animation-buzz/',
        '/^\.e-animation-float/',
        '/^\.e-animation-grow-rotate/',
        '/^\.e-animation-grow/',
        '/^\.e-animation-hang/',
        '/^\.e-animation-pop/',
        '/^\.e-animation-pulse-grow/',
        '/^\.e-animation-pulse-shrink/',
        '/^\.e-animation-pulse/',
        '/^\.e-animation-push/',
        '/^\.e-animation-rotate/',
        '/^\.e-animation-shrink/',
        '/^\.e-animation-sink/',
        '/^\.e-animation-skew-backward/',
        '/^\.e-animation-skew-forward/',
        '/^\.e-animation-skew/',
        '/^\.e-animation-wobble-bottom/',
        '/^\.e-animation-wobble-horizontal/',
        '/^\.e-animation-wobble-skew/',
        '/^\.e-animation-wobble-to-bottom-right/',
        '/^\.e-animation-wobble-to-top-right/',
        '/^\.e-animation-wobble-top/',
        '/^\.e-animation-wobble-vertical/',
        '/^\.fadeIn/',
        '/^\.fadeInDown/',
        '/^\.fadeInLeft/',
        '/^\.fadeInRight/',
        '/^\.fadeInUp/',
        '/^\.flash/',
        '/^\.headShake/',
        '/^\.jello/',
        '/^\.lightSpeedIn/',
        '/^\.pulse/',
        '/^\.rollIn/',
        '/^\.rotateIn/',
        '/^\.rotateInDownLeft/',
        '/^\.rotateInDownRight/',
        '/^\.rotateInUpLeft/',
        '/^\.rotateInUpRight/',
        '/^\.rubberBand/',
        '/^\.shake/',
        '/^\.slideInDown/',
        '/^\.slideInLeft/',
        '/^\.slideInRight/',
        '/^\.slideInUp/',
        '/^\.swing/',
        '/^\.tada/',
        '/^\.wobble/',
        '/^\.zoomIn/',
        '/^\.zoomInDown/',
        '/^\.zoomInLeft/',
        '/^\.zoomInRight/',
        '/^\.zoomInUp/',
    ];

    function __construct($site_url) {
        $this->site_url = $site_url;
    }

    function forceInclude($selectors) {
        self::$forceInclude =  array_merge(self::$forceInclude, $selectors);
    }

    function process_css($html)
    {

        // self::$html = $this->cleanHtml($html);
        self::$html = $html;
        $selectors = [];
        // $this->js_classes = $this->get_js_selector_classes($html);

        $dom = HtmlDomParser::str_get_html($html);
        $used_fonts = [];
        $used_font_weights = [
            'normal' => 1,
            '400' => 1,
        ];
        $used_css = '';

        foreach ($dom->find('style') as $style) {
            $style->setAttribute('data-berqwp-critical-css', '1');
        }

        // mark stylesheet links
        foreach ($dom->find('link') as $link) {
            $rel = strtolower($link->rel ?? '');
            $as = strtolower($link->as ?? '');

            if ($rel === 'stylesheet' || $as === 'style') {
                $link->setAttribute('data-berqwp-critical-css', '1');
            }
        }

        $nodes = $dom->find('[data-berqwp-critical-css="1"]');


        foreach ($nodes as $css_node) {
            $local_css = '';

            if ($css_node->tag == 'style') {
                $local_css = "@media all { ".$css_node->innertext." } ";

            }

            if ($css_node->tag == 'link') {

                $href = $css_node->href ?? '';
                $media = $css_node->media ?? '';
                $onload = $css_node->onload ?? '';

                if ($onload === "this.media='all'") {
                    $media = 'all';
                }

                if ($onload === "this.media=&#039;all&#039;;this.onload=null;") {
                    $media = 'all';
                }

                if ($media === 'print') {
                    continue;
                }

                if (!self::is_valid_asset($href)) {
                    continue;
                }

                // convert to abs
                $baseUrl = self::getBaseUrl($this->site_url);
                $href = self::rel2abs($href, $baseUrl);

                $response = self::fetch($href);

                if ($response === false) {
                    continue;
                }

                $local_css .= $response;
            }

            if (!empty($local_css)) {
                $matches = self::extractUrlsFromCss($local_css);

                foreach ($matches as $url) {
                    $url = trim($url);

                    if (!empty($url) && self::is_valid_asset($url)) {
                        $local_css = $this->replace_url($url, self::rel2abs($url, $href), $local_css);
                    }
                }

                if (!empty($media) && $media !== 'screen') {
                    $local_css = "@media $media {{$local_css}}";
                }

                $this->combine_css .= $local_css;

                $css = $this->extract_used_css($local_css, $selectors);
                $css = $this->minify_css($css);
                $css = preg_replace('/@media[^{]+\{\s*\}/i', '', $css);
                $css = preg_replace('/@@+/', '', $css);
                $used_css .= $css;

                preg_match_all("/font-family\s*:\s*([^!;}]+)/", $css, $mf);
                preg_match_all("/font-weight\s*:\s*([^!;}]+)/", $css, $mw);

                if (!empty($mf)) {

                    foreach ($mf[1] as $ff) {
                        if (strpos($ff, ',') !== false) {

                            foreach (explode(',', $ff) as $fm) {
                                $fm = trim($fm, " \t\n\r\0\x0B'\"");
                                $used_fonts[$fm] = 1;
                            }
                        } else {
                            $fm = trim($ff, " \t\n\r\0\x0B'\"");
                            $used_fonts[$fm] = 1;
                        }
                    }

                    foreach ($mw[1] as $fw) {
                        $weight = trim($fw, " \t\n\r\0\x0B'\"");
                        $used_font_weights[$weight] = 1;
                    }
                }


                // Insert style tag before the link tag
                $style_tag = '<style data-berqwp="inline" data-berqwp-critical-inline="1">' . $css . '</style>';
                $css_node->outertext = $style_tag . $css_node->outertext;
            }
        }

        $font_faces = $this->used_font_faces($used_fonts, $used_font_weights);

        $font_faces = str_replace('font-display:block;', 'font-display:swap;', $font_faces);
        $font_faces = str_replace('font-style:', 'font-display:swap;font-style:', $font_faces);

        // $used_keyframes = $this->used_keyframes($used_css);
        // $used_varirables = $this->extract_used_variables($used_css);

        // if (!empty($used_varirables)) {
        //     $used_varirables = ':root{' . implode(';', $used_varirables) . '}';
        // } else {
        //     $used_varirables = '';
        // }

        return $used_css . $font_faces;
        
    }

    function has_cache($url)
    {

        $path = WP_CONTENT_DIR . '/cache/berqwp/external/';

        if (!is_dir($path)) {
            if (!wp_mkdir_p($path)) {
                return false;
            }
        }

        $file_name = md5($url) . '.gz';
        $file_path = $path . $file_name;

        // return is_file($file_path) && !empty(@filemtime($file_path)) && @filemtime($file_path) > (time() - DAY_IN_SECONDS);
        return is_file($file_path) && !empty(@filemtime($file_path)) && @filemtime($file_path) > (time() - DAY_IN_SECONDS);
        // return is_file($file_path);
    }

    function cache_asset($url, $content)
    {
        $path = WP_CONTENT_DIR . '/cache/berqwp/external/';

        if (!is_dir($path)) {
            if (!wp_mkdir_p($path)) {
                return false;
            }
        }

        $file_name = md5($url) . '.gz';

        return file_put_contents($path . $file_name, gzencode($content, 9), LOCK_EX);
    }

    function cache_miss($url)
    {
        $path = WP_CONTENT_DIR . '/cache/berqwp/external/';
        if (!is_dir($path)) {
            if (!wp_mkdir_p($path)) return false;
        }
        return file_put_contents($path . md5($url) . '.gz', gzencode('berqwp-404', 9), LOCK_EX);
    }

    function is_cached_miss($url)
    {
        if (!$this->has_cache($url)) return false;
        return $this->get_cache($url) === 'berqwp-404';
    }

    function get_cache($url)
    {
        $path = WP_CONTENT_DIR . '/cache/berqwp/external/';

        if (!is_dir($path)) {
            if (!wp_mkdir_p($path)) {
                return false;
            }
        }

        $file_name = md5($url) . '.gz';

        return gzdecode(file_get_contents($path . $file_name));
    }

    function fetch($url)
    {

        $local_css = '';

        if ($this->is_self_hosted_url($url)) {
            if ($this->is_cached_miss($url)) {
                return false;
            }
            $local_path = $this->url_to_path($url);
            if ($local_path && is_file($local_path)) {
                return file_get_contents($local_path);
            }
            $this->cache_miss($url);
            return false;
        }

        if ($this->is_cached_miss($url)) {
            return false;
        }

        if ($this->has_cache($url)) {
            $local_css = $this->get_cache($url);
        } else {

            $response = wp_remote_get($url, ['timeout' => 30]);
            $is_valid_response = wp_remote_retrieve_response_code($response) >= 200 && wp_remote_retrieve_response_code($response) < 400;

            if (is_wp_error($response) || !$is_valid_response) {
                $this->cache_miss($url);
                return false;
            }

            $local_css = wp_remote_retrieve_body($response);

            // preg_match_all("/@import\s+url\(([^)]+)[^;]+;/", $local_css, $matches);
            preg_match_all('/@import\s+(?:url\s*\(\s*[\'"]?([^\'")]+)[\'"]?\s*\)|[\'"]([^\'";]+)[\'"])/', $local_css, $matches);

            if (!empty($matches[1])) {
                foreach ($matches[1] as $import_css_url) {
                    $import_css_url = trim($import_css_url, " \t\n\r\0\x0B'\"");

                    $baseUrl = self::getBaseUrl($url);
                    $import_css_url = self::rel2abs($import_css_url, $baseUrl);

                    if (!self::is_valid_asset($import_css_url)) {
                        continue;
                    }

                    $sub_response = self::fetch($import_css_url);

                    if ($sub_response === false) {
                        continue;
                    }

                    $url_matches = self::extractUrlsFromCss($sub_response);

                    foreach ($url_matches as $url_func) {
                        $url_func = trim($url_func);

                        if (!empty($url_func)) {
                            // $sub_response = str_replace($url_func, self::rel2abs($url_func, $import_css_url), $sub_response);
                            $sub_response = $this->replace_url($url_func, self::rel2abs($url_func, $import_css_url), $sub_response);
                        }
                    }

                    $local_css .= $sub_response;
                }
            }

            $this->cache_asset($url, $local_css);
        }

        return $local_css;
    }

    function replace_url($search, $replace, $content)
    {

        // match with single quotes
        if (strpos($content, "'$search'") !== false) {
            $content = str_replace("'$search'", "'$replace'", $content);
        }

        // match double quotes
        if (strpos($content, "\"$search\"") !== false) {
            $content = str_replace("\"$search\"", "\"$replace\"", $content);
        }

        // match brakets
        if (strpos($content, "($search)") !== false) {
            $content = str_replace("($search)", "($replace)", $content);
        }

        // match spaces
        if (strpos($content, " $search ") !== false) {
            $content = str_replace(" $search ", " $replace ", $content);
        }

        return $content;
    }

    static function is_valid_asset($url)
    {
        return strpos($url, 'data:') === false;
    }

    public static function rel2abs($rel, $base)
    {
        /* return if already absolute URL */
        if (parse_url($rel, PHP_URL_SCHEME) != '')
            return $rel;

        // Return with base scheme if protocol-relative (starts with //)
        if (strpos($rel, '//') === 0) {
            $scheme = parse_url($base, PHP_URL_SCHEME) ?: 'https';
            return $scheme . ':' . $rel;
        }

        /* queries and anchors */
        if ($rel[0] == '#' || $rel[0] == '?')
            return $base . $rel;

        /* parse base URL and convert to local variables:
           $scheme, $host, $path */
        extract(parse_url($base));

        /* remove non-directory element from path */
        $path = preg_replace('#/[^/]*$#', '', $path);

        /* destroy path if relative url points to root */
        if ($rel[0] == '/')
            $path = '';

        /* dirty absolute URL */
        $abs = "$host$path/$rel";

        /* replace '//' or '/./' or '/foo/../' with '/' */
        $re = array('#(/\.?/)#', '#/(?!\.\.)[^/]+/\.\./#');
        for ($n = 1; $n > 0; $abs = preg_replace($re, '/', $abs, -1, $n)) {
        }

        /* absolute URL is ready! */
        return $scheme . '://' . $abs;
    }

    public static function getBaseUrl($fileUrl)
    {
        // Parse the URL and get its components
        $parsedUrl = parse_url($fileUrl);
        $scheme = isset($parsedUrl['scheme']) ? $parsedUrl['scheme'] . '://' : '';
        $host = isset($parsedUrl['host']) ? $parsedUrl['host'] : '';
        $path = isset($parsedUrl['path']) ? $parsedUrl['path'] : '';

        // Remove the file name from the path to get the base URL
        $basePath = preg_replace('/\/[^\/]+$/', '/', $path);

        // Construct the base URL
        return $scheme . $host . $basePath;
    }

    public static function extractUrlsFromCss($cssContent)
    {
        $urls = [];
        $pattern = '/url\((.*?)\)/i';

        preg_match_all($pattern, $cssContent, $matches);

        if (!empty($matches[1])) {
            foreach ($matches[1] as $match) {
                $urls[] = trim($match, '\'" ');
            }
        }

        return $urls;
    }

    function is_self_hosted_url($url)
    {
        if (empty($url)) {
            return false;
        }

        // Relative URLs are self-hosted
        if (strpos($url, '//') === false) {
            return true;
        }

        $site_host = wp_parse_url(site_url(), PHP_URL_HOST);
        $url_host  = wp_parse_url($url, PHP_URL_HOST);

        return $site_host && $url_host && strtolower($site_host) === strtolower($url_host);
    }

    function url_to_path($url)
    {

        if (strpos($url, '?') !== false) {
            $url = explode('?', $url)[0];
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

    function used_font_faces($used_fonts, $used_font_weights)
    {

        preg_match_all("/@font-face\s*{[^}]*}/", $this->combine_css, $matches);
        $fonts = $matches[0];
        $all_fonts = [];
        $used_font_faces = '';

        foreach ($fonts as $font) {
            preg_match("/font-family\s*:\s*([^;}]+)/", $font, $mf);
            preg_match("/font-weight\s*:\s*([^;}]+)/", $font, $mw);

            $family = trim($mf[1], " \t\n\r\0\x0B'\"");
            $weight = trim($mw[1], " \t\n\r\0\x0B'\"");

            if (!empty($used_fonts[$family]) && !empty($used_font_weights[$weight])) {
                $used_font_faces .= $font;
            }

            unset($font);
        }

        return $used_font_faces;
    }

    function used_keyframes($used_css)
    {
        preg_match_all('/animation\s*:\s*([^\s!;}]+)/', $used_css, $matches);

        if (empty($matches[1])) {
            return '';
        }

        $key_frame_css = '';
        $used_keyframe_names = [];

        foreach ($matches[1] as $animation_name) {
            $animation_name = trim($animation_name);
            $used_keyframe_names[$animation_name] = 1;
        }

        $captured = 0;

        preg_match_all('/@keyframes\s+[\w-]+\s*\{(?:[^{}]*|\{[^{}]*\})*\}/', $this->combine_css, $keyframe_matches);

        if (!empty($keyframe_matches)) {
            foreach ($keyframe_matches[0] as $keyframe) {


                // if ($captured === count($used_keyframe_names)) {
                //     break;
                // }

                preg_match('/@keyframes\s+([^\s{]+)/', $keyframe, $kf_name_match);

                if (isset($used_keyframe_names[$kf_name_match[1]])) {
                    $key_frame_css .= $keyframe;
                    $captured++;
                }
            }
        }

        return $key_frame_css;
    }

    function extract_used_variables($used_css)
    {

        // preg_match_all("/var\s*\(\s*([^\s)]+)/", $used_css, $matches);
        preg_match_all("/var\s*\(\s*([^)]+)/", $used_css, $matches);

        if (!empty($matches[1])) {
            foreach ($matches[1] as $index => $value) {
                if (strpos($value, ',') !== false) {
                    foreach (explode(',', $value) as $new_dec) {
                        $matches[1][] = trim($new_dec);
                    }
                    unset($matches[1][$index]);
                }
            }
            return $this->extract_variable_declarations($matches[1]);
        }
    }

    function extract_variable_declarations($variables)
    {

        $variables = array_map(function ($item) {
            return trim($item);
        }, $variables);

        $variables = array_unique($variables);
        $variable_proerties = [];
        // preg_match_all("/:root\s*{([^;}]+)/", $this->combine_css, $matches);
        preg_match_all("/:root\s*{\s*([^}]+)/", $this->combine_css, $matches);


        // collected nested var() in :root
        foreach ($matches[1] as $properties) {
            $prop_arr = explode(';', $properties);
            $prop_arr = array_filter($prop_arr, function ($property) use ($properties, &$variables) {
                $property_name = explode(':', $property)[0];
                $property_value = trim(explode(':', $property)[1]);

                if (!empty($property_value) && strpos($property_value, 'var(') !== false) {
                    preg_match_all("/var\s*\(\s*([^)]+)/", $property_value, $var_matches);

                    foreach ($var_matches[1] as $index => $value) {
                        if (strpos($value, ',') !== false) {
                            foreach (explode(',', $value) as $new_dec) {
                                $var_matches[1][] = trim($new_dec);
                            }
                            unset($var_matches[1][$index]);
                        }
                    }

                    $variables = array_merge($variables, $var_matches[1]);
                }

                return !empty(trim($property_name)) && in_array(trim($property_name), $variables);
            });
        }

        $variables = array_unique($variables);

        foreach ($matches[1] as $properties) {
            $prop_arr = explode(';', $properties);
            $prop_arr = array_filter($prop_arr, function ($property) use ($variables) {
                $property_name = explode(':', $property)[0];
                $property_value = trim(explode(':', $property)[1]);

                if (!empty(trim($property_name)) && strpos(trim($property_name), '--') !== 0) {
                    return true;
                }

                return !empty(trim($property_name)) && in_array(trim($property_name), $variables);
            });

            if (!empty($prop_arr)) {
                foreach ($prop_arr as $prop) {
                    $variable_proerties[] = $prop;
                }
            }
        }

        return $variable_proerties;
        // return $this->extract_used_variables();
    }

    function cleanHtml($buffer) {
        $dom = HtmlDomParser::str_get_html($buffer);

        // Remove tags
        foreach ($dom->find('meta, style, script, noscript, link') as $node) {
            $node->outertext = '';
        }

        // Strip the head tag but keep body intact
        // $head = $dom->find('head', 0);
        // if ($head) {
        //     $head->innertext = ''; // Only safe after children are already emptied
        // }

        return (string) $dom;
    }

    static function next_token($sel, $current = 0)
    {
        // $tokens = [' ', '.', '#', '>', '+', '~'];
        $tokens = [' ', '.', '#'];
        // $tokens = ['.', '#',];
        $characters = str_split($sel);

        foreach ($characters as $index => $char) {

            if ($index >= $current && in_array($char, $tokens)) {

                if (!isset($characters[$index - 1])) {
                    continue;
                }

                if ($char === '.' && $characters[$index - 1] === "\\") {
                    continue;
                }

                if ($char === '#' && $characters[$index - 1] === "\\") {
                    continue;
                }

                return ['char' => $char, 'pos' => $index];
            }
        }

        return false;
    }

    private static function matches_js_class($class, $js_classes)
    {
        foreach ($js_classes as $pattern) {
            // Check if pattern ends with wildcard
            if (substr($pattern, -1) === '*') {
                // Wildcard pattern - check if class starts with prefix
                $prefix = substr($pattern, 0, -1); // Remove the '*'
                if (strpos($class, $prefix) === 0) {
                    return true;
                }
            } else {
                // Exact match
                if ($class === $pattern) {
                    return true;
                }
            }
        }

        return false;
    }


    private static function is_selector_used($selector, $html_selectors, $js_classes = [], $js_classes_hash = '')
    {

        if (empty($selector)) {
            return false;
        }

        // forece exclude
        foreach (self::$forceExclude as $keyword) {
            if (preg_match($keyword, $selector)) {
                return false;
            }
        }

        // var_dump($selector);

        if ($selector == ':root') {
            return true;
        }

        // force include
        foreach (self::$forceInclude as $keyword) {
            if (preg_match($keyword, $selector)) {
                return true;
            }
        }

        // eliminate false negatives (:not(), pseudo, etc...)
        // $selector = preg_replace('/(?<!\\\\)::?[a-zA-Z0-9_-]+(\(.+?\))?/', '', $selector);
        $selector = preg_replace('/:{1,2}[a-zA-Z-]+(\((?:[^()]*|\([^()]*\))*\))?/', '', $selector);
        $selector = trim($selector); // Remove leading/trailing spaces after pseudo-element removal

        // Return false if selector is empty after cleanup
        if (empty($selector)) {
            return true;
        }

        $selector = str_replace(' > ', ' ', $selector);
        $selector = str_replace('>', ' ', $selector);
        $selector = str_replace(' + ', ' ', $selector);
        $selector = str_replace('+', ' ', $selector);
        $selector = str_replace(' ~ ', ' ', $selector);
        $selector = str_replace('~', ' ', $selector);
        $selector = str_replace(' *', '', $selector);


        // $selector = str_replace("\\", '', $selector);
        // $selector = preg_replace('/\\\\(.)/u', '$1', $selector);

        // Conservative handling: keep CSS for selectors with universal selector (*)
        // Universal selectors require checking if ANY element exists
        if (strpos($selector, '*') !== false) {
            return true; // Keep CSS for universal selector
        }


        // var_dump($selector);
        // Remove JS-added classes from selector before checking
        // This allows checking the remaining non-JS parts of the selector

        if (strpos($selector, '.') !== false && !empty($js_classes)) {
            // var_dump($selector, $js_classes);
            // Extract all classes from the selector
            preg_match_all('/\.([a-zA-Z0-9_\\\:\\/\\-]+)/', $selector, $matches);
            if (!empty($matches[1])) {
                $has_js_class = false;

                foreach ($matches[1] as $class) {
                    // Check if this class matches any JS class pattern (exact or wildcard)
                    if (self::matches_js_class($class, $js_classes)) {
                        // Remove this class from the selector
                        $selector = preg_replace('/\.' . preg_quote($class, '/') . '(?=[\s.#>+~\[\]:,)]|$)/', '', $selector);
                        $has_js_class = true;
                        // $selector = trim($selector);
                    }
                }

                if ($has_js_class) {

                    // Clean up selector: remove extra spaces, leading/trailing spaces
                    $selector = preg_replace('/\s+/', ' ', $selector);
                    $selector = trim($selector);

                    // var_dump($selector);
                    // echo "<br>";

                    // If selector is now empty or just spaces, return true (was all JS classes)
                    if (empty($selector)) {
                        return true;
                    }
                }
            }
        }

        // Handle selectors starting with HTML tags
        $starts_with_tag = false;
        $tag_name = '';

        if (!in_array($selector[0], ['.', '#', ' '])) {
            // Extract tag name
            if (preg_match('/^([a-zA-Z][a-zA-Z0-9-]*)/i', $selector, $matches)) {
                $tag_name = strtolower($matches[1]);
                $tag_length = strlen($matches[1]);

                // Check if the tag exists in HTML
                // if (!isset($html_selectors['tags'][$tag_name])) {
                //     return false; // Tag not in HTML, selector can't match
                // }

                $tag_regex = '<' . preg_quote($tag_name, '/') . '\\b[^>]*>';

                if (!preg_match("/$tag_regex/", self::$html)) {
                    // var_dump($tag_regex);
                    return false; // Tag not in HTML, selector can't match
                }

                $starts_with_tag = true;

                // Remove tag from selector for token processing
                $selector = substr($selector, $tag_length);
                $selector = ltrim($selector); // Remove any leading space after tag

                // If nothing left after tag, return true (pure tag selector)
                if (empty($selector)) {
                    return true;
                }
            } else {
                // Can't parse as tag, return conservative true
                return true;
            }
        }

        $token = self::next_token($selector);
        $reg = '';

        $prev = null;
        $current = null;
        $next = null;
        $last_pos = -1; // Track last position
        $iteration_count = 0; // Track iterations
        $max_iterations = strlen($selector) * 1; // Reasonable max: 3 iterations per character

        $prev_class_pos = [];
        $herahicle_match = [];

        while ($token !== false) {
            $char = $token['char'];
            $pos = $token['pos'];

            // Safety check 1: Position hasn't advanced
            if ($pos === $last_pos) {
                // error_log("is_selector_used: Infinite loop detected - same position: $pos, selector: $selector");
                break;
            }

            // Safety check 2: Too many iterations
            $iteration_count++;
            if ($iteration_count > $max_iterations) {
                // error_log("is_selector_used: Infinite loop detected - max iterations exceeded ($iteration_count), selector: $selector");
                break;
            }

            $last_pos = $pos;
            $current = $char;

            // error_log("is_selector_used: Loop iteration $iteration_count - char: '$char', pos: $pos"); // DEBUG: Commented out for performance

            if ($char == '.') {
                // var_dump( $pos);
                // echo "<br>";
                $next_token = self::next_token($selector, $pos + 1);
                $next = $next_token['char'] ?? false;
                $length = ($next_token['pos'] ?? strlen($selector)) - $pos;
                $class = substr($selector, $pos + 1, $length - 1);
                $token = $next_token;


                $class = preg_replace('/\\\\(.)/u', '$1', $class);
                // var_dump('class', $class);
                // if (strpos($class, '\[') === false) {

                // }

                // $class = str_replace("\\", '', $class);

                if (strpos($class, '\[') === false && strpos($class, '[') !== false) {

                    preg_match_all('/\[((?:[^]]+)?)/', $class, $attr_matches);
                    $class = preg_replace("/\[[^>\]]+]/", '', $class);

                    foreach ($attr_matches[1] as $match) {
                        $match = explode('=', $match)[0];
                        $match  = preg_quote($match, '/');
                        $match_att = '<(?:[^>]+)?' . $match . '(?:[^>]+)?>';

                        if (!preg_match("/$match_att/", self::$html)) {
                            return false;
                        }
                    }
                }

                $esc = preg_quote($class, '/');

                // var_dump($esc);

                if ($prev == null && $next == ' ') {
                    $reg .= 'class\s*=\s*[\'"](?=[^"\']*(?:\s' . $esc . '|' . $esc . '\s|\b' . $esc . '\b))[^\'"]*[\'"][^>]*>';
                }

                if ($prev == null && $next == '.') {
                    $reg .= 'class\s*=\s*[\'"](?=[^"\']*(?:\s' . $esc . '|' . $esc . '\s|\b' . $esc . '\b))';
                }

                if ($prev == '.' && $next == '.') {
                    $reg .= '(?=[^"\']*(?:\s' . $esc . '|' . $esc . '\s))';
                }

                if ($prev == '.' && $next == ' ') {
                    $reg .= '(?=[^"\']*(?:\s' . $esc . '|' . $esc . '\s|\b' . $esc . '\b))(?:[^\'"]+)?[\'"][^>]*>';
                }

                if ($prev == ' ' && $next === false) {
                    $reg .= 'class\s*=\s*[\'"](?=[^"\']*(?:\s' . $esc . '|' . $esc . '\s|\b' . $esc . '\b))(?:[^\'"]+)?[\'"][^>]*>';
                }

                if ($prev == ' ' && $next == '.') {
                    $reg .= 'class\s*=\s*[\'"](?=[^"\']*(?:\s' . $esc . '|' . $esc . '\s|\b' . $esc . '\b))';
                }

                if ($prev == '.' && $next === false) {
                    $reg .= '(?=[^"\']*(?:\s' . $esc . '|' . $esc . '\s|\b' . $esc . '\b))(?:[^\'"]+)?[\'"]';
                }

                if ($prev == null && $next === false) {
                    $reg .= 'class\s*=\s*[\'"][^\'"]*' . $esc . '(?:[^\'"]+)?[\'"][^>]*>';
                }

                if ($prev == '#' && $next === ' ') {
                    $reg .= '.*?class\s*=\s*[\'"](?=[^"\']*(?:\s' . $esc . '|' . $esc . '\s|\b' . $esc . '\b))(?:[^\'"]+)?[\'"][^>]*>';
                }
            } elseif ($char == ' ') {
                $next_token = self::next_token($selector, $pos + 1);
                $length = ($next_token['pos'] ?? strlen($selector)) - $pos;
                $class = substr($selector, $pos + 1, $length - 1);
                $next = $next_token['char'] ?? false;
                $token = $next_token;

                // $reg .= ".*?<[^>]+";
                // var_dump($reg);
                if (!empty($reg)) {
                    if (preg_match("/$reg/", self::$html, $matches)) {
                        $prev = $char;
                        // $herahicle_match[] = $reg;
                        $reg = '';
                    } else {
                        return false;
                    }
                }
            } elseif ($char == '#') {
                $next_token = self::next_token($selector, $pos + 1);
                $next = $next_token['char'] ?? false;
                $length = ($next_token['pos'] ?? strlen($selector)) - $pos;
                $id = substr($selector, $pos + 1, $length - 1);
                $token = $next_token;
                
                $id = preg_replace('/\\\\(.)/u', '$1', $id);

                if (strpos($id, '\[') === false && strpos($id, '[') !== false) {
                    preg_match_all('/\[((?:[^]]+)?)/', $id, $attr_matches);
                    $id = preg_replace("/\[[^>\]]+]/", '', $id);

                    foreach ($attr_matches[1] as $match) {
                        $match = explode('=', $match)[0];
                        $match  = preg_quote($match, '/');
                        $match_att = '<(?:[^>]+)?' . $match . '(?:[^>]+)?>';

                        if (!preg_match("/$match_att/", self::$html)) {
                            return false;
                        }
                    }
                }

                $esc = preg_quote($id, '/');
                // var_dump($esc, $token['pos']);

                if ($prev == null && $next === false) {
                    $reg .= 'id\s*=\s*[\'"][^\'"]*' . $esc . '(?:[^\'"]+)?[\'"][^>]*>';
                }

                if ($prev == null && $next === '.') {
                    $reg .= 'id\s*=\s*[\'"][^\'"]*' . $esc . '[\'"]';
                }

                if ($prev == null && $next === ' ') {
                    $reg .= 'id\s*=\s*[\'"][^\'"]*' . $esc . '(?:[^\'"]+)?[\'"][^>]*>';
                }

                if ($prev == ' ' && $next === false) {
                    $reg .= 'id\s*=\s*[\'"][^\'"]*' . $esc . '(?:[^\'"]+)?[\'"][^>]*>';
                }

                if ($prev == ' ' && $next == '.') {
                    $reg .= 'id\s*=\s*[\'"][^\'"]*' . $esc . '(?:[^\'"]+)?[\'"]';
                }
            } elseif ($char == '>' || $char == '+' || $char == '~') {
                return true;
                // Child combinator (>), adjacent sibling (+), and general sibling (~)
                // For conservative matching, treat them like descendant combinators
                $next_token = self::next_token($selector, $pos + 1);
                $token = $next_token;
                $next = $next_token['char'] ?? false;
                $char = ' ';

                // Match any content between elements (conservative approach)
                if ($prev == '.' && ($next == '.' || $next == '#')) {
                    $reg .= '[\s\S]*?';
                }

                if ($prev == '#' && ($next == '.' || $next == '#')) {
                    $reg .= '[\s\S]*?';
                }
            } else {
                break;
            }

            $prev = $char;
        }

        // If selector started with a tag, prepend tag regex to pattern
        if ($starts_with_tag) {
            $tag_regex = '<' . preg_quote($tag_name, '/') . '\\b[^>]*>';

            if (!empty($reg)) {
                // Tag followed by other selectors
                // $reg = $tag_regex . '[\s\S]*?' . $reg;
                $reg = $tag_regex;
            } else {
                // Pure tag selector (shouldn't reach here, but safety)
                $reg = $tag_regex;
            }
        }

        // if (empty($reg) || $reg == ']*>') {
        //     return false;
        // }

        $result = false;

        if (!empty($herahicle_match)) {

            if (!empty($reg)) {
                $herahicle_match[] = $reg;
            }

            // var_dump($herahicle_match);
            // echo "<br>";
            $last_pos = 0;

            foreach ($herahicle_match as $i => $reg) {

                preg_match("/$reg/", self::$html, $matches);

                if (!empty($matches[0])) {
                    $strpos = strpos(self::$html, $matches[0]);

                    if ($strpos === false) {
                        return false;
                    }

                    if ($last_pos > 0 && $strpos < $last_pos) {
                        return false;
                    }

                    $last_pos = $strpos;

                    if ($last_pos > 0 && $strpos > $last_pos && ($i + 1) === count($herahicle_match)) {
                        return true;
                    }
                } else {
                    return false;
                }
            }
        } else {

            $result = preg_match("/$reg/", self::$html);
        }


        return $result;
    }

    function get_js_selector_classes($html)
    {

        $dom = HtmlDomParser::str_get_html($html);
        $used_class = [
            'woocommerce-js',
            'woocommerce-product-gallery*',
            'js',
        ];

        foreach ($dom->find('script') as $script) {
            $js_content = '';

            // Check if script has src attribute (external JS)
            $src = strtolower($script->src ?? '');

            if (!empty($src)) {

                // convert to abs
                $baseUrl = self::getBaseUrl($this->site_url);
                $src = self::rel2abs($src, $baseUrl);

                if ($this->has_cache($src)) {
                    $js_content = $this->get_cache($src);
                } else {

                    $response = wp_remote_get($src, ['timeout' => 30]);
                    $is_valid_response = wp_remote_retrieve_response_code($response) >= 200 && wp_remote_retrieve_response_code($response) < 400;

                    if (is_wp_error($response) || !$is_valid_response) {
                        continue;
                    }

                    $js_content = wp_remote_retrieve_body($response);

                    $this->cache_asset($src, $js_content);
                }
            } else {
                // Inline script tag - get content directly
                $js_content = $script->innertext;
            }

            // Skip if no content
            if (empty($js_content)) {
                continue;
            }

            // Search for classList.add() patterns
            preg_match_all('/classList\.add\(\s*[\'"]([^\'"]+)/', $js_content, $matches);

            foreach ($matches[1] as $class) {
                $used_class[] = $class;
            }

            // Search for .addClass() patterns (jQuery)
            preg_match_all('/\.addClass\(\s*[\'"]([^\'"]+)/', $js_content, $matches);

            foreach ($matches[1] as $class) {
                $used_class[] = $class;
            }

            unset($script);
        }

        unset($dom);

        // return array_unique($used_class);
        return $used_class;
    }

    function extract_used_css($css, $selectors)
    {
        // Remove comments
        $css = preg_replace('/\/\*[\s\S]*?\*\//', '', $css);

        // Remove @keyframes blocks
        // $css = preg_replace('/@[-\w]*keyframes[^{]+\{(?:[^{}]*\{[^}]*\})*[^}]*\}/i', '', $css);
        // $css = preg_replace('/@(?:-webkit-|-moz-|-o-|-ms-)?keyframes\s+[^{]+\{(?:[^{}]+|\{[^}]*\})*\}/is', '', $css);

        // Remove @font-face blocks
        $css = preg_replace('/@font-face\s*\{(?:[^{}]+|\{[^}]*\})*\}/is', '', $css);

        // Remove @media blocks
        // $css = preg_replace('/@media[^{]+\{(?:[^{}]+|\{[^}]*\})*\}/is', '', $css);

        // Remove @charset
        $css = preg_replace('/@charset\s+["\'][^"\']+["\']\s*;/i', '', $css);

        // Remove @import
        // $css = preg_replace('/@import\s+[^;]+;/i', '', $css);
        $css = preg_replace(
            '/@import\s+(?:url\s*\(\s*["\']?[^)]+["\']?\s*\)|["\'][^"\']+["\'])\s*[^;]*;?/i',
            '',
            $css
        );


        $css = preg_replace('/[\'"]\s*}\s*[\'"]/', 'bwpclosingcurlybracket', $css);
        $css = preg_replace('/[\'"]\s*{\s*[\'"]/', 'bwpopeningcurlybracket', $css);

        // Match CSS blocks - ensure we skip any remaining @ symbols
        // Use PREG_SET_ORDER | PREG_OFFSET_CAPTURE to get byte positions for precise removal
        preg_match_all('/([^{}]+)\{([^{}]+)\}/', $css, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
        // preg_match_all('/([^{@}]+)\{((?:[^{}"\']*|"[^"]*"|\'[^\']*\')*)\}/', $css, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);

        $removals = [];

        foreach ($matches as $match) {
            $block = trim($match[0][0]);

            // Skip at-rules
            if (!empty($block) && !preg_match('/^@/', $block)) {
                $block_selectors = trim(explode('{', $block)[0]);
                $individual_selectors = $this->split_selector_by_comma($block_selectors);
                $keep_block = false;
                $used_selectors = [];

                foreach ($individual_selectors as $sel) {
                    $sel = trim($sel);

                    if (self::is_selector_used($sel, $selectors, $this->js_classes)) {
                        $used_selectors[] = $sel;
                        $keep_block = true;
                    }

                    // if (self::is_selector_used($sel, $selectors, $this->js_classes)) {

                    //     $keep_block = true;
                    //     break;
                    // }

                }

                if ($keep_block) {
                    $new_selector_str = implode(', ', $used_selectors);
                    $block_pos = strpos($css, $block);
                    $sels_pos = $block_pos + strpos($block, $block_selectors);
                    $new_block = str_replace($block_selectors, $new_selector_str, $block);

                    $removals[] = [
                        'offset' => $match[0][1],
                        'length' => strlen($match[0][0]),
                        'replace' => $new_block,
                    ];
                }

                if (!$keep_block) {
                    $removals[] = [
                        'offset' => $match[0][1],
                        'length' => strlen($match[0][0]),
                    ];
                }
            }
        }

        // Remove blocks in reverse order to preserve earlier offsets
        for ($i = count($removals) - 1; $i >= 0; $i--) {
            $r = $removals[$i];
            // var_dump(substr($css, $r['offset'], $r['length']));
            $css = substr_replace($css, $r['replace'] ?? "", $r['offset'], $r['length']);
        }

        // Final cleanup: remove any stray @ symbols before class/ID/attribute selectors
        // These are fragments left from block removals
        $css = preg_replace('/@+(?=[.#\[])/i', '', $css);

        $css = str_replace('bwpclosingcurlybracket', '"}"', $css);
        $css = str_replace('bwpopeningcurlybracket', '"{"', $css);

        return $css;
    }

    function minify_css($css)
    {
        // Remove comments
        // $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);

        // // Remove whitespace around selectors, braces, colons, semicolons
        // $css = preg_replace('/\s*([{}:;,>+~])\s*/', '$1', $css);

        // Remove remaining newlines and tabs
        $css = str_replace(["\r\n", "\r", "\n", "\t"], '', $css);

        // Collapse multiple spaces into one
        $css = preg_replace('/\s+/', ' ', $css);
        $css = preg_replace('/\n+/', '\n', $css);

        // Remove spaces around parentheses
        // $css = preg_replace('/\s*\(\s*/', '(', $css);
        // $css = preg_replace('/\s*\)\s*/', ')', $css);

        // // Remove last semicolon before closing brace
        // $css = str_replace(';}', '}', $css);

        // Remove leading/trailing whitespace
        return trim($css);
    }

    private function split_selector_by_comma($selector)
    {
        $parts = [];
        $current = '';
        $depth = 0;
        $len = strlen($selector);

        for ($i = 0; $i < $len; $i++) {
            $char = $selector[$i];

            if ($char === '(') {
                $depth++;
                $current .= $char;
            } elseif ($char === ')') {
                $depth--;
                $current .= $char;
            } elseif ($char === '[') {
                $depth++;
                $current .= $char;
            } elseif ($char === ']') {
                $depth--;
                $current .= $char;
            } elseif ($char === ',' && $depth === 0) {
                // Only split on commas outside parentheses
                if (trim($current) !== '') {
                    $parts[] = trim($current);
                }
                $current = '';
            } else {
                $current .= $char;
            }
        }

        // Add last part
        if (trim($current) !== '') {
            $parts[] = trim($current);
        }

        return $parts;
    }

}