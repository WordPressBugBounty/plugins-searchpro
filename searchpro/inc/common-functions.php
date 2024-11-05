<?php

function bwp_pass_cookie_requirement() {
    $berqconfigs = new berqConfigs();
    $configs = $berqconfigs->get_configs();
    $excluded_cookies = $configs['exclude_cookies'];

    if (!empty($excluded_cookies)) {
        foreach ($excluded_cookies as $cookie_id) {
            foreach ($_COOKIE as $key => $value) {
                if (strpos($key, $cookie_id) === 0) {
                    return false;
                }
            }
        }
    }

    return true;
}