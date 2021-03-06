<?php

namespace Jackalope\Version;

use ArrayIterator;

use PHPCR\Version\VersionInterface;
use PHPCR\Version\VersionException;

use Jackalope\Node;
use Jackalope\ObjectManager;
use Jackalope\NotImplementedException;
use Jackalope\FactoryInterface;

/**
 * {@inheritDoc}
 *
 * @api
 */
class VersionHistory extends Node
{
    protected $objectmanager; //TODO if we would use parent constructor, this would be present
    protected $path; //TODO if we would use parent constructor, this would be present

    /**
     * Cache of all versions to only fetch them once.
     * @var array
     */
    protected $versions = null;

    /**
     * FIXME: is this sane? we do not call the parent constructor
     *
     * @param FactoryInterface $factory the object factory
     * @param ObjectManager $objectmanager
     * @param string $absPath the repository path of this version history.
     */
    public function __construct(FactoryInterface $factory, ObjectManager $objectmanager,$absPath)
    {
        $this->objectmanager = $objectmanager;
        $this->path = $absPath;
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function getVersionableIdentifier()
    {
        throw new NotImplementedException();
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function getRootVersion()
    {
        throw new NotImplementedException();
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function getAllLinearVersions()
    {
        throw new NotImplementedException();
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function getAllVersions()
    {
        if (!$this->versions) {
            $uuid = $this->objectmanager->getVersionHistory($this->path);
            $node = $this->objectmanager->getNode($uuid, '/', 'Version\\Version');
            $results = array();
            $rootNode = $this->objectmanager->getNode('jcr:rootVersion', $node->getPath(), 'Version\\Version');
            $results[$rootNode->getName()] = $rootNode;
            $this->versions = array_merge($results, $this->getEventualSuccessors($rootNode));
        }
        return new ArrayIterator($this->versions);
    }

    /**
     * Walk along the successors line to get all versions of this node
     *
     * According to spec, 3.13.1.4, these are called eventual successors
     *
     * @param VersionInterface $node the node to get successors
     *      from
     *
     * @return array list of VersionInterface
     */
    protected function getEventualSuccessors($node)
    {
        $successors = $node->getSuccessors();
        $results = array();
        foreach ($successors as $successor) {
            $results[$successor->getName()] = $successor;
            $results = array_merge($results, $this->getEventualSuccessors($successor)); //TODO: remove end recursion
        }
        return $results;
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function getAllLinearFrozenNodes()
    {
        throw new NotImplementedException();
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function getAllFrozenNodes()
    {
        throw new NotImplementedException();
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function getVersion($versionName)
    {
        $this->getAllVersions();
        if (isset($this->versions[$versionName])) {
            return $this->versions[$versionName];
        }

        throw new VersionException("No version '$versionName'");
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function getVersionByLabel($label)
    {
        throw new NotImplementedException();
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function addVersionLabel($versionName, $label, $moveLabel)
    {
        throw new NotImplementedException();
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function removeVersionLabel($label)
    {
        throw new NotImplementedException();
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function hasVersionLabel($label, $version = null)
    {
        throw new NotImplementedException();
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function getVersionLabels($version = null)
    {
        throw new NotImplementedException();
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function removeVersion($versionName)
    {
        $uuid = $this->objectmanager->getVersionHistory($this->path);
        $node = $this->objectmanager->getNode($uuid, '/', 'Version\\Version');

        $this->objectmanager->removeVersion($node->getPath(), $versionName);

        if (!is_null($this->versions)) {
            unset($this->versions[$versionName]);
        }
    }

}
