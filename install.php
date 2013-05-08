<?php


?>
<!DOCTYPE HTML>
<html>
    <head>
        <title>MovieLib - Install</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <link href="css/style.css" rel="stylesheet" type="text/css">
    </head>
    <body>
        <div id="database">
            Connection to database:
            <form action="install.php" method="post">
                <table>
                <tr><td>Server:</td><td><input type="host" name="host" value="localhost"></td></tr>
                <tr><td>Port:</td><td><input type="port" name="port" value="3306"></td></tr>
                <tr><td>User:</td><td><input type="login" name="login"></td></tr>
                <tr><td>Password:</td><td><input type="pass" name="pass"></td></tr>
                <tr><td>Database:</td><td><input type="database" name="database" value="movielib"></td></tr>
                </table>
                <input type="submit" value="OK">
            </form>
        </div>
    </body>
</html>