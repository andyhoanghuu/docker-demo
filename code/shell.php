<?php
set_include_path(get_include_path() . PATH_SEPARATOR . 'phpseclib');

include('Net/SSH2.php');

const HOST = '192.168.1.181';
const USER_NAME = 'hieu';
const PASSWORD = 'hieu';

if (isset($_POST['process']) && $_POST['process']) {
	$ssh = new Net_SSH2(HOST);
	if (!$ssh->login(USER_NAME, PASSWORD)) {
		exit('Login Failed');
	}

	echo $ssh->exec('cd /home/hieu/CMS/ce/magentoce241 && pwd && sh script/stop.sh && bin/start');
}

