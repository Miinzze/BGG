<?php
require_once 'simple_file_cache.php';
$cache = new SimpleFileCache();
$cache->cleanup();
echo "Cache cleanup completed\n";