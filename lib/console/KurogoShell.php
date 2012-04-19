#!/usr/bin/php -q
<?php

class KurogoShellDispatcher {

    protected $stdin; //standard input stream
    protected $stdout; //standard output stream
    protected $stderr; //standard error stream
    protected $params = array();
    protected $args = array();
    protected $initArgs = array();
    protected $shell = '';
    protected $shellCommand = '';
    protected $shellClass = null;
    protected $siteName;
    protected $workingPath;
    protected $rootDir;
    
    public function getParams() {
        return $this->params;
    }
    
    public function getArgs() {
        return $this->args;
    }
    
    public function getRootDir() {
        return $this->rootDir;
    }
    
    public function getSiteName() {
        return $this->siteName ? $this->siteName : '';
    }
    
    public function getShellCommand() {
        return $this->shellCommand;
    }
    
    function shiftArgs() {
		if (empty($this->args)) {
			return false;
		}
		unset($this->args[0]);
		$this->args = array_values($this->args);
		return true;
	}
    /**
     * Constructor
     *
     * @param array $args the argv.
     */
 
    public function __construct($args = array()) {
        $this->initArgs = $args;
        $this->initConstants();
        $this->parseParams($args);
        $this->initEnvironment();
        $this->dispatch();
    }
    
    protected function initConstants() {
        define('KUROGO_SHELL', true);
        
        if (function_exists('ini_set')) {
            ini_set('error_reporting', E_ALL & ~E_DEPRECATED);
            ini_set('html_errors', false);
			ini_set('implicit_flush', true);
			ini_set('max_execution_time', 0);
        }
    }
    
    protected function initEnvironment() {
        $this->stdin = fopen('php://stdin', 'r');
		$this->stdout = fopen('php://stdout', 'w');
		$this->stderr = fopen('php://stderr', 'w');
		
		$rootDir = $this->getRootDir();
		$kurogoFile = $this->getRootDir() . '/lib/Kurogo.php';

		if ($kurogoFile && require_once($kurogoFile)) {
		    $Kurogo = Kurogo::sharedInstance();
		    
		    $path = $this->getSiteName();
		    $Kurogo->initialize($path);
		} else {
		    $this->stderr("\nKurogo Console: ");
			$this->stderr("\nUnable to load Kurogo class");
			exit();
		}
		$this->shiftArgs();
    }

    protected function parseParams($params) {
        $this->_parseParams($params);
        
        if (isset($this->params['working'])) {
            $this->workingPath = $this->params['working'];
            unset($this->params['working']);
        }
        
        if (isset($this->params['site'])) {
            $this->siteName = $this->params['site'];
            unset($this->params['site']);
        }

        if (isset($this->params['root'])) {
            $this->rootDir = $this->params['root'];
            unset($this->params['root']);
        } else {
            $this->rootDir = realpath(substr(dirname(__FILE__), 0, -12));
        }
    }
    
    protected function _parseParams($params) {
        $count = count($params);
        for ($i=0; $i < $count; $i++) {
            if (isset($params[$i])) {
                if (substr($params[$i], 0, 1) === '-') {
                    $key = substr($params[$i], 1);
                    $this->params[$key] = true;
                    unset($params[$i]);
                    if (isset($params[++$i])) {
                        if (substr($params[$i], 0, 1) !== '-') {
                            $this->params[$key] = str_replace('"', '', $params[$i]);
                            unset($params[$i]);
                        } else {
                            $i--;
                        }
                    }
                } else {
                    $this->args[] = $params[$i];
                    unset($params[$i]);
                }
            }
        }
    }
    
    protected function dispatch() {
        if (isset($this->args[0])) {
            $shell = $this->args[0];
            $command = 'index';
            
            $this->shell = $shell;
            $this->shiftArgs();
            
            if (isset($this->args[0]) && $this->args[0]) {
                $command = $this->args[0];
            }
            $this->shellCommand = $command;
            $args = $this->getParams();

            $Kurogo = Kurogo::sharedInstance();
            $Kurogo->setRequest($shell, $command, $args);

            if ($module = ShellModule::factory($id, $page, $args)) {
                $Kurogo->setCurrentModule($module);
                $module->displayPage();
            } else {
                throw new KurogoException("Module $shell cannot be loaded");
            }
            
        } else {
            $this->stderr("\nKurogo Console: ");
			$this->stderr("\nNot found the shell module");
			exit();
        }
    }
    /**
     * Prompts the user for input, and returns it.
     *
     * @param string $prompt Prompt text.
     * @param mixed $options Array or string of options.
     * @param string $default Default input value.
     * @return Either the default value, or the user provided input.
     */
    public function getInput($prompt, $options = null, $default = null) {
        if (is_array($options)) {
            $printOptions = '(' . implode('/', $options) . ')';
        } else {
            $printOptions = $options;
        }
        
        if (is_null($default)) {
			$this->stdout($prompt . " $printOptions \n" . '> ', false);
		} else {
			$this->stdout($prompt . " $printOptions \n" . "[$default] > ", false);
		}
		$result = fgets($this->stdin);

		if ($result === false) {
			exit;
		}
        $result = trim($result);

		if ($default != null && empty($result)) {
			return $default;
		}
		return $result;
    }
    
    public function stdout($string, $newLine = true) {
        if ($newLine) {
            fwrite($this->stdout, $string . "\n");
        } else {
            fwrite($this->stdout, $string);
        }
    }
    
    public function stderr($string) {
        fwrite($this->stderr, 'Error: '. $string);
    }
}

$dispatcher = new KurogoShellDispatcher($argv);

