<?php

class Homes extends Lib
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Simplest usage of model method.
     * Set data to pass to view.
     * $this->data will be passed to view automatically.
     * 
     * @return void
     */
    protected function hiThere(): void
    {
        // Store to var to pass to view.
        $result = new Demo();

        $this->data = ['card-title' => 'Demo', 'message' => $result->hiThere()];
    }
}
