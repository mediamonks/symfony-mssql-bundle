# Step 0: Requirements

## FreeTDS

FreeTDS is a set of libraries for Unix and Linux that allows your programs to natively talk to Microsoft SQL Server and Sybase databases.

This bundle needs the `FreeTDS`_ and `pdo_dblib`_ driver to be installed on your system.

Make sure your freetds config (usually located in /etc/freetds/freetds.conf) contains this block:

## Install dblib & FreeTDS on OS X

The simplest way to install dblib on OS X is by using [Homebrew](http://brew.sh/). This guide will therefore assume that you have [Homebew installed](http://brew.sh/), are using PHP [installed with Homebrew](https://github.com/Homebrew/homebrew-php#installation) and using the native Apache server in OS X.

### Install

1. `brew update`
2. `brew install php56-pdo-dblib` The installation can take some time, be patient!
3. `sudo apachectl restart`

*Any version of PHP installed with Homebrew should work. Just change `php56` in the command to your version.*

### Update the FreeTDS config

To finish up we also need to do some light modifications to the FreeTDS configuration.

You should find the config file in `/usr/local/etc/freetds.conf`. Open it with your favorit text editor.

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
