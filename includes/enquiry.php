<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title><?php echo $subject; ?></title>
</head>
<body>
	<p>Hi,</p>
	<p>A new submission on your site:</p>
	<p><?php echo $name_desc; ?>: <?php echo $name; ?></p>
	<p><?php echo $email_desc; ?>: <?php echo $email; ?></p>
	<p><?php echo $phone_desc; ?>: <?php echo $phone; ?></p>
	<p><?php echo $options_desc; ?>:</p>
    <ul>
		<?php foreach($options as $option): ?>
            <li>
				<?= $option ?>
            </li>
		<?php endforeach;?>
    </ul>
</body>
</html>