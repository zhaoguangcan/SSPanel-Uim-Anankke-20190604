<?php

namespace App\Controllers;

use App\Services\View;
use App\Services\Auth;

/**
 * BaseController
 */
class BaseController
{
    /**
     * @var \Smarty
     */
    protected $view;

    /**
     * @var \App\Models\User
     */
    protected $user;

    /**
     * @var \Slim\Views\PhpRenderer
     */
    protected $renderer;

    /**
     * Construct page renderer
     */
    public function __construct(\Slim\Container $container)
    {
        $this->view = View::getSmarty();
        $this->user = Auth::getUser();

        $this->renderer = $container->get('renderer');
        if ($this->user->isLogin) {
            define('TEMPLATE_PATH', BASE_PATH . '/resources/views/' . $this->user->theme . '/');
        } else {
            define('TEMPLATE_PATH', BASE_PATH . '/resources/views/' . $_ENV['theme'] . '/');
        }
        $this->renderer->setTemplatePath(TEMPLATE_PATH);
        $this->renderer->addAttribute('user', $this->user);
    }

    /**
     * Get smarty
     *
     * @return \Smarty
     */
    public function view()
    {
        return $this->view;
    }
    /**
     * @param $response
     * @param $res
     * @return mixed
     */
    public function echoJson($response, $res)
    {
        return $response->getBody()->write(json_encode($res));
    }
}
