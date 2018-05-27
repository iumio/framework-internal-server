<?php

/**
 *
 *  * This is an iumio Framework component
 *  *
 *  * (c) RAFINA DANY <dany.rafina@iumio.com>
 *  *
 *  * iumio Framework, an iumio component [https://iumio.com]
 *  *
 *  * To get more information about licence, please check the licence file
 *
 */

namespace iumioFramework\Fis;

use iumioFramework\Core\Additional\Manager\Display\OutputManager as Output;

/**
 * Class Runner
 * This class able to run the Framework internal server (Fis)
 * @package iumioFramework\Fis
 * @category Framework
 * @licence  MIT License
 * @link https://framework.iumio.com
 * @author   RAFINA Dany <dany.rafina@iumio.com>
 */

class Runner
{
    /** Run the Framework internal server
     * @param string|null $host The host name or IP
     * @param int|null $port The port
     * @param bool $https If use https or not (required if https, the cert)
     * @param string|null $root The root directory
     * @param string|null $router The router
     * @param null $cert The SSL certificate if needed
     * @param int $cluster The server cluster : by default set to 10
     * @throws \ParseError
     */
    public function run(
        string $host = null,
        int $port = null,
        bool $https = false,
        string $root = null,
        string $router = null,
        $cert = null,
        int $cluster = 10
    ) {

        if (is_null($host) && is_null($port)) {
            $servers = ["localhost:8000"];
        } elseif (is_null($host) && !is_null($port)) {
            $servers = ["localhost:$port"];
        } elseif (!is_null($host) && is_null($port)) {
            $servers = ["$host:8000"];
        } else {
            $servers = ["$host:$port"];
        }

        if ($https) {
            $secures = $servers;
            $servers = [];
        } else {
            $secures = [];
        }

        $pb = null;
        if (is_dir(__DIR__.'/../../public')) {
            $pb = realpath(__DIR__.'/../../public');
        } elseif (__DIR__.'/../../../public') {
            $pb = realpath(__DIR__.'/../../../public');
        } else {
            throw new \ParseError("Cannot determine the public directory position.");
        }
        $docroot = is_null($root)? $pb : $root;
        $number  = $cluster;
        $cert    = is_null($cert) ? (__DIR__ . '/certificate.pem') : $cert;

        if (!$servers && !$secures) {
            Output::displayAsError("Framework internal server Error : At least 1 server must be specified.\n
        Referer to help command to get options list\n");
        }

        try {
            if ($number < 1 || $number > 20) {
                throw new \RuntimeException('The number of clusters must be between 1 and 20.');
            }
            if ($docroot !== null && !is_dir($docroot)) {
                throw new \RuntimeException("No such document root directory: ddd");
            }
            if ($router !== null && !is_file($router)) {
                throw new \RuntimeException("No such router script file: $router");
            }
            if (!is_file($cert)) {
                throw new \RuntimeException("No such certificate file: $cert");
            }
            if (!openssl_pkey_get_public("file://$cert")) {
                throw new \RuntimeException("Invalid certificate file: $cert");
            }

            $listeners = [];
            foreach ([$servers, $secures] as $type => $group) {
                foreach ($group as $i => $server) {
                    list($host, $port) = explode(':', $server, 2) + [1 => ''];
                    $ip = filter_var(gethostbyname($host), FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
                    $regex =
                        '/\A(?:[0-9]{1,4}|[1-5][0-9]{4}|6[0-4][0-9]{3}|65[0-4][0-9]{2}|655[0-2][0-9]|6553[0-5])\z/';
                    if ($ip === false || !preg_match($regex, $port)) {
                        throw new \RuntimeException("Invalid host or port: $server");
                    }
                    if (isset($listeners[$server])) {
                        throw new \RuntimeException("Duplicated entry: $server");
                    }
                    $listeners[$server] = [$host, $port, $type === 1, $cert];
                }
            }

            $loop = \React\EventLoop\Factory::create();

            $used_processes = [];
            $factory = new \mpyw\HyperBuiltinServer\BuiltinServerFactory($loop);
            $factory
                ->createMultipleAsync($number, $host, $docroot, $router)
                ->then(function (array $processes) use ($loop, $listeners, &$used_processes) {
                    $used_processes = $processes;
                    $master = new \mpyw\HyperBuiltinServer\Master($loop, $processes);
                    foreach ($listeners as $listener) {
                        call_user_func_array([$master, 'addListener'], $listener);
                    }
                })
                ->then(null, function ($e) use (&$used_processes) {
                    foreach ($used_processes as $process) {
                        $process->terminate();
                    }
                    throw $e;
                })
                ->done();

            set_time_limit(0);
            Output::displayAsGreen("Running the Framework Internal Server on ".
                (($https)? "https" : "http")."://".((is_null($host))? "localhost" : $host).
                ":".((is_null($port))? "8000" : $port)."", "none");
            $loop->run();
        } catch (\Throwable $e) {
            Output::displayAsError("Framework internal server Error :  {$e->getMessage()}");
        } catch (\Exception $e) {
            Output::displayAsError("Framework internal server Error :  {$e->getMessage()}");
            exit(1);
        }
    }
}
