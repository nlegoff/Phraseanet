<?php

namespace Alchemy\Tests\Phrasea\Authentication\Phrasea;

use Alchemy\Phrasea\Authentication\Phrasea\FailureManager;
use Alchemy\Phrasea\Model\Entities\AuthFailure;
use Gedmo\Timestampable\TimestampableListener;
use Symfony\Component\HttpFoundation\Request;

class FailureManagerTest extends \PhraseanetTestCase
{
    /**
     * @covers Alchemy\Phrasea\Authentication\Phrasea\FailureManager::saveFailure
     */
    public function testSaveFailure()
    {
        $repo = $this->getRepo();
        $em = $this->createEntityManagerMock();
        $recaptcha = $this->getReCaptchaMock(null);

        $ip = '192.168.16.178';
        $username = 'romainneutron';

        $request = $this->getRequestMock();
        $request->expects($this->any())
            ->method('getClientIp')
            ->will($this->returnValue($ip));

        $oldFailures = [
            $this->getMock('Alchemy\Phrasea\Model\Entities\AuthFailure'),
            $this->getMock('Alchemy\Phrasea\Model\Entities\AuthFailure')
        ];

        $repo->expects($this->once())
            ->method('findOldFailures')
            ->will($this->returnValue($oldFailures));

        $em->expects($this->exactly(count($oldFailures)))
            ->method('remove')
            ->with($this->isInstanceOf('Alchemy\Phrasea\Model\Entities\AuthFailure'));

        $catchFailure = null;
        $em->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf('Alchemy\Phrasea\Model\Entities\AuthFailure'))
            ->will($this->returnCallback(function ($failure) use (&$catchFailure) {
                $catchFailure = $failure;
            }));

        $manager = new FailureManager($repo, $em, $recaptcha, 9);
        $manager->saveFailure($username, $request);

