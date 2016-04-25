Step 0: Requirements *nix
=========================

FreeTDS & PHP Driver
--------------------

FreeTDS is a set of libraries for Unix and Linux that allows your programs to
natively talk to Microsoft SQL Server and Sybase databases.

This bundle needs the ``FreeTDS`` and ``pdo_dblib`` driver to be installed on your system.

Installation
````````````

Ubuntu/Debian Linux
'''''''''''''''''''

Simply install the correct packages:

1. ``apt-get -y install php56-sybase php56-odbc tdsodbc``

OS X
''''

The simplest way to install dblib on OS X is by using `Homebrew`_. This guide will therefore assume that you
have `Homebrew`_ installed, are using `PHP installed with Homebrew`_ and using the native Apache server in OS X.

1. ``brew update``
2. ``brew install php56-pdo-dblib`` The installation can take some time, be patient!
3. ``sudo apachectl restart``

.. note::

    Any version of PHP installed should work. Just change ``php56`` in the command to match your version og PHP.

FreeTDS Configuration
`````````````````````

To finish up we also need to do some modifications to the FreeTDS configuration.

You can probably find the config file in `/usr/freetds/freetds.conf` or `/usr/local/etc/freetds.conf`.
pen it with your favorite text editor.

In the ``[global]`` section, find a line that looks like

.. code-block:: yaml

    ; tds version = x

Uncomment the line and change the version number to **8.0**, you should have

.. code-block:: yaml

    tds version = 8.0

Then also add the following line below the version

.. code-block:: yaml

    client charset = UTF-8

Save the changes and you are good to go.

.. note::
    Although you would expect this to be enough for UTF-8 to be supported it isn't,
    that's why you still need this bundle to make it work.

Alternate Configuration
'''''''''''''''''''''''

When your FreeTDS config is used by multiple apps and you can not change the
global settings you can create a separate group instead

.. code-block:: yaml

    [example]
    host = <host_or_ip_of_db_server>
    tds version = 8.0
    client charset = UTF-8

Then when you specifiy the host in your Symfony parameters.yml you will use "example"
instead of the host/ip and this block with it's configuration will be used instead

.. _Homebrew: http://brew.sh/
.. _PHP installed with Homebrew: https://github.com/Homebrew/homebrew-php#installation