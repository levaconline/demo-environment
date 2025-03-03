<?php
class Tools extends Lib
{
    protected $sectionName = '';

    private $created = [];

    public function __construct()
    {
        parent::__construct();
    }

    public function getCreated()
    {
        return $this->created;
    }

    protected function createNewSection(): bool
    {
        $sectionName = (string)filter_input(INPUT_POST, 'section_name');

        $sectionName = trim($sectionName);

        if (!$this->validateSectionName($sectionName)) {
            return false;
        }

        // Class/File name to cammelCase
        $sectionName = $this->toCamelCase($sectionName);
        $this->sectionName = $sectionName;

        /**
         * TODO: call create_section.sh script from terminal
         * to create section dir and files.
         * Reason: PHP can't create files with executable permissions. (security reasons)
         */

        /*
        if (file_exists("../create_section.sh")) {
            exec("./../create_section.sh" . $sectionName);
        } else {
            $this->errors[] = "Can't find script.";
            return false;
        }
        */

        // Create section sceleton.
        if (!$this->createController($sectionName)) {
            return false;
        }

        if (!$this->createModel($sectionName)) {
            return false;
        }

        if (!$this->createView($sectionName)) {
            return false;
        }

        return true;
    }

    /**
     * Convert string to camelCase
     * @param string $str
     * @return bool
     */
    private function validateSectionName($sectionName = ''): bool
    {
        // Empty not allowed
        if (trim($sectionName) == '') {
            $this->errors[] = "Empty spaces not allowed in the section name.";
            return false;
        }

        // Reserved names
        $reserved = ['layouts'];
        if (in_array($sectionName, $reserved)) {
            $this->errors[] = "Reserved name. Please choose another.";
            return false;
        }

        // Does it already exist?
        $fn = __DIR__ . "/.." . $this->controllersDir . "/" . $sectionName . ".php";
        if (file_exists($fn)) {
            $this->errors[] = "Already exist. Please choose another name.";
            return false;
        }

        // Alowed characters?
        if (preg_match('/^[a-zA-Z0-9\-_]+$/', $sectionName) == 0) {
            $this->errors[] = "Only letters and numbers allowed.";
            return false;
        }

        // First character must ne letter
        if (is_numeric(substr(trim($sectionName), 0, 1))) {
            $this->errors[] = "First character must not be numeric.";
            return false;
        }

        return true;
    }

    /**
     * Create controller file. And fill with predefined source
     * @param string $sectionName
     * @return bool
     */
    private function createController(string $sectionName): bool
    {
        $source = '<?php
class ' . $sectionName . ' extends ' . $sectionName . 's 
{
    // TODO: Add your properties here (if needed).
            
    public function __construct () 
    {
        parent::__construct();
    }
            
    public function index () 
    {
        // Code example.
        $this->data[\'message\'] = \'<div class="alert alert-success" role="alert">
UNDER CONSTRUCTION</br>
<a href="/?c=Home">Link back</a>
</div>\';
        // TODO: Write your code.
    }

    // TODO: Write your methods.
}  
';

        $cd = __DIR__ . "/.." . $this->controllersDir;
        $path = $cd . "/" . $sectionName . ".php";

        if (!file_exists($cd)) {
            var_dump($cd);
            $this->errors[] = "Dir not found.(" . $cd . ")";
            return false;
        }

        if (!is_writable($cd)) {
            $this->errors[] = "File is not writable.";
            return false;
        }

        file_put_contents($path, $source);

        // Is created?
        if (file_exists($path)) {
            $this->created['controller'] = $path;
            return true;
        } else {
            var_dump($path);
        }


        $this->errors[] = "Can't create controller.";
        return false;
    }

    /**
     * Create model file. And fill with predefined source
     * @param string $sectionName
     * @return bool
     */
    private function createModel(string $sectionName): bool
    {
        $source = '<?php
class ' . $sectionName . 's extends Lib  
{
    // TODO: Add your properties here (if needed).
            
    public function __construct () 
    {
        parent::__construct();
    }
            
    // TODO: Write your methods.
}
';
        $path = __DIR__ . "/.." . $this->modelsDir . "/" . $sectionName . "s.php";
        @file_put_contents($path, $source);

        // Is created?
        if (file_exists($path)) {
            $this->created['model'] = $path;
            return true;
        }

        $this->errors[] = "Can't create model.";
        return false;
    }

    /**
     * Create view dir and index file. And fill with predefined source
     * @param string $sectionName
     * @return bool
     */
    private function createView(string $sectionName)
    {
        // Create dir.
        $dir = __DIR__ . "/.." . $this->viewsDir . "/" . $sectionName;
        @mkdir($dir);

        // Is created?
        if (!file_exists($dir)) {
            $this->errors[] = "Can't create view dir.";
            return true;
        }

        $source = '<div class="text-left">
                <h5>Autogenerated section sceleton: ' . $sectionName . '</h5>
                <p class="font-weight-light text-info">Open the files and write your code.</p>
            </div>
            <div class="text-center">
                                  <?php
                  echo $class->data[\'message\'];
?>

            </div>
        ';
        $path = $dir . "/index.php";
        @file_put_contents($path, $source);

        // Is created?
        if (file_exists($path)) {
            $this->created['view'] = $path;
            return true;
        }

        $this->errors[] = "Can't create view.";
        return false;
    }
}
