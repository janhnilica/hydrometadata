<?php
class Err500Controller extends Controller
{
    public function process(array $params): void
    {
        header('HTTP/1.0 500 Internal Server Error');
        $this->header["title"] = $this->createTitle("500 Internal Server Error");
        $this->view = "err500";
    }
}
