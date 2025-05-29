<?php
file_put_contents(__DIR__ . "/logout_flag.txt", "1"); // Set logout flag
header("Location: index.php?logged_out=1");
exit();
?>
