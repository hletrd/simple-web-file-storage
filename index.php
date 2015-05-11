<?php
define("DB_user", "");
define("DB_password", "");
define("DB_name", "");
define("file_limit_bytes", pow(2,30));
define("file_save_path", "./filedata/");
define("default_domain", "http://cloud.0101010101.com");

mysql_connect("localhost", DB_user, DB_password) or die("DB Error");
mysql_select_db(DB_name) or die("DB Error");
mysql_query("CREATE TABLE cloud(id int(6) NOT NULL auto_increment, filename_saved TEXT NOT NULL, filename_original TEXT NOT NULL, filesize BIGINT NOT NULL, download_cnt INT NOT NULL, ip TEXT NOT NULL, PRIMARY KEY (id))");

if (isset($_GET["upload"])){
if ($_FILES["file"]["size"] > file_limit_bytes){
echo '<!doctype html>
<head>
<meta charset="UTF-8">
<title>Simple Cloud Storage</title>
</head>
<body>
Cannot upload file over ' . file_limit_bytes . 'bytes.
</body>
</html>';
} else {
set_time_limit(0);
while(1){
$filename_md5 = substr(base64_encode(md5(microtime())), 1, 6);
$query = "SELECT * FROM cloud WHERE filename_saved='" . file_save_path . $filename_md5 . "'";
$result = mysql_query($query);
$data = mysql_fetch_row($data);
if (count($data) === 0) break;
usleep(10);
}
$filename = file_save_path . $filename_md5;
move_uploaded_file($_FILES["file"]["tmp_name"], $filename);
$query = "INSERT INTO cloud SET filename_saved='" . $filename . "', filename_original = '" . mysql_real_escape_string($_FILES["file"]["name"]) . "', filesize='" . $_FILES["file"]["size"] . "', ip='" . $_SERVER["REMOTE_ADDR"] . "'";
mysql_query($query);
echo '<!doctype html>
<head>
<meta charset="UTF-8">
<title>미러링해 드립니다</title>
</head>
<body>
You may download ' . $_FILES["file"]["name"] . ' at: <a href="' . default_domain . $filename_md5 . '">' . default_domain . $filename_md5 . '</a>
</body>
</html>';
}
} else if (isset($_GET["link"])) {
$query = "SELECT * FROM cloud WHERE filename_saved='" . file_save_path . mysql_real_escape_string($_GET["link"]) . "'";
$result = mysql_query($query);
$data = mysql_fetch_row($result);
if (strpos($_GET["link"], "/") || strpos($_GET["link"], "\\")) die("Do not hack!");
if (count($data) == 1) die("File not found");
header('Content-Type: application/octet-stream', FALSE);
header("Content-Transfer-Encoding: Binary", FALSE); 
header("Content-Disposition: attachment; filename=\"" . $data[2] . "\"", FALSE);
header("Content-Length: " . $data[3], FALSE);
set_time_limit(0);
$file = @fopen(file_save_path . $_GET["link"],"rb");
while(!feof($file))
{
	print(@fread($file, 1024*8));
	ob_flush();
	flush();
}
} else {
echo '<!doctype html>
<head>
<meta charset="UTF-8">
<title>Simple Cloud Storage</title>
</head>
<body>
<form method="POST" action="./?upload" enctype="multipart/form-data"><button onclick="document.getElementById(\'file\').click(); return false;">Select a file to upload</button><input type="file" id="file" name="file" onchange="if (document.getElementById(\'file\').files[0].size > ' . file_limit_bytes . ') alert(\'Cannot upload file over ' . file_limit_bytes . ' bytes.\'); document.getElementById(\'upload\').value = document.getElementById(\'file\').value + \' Upload\';" style="display:none"><input type="submit" id="upload" value="Upload"></form>
Powered by HLETRD
</body>
</html>';
}
?>
