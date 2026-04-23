<?php
if ($argc < 2) {
    echo "Usage: php list_files.php /path/to/directory\n";
    exit(1);
}

$startDir = rtrim($argv[1], "/");

// Check if valid directory
if (!is_dir($startDir)) {
    echo "Error: '$startDir' is not a directory.\n";
    exit(1);
}

function listFilesRecursive($baseDir) {
    $files = [];

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($baseDir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $file) {
        if ($file->isFile()) {
            // Get relative path by subtracting base directory
            $relativePath = substr($file->getPathname(), strlen($baseDir) + 1);
            $files[] = $relativePath;
        }
    }

    return $files;
}

$fileList = listFilesRecursive($startDir);

foreach ($fileList as $filePath) {
    echo $filePath . "\n";
}
