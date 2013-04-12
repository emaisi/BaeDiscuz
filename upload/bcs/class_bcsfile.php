<?php

define('BCS_ROOT', dirname(__FILE__).'/');

require_once (BCS_ROOT.'/lib/bcs.class.php');
require_once (BCS_ROOT.'/class_file.php');
require_once (BCS_ROOT.'/config.php');

class bcsfile extends file
{
	private $_baidubcs = null;
	private $_memcache = null;
	private $_bucket = null;

	public function __construct($bucket = null, $ak = null, $sk= null) {
		if ($ak != null && $sk != null) {
			$this->_baidubcs = new BaiduBCS($ak, $sk);
		} else {
			$this->_baidubcs = new BaiduBCS(BAIDU_BCS_AK, BAIDU_BCS_SK);
		}

		$this->_memcache = new BaeMemcache();

		if ($bucket != null) {
			$this->setbucket($bucket);
		} else {
			$this->setbucket(BAIDU_BCS_BUCKET);
		}
	}

	public function setbucket($bucket) {
		$this->_bucket = $bucket;
	}

	public function getbucket() {
		if (!empty($this->_bucket)) {
			return $this->_bucket;
		}  else {
			throw new BCS_Exception('No bucket set');
		}
	}

	public function fopen($filename, $mode) {
		if (!$this->is_remote($filename)) {
			return fopen($filename, $mode);
		}

		/*
		if ($this->has_mode($mode, "x") || $this->has_mode($mode, "x+")) {
			if ($this->file_exists($filename)) {
				return false;
			}
		}

		if ($this->has_mode($mode, "w") || $this->has_mode($mode, "w+")) {
			//删除原有文件
			$this->unlink($filename);
		}
		*/

		$file = array('filename' => $filename, 'mode' => $mode);
		return $file;
	}

	public function fwrite(&$file, $string, $length = null) {
		if (!is_array($file)) {
			return fwrite($file, $string, $length);
		}
		
		$object = $this->get_object_name($file['filename']);

		if ($this->has_mode($file['mode'], "a") || $this->has_mode($file['mode'], "a+")) {
			if (!isset($file['writecontent'])) {
				$file['writecontent'] = $this->get_content($object);
			}
		}/* else if (!($this->has_mode($file['mode'], "w") || $this->has_mode($file['mode'], "x"))){
			//以其他模式打开，返回失败
			return false;
		}*/

		$file['writecontent'] .= substr($string, $length);
		return $length;
	}

	public function fread(&$file, $length) {
		if (!is_array($file)) {
			return fread($file, $length);
		}
		/*
		if ($file['mode'] != 'r' && $file['mode'] != 'r+' && $file['mode'] != 'w+' && $file['mode'] != 'a+' && $file['mode'] != 'x+') {
			return false;
		}
		*/

		$object = $this->get_object_name($file['filename']);
		if (!isset($file['readcontent'])) {
			$file['readcontent'] = $this->get_content($object);
			$file['readpos'] = 0;
		}

		if ($file['readpos'] >= strlen($file['readcontent'])) {
			return EOF;
		}

		$readstring = substr($file['readcontent'] , $file['readpos'], $length);
		$file['readpos'] += strlen($readstring);
		return $readstring;
	}

	public function fflush(&$file) {
		if (!is_array($file)) {
			return fflush($file);
		}

		if (!isset($file['writecontent'])) {
			return true;
		}

		$object = $this->get_object_name($file['filename']);
		$this->put_content($object, $file['writecontent']);

		unset($file['writecontent']);
	}

	public function fclose(&$file) {
		if (!is_array($file)) {
			return fclose($file);
		}
		$this->fflush($file);
	}

	public function flock(&$file, $lock) {
		if (!is_array($file)) {
			return flock($file, $lock);
		} 
		return true;
	}

	public function file($filename) {
		if (!$this->is_remote($filename)) {
			return file($filename);
		}
		$object = $this->get_object_name($filename);
		$content = $this->get_content($object);
		return explode("\r\n", $content);
	}

	public function unlink($filename) {
		if (!$this->is_remote($path)) {
			return unlink($path);
		}

		$object = $this->get_object_name($path);
		$this->_memcache->delete($object);
      	$this->_baidubcs->delete_object($this->getbucket(), $object);
  
	}

