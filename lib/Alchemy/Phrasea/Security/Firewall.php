<?php

namespace Alchemy\Phrasea\Security;

use Silex\Application;
use Silex\Controller;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class Firewall
{
    private $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function requireSetUp()
    {
        if (!$this->app['phraseanet.configuration-tester']->isInstalled()) {
            $this->app->abort(302, 'Phraseanet is not installed', array(
                'X-Phraseanet-Redirect' => $this->app->path('setup'),
            ));
        }

        return;
    }

    public function requireAdmin()
    {
        $this->requireNotGuest();

        if (!$this->app['authentication']->getUser()->ACL()->is_admin()) {
            $this->app->abort(403, 'Admin role is required');
        }

        return $this;
    }

    public function requireAccessToModule($module)
    {
        if (!$this->app['authentication']->getUser()->ACL()->has_access_to_module($module)) {
            $this->app->abort(403, 'You do not have required rights');
        }

        return $this;
    }

    public function requireAccessToSbas($sbas_id)
    {
        if (!$this->app['authentication']->getUser()->ACL()->has_access_to_sbas($sbas_id)) {
            $this->app->abort(403, 'You do not have required rights');
        }

        return $this;
    }

    public function requireAccessToBase($base_id)
    {
        if (!$this->app['authentication']->getUser()->ACL()->has_access_to_base($base_id)) {
            $this->app->abort(403, 'You do not have required rights');
        }

        return $this;
    }

    public function requireRight($right)
    {
        if (!$this->app['authentication']->getUser()->ACL()->has_right($right)) {
            $this->app->abort(403, 'You do not have required rights');
        }

        return $this;
    }

    public function requireRightOnBase($base_id, $right)
    {
        if (!$this->app['authentication']->getUser()->ACL()->has_right_on_base($base_id, $right)) {
            $this->app->abort(403, 'You do not have required rights');
        }

        return $this;
    }

    public function requireRightOnSbas($sbas_id, $right)
    {
        if (!$this->app['authentication']->getUser()->ACL()->has_right_on_sbas($sbas_id, $right)) {
            $this->app->abort(403, 'You do not have required rights');
        }

        return $this;
    }

    public function requireNotGuest()
    {
        if ($this->app['authentication']->getUser()->is_guest()) {
            $this->app->abort(403, 'Guests do not have admin role');
        }

        return $this;
    }

    public function requireAuthentication(Request $request = null)
    {
        $params = array();
        if (null !== $request) {
            $params['redirect'] = '..'.$request->getPathInfo().'?'.$request->getQueryString();
        }
        if (!$this->app['authentication']->isAuthenticated()) {
            return new RedirectResponse($this->app->path('homepage', $params));
        }
    }

    public function addMandatoryAuthentication($controllers)
    {
        if (!$controllers instanceof ControllerCollection && !$controllers instanceof Controller) {
            throw new \InvalidArgumentException('Controllers must be either a Controller or a ControllerCollection.');
        }

        $app = $this->app;

        $controllers->before(function (Request $request) use ($app) {
            if (null !== $response = $app['firewall']->requireAuthentication($request)) {
                return $response;
            }
        });
    }

    public function requireNotAuthenticated()
    {
        if ($this->app['authentication']->isAuthenticated()) {
            return new RedirectResponse($this->app->path('prod'));
        }
    }

    public function requireOrdersAdmin()
    {
        if (false === !!count($this->app['authentication']->getUser()->ACL()->get_granted_base(array('order_master')))) {
            $this->app->abort(403, 'You are not an order admin');
        }

        return $this;
    }
}
