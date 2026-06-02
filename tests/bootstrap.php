<?php

// Register test-only class aliases before autoloading
if (! class_exists('App\\Http\\Controllers\\Frontend\\ProviderRegisterController')) {
    class_alias(
        'App\\Http\\Controllers\\Auth\\ProviderRegisterController',
        'App\\Http\\Controllers\\Frontend\\ProviderRegisterController'
    );
}
