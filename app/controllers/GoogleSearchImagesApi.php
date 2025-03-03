<?php
class GoogleSearchImagesApi extends GoogleSearchImagesApis
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Index page
     */
    public function index(): void
    {
        // Code example.
        $this->generateToken();
        $this->data['q'] = $_POST['q'] ?? '';
    }

    /**
     * Call model method for images search.
     */
    public function getImages(): void
    {
        if ($this->searchImages()) {
            $this->messages[] = 'Search Results.';
        } else {
            $this->messages[] = 'No results found.';
        }
    }
}