	public function file_exists($path)  {
		if (!$this->is_remote($path)) {
			return file_exists($path);
		}
		$object = $this->get_object_name($path);
		if ($content = $this->_memcache->get($object)) {
			return true;
		}
		return $this->_baidubcs->is_object_exist($this->getbucket(), $object);
	}

	public function filemtime($filename) {
		if (!$this->is_remote($filename)) {
			return filemtime($filename);
		}

		$object = $this->get_object_name($filename);
		$response = $this->_baidubcs->get_object_info($this->getbucket(), $object);
		if (!$response->isOK()) {
			return null;
		}
		return strtotime($response->header['Last-Modified']);
	}

	public function filesize($filename) {
		if (!$this->is_remote($filename)) {
			return filesize($filename);
		}

		$object = $this->get_object_name($filename);
		if ($content = $this->_memcache->get($object)) {
			return strlen($content);
		}
		$response = $this->_baidubcs->get_object_info($this->getbucket(), $object);
		if (!$response->isOK()) {
			return false;
		}
		return intval($response->header['Content-Length']);
	}

	public function touch($filename) {
		if (!$this->is_remote($filename)) {
			return touch($filename);
		}

		$object = $this->get_object_name($filename);
      	$this->put_content($object, '', 'a');
	}

	public function mkdir($filename) {
		if (!$this->is_remote($filename)) {
			return mkdir($filename);
		}
		return true;
	}

	public function file_get_contents($filename) {
		if (!$this->is_remote($filename)) {
			return file_get_contents($filename);
		}
		$object = $this->get_object_name($filename);
		return $this->get_content($object);
	}

	public function file_put_contents($filename, $data, $mode = null, $context = null) {
		if (!$this->is_remote($filename)) {
			return file_put_contents($filename, $data);
		}
		$object = $this->get_object_name($filename);
		return $this->put_content($object, $data);
	}

	public function rename($source, $dest, $context = null) {
		if (!$this->is_remote($source)) {
			return rename($source, $dest);
		}
		$this->_baidubcs->copy_object($source, $dest);
		$this->delete($source);
	}

	public function opendir($filepath) {
		if (!$this->is_remote($filepath)) {
			return opendir($filepath);
		}

		$object = $this->get_object_name($filepath);
		$response = $this->_baidubcs->list_object_by_dir($this->getbucket(), $object);
		if ($response->isOK()) {
			return array('current' => 0, 'files' => json_decode($response->body));
		} else {
			return array();
		}
	}

	public function readdir(&$dir) {
		if (!is_array($dir)) {
			return readdir($dir);
		} 
		$i = 0;
		foreach($dir['files']['object_list'] as $object) {
			if ($i == $dir['current']) {
				$dir['current'] = $i + 1;
				return $this->get_full_path($object['object']);
			}
			$i++;
		}
		return false;
	}

	public function closedir($dir) {
		if (!is_array($dir)) {
			return closedir($dir);
		} else {
			//nothing to do
			return;
		}
	}

	public function is_dir($filepath) {
		if (!$this->is_remote($filepath)) {
			return is_dir($filepath);
		}
		return !$this->file_exists($filepath);
	}

	public function is_file($filepath) {
		if (!$this->is_remote($filepath)) {
			return is_file($filepath);
		}
		return $this->file_exists($filepath);
	}

	public function save_file($filename) {
		if (!$this->is_remote($filename)) {
			return $filename;
		}
		$object = $this->get_object_name($filename);
		$tmpfile = $this->download($object);
		if ($tmpfile) {
			return $tmpfile;
		}
		return '';
	}

	public function readfile($filename) {
		if (!$this->is_remote($filename)) {
			return readfile($filename);
		}
		
		$object = $this->get_object_name($filename);
		echo $this->get_content($object);
	}

	public function upload($source, $target) {
		$object = $this->get_object_name($target);
		$response = $this->_baidubcs->create_object($this->getbucket(), $object, $source);
		if ($response->isOK()) {
			return true;
		} else {
			return false;
		}
	}

	public function include_file($filename) {
		global $_G;
		
		if (!$this->is_remote($filename)) {
			return include $filename;
		}
		$object = $this->get_object_name($filename);
		$tmpfile = $this->download($object);
		if ($tmpfile) {
			include $tmpfile;
			unlink($tmpfile);
		}
	}

