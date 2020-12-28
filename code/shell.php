<?php
if (file_exists('config.php')) {
	include('config.php');
} else {
	echo 'Config required!';
	die;
}

set_include_path(get_include_path() . PATH_SEPARATOR . 'phpseclib');
include('Net/SSH2.php');

try {
	if (isset($_POST['process']) && $_POST['process']) {
		$credentials = [
			'host' => HOST,
			'username' => USER_NAME,
			'password' => PASSWORD,
			'path' => PATH,
			'command_up' => COMMAND_UP,
			'command_down' => COMMAND_DOWN,
			'life_time' => LIFE_TIME,
			'demo_url' => DEMO_URL,
		];

		//TODO: check site is working?
		$is404 = is_404($credentials['demo_url']);
		if (! $is404) {
			// Redirect to demo URL.
			header('Location: ' . DEMO_URL);
			exit();
		}
		//
		// // SOCKS5 connection info
		// $fsock = @fsockopen($credentials['host'], 22, $errno, $errstr, 1);
		// if (!$fsock) {
		// 	throw new Exception($errstr);
		// }

		// Init.
		$lofdemo = new Docker_Demo( $credentials );

		// Create and write files into server.
		// TODO: check permission
		$lofdemo->createRegisterCronFile();
		$lofdemo->createActionCronFile();

		// Run cron sh file and start docker.
		$ssh = $lofdemo->getSsh();
		$ssh->exec('cd '.PATH.' && pwd && chmod -x stop.sh && sh stop.sh && ' . COMMAND_UP);

		// Redirect to demo URL.
		header('Location: ' . DEMO_URL);
		exit();
	}
} catch (Exception $e) {
	echo $e->getMessage();
}

class Docker_Demo {
	/**
	 * @var object
	 */
	protected $ssh;

	/**
	 * @var string
	 */
	protected $host;

	/**
	 * @var string
	 */
	protected $username;

	/**
	 * @var string
	 */
	protected $password;

	/**
	 * @var string
	 */
	protected $path;

	/**
	 * @var string
	 */
	protected $command_up;

	/**
	 * @var string
	 */
	protected $command_down;

	/**
	 * @var string
	 */
	protected $life_time;

	/**
	 * Docker_Demo constructor.
	 *
	 * @param array $credentials
	 */
	public function __construct(array $credentials)
	{
		$credentials = $this->parse_args( $credentials, [
			'host' => '',
			'username' => '',
			'password' => '',
			'path' => '',
			'command_up' => 'docker-compose up -d',
			'command_down' => 'docker-compose down',
			'life_time' => '60',
		] );

		$this->host = $credentials['host'];
		$this->username = $credentials['username'];
		$this->password = $credentials['password'];
		$this->path = $credentials['path'];
		$this->command_up = $credentials['command_up'];
		$this->command_down = $credentials['command_down'];
		$this->life_time = $credentials['life_time'];

		try {
			$ssh = new Net_SSH2($this->host);

			$login = $ssh->login($this->username, $this->password);

			if (!$login) {
				throw new Exception('Login Failed');
			}

			$this->ssh = $ssh;
		} catch (Exception $e) {

		}
	}

	/**
	 * @return \Net_SSH2|object
	 */
	public function getSsh() {
		return $this->ssh;
	}

	/**
	 * Create a register file for crontab. This file the same with command: crontab -e
	 *
	 * @param string $path
	 */
	public function createRegisterCronFile()
	{
		$file = $this->path . '/' . 'stop.sh';
		$command = 'echo "crontab -l > lofstopdemo" >> ' . $file . ' && ';
		$command .= 'echo \'echo "*/' . $this->life_time . ' * * * * sh ' . $this->path . '/stop-docker.sh" >> lofstopdemo\' >> ' . $file . ' && ';
		$command .= 'echo "crontab lofstopdemo" >> ' . $file . ' && ';
		$command .= 'echo "rm lofstopdemo" >> ' . $file . ' && ';
		$command .= 'cat ' . $file;
		$this->ssh->exec($command);
	}

	/**
	 * Create a action file for crontab. It means the content of crontab.
	 *
	 * @param string $path
	 */
	public function createActionCronFile()
	{
		// $content = 'cd '.PATH.' && ' . COMMAND_DOWN . "\n";
		// $content .= 'crontab -u '.USER_NAME.' -l | grep -v \'sh '.PATH.'/stop-docker.sh\'  | crontab -u '.USER_NAME.' -' . "\n";

		$file = $this->path . '/' . 'stop-docker.sh';
		$command = 'echo "cd ' . $this->path . ' && '. $this->command_down .'" >> ' . $file . ' && ';
		$command .= 'echo "crontab -u ' . $this->username . ' -l | grep -v \'sh ' . $this->path . '/stop-docker.sh\' | crontab -u '.$this->username.' -" >> ' . $file . ' && ';
		$command .= 'cat ' . $file;
		$this->ssh->exec($command);
	}

	/**
	 * Parse array.
	 *
	 * @param       $args
	 * @param array $defaults
	 * @return array
	 */
	protected function parse_args( $args, $defaults = array() ) {
		if ( is_object( $args ) ) {
			$parsed_args = get_object_vars( $args );
		} elseif ( is_array( $args ) ) {
			$parsed_args =& $args;
		} else {
			$this->parse_str( $args, $parsed_args );
		}

		if ( is_array( $defaults ) && $defaults ) {
			return array_merge( $defaults, $parsed_args );
		}
		return $parsed_args;
	}

	/**
	 * Parse string.
	 *
	 * @param $string
	 * @param $array
	 */
	protected function parse_str( $string, &$array ) {
		parse_str( $string, $array );
	}
}

function is_404($url) {
	$handle = curl_init($url);
	curl_setopt($handle,  CURLOPT_RETURNTRANSFER, TRUE);

	/* Get the HTML or whatever is linked in $url. */
	$response = curl_exec($handle);

	/* Check for 404 (file not found). */
	$httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
	curl_close($handle);

	/* If the document has loaded successfully without any redirection or error */
	if ($httpCode >= 200 && $httpCode < 300) {
		return false;
	} else {
		return true;
	}
}
