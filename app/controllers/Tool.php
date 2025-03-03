<?php
class Tool extends Tools
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        // Do nothin in simplified demo version
    }

    /**
     * Call method in model for create new section
     */
    public function createsection(): void
    {
        $data['data'] = $this->createNewSection();
        if ($data['data'] === false) {
            $this->data['message'] = '<div class="alert alert-danger" role="alert">
            ' . print_r($this->errors, true) . '
            </div>';
        } else {
            $created = $this->created2String($this->getCreated());
            $this->data['message'] = '<div class="alert alert-success" role="alert">
  ' . $created . '
  Structure created.
  <a href="/?c=' . $this->sectionName . '">Link</a>
</div>';
        }
    }

    /**
     * Transforn result of create new MVC structure to string. (as report)
     */
    private function created2String(array $created = []): string
    {
        if (empty($created)) {
            return "Created:";
        }

        $return = "<br>Created:<br>-------<br>";
        foreach ($created as $mvc => $path) {
            $return .= $mvc . "<br>" . $path . "<br>";
        }
        return $return . "<br>";
    }
}
