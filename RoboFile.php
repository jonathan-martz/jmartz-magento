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

			return \json_decode($file,true);
		}

		return [];
	}

    public function composerInstall()
    {
    	$config = $this->loadRoboConfig();

		$this->taskSshExec($config['host'], $config['user'])
			->remoteDir('/var/www/' . $config['folder'] . '/releases/' . $config['tmp'])
			->exec('composer install --ignore-platform-reqs')
			->exec('cd src && composer install --ignore-platform-reqs')
			->run();
    }

    public function su(){
		$this->stopOnFail(true);

		$config = $this->loadRoboConfig();

		$this->taskSshExec($config['host'], $config['user'])
			->remoteDir('/var/www/' . $config['folder'] . '/releases/' . $config['tmp'])
			->exec('cd src && bin/magento setup:upgrade')
			->run();

		$this->stopOnFail(false);
	}

    public function sdc(){
		$this->stopOnFail(true);
		$config = $this->loadRoboConfig();

		$this->taskSshExec($config['host'], $config['user'])
			->remoteDir('/var/www/' . $config['folder'] . '/releases/' . $config['tmp'])
			->exec('cd src && bin/magento setup:di:compile')
			->run();

		$this->stopOnFail(false);
	}

    public function sscd(){
		$this->stopOnFail(true);

		$config = $this->loadRoboConfig();

		$this->taskSshExec($config['host'], $config['user'])
			->remoteDir('/var/www/' . $config['folder'] . '/releases/' . $config['tmp'])
			->exec('cd src && bin/magento setup:static-content:deploy -f')
			->run();

		$this->stopOnFail(false);
	}

	public function cacl(){
		$this->stopOnFail(true);

		$config = $this->loadRoboConfig();

		$this->taskSshExec($config['host'], $config['user'])
			->remoteDir('/var/www/' . $config['folder'] . '/releases/' . $config['tmp'])
			->exec('cd src && bin/magento ca:cl')
			->run();

		$this->stopOnFail(false);
	}

	public function phanCheck(){
		$config = $this->loadRoboConfig();

		$filename = 'src/var/log/phan.json';
		$this->taskSshExec($config['host'], $config['user'])
			->remoteDir('/var/www/' . $config['folder'] . '/releases/' . $config['tmp'])
			->exec('vendor/bin/phan -m json -o '.$filename.' --unused-variable-detection')
			->run();

		// add Module for this Kind of Phan Error Check
		// if(\file_exists($filename)){
		// 	$json = \file_get_contents($filename);
		// 	$errors = \json_decode($json);
		// 	if(count($errors) !== 0){
		// 		exit("Phan detected some errors take a look at log/phan.json and fix the errors.");
		// 	}
		// }
    }

	public function deploy()
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
			->excludeVcs()
			->toPath('/var/www/' . $config['folder'] . '/releases/' . $config['tmp'])
			->recursive()
			->run();

        $this->taskSshExec($config['host'], $config['user'])
            ->remoteDir('/var/www/' . $config['folder'] . '/releases/'. $config['tmp'].'/src/app/etc')
			->exec('FILE=env.php;if [ -f "$FILE" ]; then;rm $FILE;fi')
            ->exec('ln -s /var/www/' . $config['folder'] . '/shared/env.php')
            ->run();
	}

	public function publishVersion(){
		$config = $this->loadRoboConfig();

		$this->taskSshExec($config['host'], $config['user'])
			->remoteDir('/var/www/' . $config['folder'].'/releases')
			->exec('rm current')
			->exec('ln -sd /var/www/' . $config['folder'] . '/releases/' . $config['tmp'] . ' current')
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

	public function downloadN98(){
		$config = $this->loadRoboConfig();

		$this->taskSshExec($config['host'], $config['user'])
			->remoteDir('/var/www/' . $config['folder'] . '/releases/' . $config['tmp'])
			->exec('curl -O https://files.magerun.net/n98-magerun2.phar')
			->run();
	}

	public function removeOldRevisions() {
		$config = $this->loadRoboConfig();

		$this->say('Removing Old Revisions');
		return $this->taskSshExec($config['host'],$config['user'])
			->remoteDir('/var/www/' . $config['folder'] . '/releases/')
			->exec(
				'ls -t . | '.
				'tail -n +5 | '.
				'xargs -I {} rm -rf ./{}'
			)
			->run();
	}
}

?>
