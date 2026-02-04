<?php
$conn = mysqli_connect('127.0.0.1', 'root', '', 'dometopia');
$result = mysqli_query($conn, "DESCRIBE fm_goods");
echo "<h1>Columns</h1>";
echo "<table border=1><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
while($row = mysqli_fetch_assoc($result)) {
    echo "<tr>";
    foreach($row as $val) echo "<td>$val</td>";
    echo "</tr>";
}
echo "</table>";

$result = mysqli_query($conn, "SHOW INDEX FROM fm_goods");
echo "<h1>Indexes</h1>";
echo "<table border=1><tr><th>Key_name</th><th>Column_name</th><th>Non_unique</th></tr>";
while($row = mysqli_fetch_assoc($result)) {
    echo "<tr>";
    echo "<td>{$row['Key_name']}</td><td>{$row['Column_name']}</td><td>{$row['Non_unique']}</td>";
    echo "</tr>";
}
echo "</table>";
