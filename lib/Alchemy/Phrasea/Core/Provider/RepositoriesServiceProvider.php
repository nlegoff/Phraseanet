<?php

/*
 * This file is part of Phraseanet
 *
 * (c) 2005-2014 Alchemy
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Alchemy\Phrasea\Core\Provider;

use Alchemy\Phrasea\Application as PhraseaApplication;
use Silex\Application;
use Silex\ServiceProviderInterface;

class RepositoriesServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['repo.users'] = $app->share(function (PhraseaApplication $app) {
            return $app['orm.em']->getRepository('Phraseanet:User');
        });
        $app['repo.auth-failures'] = $app->share(function (PhraseaApplication $app) {
            return $app['orm.em']->getRepository('Phraseanet:AuthFailure');
        });
        $app['repo.sessions'] = $app->share(function (PhraseaApplication $app) {
            return $app['orm.em']->getRepository('Phraseanet:Session');
        });
        $app['repo.tasks'] = $app->share(function (PhraseaApplication $app) {
            return $app['orm.em']->getRepository('Phraseanet:Task');
        });
        $app['repo.registrations'] = $app->share(function (PhraseaApplication $app) {
            return $app['orm.em']->getRepository('Phraseanet:Registration');
        });
        $app['repo.baskets'] = $app->share(function (PhraseaApplication $app) {
            return $app['orm.em']->getRepository('Phraseanet:Basket');
        });
        $app['repo.basket-elements'] = $app->share(function (PhraseaApplication $app) {
            return $app['orm.em']->getRepository('Phraseanet:BasketElement');
        });
        $app['repo.validation-participants'] = $app->share(function (PhraseaApplication $app) {
            return $app['orm.em']->getRepository('Phraseanet:ValidationParticipant');
        });
        $app['repo.story-wz'] = $app->share(function (PhraseaApplication $app) {
            return $app['orm.em']->getRepository('Phraseanet:StoryWZ');
        });
        $app['repo.orders'] = $app->share(function (PhraseaApplication $app) {
            return $app['orm.em']->getRepository('Phraseanet:Order');
        });
        $app['repo.order-elements'] = $app->share(function (PhraseaApplication $app) {
            return $app['orm.em']->getRepository('Phraseanet:OrderElement');
        });
        $app['repo.feeds'] = $app->share(function (PhraseaApplication $app) {
            return $app['orm.em']->getRepository('Phraseanet:Feed');
        });
        $app['repo.feed-entries'] = $app->share(function (PhraseaApplication $app) {
            return $app['orm.em']->getRepository('Phraseanet:FeedEntry');
        });
        $app['repo.feed-items'] = $app->share(function (PhraseaApplication $app) {
            return $app['orm.em']->getRepository('Phraseanet:FeedItem');
        });
        $app['repo.feed-publishers'] = $app->share(function (PhraseaApplication $app) {
            return $app['orm.em']->getRepository('Phraseanet:FeedPublisher');
        });
        $app['repo.feed-tokens'] = $app->share(function (PhraseaApplication $app) {
            return $app['orm.em']->getRepository('Phraseanet:FeedToken');
        });
        $app['repo.aggregate-tokens'] = $app->share(function (PhraseaApplication $app) {
            return $app['orm.em']->getRepository('Phraseanet:AggregateToken');
        });
        $app['repo.usr-lists'] = $app->share(function (PhraseaApplication $app) {
            return $app['orm.em']->getRepository('Phraseanet:UsrList');
        });
        $app['repo.usr-list-owners'] = $app->share(function (PhraseaApplication $app) {
            return $app['orm.em']->getRepository('Phraseanet:UsrListOwner');
        });
        $app['repo.usr-list-entries'] = $app->share(function (PhraseaApplication $app) {
            return $app['orm.em']->getRepository('Phraseanet:UsrListEntry');
        });
        $app['repo.lazaret-files'] = $app->share(function (PhraseaApplication $app) {
            return $app['orm.em']->getRepository('Phraseanet:LazaretFile');
        });
        $app['repo.usr-auth-providers'] = $app->share(function (PhraseaApplication $app) {
            return $app['orm.em']->getRepository('Phraseanet:UsrAuthProvider');
        });
        $app['repo.ftp-exports'] = $app->share(function (PhraseaApplication $app) {
            return $app['orm.em']->getRepository('Phraseanet:FtpExport');
        });
        $app['repo.user-queries'] = $app->share(function (PhraseaApplication $app) {
            return $app['orm.em']->getRepository('Phraseanet:UserQuery');
        });
        $app['repo.tokens'] = $app->share(function ($app) {
            return $app['orm.em']->getRepository('Phraseanet:Token');
        });
        $app['repo.presets'] = $app->share(function (PhraseaApplication $app) {
            return $app['orm.em']->getRepository('Phraseanet:Preset');
        });
        $app['repo.api-accounts'] = $app->share(function (PhraseaApplication $app) {
            return $app['orm.em']->getRepository('Phraseanet:ApiAccount');
        });
        $app['repo.api-logs'] = $app->share(function (PhraseaApplication $app) {
            return $app['orm.em']->getRepository('Phraseanet:ApiLog');
        });
        $app['repo.api-applications'] = $app->share(function (PhraseaApplication $app) {
            return $app['orm.em']->getRepository('Phraseanet:ApiApplication');
        });
        $app['repo.api-oauth-codes'] = $app->share(function (PhraseaApplication $app) {
            return $app['orm.em']->getRepository('Phraseanet:ApiOauthCode');
        });
        $app['repo.api-oauth-tokens'] = $app->share(function (PhraseaApplication $app) {
            return $app['orm.em']->getRepository('Phraseanet:ApiOauthToken');
        });
        $app['repo.api-oauth-refresh-tokens'] = $app->share(function (PhraseaApplication $app) {
            return $app['orm.em']->getRepository('Phraseanet:ApiOauthRefreshToken');
        });
        $app['repo.webhook-event'] = $app->share(function (PhraseaApplication $app) {
            return $app['orm.em']->getRepository('Phraseanet:WebhookEvent');
        });
        $app['repo.webhook-delivery'] = $app->share(function (PhraseaApplication $app) {
            return $app['orm.em']->getRepository('Phraseanet:WebhookEventDelivery');
        });
    }

    public function boot(Application $app)
    {
    }
}
