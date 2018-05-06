<?php
// デバッグモード
define('DEBUG', false);
// ffmpeg
define('FFMPEG_BINARY', '/usr/local/bin/ffmpeg');
// tmp directory
define('WORKING_DIR', '/var/www/video/work/');
// input directory
define('TS_DIR', '/mnt/ts/');
// output directory
define('MAX_PROCESS', 5);
// 全力出しちゃう？
define('MAX_THREADS', 6);
// 変換処理1回毎の休憩時間(秒)
define('SLEEP_TIME', 20);
?>
