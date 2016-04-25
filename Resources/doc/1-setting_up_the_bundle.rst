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

