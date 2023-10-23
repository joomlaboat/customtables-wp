<?php

use CustomTables\CT;
use CustomTables\IntegrityChecks;

$ct = new CT;

$result = IntegrityChecks::check($ct);

if (count($result) > 0)
    echo '<ol><li>' . implode('</li><li>', $result) . '</li></ol>';
else
    echo '<p>Database table structure is up-to-date.</p>';
