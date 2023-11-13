<?php
/* First jira instance */
$jiraDomainInit = 'jira.example.com';
$jiraUserInit = 'login1';
$jiraPswInit = 'p@ssw0rd1';

/* Second jira instance */
$jiraDomainRecip = 'jira2.company.org';
$jiraUserRecip = 'login2';
$jiraPswRecip = 'p@ssw0rd2';

/* This involves copying worklogs to a second instance of jira with a similar list of issue keys */
$issueJqlInit = 'project+%3D+TEST+AND+component+%3D+Core';

/* Uncomment this line instead of "$issueJqlInit" and enter the issue keys into the array. */
//$issueKeysInit = [];

/* Along with the line above, uncomment this line instead of "$issueJqlInit" and enter into the array
the issue keys of the second jira instance corresponding to the issue keys of the first jira instance in the same order. */
//$issueKeysRecip = [];

$includedWorkers = [];
$excludedWorkers = [];
?>
