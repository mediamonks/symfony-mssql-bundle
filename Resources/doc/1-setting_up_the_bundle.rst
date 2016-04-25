Step 1: Setting up the bundle
=============================

A) Download the Bundle
----------------------

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

.. code-block:: bash

    $ composer require mediamonks/mssql-bundle ~1.0

This command requires you to have Composer installed globally, as explained
in the `installation chapter`_ of the Composer documentation.

B) Enable the Bundle
--------------------

Then, enable the bundle by adding it to the list of registered bundles
in the ``app/AppKernel.php`` file of your project:

.. code-block:: php

    <?php
    // app/AppKernel.php

    // ...
    class AppKernel extends Kernel
    {
        public function registerBundles()
        {
            $bundles = [
                // ...

                new MediaMonks\RestApiBundle\MediaMonksMssqlBundle(),
            ];

            // ...
        }

        // ...
    }


C) Enable the driver
--------------------

Now you should be able to enable the driver by updating your
Doctrine DBAL config in the ``app/config/config.yml`` so it looks like this:

.. code-block:: yaml

    doctrine:
        dbal:
            driver_class: MediaMonks\MssqlBundle\Doctrine\DBAL\Driver\PDODblib\Driver
            wrapper_class: MediaMonks\MssqlBundle\Doctrine\DBAL\Connection
            host:     "%database_host%"
            port:     "%database_port%"
            dbname:   "%database_name%"
            user:     "%database_user%"
            password: "%database_password%"
            charset:  UTF-8

.. caution::

    Make sure the ``driver`` parameter is no longer present in your configuration
    otherwise this bundle will probably not work due to conflicts.

.. _`installation chapter`: https://getcomposer.org/doc/00-intro.md

D) Enable the Composer script
-----------------------------

For persisting entities with Doctrine ORM it is needed to override some classes within the Doctrine
ORM namespace. Unfortunately Doctrine uses a lot of private methods which keeps us from using OOP
to solve this issue. To work around this the namespaces in a few files are modified so Doctrine loads
our classes instead of theirs to make it work again.

Open your ``composer.json`` file and make sure the script is added to ``post-install-cmd`` and
``post-update-cmd``

.. code-block:: json
    "scripts": {
        "post-install-cmd": [
            "MediaMonks\\MssqlBundle\\Composer\\ScriptHandler::ensureDoctrineORMOverrides"
        ],
        "post-update-cmd": [
            "Sensio\\Bundle\\DistributionBundle\\Composer\\ScriptHandler::buildBootstrap"
        ]
    }

.. note::
    Probably other scripts are already present, make sure this script is added first.

.. caution::
    This will modify files in your ``vendor`` folder, make sure this script is ran when doing automated deploys.


E) Enable database sessions (optional)
======================================

Since the default PDO Session Handler provided by Symfony does not support ``pdo_dblib``
a custom handler is needed. Luckily the configuring it is very similar as configuring the default one.

Open up ``app/services.yml`` and add these services:

.. code-block:: yaml

    services:
        pdo:
            class: MediaMonks\MssqlBundle\PDO\PDO
            arguments:
                host: "%database_host%"
                port: "%database_port%"
                dbname: "%database_name%"
                user: "%database_user%"
                password: "%database_password%"
                options:
            calls:
                - [setAttribute, [3, 2]] # \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION

        session.handler.pdo:
            class:     MediaMonks\MssqlBundle\Session\Storage\Handler\PdoSessionHandler
            public:    false
            arguments: ["@pdo"]

Then open up ``app/config.yml`` and change the session handler id to the one we just created:

.. code-block:: yaml

    framework:
        session:
            handler_id: session.handler.pdo