        $this->assertEquals($ip, $catchFailure->getIp());
        $this->assertEquals(true, $catchFailure->getLocked());
        $this->assertEquals($username, $catchFailure->getUsername());
    }

    /**
     * @covers Alchemy\Phrasea\Authentication\Phrasea\FailureManager::checkFailures
     */
    public function testCheckFailures()
    {
        $repo = $this->getRepo();
        $em = $this->createEntityManagerMock();
        $recaptcha = $this->getReCaptchaMock(null);
        $request = $this->getRequestMock();

        $username = 'romainneutron';

        $oldFailures = [];

        $repo->expects($this->once())
            ->method('findLockedFailuresMatching')
            ->will($this->returnValue($oldFailures));

        $manager = new FailureManager($repo, $em, $recaptcha, 9);
        $manager->checkFailures($username, $request);
    }

    /**
     * @covers Alchemy\Phrasea\Authentication\Phrasea\FailureManager::checkFailures
     */
    public function testCheckFailuresLessThan9()
    {
        $repo = $this->getRepo();
        $em = $this->createEntityManagerMock();
        $recaptcha = $this->getReCaptchaMock(null);
        $request = $this->getRequestMock();

        $username = 'romainneutron';

        $oldFailures = $this->ArrayIze(function () {
            return $this->getMock('Alchemy\Phrasea\Model\Entities\AuthFailure');
        }, 8);

        $repo->expects($this->once())
            ->method('findLockedFailuresMatching')
            ->will($this->returnValue($oldFailures));

        $manager = new FailureManager($repo, $em, $recaptcha, 9);
        $manager->checkFailures($username, $request);
    }

    /**
     * @covers Alchemy\Phrasea\Authentication\Phrasea\FailureManager::checkFailures
     */
    public function testCheckFailuresMoreThan9WithoutCaptcha()
    {
        $repo = $this->getRepo();
        $em = $this->createEntityManagerMock();
        $recaptcha = $this->getReCaptchaMock(false);
        $request = $this->getRequestMock();

        $username = 'romainneutron';

        $oldFailures = $this->ArrayIze(function () {
            return $this->getMock('Alchemy\Phrasea\Model\Entities\AuthFailure');
        }, 10);

        $repo->expects($this->once())
            ->method('findLockedFailuresMatching')
            ->will($this->returnValue($oldFailures));

        $manager = new FailureManager($repo, $em, $recaptcha, 9);
        $manager->checkFailures($username, $request);
    }

    /**
     * @covers Alchemy\Phrasea\Authentication\Phrasea\FailureManager::checkFailures
     */
    public function testCheckFailuresMoreThan9WithCorrectCaptcha()
    {
        $repo = $this->getRepo();
        $em = $this->createEntityManagerMock();
        $request = $this->getRequestMock();
        $recaptcha = $this->getReCaptchaMock(true, $request, true);

        $username = 'romainneutron';

        $oldFailures = $this->ArrayIze(function () {
            $failure = $this->getMock('Alchemy\Phrasea\Model\Entities\AuthFailure');
            $failure->expects($this->once())
                ->method('setLocked')
                ->with($this->equalTo(false));

            return $failure;
        }, 10);

        $repo->expects($this->once())
            ->method('findLockedFailuresMatching')
            ->will($this->returnValue($oldFailures));

        $manager = new FailureManager($repo, $em, $recaptcha, 9);
        $manager->checkFailures($username, $request);
    }

    /**
     * @expectedException \Alchemy\Phrasea\Authentication\Exception\RequireCaptchaException
     * @covers Alchemy\Phrasea\Authentication\Phrasea\FailureManager::checkFailures
     */
    public function testCheckFailuresMoreThan9WithIncorrectCaptcha()
    {
        $repo = $this->getRepo();
        $em = $this->createEntityManagerMock();
        $request = $this->getRequestMock();
        $recaptcha = $this->getReCaptchaMock(true, $request, false);

        $username = 'romainneutron';

        $oldFailures = $this->ArrayIze(function () {
            return $this->getMock('Alchemy\Phrasea\Model\Entities\AuthFailure');
        }, 10);

        $repo->expects($this->once())
            ->method('findLockedFailuresMatching')
            ->will($this->returnValue($oldFailures));

        $manager = new FailureManager($repo, $em, $recaptcha, 9);
        $manager->checkFailures($username, $request);
    }

    public function testCheckFailuresTrialsIsConfigurableUnderThreshold()
    {
        $repo = $this->getRepo();
        $em = $this->createEntityManagerMock();
        $recaptcha = $this->getReCaptchaMock(null);
        $request = $this->getRequestMock();

        $username = 'romainneutron';

        $oldFailures = $this->ArrayIze(function () {
            return $this->getMock('Alchemy\Phrasea\Model\Entities\AuthFailure');
        }, 2);

        $repo->expects($this->once())
            ->method('findLockedFailuresMatching')
            ->will($this->returnValue($oldFailures));

        $manager = new FailureManager($repo, $em, $recaptcha, 2);
        $manager->checkFailures($username, $request);
    }

    public function testTrialsIsConfigurable()
    {
        $em = $this->createEntityManagerMock();

        $recaptcha = $this->getReCaptchaMock(null);

        $manager = new FailureManager($this->createEntityRepositoryMock(), $em, $recaptcha, 2);
        $this->assertEquals(2, $manager->getTrials());
    }

    /**
     * @expectedException \Alchemy\Phrasea\Authentication\Exception\RequireCaptchaException
     * @covers Alchemy\Phrasea\Authentication\Phrasea\FailureManager::checkFailures
     */
    public function testCheckFailuresTrialsIsConfigurableOverThreshold()
    {
        $repo = $this->getRepo();
        $em = $this->createEntityManagerMock();
        $request = $this->getRequestMock();
        $recaptcha = $this->getReCaptchaMock(true, $request, false);

        $username = 'romainneutron';

        $oldFailures = $this->ArrayIze(function () {
            return $this->getMock('Alchemy\Phrasea\Model\Entities\AuthFailure');
        }, 3);

        $repo->expects($this->once())
            ->method('findLockedFailuresMatching')
            ->will($this->returnValue($oldFailures));

        $manager = new FailureManager($repo, $em, $recaptcha, 2);
        $manager->checkFailures($username, $request);
    }

    public function testFailureOlderThan2MonthsAreRemovedOnFailure()
    {
        self::$DI['app']['orm.em']->getEventManager()->removeEventSubscriber(new TimestampableListener());
        $recaptcha = $this->getReCaptchaMock(null);

        $ip = '192.168.16.178';
        $username = 'romainneutron';

        $request = $this->getRequestMock();
        $request->expects($this->any())
            ->method('getClientIp')
            ->will($this->returnValue($ip));

        $this->assertCount(10, self::$DI['app']['orm.em']->getRepository('Phraseanet:AuthFailure')
                ->findOldFailures());
        $this->assertCount(12, self::$DI['app']['orm.em']->getRepository('Phraseanet:AuthFailure')
                ->findAll());

        $manager = new FailureManager(self::$DI['app']['repo.auth-failures'], self::$DI['app']['orm.em'], $recaptcha, 9);
        $manager->saveFailure($username, $request);

        $this->assertCount(0, self::$DI['app']['orm.em']->getRepository('Phraseanet:AuthFailure')
                ->findOldFailures());
        $this->assertCount(3, self::$DI['app']['orm.em']->getRepository('Phraseanet:AuthFailure')
                ->findAll());

        self::$DI['app']['orm.em']->getEventManager()->addEventSubscriber(new TimestampableListener());
    }

    private function ArrayIze($failure, $n)
    {
        $failures = [];

        for ($i = 0; $i != $n; $i++) {
            $failures[] = $failure();
        }

        return $failures;
    }

    private function getReCaptchaMock($isSetup = true, Request $request = null, $isValid = false)
    {
        $recaptcha = $this->getMockBuilder('Neutron\ReCaptcha\ReCaptcha')
            ->disableOriginalConstructor()
            ->getMock();

        if ($request) {
            $response = $this->getMockBuilder('Neutron\ReCaptcha\Response')
                ->disableOriginalConstructor()
                ->getMock();

            $response->expects($this->once())
                ->method('isValid')
                ->will($this->returnValue($isValid));

            $recaptcha->expects($this->once())
                ->method('bind')
                ->with($this->equalTo($request))
                ->will($this->returnValue($response));
        }

        if (null !== $isSetup) {
            $recaptcha->expects($this->once())
                ->method('isSetup')
                ->will($this->returnValue($isSetup));
        }

        return $recaptcha;
    }

    private function getRequestMock()
    {
        return $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function getRepo()
    {
        return $this->getMockBuilder('Alchemy\Phrasea\Model\Repositories\AuthFailureRepository')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
