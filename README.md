# ConPdo
------------

It is a class inheriting PDO to add log audition feature.

I create this project to make easer the usage of PDO connector and to handle the Log Audition and debug code.

When we connect to the database, a transaction is automaticaly started. All parameter we pass to the statement is stored and after the Execute command we can get a audition log information with a Hash verification code.


# Configure the class
------------

At the start of the file ```conpdo.class.php``` you can find some constants.

Configure the database connection informoing the URL, Database Name, Username and Password. The other constants are already filled with default values.
 
 
# Example
------------

#### The table to run the test

You can find the SQL command to create the table and prepare the records to be tested on the file ```database.sql```.


#### To run the test

The file ```example.php``` demonstrate the usage of the class executing a query statement and generate the log audition information. After all, we validate the log audition.
