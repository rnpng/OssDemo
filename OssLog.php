<?php
class OssLog {
    private $filename;

    public function __construct($filename) {
        $this->filename = $filename;
    }

    public function write($message) {
        $folder = __DIR__ .'\\LOG\\' . date('Ymd');
        if(!is_dir($folder)){
            mkdir($folder, 0777, true);
        }
        $file = $folder . '\\' .$this->filename;
        $handle = fopen($file, 'a+');

        fwrite($handle, date('Y-m-d G:i:s') . ' - ' . print_r($message, true)  . "\n");

        fclose($handle);
    }
}
