<?php
class Err401Controller extends Controller
{
    public function process(array $params): void
    {
        header('HTTP/1.0 401 Unauthorized');
        $this->header["title"] = $this->createTitle("401 Unauthorized");
        $this->view = "err401";
    }
}
