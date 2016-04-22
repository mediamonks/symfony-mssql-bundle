[![Total Downloads](https://poser.pugx.org/mediamonks/mssql-bundle/downloads)](https://packagist.org/packages/mediamonks/mssql-bundle)
[![Latest Stable Version](https://poser.pugx.org/mediamonks/mssql-bundle/v/stable)](https://packagist.org/packages/mediamonks/mssql-bundle)
[![Latest Unstable Version](https://poser.pugx.org/mediamonks/mssql-bundle/v/unstable)](https://packagist.org/packages/mediamonks/mssql-bundle)
[![SensioLabs Insight](https://img.shields.io/sensiolabs/i/c42e43fd-9c7b-47e1-8264-3a98961e9236.svg)](https://insight.sensiolabs.com/projects/c42e43fd-9c7b-47e1-8264-3a98961e9236)
[![License](https://poser.pugx.org/mediamonks/mssql-bundle/license)](https://packagist.org/packages/mediamonks/mssql)

# MediaMonksMssqlBundle

This bundle provides a driver which makes Symfony and Doctrine (DBAL and ORM) support dblib driver on *nix systems.

- Uses pdo_sqlsrv on Windows and pdo_dblib on unix
- Supports UTF-8
- Supports transactions
- Supports Doctrine ORM
- Supports Sonata Admin
- Supports Symfony Sessions

There are a few bundles and packages out there which can handle dblib but unfortunately we could not make it work with UTF-8 so we spent a while on cracking this issue. Since others had already done a lot of work on this we felt we had to return the favour to the community and release this bundle.

# A Big Thanks

- [Realestate.co.nz](http://www.realestate.co.nz/) for their awesome [MssqlBundle](https://github.com/realestateconz/MssqlBundle/) under the MIT licence, the base for this bundle
- [Leaseweb](https://www.leaseweb.com/) for their [PdoDblib package](https://github.com/LeaseWeb/LswDoctrinePdoDblib), it was very useful too
- Michal for his hard work in the past on projects where dblib had to be used, your input for this bundle was of much valuable
- Arjen for his dedicated work on the function that prepares the query for supporting UTF-8 ♥
- Tonny & Elmar from the .NET team for their help on working with Microsoft SQL Server

# Notes

Some things which are good to know:

- Do not use pdo_dblib unless you have very good reasons, use a different driver if you get the chance
- You probably need to be change the FreeTDS conf (usually in /etc/freetds/freetds.conf), make sure you can
- Support for ORM requires modifications to some Doctrine files in the vendor folder, a script for Composer which does this automatically is provided in this bundle
- When doing manual queries you should always use executeQuery() and executeUpdate() on Doctrine DBAL
- Named parameters are not supported, UTF-8 characters will be saved as ?
- This bundle was only tested on SQL Server 2008 R2 SP2
- Joined inheritance mapping in ORM is not supported yet since we did not need it, this might be supported in the future
- Symfony session handler does not use locking now, this might be supported in the future

# Documentation

Please refer to the files in the [/Resources/doc](/Resources/doc) folder.