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
		];

		// SOCKS5 connection info
		$fsock = @fsockopen($credentials['host'], 22, $errno, $errstr, 1);
		if (!$fsock) {
			throw new Exception($errstr);
		}

		$lofdemo = new Docker_Demo( $credentials );
		$ssh = $lofdemo->getSsh();
		// echo $ssh->exec('cd '.PATH.' && pwd && sh script/stop.sh && bin/start');
		$ssh->exec('cd '.PATH.' && pwd && '. COMMAND_UP);

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
	 */
	public function createRegisterCronFile()
	{

	}

	/**
	 * Create a action file for crontab. It means the content of crontab.
	 */
	public function createActionCronFile()
	{

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
