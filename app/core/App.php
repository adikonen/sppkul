<?php

/**
 * Main Class for routing and select controller to handle request
 */
class App
{
    /**
     * @var $controller
     * Default Controller for handle request
     */
    protected $controller = 'Redirector';

    /**
     * @var string $method
     * Default controller method for handle request
     */
    protected $method = 'index';

    /**
     * @var array $params
     * Default parameters for controller method
     */
    protected $params = [];

    /**
     *  select the controller, method and it params
     */
    public function __construct()
    {
        $url = $this->parseUrl();
        if (isset($url[0])) {
            if (file_exists('app/controllers/' . $url[0] . '.php')) {
                $this->controller = $url[0];
                unset($url[0]);
            }
        }
        
        require_once 'app/controllers/' . $this->controller . '.php';
        $this->controller = new $this->controller;
        
        if (isset($url[1])) {
            if (method_exists($this->controller, $url[1])) {
                $this->method = $url[1];
                unset($url[1]);
            }
        }
        
        if (!empty($url)) {
            $this->params = array_values($url);
        }
        
        
        $post_method = ['store','update','action'];
        
        // automatically http_post only each method in $post_method
        if (in_array($this->method, $post_method)) {
            http_post_only();
        }

        try {
            call_user_func_array([$this->controller, $this->method], $this->params);
        } catch (Exception $err) {
            Flasher::set('warning', 'Terjadi kesalahan pada sistem. Hubungi developer dengan segera.');
            ErrorHandler::log($err, 'Something went wrong', [
                'controller' => $this->controller,
                'method' => $this->method,
                'parameter' => $this->params
            ]);
        }
    }

    /**
     * convert string url to array
     * example: 
     * http://localhost/spp/public/admin_kelas/edit/4/
     * 
     * it will return
     * ['admin_kelas','edit','4'];
     * 
     * @return array
     */
    public function parseURL()
    {
        if (isset($_GET['url'])) {
            $url = filter_var($_GET['url'], FILTER_SANITIZE_URL);
            $url = trim($url,'/');
            $url = explode('/', $url);
            return $url;
        }
    }
}