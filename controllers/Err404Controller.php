<?php
class Err404Controller extends Controller
{
    public function process(array $params): void
    {
        header('HTTP/1.0 404 Not found');
        $this->header["title"] = $this->createTitle("404 Not found");
        $this->view = "err404";
    }
}
