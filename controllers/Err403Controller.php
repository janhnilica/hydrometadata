<?php
class Err403Controller extends Controller
{
    public function process(array $params): void
    {
        header('HTTP/1.0 403 Forbidden');
        $this->header["title"] = $this->createTitle("403 Forbidden");
        $this->view = "err403";
    }
}
