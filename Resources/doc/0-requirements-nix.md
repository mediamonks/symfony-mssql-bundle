# Step 0: Requirements *nix

## FreeTDS

FreeTDS is a set of libraries for Unix and Linux that allows your programs to natively talk to Microsoft SQL Server and Sybase databases.

This bundle needs the `FreeTDS` and `pdo_dblib` driver to be installed on your system.

## Installation

### Ubuntu/Debian Linux

1. sudo apt-get -y install php56-odbc tdsodbc

### OS X

The simplest way to install dblib on OS X is by using [Homebrew](http://brew.sh/). This guide will therefore assume that you have [Homebew installed](http://brew.sh/), are using PHP [installed with Homebrew](https://github.com/Homebrew/homebrew-php#installation) and using the native Apache server in OS X.

1. `brew update`
2. `brew install php56-pdo-dblib` The installation can take some time, be patient!
3. `sudo apachectl restart`

*Any version of PHP installed should work. Just change `php56` in the command to your version.*

## Config

To finish up we also need to do some modifications to the FreeTDS configuration.

You can probably find the config file in `/usr/freetds/freetds.conf` or `/usr/local/etc/freetds.conf`. Open it with your favorite text editor.

In the `[global]` section, find a line that looks like

```
# TDS protocol version
; tds version = x
```

Uncomment the line and change the version number to **8**, you should have

```
# TDS protocol version
tds version = 8
```

And finally, add the following line below the version

```
# Charset
client charset = UTF-8
```

Save the changes and you are good to go.


## Alternate Config

When your FreeTDS config is used by multiple apps and you can not change the global settings you can create a separate group instead

```
[example]
host = <host_or_ip_of_db_server>
tds version = 8.0
client charset = UTF-8
```

Then when you specifiy the host in your Symfony parameters.yml you will use "example" instead of the host/ip and this block with it's configuration will be used instead
