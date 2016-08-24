# php-pdo-class
PHP PDO class
<br>
<br>
Connection<br>
$db = new Database('host', 'username', 'password', 'database');
<br><br>
Select<br>
$db->select(['id', 'name'])
    ->from('users')
    ->where(['username = ' => 'webdeziner']);<br>
$rows = $db->fetchAll();
<br>
foreach($rows as $row) {<br>
    echo $row['id'].': '.$row['name'];<br>
}
<br><br>
Insert<br>
$db->insert('users')
    ->set(array('username' => 'user1', 'password' => 'password1', 'name' => 'User number one'));
<br><br>
Update<br>
$db->update('users')
    ->set(array('username' => 'testuser'))
    ->where(['id = ' => 1]);
<br><br>
Delete<br>
$db->delete('users')
    ->where(['id =' => 1]);
