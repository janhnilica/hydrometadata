<?php
class RouterController extends Controller
{
    /**
     * inner controller
     * @var Controller
     */
    protected Controller $controller;
    
    /**
     * translates this-type-of-string to thisTypeOfString
     * @param string $text
     * @return string
     */
    private function translateToCamelCase(string $text): string
    {
        $text = str_replace('-', ' ', $text);
        $text = ucwords($text);
        $text = str_replace(' ', '', $text);
        return $text;
    }
    
    /**
     * decomposes url according to slashes
     * returns array of url elements
     * @param string $url
     * @return array
     */
    private function parseURL(string $url): array
    {
        $path = parse_url($url)["path"];
        $path = ltrim($path, "/");
        $path = trim($path);
        $path = explode("/", $path);
        return $path;
    }
    
    /**
     * router implementation of the process method
     * @param array $params (contains url as the only element)
     * @return void
     */
    public function process(array $params): void
    {
        $parsedURL = $this->parseURL($params[0]);
        
        // no controller in url -> redirect to homepage
        if (empty($parsedURL[0]))
            $this->redirect(HOMEPAGE);
        
        // create inner controller
        $controllerClass = $this->translateToCamelCase(array_shift($parsedURL)) . 'Controller';
        if (file_exists('controllers/' . $controllerClass . '.php'))
            $this->controller = new $controllerClass;
        else
            $this->redirect(ERR_NOTFOUND);
        
        // call inner controller
        $this->controller->process($parsedURL);
        
        // get header
        $this->data['title'] = $this->controller->header['title'];
        $this->data['description'] = $this->controller->header['description'];
        $this->data['keywords'] = $this->controller->header['keywords'];
        
        // set layout
        $this->view = 'layout';
        
        // get SESSION messages
        $this->data["messages"] = $this->getMessages();
    }
}
