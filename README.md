<p align="center"><a href="https://framework.iumio.com" target="_blank">
    <img src="https://framework.iumio.com/images/iumio-framework-horizontal.png" width="350">
</a></p>

iumio Framework Internal Server (Fis)
======================================

@ Let's create more simply

Description
------------

Reverse proxy for PHP built-in server which supports multiprocessing and TLS/SSL encryption adapted for [iumio Framework][1]

## Installing

### Global install

```
composer global require iumio/framework-internal-server:^2.0
```

If not yet, you must add **`~/.composer/vendor/bin`** to `$PATH`.  
Append the following statement to `~/.bashrc`, `~/.zshrc` or what not.

```bash
export PATH="$HOME/.composer/vendor/bin:$PATH"
```

### Local install only for development environment

```
composer require --dev iumio/framework-internal-server:^2.0
```

Use **`vendor/bin/hyper-run`** as the execution path.

## Usage

```ShellSession
iumio-user@localhost:~$ hyper-run -h

Usage:
    hyper-run <options>

Example:
    hyper-run -S localhost:8080 -s localhost:8081

[Required]
    -S   "<Host>:<Port>" of an HTTP server. Multiple arguments can be accepted.
    -s   "<Host>:<Port>" of an HTTPS server. Multiple arguments can be accepted.

[Optional]
    -n   The number of PHP built-in server clusters, from 1 to 20. Default is 10.
    -t   Path for the document root. Default is the current directory.
    -r   Path for the router script. Default is empty.
    -c   Path for the PEM-encoded certificate.
         Default is "/Users/iumio-user/.composer/vendor/iumio/framework-internal-server/certificate.pem".

Restrictions:
    - The option -s is only supported on PHP 5.6.0 or later.
    - Access logs will not be displayed on Windows.

mpyw@localhost:~$
```

## Example

```
hyper-run -S localhost:8080 -s localhost:8081 -t src/app/www
```

It listens on

- `http://localhost:8080`
- `https://localhost:8081`

using the directory `src/app/www` as the document root.

## Note for Windows users

Unfortunately, `cmd.exe` has no option to run via shebang `#!/usr/bin/env php`, so you need to create the following batch file in the proper directory.

### For Standalone PHP

```bat
@echo OFF
"C:\php\php.exe" "%HOMEPATH%\.composer\vendor\iumio/framework-internal-server\hyper-run" %*
```

### For XAMPP

```bat
@echo OFF
"C:\xampp\php\php.exe" "%HOMEPATH%\.composer\vendor\iumio/framework-internal-server\hyper-run" %*
```

### Used with iumio Framework Console Manager

```
iumio-user@localhost:~$ php bin/manager server:start
```

* Warning : iumio Framework is not compatible with https protocol with php built-in server.

Documentation
-------------

* Read the [Documentation][4] if you are new iumio Framework user.


Contributing
------------

Framework Internal Server is forked from [mpyw/php-hyper-builtin-server][8] to make it compatible with iumio Framework.

iumio Framework is an Open Source with MIT Licence.
We need any help to continue the framework development and create a new community.


About Us
--------

iumio Framework is an [iumio component][5], created by [RAFINA Dany][6] and co-founded by [HURON Kevin][7]

[1]: https://framework.iumio.com
[2]: https://framework.iumio.com/installation/manual
[3]: https://framework.iumio.com/download/SE#fh5co-features
[4]: https://framework.iumio.com/doc
[5]: https://iumio.com
[6]: https://www.linkedin.com/in/dany-rafina-672041b3/
[7]: http://kevinhuron.fr/
[8]: https://github.com/mpyw/php-hyper-builtin-server
