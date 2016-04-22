# Step 0: Requirements Windows

## PHP Driver

1. Download the latest version of [Drivers for PHP for SQL Server](https://www.microsoft.com/en-us/download/details.aspx?id=20098) from the Microsoft website.
2. Extract the drivers to a folder where you can find them again
3. Copy both dlls (php_pdo_sqlsrv_*_*.dll & and php_sqlsrv_*_*.dll) for your php version and thread safety setting to the extension dir of your php installation
4. Enable the drivers in your php.ini:
    - extension=php_pdo_sqlsrv_*_*.dll
    - extension=php_pdo_sqlsrv_*_*.dll
5. Restart your webserver
