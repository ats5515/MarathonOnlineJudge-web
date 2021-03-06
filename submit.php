<?php
require('./template/useapi.php');
session_start();
$login_user = null;
$login_state = false;
if (isset($_SESSION["username"])) {
	$login_user = $_SESSION["username"];
	$login_state = true;
}
if (!$login_state) {
	header("Location: ./login.php");
	exit();
}

$usercode = null;
if (isset($_POST["usercode"])) {
	$usercode = $_POST["usercode"];
}
if (!$usercode) {
	header("Location: ./index.php");
	exit();
}

$lang = null;
if (isset($_POST["lang"])) {
	$lang = $_POST["lang"];
}
if (!$lang) {
	header("Location: ./index.php");
	exit();
}

run_cmd_exec("languages", $lang_list, $return_var);
if (!in_array($lang, $lang_list)) {
	header("Location: /index.php");
	exit();
}


$problem_id = null;
if (isset($_POST["problemId"])) {
	$problem_id = $_POST["problemId"];
}
if (!$problem_id) {
	header("Location: /index.php");
	exit();
}

$submission_id = run_cmd("get_next_submission_id");
$submission_dir = str_replace(
	PHP_EOL,
	'',
	run_cmd("submissions_path") . '/' . $submission_id
);
run_cmd("mkdir " . $submission_dir);
run_cmd("chmod 777 " . $submission_dir);


$submission_info["user"] = $login_user;
$submission_info["date"] = date('Y/m/d H:i:s');
$submission_info["problemId"] = $problem_id;
$submission_info["lang"] = $lang;

file_put_contents(
	$submission_dir . '/info.json',
	json_encode($submission_info, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
);

$extension = run_cmd("convert_to_extension " . $lang);

file_put_contents(
	$submission_dir . '/main.' . $extension,
	$usercode
);

$cmd = 'judge_background ' . $submission_id;
run_cmd($cmd);

header("Location: ./submission.php?id=" . $submission_id);
exit();
