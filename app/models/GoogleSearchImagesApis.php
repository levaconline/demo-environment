<?php
class GoogleSearchImagesApis extends Lib
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Search images
     *
     * @return bool
     */
    protected function searchImages(): bool
    {
        // Code example.
        $this->data['q'] = htmlspecialchars(filter_input(INPUT_POST, 'q', 513));

        $token = htmlspecialchars(filter_input(INPUT_POST, 'token', 513));
        if (!$this->isValidToken($token)) {
            $this->data['messages'] = 'Invalid token.';
            return false;
        }

        // TODO: validate queri input string (Security reason).
        $gar = new GoogleSearchAPI($this->data['q']);
        $result = $gar->googleApiGet();

        // Convert json to array of objects.
        $this->data['result'] = json_decode($result, true);

        return true;
    }
}
