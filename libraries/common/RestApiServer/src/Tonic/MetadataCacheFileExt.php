<?php

namespace Tonic;

/**
 * This class has a check of release version and clean cache if it's not actual
 */
class MetadataCacheFileExt extends MetadataCacheFile {

	private $pointsDir = '/home/projects/points/';

	private function symlinkName() {
		$basename = basename(GIRAR_BASE_DIR);
		$symlinkName = substr($basename, 0, strrpos($basename, '-'));
		return $symlinkName;
	}

	private function symlinkPath() {
		return $this->pointsDir . $this->symlinkName();
	}

	private function isExpired() {
		$symlinkPath = $this->symlinkPath();
		if (!is_readable($symlinkPath)) {
			return true;
		}
		$cacheFileMtime = filemtime($this->filename);
		$symlinkMtime = filemtime($symlinkPath);
		return $cacheFileMtime < $symlinkMtime;
	}

	public function isCached() {
		return (parent::isCached() && ! $this->isExpired());
	}

}

?>