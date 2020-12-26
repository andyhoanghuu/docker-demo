<?php
require_once 'shell.php';

?>
<!DOCTYPE html>
<html>
<head>
    <title>Your demo is preparing...</title>
</head>
<body>
<div class="demo-info visible" style="text-align: center">
    <h1>Click this button below to prepare the demo.</h1>
    <form id="demo-form" action="shell.php" method="POST">
        <input type="hidden" name="process" value="1">
        <p>To do Captcha here!</p>
        <button type="submit">Proceed</button>
    </form>
    <div>
        <p>Got Stuck with something, Please raise a ticket at <a target="_blank" href="http://landofcoder.com/">LandOfCoder Helpdesk</a></p>
    </div>
</body>
</html>
