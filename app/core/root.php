<?php

/**
 * Root class. All begins and all ends here.
 * @author Aleksandar Todorovic
 */
class Root
{
    // IMPORTANT! Change location (out of http access)
    private string $envFilePath = __DIR__ . '/../../.env';

    // Parsed data from .env file
    protected $envData = [];

    // Will be set automatically by index file.
    public $interfaceLanguage = '';
    public $controller = '';
    public $action = '';
    public $views = '';
    public $view = '';
    public $layout = '';

    // Will be set by scripts (run time)
    public $data = []; // Data for pass to View.
    protected $errors = [];
    protected $alerts = [];
    protected $messages = [];
    protected $loggedin = false; // User is logged or not?
    protected $startTime = 0;  // For benchmark.
    protected $ip = '';
    protected $proxyUser = false; //Basic try to check is proxy or not.
    protected $userId = 0;

    // Part to be read from config file. Can be added anything if you know how.
    public $projectName = "Demo";
    protected $controllersDir = '/controllers';
    protected $modelsDir = '/models';
    protected $viewsDir = '/views';
    protected $storageDir = '/storage';
    protected $galleryDir = "/storage/gallery";
    protected $jsDir = "/app/helpers/js/";
    protected $cssDir = "/app/helpers/css/";
    protected $logsDir = __DIR__ . "/../misc/logs";


    public function __construct()
    {
        // For benchmark.
        $this->startTime = microtime(true);

        // Get custom env data.
        $this->parseCustomEnv();

        // Logged user or not? 
        $this->userId = (isset($_SESSION['usid']) && (int)$_SESSION['usid'] > 0) ? (int)$_SESSION['usid'] : 0;

        // IP
        $this->getIP();

        $this->loggedin = $this->userId > 0 ? true : false;
    }

    /**
     * Get user ID
     * @return int
     */
    public function getUid(): int
    {
        return $this->userId;
    }

    /**
     * Get user IP
     * @return string
     */
    public function currentIP(): string
    {
        return $this->ip;
    }

    /**
     * Write string to destination file. (if file does not exist it will be created)
     * @param string $to (file path)
     * @param string $line (string to write)
     * @return void
     */
    protected function writeLine(string $to, string $line = ''): void
    {
        if (!file_exists($to)) {
            try {
                $this->createDirsStructure($to);
            } catch (Exception $e) {
                $this->logMe(__CLASS__ . '.log', $e->getMessage());
            }
        }
        $f = fopen($to, 'a');
        fwrite($f, $line . "\n");
        fclose($f);
    }

    // Write string to destination file. (if file does not exist it will be created)
    /**
     * Write string to destination file. (if file does not exist it will be created)
     * @param string $to (file path)
     * @param string $line (string to write)
     * @return bool
     */
    protected function writeSLine(string $to, string $line = ''): bool
    {
        if (!$this->pathExists($to)) {
            return false;
        }

        $f = fopen($to, 'a');
        fwrite($f, $line);
        fclose($f);

        return true;
    }

    /**
     * Write CSV line to destination file.
     * @param string $to (file path)
     * @param array $data (array to write)
     * @return bool
     */
    protected function writeCSVLine(string $to, array $data): bool
    {
        if (!$this->pathExists($to)) {
            return false;
        }

        $f = fopen($to, 'a');
        fputcsv($f, $data, ';', '"');
        fclose($f);

        return true;
    }

    /**
     * Create complete path with dirs and subdirs (if not exist).
     * Take care about permissions - defauld is 777 but it is is  dangerous 
     * so, change permissions to fit to exactely ehat you need.
     * 
     * @param string $path
     * 
     * @return bool 
     */
    private function createDirsStructure(string $path = ''): bool
    {
        if (trim($path) == '') {
            return false;
        }

        $dirs = pathinfo($path);

        if (!file_exists($dirs['dirname'])) {
            @mkdir($dirs['dirname'], 0755, true);
        }

        if (isset($dirs['basename'])) {
            $this->resetLogFile($path);
        }

        // If path not created, throe exception.
        if (!file_exists($path)) {
            // If server is setup as need, ti will vrite own error log with following message
            throw new Exception("Can\'t create path: {$path}. Possible permissions issue.");
            return false;
        } else {
            return true;
        }
    }

    /**
     * When you need UTF-8 BOM file
     * UTF-8 BOM is not recommended but in some cases (rare languages chars it can help)
     * @param string $to (file path)
     * 
     * @return void
     */
    protected function bomFile(string $to): void
    {
        $f = @fopen($to, 'w');
        @fputs($f, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));
        @fclose($f);
    }

    /**
     * Truncate file
     * 
     * @param string $filePath
     * 
     * @return void
     */
    protected function resetLogFile(string $filePath): void
    {
        $f = @fopen($filePath, "w");
        @fwrite($f, '');
        @fclose($f);
    }

    /**
     * Write line to log file
     * 
     * @param string $filename
     * @param string $line
     * 
     * @return void
     */
    protected function logMe(string $filename, string $line = ''): void
    {
        $this->writeLine($this->logsDir . "/" . $filename, $line);
    }

    /**
     * Put execTime to data array
     * @return float
     */
    public function execTime(): float
    {
        return round((microtime(true) - $this->startTime), 5);
    }

    /**
     * Try to find user's IP
     * @return void
     */
    protected function getIP(): void
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            //ip from share internet
            $this->ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            //ip pass from proxy
            $this->ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            $this->proxyUser = true;
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            $this->ip = $_SERVER['REMOTE_ADDR'];
        } else {
            $this->ip = '';
        }
    }

    /**
     * Check path existance. If path does not exist, 
     * try to create it.
     * 
     * @param string $path
     * 
     * @return bool
     */
    private function pathExists(string $path): bool
    {
        if (!file_exists($path)) {
            try {
                $this->createDirsStructure($path);
            } catch (Exception $e) {
                $this->logMe(__CLASS__ . '.log', $e->getMessage());
                return false;
            }
        }

        return true;
    }

    /**
     * Usually dedicated for forms security.
     * 
     * @return void
     */
    protected function generateToken(): void
    {
        $this->data['token'] = $_SESSION['token'] = bin2hex(random_bytes(35));
    }

    /**
     * Check token 
     * 
     * @return bool
     */
    protected function isValidToken($token = null): bool
    {
        if (!$token || $token !== $_SESSION['token']) {
            // return 405 http status code
            header($_SERVER['SERVER_PROTOCOL'] . ' 405 Method Not Allowed');

            // if header fail, die as failfafe.
            die();
        }
        return true;
    }

    /**
     * Parse custom .env file
     */
    protected function parseCustomEnv(): bool
    {
        if (!file_exists($this->envFilePath)) {
            $this->logMe('root.log', "No .env file fo8und: " . $this->envFilePath);
            return false;
        }

        $env = file_get_contents($this->envFilePath);

        $env = explode("\n", $env);

        $envData = [];
        foreach ($env as $line) {
            $line = trim($line);
            if (empty($line) || strpos($line, '#') === 0) {
                continue;
            }

            $line = explode("=", $line);
            $envData[$line[0]] = $line[1];
        }

        $this->envData = $envData;
        return true;
    }
}
