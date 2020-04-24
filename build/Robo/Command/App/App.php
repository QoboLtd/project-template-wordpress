<?php

namespace Qobo\Robo\Command\App;

use Qobo\Robo\AbstractCommand;
use Qobo\Robo\Runner;

class App extends AbstractCommand
{

    /**
     * @var array $defaultEnv Default values if missing in env
     */
    protected $defaultEnv = [
		'SYSTEM_COMMAND_WPCLI'  => './vendor/bin/wp --allow-root --path=webroot/wp',
		'CHMOD_FILE_MODE'		=> '0664',
		'CHMOD_DIR_MODE'		=> '02775'
    ];

    /**
     * Install a project
     *
     * @param string $env Custom env in KEY1=VALUE1,KEY2=VALUE2 format
     *
     * @return bool true on success or false on failure
     */
    public function appInstall($env = '')
    {
        $env = $this->getDotenv($env);

        if ($env === false || !$this->preInstall($env)) {
            $this->exitError("Failed to do pre-install ");
        }

        $result = $this->installWp($env);

        if (!$result) {
            return false;
        }

        $result = $this->taskExec('touch ./webroot/wp-content/wp-cache-config.php ./webroot/wp-content/advanced-cache.php')->run();

        if (!$result->wasSuccessful()) {
            return false;
        }

        if (isset($env['CRON_ENABLED']) && $env['CRON_ENABLED']) {
            $this->installCron($env);
        }

        return ($this->setPathPermissions($env) && $this->postInstall());
    }

    /**
     * Update a project
     *
     * @param string $env Custom env in KEY1=VALUE1,KEY2=VALUE2 format
     *
     * @return bool true on success or false on failure
     */
    public function appUpdate($env = '')
    {
        $env = $this->getDotenv($env);

        if ($env === false || !$this->preInstall($env)) {
            $this->exitError("Failed to do app:update");
        }

        $result = $this->updateWp($env);

        if (!$result) {
            return false;
        }

        $result = $this->taskExec('touch ./webroot/wp-content/wp-cache-config.php ./webroot/wp-content/advanced-cache.php')->run();

        if (!$result->wasSuccessful()) {
            return false;
        }

        if (isset($env['CRON_ENABLED']) && $env['CRON_ENABLED']) {
            $this->installCron($env);
        }

        return ($this->setPathPermissions($env) && $this->postInstall());
    }

    /**
     * Remove a project
     *
     * @return bool true on success or false on failure
     */
    public function appRemove()
    {
        $env = $this->getDotenv();

        // drop project database
        $result = $this->taskMysqlDbDrop()
            ->db($this->getValue('DB_NAME', $env))
            ->user($this->getValue('DB_ADMIN_USER', $env))
            ->pass($this->getValue('DB_ADMIN_PASS', $env))
            ->hide($this->getValue('DB_ADMIN_PASS', $env))
            ->host($this->getValue('DB_HOST', $env))
            ->run();

        if (!$result->wasSuccessful()) {
            return false;
        }

        // Remove .env
        if (!file_exists('.env') || !unlink('.env')) {
            $this->exitError("Failed to do app:remove");
        }

        $this->uninstallCron($env);

        return true;
    }

