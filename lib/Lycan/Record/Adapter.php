<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Lycan\Record;

abstract class Adapter implements Interfaces\Adapter 
{
    /**
     * The database connection resource
     *
     * @var Resource
     * @access protected
     */
    protected $connection;

    /**
     * Database host name
     *
     * @var string
     * @access protected
     */
    protected $host;

    /**
     * Database user name
     *
     * @var string
     * @access protected
     */
    protected $user;

    /**
     * Database password
     *
     * @var string
     * @access protected
     */
    protected $password;

    /**
     * Database name
     *
     * @var string
     * @access protected
     */
    protected $database;

    /**
     * Database charset
     *
     * @var string
     * @access protected
     */
    protected $charset;

}
