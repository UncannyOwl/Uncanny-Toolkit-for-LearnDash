<?php
/**
 * Simple namespace scoper for usage-reports
 * Replaces UncannyOwl\UsageReports with plugin-specific namespaces
 * Modifies files in-place for simplicity
 */

$scopePrefix = getenv('USAGE_REPORTS_SCOPE') ?: 'UncannyOwl';

// If no scope prefix is provided, nothing to do
if ($scopePrefix === 'UncannyOwl') {
    echo "No scoping needed - using default namespace\n";
    exit(0);
}

// Files to process
$files = [
    'abstract-report.php',
    'reporting-schedule.php'
];

foreach ($files as $file) {
    if (!file_exists($file)) {
        echo "Skipping missing file: {$file}\n";
        continue;
    }
    
    $content = file_get_contents($file);
    
    // Replace namespace declaration
    $content = preg_replace(
        '/namespace UncannyOwl\\\\UsageReports;/',
        "namespace {$scopePrefix}\\UsageReports;",
        $content
    );
    
    // Replace use statements
    $content = preg_replace(
        '/use UncannyOwl\\\\UsageReports\\\\/',
        "use {$scopePrefix}\\UsageReports\\",
        $content
    );
    
    // Replace class references in strings/comments
    $content = str_replace(
        'UncannyOwl\\UsageReports',
        $scopePrefix . '\\UsageReports',
        $content
    );
    
    file_put_contents($file, $content);
    echo "Scoped: {$file}\n";
}

echo "Namespace scoping completed: UncannyOwl\\UsageReports -> {$scopePrefix}\\UsageReports\n"; 