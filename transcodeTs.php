<?php
include dirname(__FILE__).'/settings.php';
include dirname(__FILE__).'/../common/functions.php';

$dp = false;
try {
	$count = 0;
	
	$dp = opendir(TS_DIR);
	while (($file = readdir($dp)) !== false) {
		if ($file === '.' || $file === '..') {
			continue;
		}
		$fileName = $file;
		$fullPath = TS_DIR.$fileName;
		$extSplit = explode('.', $fileName);
		$ext = end($extSplit);
		echo "Input file: ".$fullPath."\n";
		if (!in_array($ext, array('mp4'))) {
			echo "Skipped\n";
			continue;
		}
		if (!is_file($fullPath)) {
			echo "Skipped\n";
			continue;
		}
		$md5 = md5($fullPath);
		$outFile = md5ToPath($md5);
		echo "Output file: ".$outFile."\n";
		$tmpFile = WORKING_DIR.$md5.'.mp4';
		if (file_exists($outFile) || file_exists($tmpFile)) {
			echo "Skipped\n";
			continue;
		}
		$beforeSize = filesize($fullPath);
		sleep(2);
		$afterSize = filesize($fullPath);
		if ($afterSize != $beforeSize) {
			echo "Skipped\n";
			continue;
		}
		if (isSd($fullPath)) {
			echo "Skipped\n";
			continue;
		}
		mkdir(dirname($outFile), 0755, true);
		$ret = trans($fullPath, $tmpFile);
		if ($ret == 0) {
			rename($tmpFile, $outFile);
			if (file_exists($tmpFile)) {
				unlink($tmpFile);
			}
			if (++$count >= MAX_PROCESS) {
				break;
			}
			sleep(SLEEP_TIME);
		}
	}
	if ($dp !== false) {
		closedir($dp);
	}
	exit(0);
} catch (Exception $e) {
	if ($dp !== false) {
		closedir($dp);
	}
	exit(1);
}


function trans($from, $tmp) {
	$return = 0;
	$command = FFMPEG_BINARY.' -hwaccel cuvid -c:v h264_cuvid '
		.'-hwaccel_device 0 '
		.'-y -i "'.$from.'" ';
	if (DEBUG) {
		$command .= '-ss 36 -t 100 ';
	}
	$command .= '-vsync 1 -cmp chroma -vf '
		.'scale_npp=w=720:h=480,hwdownload,format=nv12 '
		.'-aspect 16:9 '
		.'-brand mp42 -acodec copy -threads '.MAX_THREADS.' '
		.'-movflags faststart '
		.'-c:v h264_nvenc '
		.'-rc vbr_hq -b:v 860k -maxrate:v 3740k -b_adapt 1 '
		.'-spatial_aq 1 -rc-lookahead 600 '
		.'-preset slow -profile:v high "'.$tmp.'"';

	passthru($command, $return);
	return $return;
}

function isSd($input) {
	$command = FFMPEG_BINARY.' -y -i "'.$input.'" 2>&1';
	exec($command, $output);
	$regexp = '/Video: h264.* 720x480 /';
	foreach ($output as $line) {
		if (preg_match($regexp, $line)) {
			return true;
		}
	}
	return false;
}
?>
