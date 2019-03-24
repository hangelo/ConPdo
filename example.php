<?php

// The class ConPdo
require 'conpdo.class.php';


// Parameters to be used in the query
$id = '1';
$name = 'Henrique%';


try {

    // Open the database connection and start the transaction
	Transaction::Start();

	// SQL Statement to query from the database
	$sql = 'SELECT ACC_ID, ACC_NAME FROM ACCOUNT WHERE ACC_ID = :id OR ACC_NAME like :name LIMIT 0, 10';

    // Prepare the statement above
	$qry = Transaction::ExecutePrepare($sql);

    // Pass the parameters
	$qry->bindParam(':id', $id);
    $qry->bindParam(':name', $name);

    // Execute the statement
	$qry->execute();

    // Get records from the statement
    $content = '';
    while ($r = $qry->fetch()) {
        $content .= ($content != '' ? "\n" : '').$r['ACC_ID'].' - '.$r['ACC_NAME'];
    }

    // Get the log information of the statement above
    $log_information = $qry->GetStatementAudit();

    // Validate the log information
    $log_validation = ($qry->ValidateStatementAudit($log_information) ? 'Log verified' : 'This log was violated');

	// Commit the transaction
	Transaction::Commit();

} catch ( Exception $e ) {
    // Rollback if something goes wrong
	Transaction::rollback($e->getMessage(), true);
}


// Print the results
echo <<<END
### Content of the query
$content

### Log information
$log_information

### Log validation
$log_validation
END;