    /**
     * Do wordpress related install things
     *
     * @param array $env Environment variables
     * @return bool true on success or false on failure
     */
    protected function installWp($env)
    {
        // Check DB connectivity and get server time
        $result = $this->taskMysqlBaseQuery()
            ->query("SELECT NOW() AS ServerTime")
            ->user($this->getValue('DB_ADMIN_USER', $env))
            ->pass($this->getValue('DB_ADMIN_PASS', $env))
            ->hide($this->getValue('DB_ADMIN_PASS', $env))
            ->host($this->getValue('DB_HOST', $env))
            ->run();

        if (!$result->wasSuccessful()) {
            return false;
        }
        $this->say(implode(": ", $result->getData()['data'][0]['output']));

        // prepare all remaining tasks in this array
        $tasks = [];

        // create DB
        $tasks []= $this->taskMysqlDbCreate()
            ->db($this->getValue('DB_NAME', $env))
            ->user($this->getValue('DB_ADMIN_USER', $env))
            ->pass($this->getValue('DB_ADMIN_PASS', $env))
            ->hide($this->getValue('DB_ADMIN_PASS', $env))
            ->host($this->getValue('DB_HOST', $env));

        // Parse install script template
        $tasks []= $this->taskTemplateProcess()
            ->wrap('%%')
            ->tokens($env)
            ->src('etc/wp-cli.install')
            ->dst('etc/wp-cli.install.sh');

        // Run install script
        $tasks []= $this->taskExec('/bin/bash etc/wp-cli.install.sh');

        // Parse content script template
        $tasks []= $this->taskTemplateProcess()
            ->wrap('%%')
            ->tokens($env)
            ->src('etc/wp-cli.content')
            ->dst('etc/wp-cli.content.sh');

        // Run content script
		$tasks []= $this->taskExec('/bin/bash etc/wp-cli.content.sh');

        // Now as we have all tasks prepared in order,
        // run one-by-one and stop on first fail
        foreach ($tasks as $task) {
            $result = $task->run();
            if (!$result->wasSuccessful()) {
                return false;
            }
        }

        // shoul be ok by here
        return true;
    }


