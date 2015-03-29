<?php
    $base64 = isset($_REQUEST['base64'])? $_REQUEST['base64'] : '';
    $dec = '';
    $uns = '';
    if (strlen($base64)) {
        $dec = @base64_decode($base64);
    }
	if (strlen($dec)) {
        $uns = @unserialize($dec);
    }
    if (!$uns) $uns = @unserialize($base64);
?>
<html>
<head><meta http-equiv="content-type" value="text/html; charset=utf-8" />
<body>
<h2>Base 64:</h2>
<form method="post">
    <textarea name="base64" cols="80" rows="10"><?php if (strlen($base64)) echo htmlspecialchars($base64); ?></textarea>
    <br />
    <input type="submit" />
</form>
<h2>Text</h2>
<pre style="max-width: 100%; overflow-x: scroll"><?php echo htmlspecialchars($dec); ?></pre>
<h2>Unserialize</h2>
<?php var_dump($uns); ?>
</body>
</html>