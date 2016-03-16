<?php
/**
 * loader.class.php
 * 处理文件加载. 文件下载等相关功能
 */
 
if (!defined('IN_IDEACMS')) exit();

class loader extends Fn_base {
 	
 	/**
 	 * http下载文件
 	 * 
     * Reads a file and send a header to force download it.
 	 * @copyright www.doophp.com    
     * @param string $file_str File name with absolute path to it
     * @param bool $isLarge If True, the large file will be read chunk by chunk into the memory.
     * @param string $rename Name to replace the file name that would be downloaded
     */
    public static function download($file, $isLarge = FALSE, $rename = NULL){
        if (headers_sent()) return false;
        if (!$file) exit('Error 404:The file not found!');
        if ($rename==NULL) {
            if (strpos($file, '/')===FALSE && strpos($file, '\\')===FALSE)
                $filename = $file;
            else {
                $filename = basename($file);
            }
        } else {
            $filename = $rename;
        }
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));
        ob_clean();
        flush();
        if($isLarge)
            self::readfile_chunked($file);
        else
            readfile($file);
    }

    /**
     * Read a file and display its content chunk by chunk
     * 
     * @param string $filename
     * @param bool $retbytes
     * @return mixed
     */
    private function readfile_chunked($filename, $retbytes = TRUE, $chunk_size = 1024) {
        $buffer = '';
        $cnt    = 0;
        $handle = fopen($filename, 'rb');
        if ($handle === false) return false;
        while (!feof($handle)) {
            $buffer = fread($handle, $chunk_size);
            echo $buffer;
            ob_flush();
            flush();
            if ($retbytes) $cnt += strlen($buffer);
        }
        $status = fclose($handle);
        if ($retbytes && $status) {
            return $cnt; // return num. bytes delivered like readfile() does.
        }
        return $status;
    }
}