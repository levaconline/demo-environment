<?php

class Home extends Homes
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
     * Call model method.
     */
    public function index(): void
    {

        // array $this->data may be filled in controller or in model.
        // array $this->data will be passed to view automatically.
        $this->hiThere();
    }
}
