<?php

namespace App\Controllers;

use App\Models\InviteCode;
use App\Utils\AliPay;
use App\Utils\TelegramSessionManager;
use App\Utils\TelegramProcess;
use App\Utils\Geetest;

use Slim\Http\Request;
use Slim\Http\Response;

/**
 *  HomeController
 */
class HomeController extends BaseController
{
    public function index(Request $request, Response $response, array $args): Response
    {
        $GtSdk = null;
        $recaptcha_sitekey = null;
        if ($_ENV['captcha_provider'] != '') {
            switch ($_ENV['captcha_provider']) {
                case 'recaptcha':
                    $recaptcha_sitekey = $_ENV['recaptcha_sitekey'];
                    break;
                case 'geetest':
                    $uid = time() . rand(1, 10000);
                    $GtSdk = Geetest::get($uid);
                    break;
            }
        }

        if ($_ENV['enable_telegram'] == 'true') {
            $login_text = TelegramSessionManager::add_login_session();
            $login = explode("|", $login_text);
            $login_token = $login[0];
            $login_number = $login[1];
        } else {
            $login_token = '';
            $login_number = '';
        }

        return $this->renderer->render($response, 'index.phtml', [
            'geetest_html' => $GtSdk,
            'login_token' => $login_token,
            'login_number' => $login_number,
            'telegram_bot' => $_ENV['telegram_bot'],
            'enable_logincaptcha' => $_ENV['enable_login_captcha'],
            'enable_regcaptcha' => $_ENV['enable_reg_captcha'],
            'base_url' => $_ENV['baseUrl'],
            'recaptcha_sitekey' => $recaptcha_sitekey,
        ]);
    }

    public function indexold(Request $request, Response $response, array $args): Response
    {
        return $this->renderer->render($response, 'indexold.phtml');
    }

    public function code(Request $request, Response $response, array $args): Response
    {
        $codes = InviteCode::where('user_id', '=', '0')->take(10)->get();
        return $this->view()->assign('codes', $codes)->display('code.tpl');
    }

    public function tos(Request $request, Response $response, array $args): Response
    {
        return $this->renderer->render($response, 'tos.phtml');
    }

    public function staff(Request $request, Response $response, array $args): Response
    {
        return $this->renderer->render($response, 'staff.phtml');
    }

    public function telegram($request, $response, $args)
    {
        $token = "";
        if (isset($request->getQueryParams()["token"])) {
            $token = $request->getQueryParams()["token"];
        }

        if ($token == $_ENV['telegram_request_token']) {
            TelegramProcess::process();
        } else {
            echo("不正确请求！");
        }
    }

    public function page404(Request $request, Response $response, array $args): Response
    {
        return $this->renderer->render($response, '404.phtml');
    }

    public function page405(Request $request, Response $response, array $args): Response
    {
        return $this->renderer->render($response, '405.phtml');
    }

    public function page500(Request $request, Response $response, array $args): Response
    {
        return $this->renderer->render($response, '500.phtml');
    }

    public function getOrderList(Request $request, Response $response, array $args): Response
    {
        $key = $request->getParam('key');
        if (!$key || $key != $_ENV['key']) {
            $res['ret'] = 0;
            $res['msg'] = "错误";
            $response->getBody()->write(json_encode($res));
            return $response;
        }
        $response->getBody()->write(json_encode(['data' => AliPay::getList()]));
        return $response;
    }

    public function setOrder(Request $request, Response $response, array $args): Response
    {
        $key = $request->getParam('key');
        $sn = $request->getParam('sn');
        $url = $request->getParam('url');
        if (!$key || $key != $_ENV['key']) {
            $res['ret'] = 0;
            $res['msg'] = "错误";
            $response->getBody()->write(json_encode($res));
            return $response;
        }
        $response->getBody()->write(json_encode(['res' => AliPay::setOrder($sn, $url)]));
        return $response;
    }
}
