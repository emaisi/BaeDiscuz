<?php

class file {

	private $remotedirs = array();
	private $remotefiles = array();

	private $tempdir;
	private $rootpath;

	public function add_remote_dir($dirname) {
		$dirname = $this->get_full_path($dirname);
		if (array_key_exists($dirname, $this->remotedirs)) {
			return;
		}
		array_push($this->remotedirs, $dirname, $dirname);
	}

	public function remove_remote_dir($dirname) {
		$dirname = $this->get_full_path($dirname);
		unset($this->remotedirs[$dirname]);
	}

	public function add_remote_file($filename) {
		$filename = $this->get_full_path($filename);
		if (array_key_exists($filename, $this->remotefiles)) {
			return;
		}
		array_push($this->remotefiles, $filename, $filename);
	}

	public function remove_remote_file($filename) {
		$filename = $this->get_full_path($filename);
		unset($this->remotefiles[$filename]);
	}

	public function set_temp_dir($dir) {
		$this->tempdir = $dir;
	}

	public function get_temp_dir() {
		if (!is_dir($this->tempdir)) {
			@mkdir($this->tempdir);
		}
		return $this->tempdir;
	}

	public function set_root_path($path) {

		$this->rootpath = $this->normal_path($path);
	}

	public function get_rootpath() {
		return $this->rootpath;
	}

	public function get_relativepath($path) {
		$path = $this->get_full_path($path);
		return substr($path, strlen($this->rootpath));
	}

	public function is_remote($filename) {
		$filename = $this->get_full_path($filename);
		foreach($this->remotedirs as $dir) {
			if (strpos($filename, $dir) !== false) {
				return true;
			}
		}
		foreach($this->remotefiles as $file) {
			if (strpos($filename, $file) !== false) {
				return true;
			}
		}
		return false;
	}

	private function get_full_path($path) {
		$path = $this->normal_path($path);
		if (strpos($path, $this->rootpath) === false) {
			$path = $this->rootpath.DIRECTORY_SEPARATOR.$path;
		}
		$path = $this->normal_path($path);
		return $path;
	}

	public function normal_path($path) {
		//替换掉错误的目录分割符
		if (DIRECTORY_SEPARATOR == '/') {
			$path = str_replace('\\', DIRECTORY_SEPARATOR, $path);
		} else {
			$path = str_replace('/', DIRECTORY_SEPARATOR, $path);
		}

		//替换掉本地目录符号
		$result = str_replace (DIRECTORY_SEPARATOR.'.'.DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $path);
		while ( $result !== $path ) {
			$path = $result;
			$result = str_replace (DIRECTORY_SEPARATOR.'.'.DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $path);
		}
		$path = $result;

		//替换掉重复
		$result = str_replace(DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $path);
		while ( $result !== $path ) {
			$path = $result;
			$result = str_replace (DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $path);
		}
		$path = $result;

		//替换掉上层目录符号
		if (DIRECTORY_SEPARATOR == '/') {
			$pattern = "/\/[^\/]+\/\.\.\//";
		} else {
			$pattern = "/\\\[^\\\]+\\\\.\.\\\/";
		}

		$result = preg_replace($pattern, DIRECTORY_SEPARATOR, $path);
		while($result !== $path) {
			$path = $result;
			$result = preg_replace($pattern, DIRECTORY_SEPARATOR, $path);
		}
		$path = $result;
		return strtolower($path);
	}
}

?>