	public function include_once_file($filename) {
		global $_G;

		if (!$this->is_remote($filename)) {
			return include_once $filename;
		}
		$object = $this->get_object_name($filename);

		$tmpfile = $this->download($object);
		if ($tmpfile) {
			include_once $tmpfile;
		}	
	}

	public function require_file($filename) {
		global $_G;

		if (!$this->is_remote($filename)) {
			return require $filename;
		}
		$object = $this->get_object_name($filename);
		$tmpfile = $this->download($object);
		if ($tmpfile) {
			require $tmpfile;
		}
	}

	public function require_once_file($filename) {
		global $_G;

		if (!$this->is_remote($filename)) {
			return require_once $filename;
		}
		$object = $this->get_object_name($filename);
		$tmpfile = $this->download($object);
		if ($tmpfile) {
			require_once $tmpfile;
		}
	}

	public function url($filename = ''){
		$object = $this->get_object_name($filename);
		return 'http://bcs.duapp.com/'.$this->getbucket().$object;
		//return $this->_baidubcs->generate_get_object_url($this->getbucket(), $object);
	}

	public function dir($dir = '/', $list_model = 2, $opt = array()) {
		return $this->_baidubcs->list_object_by_dir($this->getbucket(), $dir, $list_model, $opt);
	}

	private function get_content($object) {
		if ($content = $this->_memcache->get($object)) {
			return $content;
		}
		$response = $this->_baidubcs->get_object($this->getbucket(), $object);
		if(!$response->isOK()) {
			return null;
		}

		$this->_memcache->set($object, $response->body);
		return $response->body;
	}

	private function put_content($object, $content, $mode='w') {
		//写入到临时文件
		$tempfile = $this->get_temp_file($this->get_file_extension($object));
		$writecontent = '';
		if ($this->has_mode($mode, 'a')) {
			$writecontent.= $this->get_content($object);
		}
		$writecontent.= $content;

		@touch($tempfile);
		@file_put_contents($tempfile, $writecontent);

		//放到memcache中
		if (strlen($writecontent) < 1024 * 1024) {
			$this->_memcache->set($object, $writecontent);
		} else {
			$this->_memcache->delete($object);
		}
		//删除已经存在的object
		//@$this->_baidubcs->delete_object($this->getbucket(), $object);
		//上传到云存储,并删除临时文件
		$response = $this->_baidubcs->create_object($this->getbucket(), $object, $tempfile);

		//删除临时文件
		@unlink($tempfile);

		if ($response->isOK()) {
			return strlen($content);
		} else {
			return false;
		}
	}

	private function download($object) {
		$tempfile = $this->get_temp_file($this->get_file_extension($object));

		if ($content = $this->_memcache->get($object)) {
			file_put_contents($tempfile, $content);
			return $tempfile;
		}

		$content = $this->get_content($object);
		if(!$content) {
			return null;
		}
		file_put_contents($tempfile, $content);
		return $tempfile;
	}

	public function copy($source, $dest) {
		$this->_baidubcs->copy_object($source, $dest);
		$this->delete($source);
	}

	public function is_writable($filename) {
		if (!$this->is_remote($filename)) {
			return  is_writable($filename);
		}
		return true;
	}

	private function get_temp_file($extension = 'tmp') {
		return $this->get_temp_dir().DIRECTORY_SEPARATOR.rand(1, 10000).'.'.$extension;
	}

	private function get_object_name($filename) {
		$relativefilename = $this->get_relativepath($filename);
		$relativefilename = str_replace('\\', '/', $relativefilename);
		$relativefilename = '/'.$relativefilename;
		$relativefilename = str_replace('//', '/', $relativefilename);
		return $relativefilename;
	}

	private function get_file_extension($filename) {
		$extension = explode ('.', $filename);
		$extension = array_pop($extension);
		return $extension;
	}

	private function has_mode($value, $mode) {
		for($i = 0; $i < strlen($mode); $i++) {
			$c = substr($mode, $i, 1);
			if (strpos($value, $c) === false) {
				return false;
			}	
		}
		return true;
	}

}

?>