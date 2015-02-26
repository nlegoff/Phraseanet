<?php

/*
 * This file is part of Phraseanet
 *
 * (c) 2005-2015 Alchemy
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Alchemy\Phrasea\Controller\Root;

use Alchemy\Phrasea\Model\Entities\SessionModule;
use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class Session implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $app['controller.session'] = $this;

        $controllers = $app['controllers_factory'];

        $controllers->post('/update/', 'controller.session:updateSession')
            ->bind('update_session');

        $controllers->post('/notifications/', 'controller.session:getNotifications')
            ->bind('list_notifications');

        $controller = $controllers->post('/delete/{id}', 'controller.session:deleteSession')
            ->bind('delete_session');

        $app['firewall']->addMandatoryAuthentication($controller);

        return $controllers;
    }

    /**
     * Check things to notify
     *
     * @param  Application  $app
     * @param  Request      $request
     * @return JsonResponse
     */
    public function getNotifications(Application $app, Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            $app->abort(400);
        }

        $ret = [
            'status'  => 'unknown',
            'message' => '',
            'notifications' => false,
            'changed' => []
        ];

        if ($app['authentication']->isAuthenticated()) {
            $usr_id = $app['authentication']->getUser()->getId();
            if ($usr_id != $request->request->get('usr')) { // I logged with another user
                $ret['status'] = 'disconnected';

                return $app->json($ret);
            }
        } else {
            $ret['status'] = 'disconnected';

            return $app->json($ret);
        }

        try {
            $app['phraseanet.appbox']->get_connection();
        } catch (\Exception $e) {
            return $app->json($ret);
        }

        if (1 > $moduleId = (int) $request->request->get('module')) {
            $ret['message'] = 'Missing or Invalid `module` parameter';

            return $app->json($ret);
        }

        $ret['status'] = 'ok';

        $ret['notifications'] = $app['twig']->render('prod/notifications.html.twig', [
            'notifications' => $app['events-manager']->get_notifications()
        ]);

        $baskets = $app['orm.em']->getRepository('Phraseanet:Basket')->findUnreadActiveByUser($app['authentication']->getUser());

        foreach ($baskets as $basket) {
            $ret['changed'][] = $basket->getId();
        }

        if (in_array($app['session']->get('phraseanet.message'), ['1', null])) {
            if ($app['phraseanet.configuration']['main']['maintenance']) {
                $ret['message'] .= _('The application is going down for maintenance, please logout.');
            }

            if ($app['conf']->get(['registry', 'maintenance', 'enabled'], false)) {
                $ret['message'] .= strip_tags($app['conf']->get(['registry', 'maintenance', 'enabled']));
            }
        }

        return $app->json($ret);
    }

    /**
     * Check session state
     *
     * @param  Application  $app
     * @param  Request      $request
     * @return JsonResponse
     */
    public function updateSession(Application $app, Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            $app->abort(400);
        }

        $ret = [
            'status'  => 'unknown',
            'message' => '',
            'notifications' => false,
            'changed' => []
        ];

        if ($app['authentication']->isAuthenticated()) {
            $usr_id = $app['authentication']->getUser()->getId();
            if ($usr_id != $request->request->get('usr')) { // I logged with another user
                $ret['status'] = 'disconnected';

                return $app->json($ret);
            }
        } else {
            $ret['status'] = 'disconnected';

            return $app->json($ret);
        }

        try {
            $app['phraseanet.appbox']->get_connection();
        } catch (\Exception $e) {
            return $app->json($ret);
        }

        if (1 > $moduleId = (int) $request->request->get('module')) {
            $ret['message'] = 'Missing or Invalid `module` parameter';

            return $app->json($ret);
        }

        $session = $app['repo.sessions']->find($app['session']->get('session_id'));
        $session->setUpdated(new \DateTime());

        if (!$session->hasModuleId($moduleId)) {
            $module = new SessionModule();
            $module->setModuleId($moduleId);
            $module->setSession($session);
            $app['orm.em']->persist($module);
        } else {
            $app['orm.em']->persist($session->getModuleById($moduleId)->setUpdated(new \DateTime()));
        }

        $app['orm.em']->persist($session);
        $app['orm.em']->flush();

        $ret['status'] = 'ok';

        $ret['notifications'] = $app['twig']->render('prod/notifications.html.twig', [
            'notifications' => $app['events-manager']->get_notifications()
        ]);

        $baskets = $app['repo.baskets']->findUnreadActiveByUser($app['authentication']->getUser());

        foreach ($baskets as $basket) {
            $ret['changed'][] = $basket->getId();
        }

        if (in_array($app['session']->get('phraseanet.message'), ['1', null])) {
            if ($app['conf']->get(['main', 'maintenance'])) {
                $ret['message'] .= $app->trans('The application is going down for maintenance, please logout.');
            }

            if ($app['conf']->get(['registry', 'maintenance', 'enabled'])) {
                $ret['message'] .= strip_tags($app['conf']->get(['registry', 'maintenance', 'message']));
            }
        }

        return $app->json($ret);
    }

    /**
     * Deletes identified session
     *
     * @param Application $app
     * @param Request     $request
     * @param integer     $id
     *
     * @return RedirectResponse|JsonResponse
     */
    public function deleteSession(Application $app, Request $request, $id)
    {
        $session = $app['repo.sessions']->find($id);

        if (null === $session) {
            $app->abort(404, 'Unknown session');
        }

        if (null === $session->getUser()) {
            $app->abort(403, 'Unauthorized');
        }

        if ($session->getUser()->getId() !== $app['authentication']->getUser()->getId()) {
            $app->abort(403, 'Unauthorized');
        }

        $app['orm.em']->remove($session);
        $app['orm.em']->flush();

        if ($app['request']->isXmlHttpRequest()) {
            return $app->json([
                'success' => true,
                'session_id' => $id
            ]);
        }

        return $app->redirectPath('account_sessions');
    }
}
