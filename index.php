<?php
define("DB_user", "cloud");
define("DB_password", "");
define("DB_name", "cloud");
define("file_limit_bytes", 8*pow(2,30));
define("file_save_path", "./filedata/");
define("default_domain", "https://cloud.0101010101.com/");

mysql_connect("localhost", DB_user, DB_password) or die("DB Error");
mysql_select_db(DB_name) or die("DB Error");
mysql_query("CREATE TABLE cloud(id int NOT NULL auto_increment, filename_saved TEXT NOT NULL, filename_original TEXT NOT NULL, filesize BIGINT NOT NULL, download_cnt INT NOT NULL, ip TEXT NOT NULL, hash TEXT NOT NULL, PRIMARY KEY (id))");
mysql_query("CREATE TABLE log(id int NOT NULL auto_increment, filename_saved TEXT NOT NULL, filename_original TEXT NOT NULL, filesize BIGINT NOT NULL, ip TEXT NOT NULL, PRIMARY KEY (id))");
mysql_query("ALTER TABLE cloud CONVERT TO CHARSET utf8;");
mysql_query("ALTER TABLE log CONVERT TO CHARSET utf8;");
mysql_query("SET session character_set_connection=utf8;");
mysql_query("SET session character_set_results=utf8;");
mysql_query("SET session character_set_client=utf8;");

