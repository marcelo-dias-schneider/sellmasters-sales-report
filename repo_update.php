<?php

$output = shell_exec('git pull');
echo "<pre>$output</pre>";

$message = $output;
$subject = "repo update on gituhb";
$email = "marcelo.d.schneider@gmail.com";
mail($email, $subject, $message);
