# Laravel-Piwik Bundle Version 1.0.3, by Rob Brazier

Laravel-Piwik is an easy way to interface with the Piwik Analytics API

Install using Artisan CLI:

    php artisan bundle:install piwik

Add the following line to application/bundles.php

    return array(
        'piwik' => array('auto' => true, 'handles'=>'piwik_install'),
    );

<!--Add the following to the application.php config file (if you want to use `Piwik` instead of `Piwik\Piwik`:

    'Piwik' => 'Piwik\\Piwik',
-->
More detailed installation process [here](http://robbrazier.com/portfolio/laravel-piwik/installation)

Usage
-----
There are two ways to use this class: you can call it statically or non-statically.

Static:

	Piwik::actions();

Non-static:

    $piwik = new Piwik();
	$piwik->actions();