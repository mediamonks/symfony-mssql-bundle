[![Total Downloads](https://poser.pugx.org/mediamonks/mssql-bundle/downloads)](https://packagist.org/packages/mediamonks/mssql-bundle)
[![Latest Stable Version](https://poser.pugx.org/mediamonks/mssql-bundle/v/stable)](https://packagist.org/packages/mediamonks/mssql-bundle)
[![Latest Unstable Version](https://poser.pugx.org/mediamonks/mssql-bundle/v/unstable)](https://packagist.org/packages/mediamonks/mssql-bundle)
[![SensioLabs Insight](https://img.shields.io/sensiolabs/i/dae91dea-607a-4b55-9c10-593f5908fc5f.svg)](https://insight.sensiolabs.com/projects/dae91dea-607a-4b55-9c10-593f5908fc5f)
[![License](https://poser.pugx.org/mediamonks/mssql-bundle/license)](https://packagist.org/packages/mediamonks/mssql)

# MediaMonksMssqlBundle

This bundle provides a driver which makes Symfony and Doctrine (DBAL and ORM) support dblib driver on *nix systems while also being compatible with sqlsrv on Windows.

- Uses pdo_sqlsrv on Windows and pdo_dblib on unix
- Supports UTF-8
- Supports transactions
- Supports Doctrine ORM
- Supports Sonata Admin
- Supports Symfony Sessions

There are a few bundles and packages out there which can handle dblib but unfortunately we could not make it work with UTF-8 so we spent a while on cracking this issue. Since others had already done a lot of work on this we felt we had to return the favour to the community and release this bundle.

# A Big Thanks

- [Realestate.co.nz](http://www.realestate.co.nz/) for their awesome [MssqlBundle](https://github.com/realestateconz/MssqlBundle/) which was the base for this bundle
- [Leaseweb](https://www.leaseweb.com/) for their [PdoDblib package](https://github.com/LeaseWeb/LswDoctrinePdoDblib) which was very useful too
- Michal for his hard work in the past on projects where dblib had to be used, your input for this bundle very valuable
- Arjen for his dedicated work on the function that prepares the query for supporting UTF-8 ♥
- Tonny & Elmar from the .NET team for their help on working with Microsoft SQL Server

# Notes

- Do not use pdo_dblib unless you have very good reasons, use a different driver if you get the chance
- You probably need to be change the FreeTDS conf, make sure you can
- Support for ORM requires modifications to some Doctrine files in the vendor folder, a script for Composer which does this automatically is provided in this bundle
- When doing manual queries you should always use executeQuery() and executeUpdate() on Doctrine DBAL
- Named parameters are not supported, UTF-8 characters will be saved as ?
- This bundle was only tested on SQL Server 2008 R2 SP2 and nvarchar fields
- Joined inheritance mapping in ORM is not supported yet since we did not need it, this might be supported in the future
- Symfony session handler does not use locking now, this might be supported in the future
- There are no tests since original tests on Doctrine are also likely to fail, take this bundle as-is but please create a PR if you have found an issue and fixed it
- MediaMonks is not responsible for any data loss, use dblib and this driver at your own risk

# Documentation

Please refer to the files in the [/Resources/doc](/Resources/doc) folder.
