<?php
class AboutController extends Controller
{
    public function process(array $params): void
    {
        $this->header["title"] = $this->createTitle("About");
        $this->view = "about";
    }
}
