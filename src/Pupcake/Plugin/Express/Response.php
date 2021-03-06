<?php
namespace Pupcake\Plugin\Express;

use Pupcake;

class Response extends Pupcake\Object
{
    private $plugin;
    private $app_instance;
    private $route;
    private $req;
    private $in_inner_route;

    public function __construct($plugin, $route, $req)
    {
        $this->plugin = $plugin;
        $this->app_instance = $plugin->getAppInstance();
        $this->route = $route;
        $this->req = $req;
        $this->in_inner_route = false;
        $plugin->trigger("pupcake.plugin.express.response.create", "", array("response" => $this));
    }

    public function getAppInstance()
    {
        return $this->app_instance;
    }

    public function send($output)
    {
        if ($this->in_inner_route) {
            $this->plugin->storageSet('inner_route_output', $output); 
        }
        else{
            $this->plugin->storageSet('output', $output); 
        }
    }

    public function sendJSON($data = array())
    {
        $this->contentType("application/json")->send(json_encode($data));
    }

    public function contentType($content_type)
    {
        $this->app_instance->setHeader("Content-type: $content_type");
        return $this;
    }

    public function redirect($uri)
    {
        $this->plugin->getAppInstance()->redirect($uri);
    }

    public function forward($request_type, $uri, $request_params = array())
    {
        return $this->app_instance->forward($request_type, $uri, $request_params);
    }

    public function toRoute($request_type, $route_pattern, $params)
    {
        $this->in_inner_route = true;
        $router = $this->plugin->getAppInstance()->getRouter();
        $route = $router->getRoute($request_type, $route_pattern);
        $route->setParams($params);
        $this->req->setRoute($route);
        $route->execute(array($this->req, $this));
        $this->req->setRoute($this->route); //set back the request route
        $this->in_inner_route = false;
        return $this->plugin->storageGet('inner_route_output');
    }

    public function inInnerRoute()
    {
        return $this->in_inner_route;
    }
}

