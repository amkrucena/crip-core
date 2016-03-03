<?php namespace Crip\Core\Contracts;

/**
 * Interface IFileSystemObject
 * @package Crip\Core\Contracts
 */
interface IFileSystemObject
{

    /**
     * Get system path
     *
     * @return string
     */
    public function getSysPath();

    /**
     * Get system object name
     *
     * @return string
     */
    public function getName();

    /**
     * Set system object name
     *
     * @param string $name
     *
     * @return IFileSystemObject
     */
    public function setName($name);

}