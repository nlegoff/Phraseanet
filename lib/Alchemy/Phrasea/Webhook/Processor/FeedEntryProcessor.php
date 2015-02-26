<?php

namespace Alchemy\Phrasea\Webhook\Processor;

class FeedEntryProcessor extends AbstractProcessor implements ProcessorInterface
{
    public function process()
    {
        $data = $this->event->getData();

        if (!isset($data->{"entry_id"})) {
            return null;
        }

        $entry = $this->app['orm.em']->getRepository('Phraseanet::Entry')->find($data->{"entry_id"});

        if (null === $entry) {
            return null;
        }

        $data = $this->event->getData();

        $feed = $entry->getFeed();

        $query = $this->app['phraseanet.user-query'];

        $query->include_phantoms(true)
            ->include_invite(false)
            ->include_templates(false)
            ->email_not_null(true);

        if ($feed->getCollection($this->app)) {
            $query->on_base_ids([$feed->getCollection($this->app)->get_base_id()]);
        }

        $start = 0;
        $perLoop = 100;
        $users = [];

        do {
            $results = $query->limit($start, $perLoop)->execute()->get_results();
            foreach ($results as $user) {
                $users[] = [
                    'email' => $user->getEmail(),
                    'firstname' => $user->getFirstname() ?: null,
                    'lastname' => $user->getLastname() ?: null,
                ];
            }
            $start += $perLoop;
        } while (count($results) > 0);

        return [
            'event' => $this->event->getName(),
            'users_were_notified' => isset($data->{'notify_email'}) ?: !!$data->{"notify_email"},
            'feed' => [
                'id' => $feed->getId(),
                'title' => $feed->getTitle(),
                'description' => $feed->getSubtitle(),
            ],
            'entry' => [
                'id' => $entry->getId(),
                'author' => [
                    'name' => $entry->getAuthorName(),
                    'email' => $entry->getAuthorEmail()
                ],
                'title' => $entry->getTitle(),
                'description' => $entry->getSubtitle(),
            ],
            'users' => $users
        ];
    }
}
