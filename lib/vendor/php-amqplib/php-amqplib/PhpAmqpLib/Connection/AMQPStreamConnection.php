<?php
namespace PhpAmqpLib\Connection;

use PhpAmqpLib\Wire\IO\StreamIO;

class AMQPStreamConnection extends AbstractConnection
{
    /**
     * @param string $host
     * @param string $port
     * @param string $user
     * @param string $password
     * @param string $vhost
     * @param bool $insist
     * @param string $login_method
     * @param null $login_response
     * @param string $locale
     * @param float $connection_timeout
     * @param float $read_write_timeout
     * @param null $context
     * @param bool $keepalive
     * @param int $heartbeat
     */
    public function __construct(
        $host,
        $port,
        $user,
        $password,
        $vhost = '/',
        $insist = false,
        $login_method = 'AMQPLAIN',
        $login_response = null,
        $locale = 'en_US',
        $connection_timeout = 3.0,
        $read_write_timeout = 3.0,
        $context = null,
        $keepalive = false,
        $heartbeat = 0
    ) {
        $io = new StreamIO(
            $host,
            $port,
            $connection_timeout,
            $read_write_timeout,
            $context,
            $keepalive,
            $heartbeat
        );

        parent::__construct(
            $user,
            $password,
            $vhost,
            $insist,
            $login_method,
            $login_response,
            $locale,
            $io,
            $heartbeat,
            $connection_timeout
        );

        // save the params for the use of __clone, this will overwrite the parent
        $this->construct_params = func_get_args();
    }

    protected static function try_create_connection($host, $port, $user, $password, $vhost, $options){
        $insist = isset($options['insist']) ?
                        $options['insist'] : false;
        $login_method = isset($options['login_method']) ?
                              $options['login_method'] :'AMQPLAIN';
        $login_response = isset($options['login_response']) ?
                                $options['login_response'] : null;
        $locale = isset($options['locale']) ?
                        $options['locale'] : 'en_US';
        $connection_timeout = isset($options['connection_timeout']) ?
                                    $options['connection_timeout'] : 3.0;
        $read_write_timeout = isset($options['read_write_timeout']) ?
                                    $options['read_write_timeout'] : 3.0;
        $context = isset($options['context']) ?
                         $options['context'] : null;
        $keepalive = isset($options['keepalive']) ?
                           $options['keepalive'] : false;
        $heartbeat = isset($options['heartbeat']) ?
                           $options['heartbeat'] : 0;
        return new static($host,
                          $port,
                          $user,
                          $password,
                          $vhost,
                          $insist,
                          $login_method,
                          $login_response,
                          $locale,
                          $connection_timeout,
                          $read_write_timeout,
                          $context,
                          $keepalive,
                          $heartbeat);
    }
}
