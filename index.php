<?php
define("DB_user", "");
define("DB_password", "");
define("DB_name", "");
define("file_limit_bytes", 4*pow(2,30));
define("file_save_path", "./filedata/");
define("default_domain", "https://cloud.0101010101.com/");

mysql_connect("localhost", DB_user, DB_password) or die("DB Error");
mysql_select_db(DB_name) or die("DB Error");
mysql_query("CREATE TABLE cloud(id int NOT NULL auto_increment, filename_saved TEXT NOT NULL, filename_original TEXT NOT NULL, filesize BIGINT NOT NULL, download_cnt INT NOT NULL, ip TEXT NOT NULL, PRIMARY KEY (id))");
mysql_query("CREATE TABLE log(id int NOT NULL auto_increment, filename_saved TEXT NOT NULL, filename_original TEXT NOT NULL, filesize BIGINT NOT NULL, ip TEXT NOT NULL, PRIMARY KEY (id))");

if (isset($_GET["upload"])){
	if ($_FILES["file"]["size"] > file_limit_bytes){
		echo '<!doctype html>
		<head>
		<meta charset="UTF-8">
		<title>Simple file storage</title>
		</head>
		<body>
		' . file_limit_bytes/1024/1024 . 'MiB가 넘는 파일은 업로드하실 수 없습니다.
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
		move_uploaded_file($_FILES["file"]["tmp_name"], $filename);
		$query = "INSERT INTO cloud SET filename_saved='" . $filename . "', filename_original = '" . mysql_real_escape_string($_FILES["file"]["name"]) . "', filesize='" . $_FILES["file"]["size"] . "', ip='" . $_SERVER["REMOTE_ADDR"] . "'";
		mysql_query($query);
		echo '<!doctype html>
		<head>
		<meta charset="UTF-8">
		<title>Simple file storage</title>
		</head>
		<body>
		' . htmlspecialchars($_FILES["file"]["name"]) . '는 다음 링크에서 다운로드하실 수 있습니다: <a href="' . default_domain . $random . '">' . default_domain . $random . '</a>
		</body>
		</html>';
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
echo '<!doctype html>
<head>
<meta charset="UTF-8">
<title>Simple file storage</title>
</head>
<body>
최대 업로드 가능 용량: ' . file_limit_bytes/1024/1024 . ' MiB<br /><br />
<form method="POST" action="./?upload" enctype="multipart/form-data"><button onclick="document.getElementById(\'file\').click(); return false;">1. 업로드할 파일을 선택하세요.</button><input type="file" id="file" name="file" onchange="if (document.getElementById(\'file\').files[0].size > ' . file_limit_bytes . ') alert(\'Cannot upload file over ' . file_limit_bytes . ' bytes.\'); document.getElementById(\'upload\').value = document.getElementById(\'file\').value + \' Upload\';" style="display:none"><br /><br /><input type="submit" id="upload" value="2. 업로드"></form>
<br />
<script src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<ins id="ad" class="adsbygoogle"
style="display:inline-block;width:336px;height:280px"
data-ad-client="ca-pub-8739077797209742"
data-ad-slot="2072551166"></ins>
<script>
(adsbygoogle = window.adsbygoogle || []).push({});
if (document.getElementById("ad").getAttribute("data-adsbygoogle-status") == null) {
	alert("AdBlock 꺼주세요 ㅠㅠ");
}
</script>
<br />
Powered by HLETRD
<br />
*과도한 서버 부하를 일으키는 파일은 임의로 삭제될 수 있습니다. 하루 30~40GB 이상의 트래픽을 잡아먹거나, 동일 IP에서 업로드한 파일이 100GB 이상의 용량을 차지할 경우 삭제 대상이 될 수 있습니다.
</body>
</html>';
}
?>