    /**
     * Update a wordpress project
     *
     * @param array $env Environment variables
     * @return bool true on success or false on failure
     */
    protected function updateWp($env)
    {
        $result = $this->taskMysqlBaseQuery()
            ->query("SELECT NOW() AS ServerTime")
            ->user($this->getValue('DB_ADMIN_USER', $env))
            ->pass($this->getValue('DB_ADMIN_PASS', $env))
            ->hide($this->getValue('DB_ADMIN_PASS', $env))
            ->host($this->getValue('DB_HOST', $env))
            ->run();

        if (!$result->wasSuccessful()) {
            return false;
        }
        $this->say(implode(": ", $result->getData()['data'][0]['output']));

        $tasks = [];

        $tasks []= $this->taskTemplateProcess()
            ->wrap('%%')
            ->tokens($env)
            ->src('etc/wp-cli.update')
            ->dst('etc/wp-cli.update.sh');

		$tasks []= $this->taskExec('/bin/bash etc/wp-cli.update.sh');

        foreach ($tasks as $task) {
            $result = $task->run();
            if (!$result->wasSuccessful()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Recreates and reloads environment
     *
     * @param string $env Custom env in KEY1=VALUE1,KEY2=VALUE2 format
     *
     * @return mixed Env array or false on failure
     */
    protected function getDotenv($env = '')
    {
        $batch = $this->collectionBuilder();


        $task = $batch->taskProjectDotenvCreate()
            ->env('.env')
            ->template('.env.example');

        $vars = explode(',', $env);
        foreach ($vars as $var) {
            $var = trim($var);
            if (preg_match('/^(.*?)=(.*?)$/', $var, $matches)) {
                $task->set($matches[1], $matches[2]);
            }
        }


        $result = $task->taskDotenvReload()
                ->path('.env')
            ->run();

        if (!$result->wasSuccessful()) {
            return false;
        }

		$env = $result->getData()['data'];
		foreach ($this->defaultEnv as $k => $v) {
			if (!array_key_exists($k, $env)) {
				$env[$k] = $v;
			}
		}

		return $env;
    }

    /**
     * Find a value for configuration parameter
     *
     * @param string $name Parameter name
     * @param array $env Environment
     *
     * @return string
     */
    protected function getValue($name, $env)
    {
        // try to match in given $env
        if (!empty($env) && isset($env[$name])) {
            return $env[$name];
        }

        // look in real ENV
        $value = getenv($name);
        if ($value !== false) {
            return $value;
        }

        // look in the defaults
        if (!empty($this->defaultEnv) && isset($this->defaultEnv[$name])) {
            return $this->defaultEnv[$name];
        }

        // return null if nothing
        return null;
    }

    protected function preInstall($env)
    {
        // old :builder:init
        if (!$this->versionBackup("build/version")) {
            return false;
        }

        // old :file:process
        return $this->taskTemplateProcess()
            ->wrap('%%')
            ->tokens($env)
            ->src(getenv('TEMPLATE_SRC'))
            ->dst(getenv('TEMPLATE_DST'))
            ->run()
            ->wasSuccessful();
    }

    protected function postInstall()
    {
        return $this->versionBackup("build/version.ok");
    }

    protected function versionBackup($path)
    {
        $projectVersion = $this->getProjectVersion();
        if (file_exists($path)) {
            rename($path, "$path.bak");
        }
        return (file_put_contents($path, $projectVersion) === false) ? false : true;
    }

    protected function getProjectVersion()
    {
        $envVersion = getenv('GIT_BRANCH');
        if (!empty($envVersion)) {
            return $envVersion;
        }

        $result = $this->taskGitHash()->run();
        if ($result->wasSuccessful()) {
            return $result->getData()['data'][0]['message'];
        }
        return "Unknown";
    }

    /**
     * Install system cron job for the project
     */
    protected function installCron($env)
    {
        $projectPath = "{$env['NGINX_ROOT_PREFIX']}/{$env['NGINX_SITE_MAIN']}";

        if (!file_exists("$projectPath/bin/cron.sh") || file_exists("/etc/cron.d/{$env['NGINX_SITE_MAIN']}")) {
            return;
        }

        $this->taskExec('echo "* * * * * root ' . $projectPath . '/bin/cron.sh >> ' . $projectPath . '/logs/cron.log 2>&1" > /etc/cron.d/' . $env['NGINX_SITE_MAIN'])->run();
        if (!is_dir("$projectPath/logs")) {
            $this->taskExec('mkdir ' . $projectPath . '/logs')->run();
        }
        $this->taskExec('service crond reload')->run();
    }

    /**
     * Uninstall system cron job for the project
     */
    protected function uninstallCron($env)
    {
        if (!file_exists("/etc/cron.d/{$env['NGINX_SITE_MAIN']}")) {
            return;
        }
        $this->taskExec("rm -f '/etc/cron.d/{$env['NGINX_SITE_MAIN']}'")->run();
    }

    /**
     * Set correct paths permissions and ownerships
     */
    protected function setPathPermissions($env)
    {
        $lastError = Runner::getLastError();

        $dirMode = $this->getValue('CHMOD_DIR_MODE', $env);
        $fileMode = $this->getValue('CHMOD_FILE_MODE', $env);

        $chmodPaths = array_filter(explode(",", $this->getValue('CHMOD_PATH', $env)));
        $chownPaths = array_filter(explode(",", $this->getValue('CHOWN_PATH', $env)));
        $chgrpPaths = array_filter(explode(",", $this->getValue('CHGRP_PATH', $env)));

        $user = $this->getValue('CHOWN_USER', $env);
        $group = $this->getValue('CHGRP_GROUP', $env);

        $base = str_replace("build/Robo/Command/App", "",  __DIR__);

        $tasks = [];

        if (!empty($fileMode) && !empty($dirMode)) {
            foreach ($chmodPaths as $path) {
                if (!file_exists("$base$path")) {
                    continue;
                }

                // Chmod dir
                $tasks []= $this->taskFileChmod()
                    ->path("$base$path")
                    ->fileMode($fileMode)
                    ->dirMode($dirMode)
                    ->recursive(true);
            }
        }

        if (!empty($user)) {
            foreach ($chownPaths as $path) {
                if (!file_exists("$base$path")) {
                    continue;
                }

                // Chown dir
                $tasks []= $this->taskFileChown()
                    ->path("$base$path")
                    ->user($user)
                    ->recursive(true);
            }
        }

        if (!empty($group)) {
            foreach ($chgrpPaths as $path) {

                if (!file_exists("$base$path")) {
                    continue;
                }

                // Chgrp dir
                $tasks []= $this->taskFileChgrp()
                    ->path("$base$path")
                    ->group($group)
                    ->recursive(true);
            }
        }

        // execute all tasks
        foreach ($tasks as $task) {
            $error = false;
            try {
                $result = $task->run();
                if (!$result->wasSuccessful()) {
                    $error = true;
                    print "Failed to run task\n";
                }
            } catch (\Exception $e) {
                print "{$e->getMessage()}\n";
                $error = true;
            }

            if ($error && !$this->getValue('IGNORE_FS_ERRORS', $env)) {
                return false;
            }
        }

        Runner::setLastError($lastError);

        return true;
    }
}
