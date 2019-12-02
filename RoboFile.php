<?php

class RoboFile extends \Robo\Tasks
{
	public function generateRoboConfigDevelop(){
		$filename = 'robo-config.json';

		$robo['user'] = 'root';
		$robo['host'] = '195.201.38.163';
		$robo['tmp'] = date('d-m-Y-h-i-s');
		$robo['folder'] = 'magento-develop.jmartz.de';

		if(\file_exists($filename)){
			\exec('rm '.$filename);
		}

		\file_put_contents('robo-config.json', \json_encode($robo, JSON_FORCE_OBJECT));
	}

	public function generateRoboConfigMaster(){
		$filename = 'robo-config.json';

		$robo['user'] = 'root';
		$robo['host'] = '195.201.38.163';
		$robo['tmp'] = date('d-m-Y-h-i-s');
		$robo['folder'] = 'magento.jmartz.de';

		if(\file_exists($filename)){
			\exec('rm '.$filename);
		}

		\file_put_contents('robo-config.json', \json_encode($robo, JSON_FORCE_OBJECT));
	}

	public function loadRoboConfig():array{
		$filename = 'robo-config.json';

		if(file_exists($filename)){
			$file = \file_get_contents($filename);

			return \json_decode($file);
		}

		return [];
	}

    public function composerInstall()
    {
    	$config = $this->loadRoboConfig();

		$this->stopOnFail(true);
		$this->_exec('composer install --ignore-platform-reqs');
        $this->_exec('cd src && composer install --ignore-platform-reqs');
        $this->stopOnFail(false);
    }

	public function phanCheck(){
		$config = $this->loadRoboConfig();

		$this->stopOnFail(false);
    	$filename = 'src/var/log/phan.json';
		$this->_exec('vendor/bin/phan -m json -o '.$filename.' --dead-code-detection --unused-variable-detection');
		if(\file_exists($filename)){
			$json = \file_get_contents($filename);
			$errors = \json_decode($json);
			if(count($errors) !== 0){
				exit("Phan detected some errors take a look at log/phan.json and fix the errors.");
			}
		}
		$this->stopOnFail(true);
    }

	public function deploy($user,$host,$tmp,$folder)
	{
		$config = $this->loadRoboConfig();

		$this->taskSshExec($config['host'], $config['user'])
			->remoteDir('/var/www/' . $config['folder'] . '/releases')
			->exec('mkdir ' . $config['tmp'])
			->run();
		$this->stopOnFail(false);

		$this->taskRsync()
			->fromPath('.')
			->toHost($config['host'])
			->toUser($config['user'])
			->verbose()
			->stats()
			->progress()
			->excludeVcs()
			->toPath('/var/www/' . $config['folder'] . '/releases/' . $config['tmp'])
			->recursive()
			->run();
	}

	public function publishVersion(){
		$config = $this->loadRoboConfig();

		$this->taskSshExec($config['host'], $user)
			->remoteDir('/var/www/' . $folder)
			->exec('rm current')
			->exec('ln -sd /var/www/' . $folder . '/releases/' . $tmp . '/src/pub current')
			->run();

		$this->taskSshExec($config['host'], $config['user'])
			->remoteDir('/var/www/' . $config['folder'].'/releases')
			->exec('rm current')
			->exec('ln -sd /var/www/' . $config['host'] . '/releases/' . $config['tmp'] . '/src current')
			->run();

		$this->taskSshExec($config['host'], $config['user'])
			->remoteDir('/var/www/' . $config['folder'])
			->exec('chown -R www-data:www-data /var/www/' . $config['folder'])
			->run();

		$this->taskSshExec($config['host'], $config['user'])
			->remoteDir('/var/www/' . $config['folder'])
			->exec('service php7.3-fpm restart')
			->run();
	}
}

?>
