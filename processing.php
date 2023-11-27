<?php
$jiraDomain = $jiraDomainInit;

// Get issues by query
if( empty( $issueKeysInit ) && empty( $issueJqlInit ) ) {
	exit( 'No sources for import!' . "\n" );
}
elseif( !empty( $issueKeysInit ) && empty( $issueKeysRecip ) ) {
	exit( 'No destination for export!' . "\n" );
}
if( empty( $issueKeysInit ) ) {
	$issues = jira_req( '/rest/api/2/search?jql=' . $issueJqlInit . '&fields=key' );
	// Fill new array with issue keys
	foreach( $issues['issues'] as $issue ) {
		$keys[] = $issue['key'];
	}
} else {
	$keys = $issueKeysInit;
}

// Get worklogs by issue keys
$i = 0;
foreach( $keys as $key ) {
	$jiraDomain = $jiraDomainInit;
	$data = empty( $includedWorkers ) ? [ 'taskKey' => [ $key ] ] : [ 'taskKey' => [ $key ], 'worker' => $includedWorkers ];
	$worklogs = jira_req( '/rest/tempo-timesheets/4/worklogs/search', 'POST', $data );

	foreach( $worklogs as $worklog ) {
		if( in_array( $worklog['worker'], $excludedWorkers ) ) {
			continue;
		}
		$issueKeyRecip = !empty( $issueKeysRecip ) ? $issueKeysRecip[$i] : $worklog['issue']['key'];
		$worklogToPost = [
			'comment' => $worklog['comment'],
			'timeSpentSeconds' => $worklog['timeSpentSeconds'],
			'attributes' => !empty( $worklog['attributes'] ) ? $worklog['attributes'] : (object) null,
			'started' => $worklog['started'],
			'originTaskId' => $issueKeyRecip,
			'worker' => $worklog['worker']
		];

		// Post worklogs to recipient jira
		$jiraDomain = $jiraDomainRecip;
		$data = $worklogToPost;
		$postedWorklog = jira_req( '/rest/tempo-timesheets/4/worklogs', 'POST', $data );

		if( isset( $postedWorklog['errors'] ) || isset( $postedWorklog['status-code'] ) ) {
			$worklogToPost = array_merge( [ 'ERRORS' => [ 'init_Worklog_ID' => $worklog['originId'] ] ], $worklogToPost );
			$worklogErrors = $postedWorklog['errors'] ?? [ 'message' => $postedWorklog['message'] ];
			$worklogToPost['ERRORS'] = array_merge( $worklogToPost['ERRORS'], $worklogErrors );
			$errors[] = $worklogToPost;
			if( isset( $postedWorklog['errors']['worker'] ) || isset( $postedWorklog['errors']['permission'] ) ) {
				$worklogFailedWorkers[] = $worklogToPost['worker'];
				// Get usernames by user keys
				$jiraDomain = $jiraDomainInit;
				$worklogFailedUsername = jira_req( '/rest/api/2/user?key=' . $worklogToPost['worker'] );
				$worklogFailedUsernames[] = $worklogFailedUsername['name'];
			}
		}
	}
	$i++;
}
if( isset( $errors ) ) {
	file_put_contents( 'errors-log.txt', date( '[Y-m-d H:i:s]' ) . "\n" . print_r( $errors, TRUE ) . "\n", FILE_APPEND );
	// Put failed workers to errors log
	if( isset( $worklogFailedWorkers ) ) {
		file_put_contents( 'errors-log.txt', 'FAILED WORKERS: ' . json_encode( array_values( array_unique( $worklogFailedWorkers ) ) ) . "\n", FILE_APPEND );
		file_put_contents( 'errors-log.txt', 'FAILED USERNAMES: ' . json_encode( array_values( array_unique( $worklogFailedUsernames ) ) ), FILE_APPEND );
	}
	file_put_contents( 'errors-log.txt', "\n\n\n\n", FILE_APPEND );
}
?>
