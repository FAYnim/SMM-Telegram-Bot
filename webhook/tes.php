<?php
	$host = $_SERVER["HTTP_HOST"];
	$path = $_SERVER["PHP_SELF"];
	$self = dirname($_SERVER["PHP_SELF"]);

	echo $host.$path."\n";
	echo $self."\n";

	$url_rep = str_replace($self, "", $path);
	$url = $host.$url_rep;
	echo $url."\n";
?>
