<?php

require_once 'config.php';
require_once 'database.php';

// Connection
$db = new Database($config['db']['host'], $config['db']['username'], $config['db']['password'], $config['db']['database']);
// Select
$db->select(['id', 'name'])
    ->from('users')
    ->where(['username = ' => 'webdeziner']);
$rows = $db->fetchAll();

foreach($rows as $row) {
    echo $row['id'].': '.$row['name']."<br>";
}
// Insert
$db->insert('users')
    ->set(array('username' => 'user1', 'password' => 'password1', 'name' => 'User number one'));
// Update
$db->update('users')
    ->set(array('username' => 'testuser'))
    ->where(['id = ' => 1]);
// Delete
$db->delete('users')
    ->where(['id =' => 1]);
