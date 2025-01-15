<?php

$dt = new DateTime("now", new DateTimeZone('America/New_York'));

echo $dt->format('m/d/Y, H:i:s');
