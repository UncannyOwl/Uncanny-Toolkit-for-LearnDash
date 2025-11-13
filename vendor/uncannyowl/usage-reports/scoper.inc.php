<?php

declare(strict_types=1);

use Isolated\Symfony\Component\Finder\Finder;

// Get the scope prefix from environment variable (set by consuming plugins)
$scopePrefix = getenv('USAGE_REPORTS_SCOPE') ?: 'UncannyOwl';

return [
    // The prefix configuration. If a non-null value is set, a random prefix will not be generated.
    'prefix' => $scopePrefix . '\\UsageReports',

    // By default when running php-scoper add-prefix, it will prefix all relevant code found in the current working
    // directory. You can however define which files should be scoped by defining a collection of Finders in the
    // following configuration key.
    'finders' => [
        Finder::create()
            ->files()
            ->ignoreVCS(true)
            ->notName('/.*\\.phar/')
            ->notName('scoper.inc.php')
            ->notName('composer.json')
            ->exclude([
                'tests',
                'test',
                'Tests',
                'Test',
            ])
            ->in('.'),
    ],

    // Whitelists a list of files. Unlike the other whitelist options, this one is about completely leaving
    // a file untouched.
    // Paths are relative to the configuration file unless if they are already absolute
    'exclude-files' => [
        'scoper.inc.php',
    ],

    // When scoping PHP files, there will be scenarios where some of the code being scoped indirectly references the
    // original namespace. These will include, for example, strings or string manipulations. PHP-Scoper has limited
    // support for prefixing such strings. To cover the scenarios it does not spot, you can define patchers to cover
    // those cases.
    'patchers' => [
        // You can add custom patchers here if needed
    ],

    // PHP-Scoper's goal is to prefix all PHP vendor code of a given project. However, you may want to share a common API
    // between the bundled code of your PHAR and the consumer code. For that purpose, you can "expose" symbols
    'expose-global-constants' => false,
    'expose-global-classes' => false,
    'expose-global-functions' => false,

    // If true, uncovers any symbol that was exposed by the other 'expose-*' options
    'expose-namespaces' => [],
]; 