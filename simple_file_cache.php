<?php
class SimpleFileCache {
    private $cacheDir = 'cache/';
    
    public function __construct() {
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }
    
    public function get($key) {
        $filename = $this->cacheDir . md5($key) . '.cache';
        
        if (!file_exists($filename)) {
            return null;
        }
        
        $data = file_get_contents($filename);
        $data = @unserialize($data);
        
        if ($data === false || !isset($data['expires'])) {
            return null;
        }
        
        // Prüfe Ablaufzeit
        if ($data['expires'] < time()) {
            @unlink($filename);
            return null;
        }
        
        return $data['value'];
    }
    
    public function set($key, $value, $ttl = 3600) {
        $filename = $this->cacheDir . md5($key) . '.cache';
        
        $data = [
            'value' => $value,
            'expires' => time() + $ttl
        ];
        
        @file_put_contents($filename, serialize($data));
        @chmod($filename, 0644);
        return true;
    }
    
    public function delete($key) {
        $filename = $this->cacheDir . md5($key) . '.cache';
        if (file_exists($filename)) {
            @unlink($filename);
        }
        return true;
    }
    
    public function deletePattern($pattern) {
        $files = glob($this->cacheDir . '*.cache');
        $count = 0;
        foreach ($files as $file) {
            $data = @unserialize(file_get_contents($file));
            if ($data && isset($data['key']) && strpos($data['key'], $pattern) !== false) {
                @unlink($file);
                $count++;
            }
        }
        return $count;
    }
    
    public function clear() {
        $files = glob($this->cacheDir . '*.cache');
        foreach ($files as $file) {
            @unlink($file);
        }
        return true;
    }
    
    public function cleanup() {
        $files = glob($this->cacheDir . '*.cache');
        $deleted = 0;
        foreach ($files as $file) {
            $data = @unserialize(file_get_contents($file));
            if (!$data || !isset($data['expires']) || $data['expires'] < time()) {
                @unlink($file);
                $deleted++;
            }
        }
        return $deleted;
    }
}