if (isset($_GET["upload"])){
	if ($_FILES["file"]["size"] > file_limit_bytes){
		echo '<!doctype html>
		<head>
		<meta charset="UTF-8">
		<meta id="viewport" name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
		<title>Simple file storage</title>
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
		</head>
		<body>
		<div class="container">
		<div class="col-xs-12">
		<div style="height: 15px;"></div>
		<label>' . file_limit_bytes/1024/1024 . 'MiB가 넘는 파일은 업로드하실 수 없습니다.</label>
		</div>
		</div>
		</body>
		</html>';
	} else {
		set_time_limit(0);
		$randbase = '23456789abcdefghijkmnpqrstuvwxyz';
		$randbase = str_split($randbase);
		$random = '';
		while(1){
			$random = '';
			for ($i = 0; $i < 8; $i++) {
				$random .= $randbase[rand(0,31)];
			}
			$query = "SELECT * FROM cloud WHERE filename_saved='" . file_save_path . $random . "'";
			$result = mysql_query($query);
			$data = mysql_fetch_row($result);
			if (count($data) <= 2) break;
		}
		$filename = file_save_path . $random;
		$md5 = md5_file($_FILES["file"]["tmp_name"]);
		$query = "SELECT * FROM cloud WHERE hash='" . $md5 . "'";
		$result = mysql_query($query);
		$data = mysql_fetch_row($result);
		if (count($data) > 2) {
			echo '<!doctype html>
			<head>
			<meta charset="UTF-8">
			<meta id="viewport" name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
			<title>Simple file storage</title>
			<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
			<script src="//code.jquery.com/jquery-1.12.1.min.js"></script>
			<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>
			<script src="//cdnjs.cloudflare.com/ajax/libs/clipboard.js/1.5.5/clipboard.min.js"></script>
			</head>
			<body>
			<div class="container">
			<div class="col-xs-12">
			<div style="height: 15px;"></div>
			<label>' . htmlspecialchars($_FILES["file"]["name"]) . '와 해시가 일치하는 파일이 이미 업로드되어 있습니다(MD5 해시: ' . $md5 . '): <a class="btn btn-primary" href="' . default_domain . explode('/', $data[1])[2] . '">' . default_domain . explode('/', $data[1])[2] . '</a> <button id="copy" class="btn btn-default" data-clipboard-text="' . default_domain . explode('/', $data[1])[2] . '" data-toggle="tooltip" data-placement="right" title="복사되었습니다!">복사</button></label>
			<script>
			var cb = new Clipboard("#copy");
			cb.on(\'success\', function(e) {
				$("#copy").tooltip("show");
				setTimeout(function(){$("#copy").tooltip("destroy");},2500);
			});
			</script>
			</div>
			</div>
			</body>
			</html>';
		} else {
			move_uploaded_file($_FILES["file"]["tmp_name"], $filename);
			$query = "INSERT INTO cloud SET filename_saved='" . $filename . "', filename_original = '" . mysql_real_escape_string($_FILES["file"]["name"]) . "', filesize='" . $_FILES["file"]["size"] . "', ip='" . $_SERVER["REMOTE_ADDR"] . "', hash='" . $md5 . "'";
			mysql_query($query);
			echo '<!doctype html>
			<head>
			<meta charset="UTF-8">
			<meta id="viewport" name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
			<title>Simple file storage</title>
			<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
			<script src="//code.jquery.com/jquery-1.12.1.min.js"></script>
			<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>
			<script src="//cdnjs.cloudflare.com/ajax/libs/clipboard.js/1.5.5/clipboard.min.js"></script>
			</head>
			<body>
			<div class="container">
			<div class="col-xs-12">
			<div style="height: 15px;"></div>
			<label>' . htmlspecialchars($_FILES["file"]["name"]) . '는 다음 링크에서 다운로드하실 수 있습니다(MD5 해시: ' . $md5 . '): <a class="btn btn-primary" href="' . default_domain . $random . '">' . default_domain . $random . '</a> <button id="copy" class="btn btn-default" data-clipboard-text="' . default_domain . $random . '" data-toggle="tooltip" data-placement="right" title="복사되었습니다!">복사</button></label>
			<script>
			var cb = new Clipboard("#copy");
			cb.on(\'success\', function(e) {
				$("#copy").tooltip("show");
				setTimeout(function(){$("#copy").tooltip("destroy");},2500);
			});
			</script>
			</div>
			</div>
			</body>
			</html>';
		}
	}
} else if (isset($_GET["link"])) {
	$query = "SELECT * FROM cloud WHERE filename_saved='" . file_save_path . mysql_real_escape_string($_GET["link"]) . "'";
	$result = mysql_query($query);
	$data = mysql_fetch_row($result);
	if (strpos($_GET["link"], "/") || strpos($_GET["link"], "\\")) die("Do not hack!");
	if (count($data) == 1) die("File not found");
	$query = "INSERT INTO log SET filename_saved='" . mysql_real_escape_string($data[1]) . "', filename_original = '" . mysql_real_escape_string($data[2]) . "', filesize='" . mysql_real_escape_string($data[3]) . "', ip='" . $_SERVER["REMOTE_ADDR"] . "'";
	mysql_query($query);
	$query = "UPDATE cloud SET download_cnt='" . (intval($data[4])+1) . "' WHERE filename_saved='" . file_save_path . mysql_real_escape_string($_GET["link"]) . "'";
	mysql_query($query);

	header('Content-Type: application/octet-stream', FALSE);
	header("Content-Transfer-Encoding: Binary", FALSE); 
	header("Content-Disposition: attachment; filename=\"" . $data[2] . "\"", FALSE);
	header("Content-Length: " . $data[3], FALSE);
	set_time_limit(0);
	$file = @fopen(file_save_path . $_GET["link"],"rb");
	while(!feof($file)) {
		print(@fread($file, 1048576));
		ob_flush();
		flush();
	}
} else {
exec('df | grep /var', $output);
preg_match('/([0-9]+)\s+([0-9]+)\s+([0-9]+)\s+([0-9]+)/', $output[0], $matches);
echo '<!doctype html>
<head>
<meta charset="UTF-8">
<meta id="viewport" name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
<title>Simple file storage</title>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
<style type="text/css">
#filedrag {
	display: none;
}
</style>
</head>
<body>
<div class="container">
<div class="col-xs-12">
<div style="height: 15px;"></div>
<div class="row"><label><strong>최대 업로드 가능 용량</strong>: ' . file_limit_bytes/1024/1024 . ' MiB</label></div>
<div style="height: 5px;"></div>
<form method="POST" action="./?upload" enctype="multipart/form-data" onsubmit="if(!document.getElementById(\'file\').value) {alert(\'파일을 선택하세요!\'); return false;} else return true;">
<div class="row"><button class="btn btn-primary btn-lg" onclick="document.getElementById(\'file\').click(); return false;">1. 업로드할 파일을 선택하세요.</button>&nbsp;&nbsp;<div id="filedrag" class="btn btn-default btn-lg">또는 여기에 놓으세요.</div><input type="file" id="file" name="file" onchange="if (document.getElementById(\'file\').files[0].size > ' . file_limit_bytes . ') alert(\'' . file_limit_bytes . ' 바이트를 넘는 파일은 업로드할 수 없습니다.\'); document.getElementById(\'upload\').value = \'2. \' + document.getElementById(\'file\').value + \' 업로드\';" style="display:none"></div>
<div style="height: 15px;"></div>
<div class="row"><input class="btn btn-success btn-lg" type="submit" id="upload" value="2. 업로드"></div>
</form>
<div style="height: 15px;"></div>
<div class="row">
<script src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<ins id="ad" class="adsbygoogle"
style="display:inline-block;width:336px;height:280px"
data-ad-client="ca-pub-8739077797209742"
data-ad-slot="2072551166"></ins>
<script>
(adsbygoogle = window.adsbygoogle || []).push({});
</script>
</div>
<div style="height: 15px;"></div>
<ul>
<li><strong>개인정보나 기타 민감한 정보가 담긴 파일을 암호화되지 않은 채로 올리지 마세요.</strong></li>
<li>파일은 기본적으로 무기한 보관됩니다. 다만 단일 파일이 하루 50GB 이상의 트래픽을 사용하거나, 동일 IP에서 업로드한 파일이 50GB 이상의 용량을 차지할 경우 삭제될 수 있습니다.</li>
<li>서버가 위치한 국가(대한민국)에서 법적으로 문제가 되는 파일은 삭제될 수 있습니다. <s>사실 암호 건 채 압축해서 올리시면 됩니다.</s></li>
<!--<li>현재 총 용량 ' . (intval(($matches[2]+$matches[3])/1024/1024*1000)/1000) . 'GiB 중 ' . (intval($matches[2]/1024/1024*1000)/1000) . 'GiB를 사용중으로, ' . (intval($matches[3]/1024/1024*1000)/1000) . 'GiB의 여유공간이 있습니다.</li>-->
</ul>
<div style="height: 15px;"></div>
<!--<div class="row">
<div class="progress">
	<div class="progress-bar progress-bar-success progress-bar-striped" style="width: ' . $matches[4] . '%">
		' . (intval($matches[2]/1024/1024*1000)/1000) . ' GiB / ' . (intval(($matches[2]+$matches[3])/1024/1024*1000)/1000) . ' GiB
	</div>
</div>-->
</div>
<div style="height: 15px;"></div>
<div class="row">
Powered by HLETRD / 문의: <a href="mailto:01@0101010101.com">01@0101010101.com</a><br />
Running on CentOS 7 + Apache + MariaDB + PHP Stack
</div>
</div>
</div>
<script>
if (!document.getElementById("aswift_0_expand")) {
	alert("AdBlock 꺼주세요 ㅠㅠ");
}
if (window.File && window.FileList && window.FileReader) {
	document.getElementById("filedrag").addEventListener("drop", function(e) {
		e.preventDefault();
		e.stopPropagation();
		var files = e.target.files || e.dataTransfer.files;
		var file = files[0];
		document.getElementById("upload").value = "2. " + file.name + " 업로드";
		document.getElementById("file").files = files;
	});
	document.getElementById("filedrag").addEventListener("dragover", function(e) {
		e.preventDefault();
	});
	document.getElementById("filedrag").style.display = "inline-block";
}
</script>
</body>
</html>';
}
?>
