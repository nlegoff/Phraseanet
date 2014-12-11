<?php

namespace Proxies\__CG__\Entities;

/**
 * THIS CLASS WAS GENERATED BY THE DOCTRINE ORM. DO NOT EDIT THIS FILE.
 */
class __CG__EntitiesStoryWZ extends \Entities\StoryWZ implements \Doctrine\ORM\Proxy\Proxy
{
    private $_entityPersister;
    private $_identifier;
    public $__isInitialized__ = false;
    public function __construct($entityPersister, $identifier)
    {
        $this->_entityPersister = $entityPersister;
        $this->_identifier = $identifier;
    }
    /** @private */
    public function __load()
    {
        if (!$this->__isInitialized__ && $this->_entityPersister) {
            $this->__isInitialized__ = true;

            if (method_exists($this, "__wakeup")) {
                // call this after __isInitialized__to avoid infinite recursion
                // but before loading to emulate what ClassMetadata::newInstance()
                // provides.
                $this->__wakeup();
            }

            if ($this->_entityPersister->load($this->_identifier, $this) === null) {
                throw new \Doctrine\ORM\EntityNotFoundException();
            }
            unset($this->_entityPersister, $this->_identifier);
        }
    }

    /** @private */
    public function __isInitialized()
    {
        return $this->__isInitialized__;
    }

    public function getId()
    {
        if ($this->__isInitialized__ === false) {
            return (int) $this->_identifier["id"];
        }
        $this->__load();

        return parent::getId();
    }

    public function setSbasId($sbasId)
    {
        $this->__load();

        return parent::setSbasId($sbasId);
    }

    public function getSbasId()
    {
        $this->__load();

        return parent::getSbasId();
    }

    public function setRecordId($recordId)
    {
        $this->__load();

        return parent::setRecordId($recordId);
    }

    public function getRecordId()
    {
        $this->__load();

        return parent::getRecordId();
    }

    public function getRecord(\Alchemy\Phrasea\Application $app)
    {
        $this->__load();

        return parent::getRecord($app);
    }

    public function setRecord(\record_adapter $record)
    {
        $this->__load();

        return parent::setRecord($record);
    }

    public function setUsrId($usrId)
    {
        $this->__load();

        return parent::setUsrId($usrId);
    }

    public function getUsrId()
    {
        $this->__load();

        return parent::getUsrId();
    }

    public function setUser(\User_Adapter $user)
    {
        $this->__load();

        return parent::setUser($user);
    }

    public function getUser(\Alchemy\Phrasea\Application $app)
    {
        $this->__load();

        return parent::getUser($app);
    }

    public function setCreated($created)
    {
        $this->__load();

        return parent::setCreated($created);
    }

    public function getCreated()
    {
        $this->__load();

        return parent::getCreated();
    }

    public function __sleep()
    {
        return array('__isInitialized__', 'id', 'sbas_id', 'record_id', 'usr_id', 'created');
    }

    public function __clone()
    {
        if (!$this->__isInitialized__ && $this->_entityPersister) {
            $this->__isInitialized__ = true;
            $class = $this->_entityPersister->getClassMetadata();
            $original = $this->_entityPersister->load($this->_identifier);
            if ($original === null) {
                throw new \Doctrine\ORM\EntityNotFoundException();
            }
            foreach ($class->reflFields as $field => $reflProperty) {
                $reflProperty->setValue($this, $reflProperty->getValue($original));
            }
            unset($this->_entityPersister, $this->_identifier);
        }
    }
}
