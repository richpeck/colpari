<?php

	$availableLanguages		= array(
		'de'			=> 'Deutschland',
		'en'			=> 'English',
		'ee'			=> 'Eesti',
		'es'			=> 'España',
		'fr'			=> 'France',
		'hu'			=> 'Hungary',
		'it'			=> 'Italia',
		'nl'			=> 'Nederland',
		'ru'			=> 'Россия',
		'zh'			=> '台灣',
		'ph'			=> 'Philippines',
	);

	$timifyWidgetId			= get_option('timify_widget_id');
	$timifyWidgetLanguage	= get_option('timify_widget_language');
	$timifyWidgetPosition	= get_option('timify_widget_position');
	$timifyWidgetButtonLabel= get_option('timify_widget_button_label');

	$error					= array();

	if (isset($_POST['timify_widget_save'])) {

		if (!isset($_POST['timify_widget_id']) || trim($_POST['timify_widget_id']) === '') {
			$error['timify_widget_id']			= 'Please fill your timify widget id!';
		}

		if (!isset($_POST['timify_widget_language']) || trim($_POST['timify_widget_language']) === '') {
			$error['timify_widget_language']	= 'Please choose your widget language!';
		}

		if (!isset($_POST['timify_widget_position']) || trim($_POST['timify_widget_position']) === '') {
			$error['timify_widget_position']	= 'Please choose your widget position!';
		}

		if (isset($_POST['timify_widget_position']) &&
			array_search($_POST['timify_widget_position'], array('after_post')) !== false &&
			(!isset($_POST['timify_widget_button_label']) || trim($_POST['timify_widget_button_label']) === '')) {

			$error['timify_widget_button_label']= 'Please fill the label of the button!';
		}

		if (count($error) === 0) {

			//Check is exist timify widget id
			if ($timifyWidgetId === false) {
				add_option('timify_widget_id', $_POST['timify_widget_id']);
			}
			else {
				update_option('timify_widget_id', $_POST['timify_widget_id']);
			}

			//Check is exist timify widget language
			if ($timifyWidgetLanguage === false) {
				add_option('timify_widget_language', $_POST['timify_widget_language']);
			}
			else {
				update_option('timify_widget_language', $_POST['timify_widget_language']);
			}

			//Check is exist timify widget position
			if ($timifyWidgetPosition === false) {
				add_option('timify_widget_position', $_POST['timify_widget_position']);
			}
			else {
				update_option('timify_widget_position', $_POST['timify_widget_position']);
			}

			//Check is exist timify widget button label
			if ($timifyWidgetButtonLabel === false) {
				add_option('timify_widget_button_label', $_POST['timify_widget_button_label']);
			}
			else {
				update_option('timify_widget_button_label', $_POST['timify_widget_button_label']);
			}

			$_SESSION['timifyWidgetTmp']['success_message']	= 'The data was saved successfully!';
		}
	}
	else {
		$_POST['timify_widget_id']			= $timifyWidgetId;
		$_POST['timify_widget_language']	= $timifyWidgetLanguage;
		$_POST['timify_widget_position']	= $timifyWidgetPosition;
		$_POST['timify_widget_button_label']= $timifyWidgetButtonLabel;
	}

?>

	<link rel="stylesheet" href="<?php echo plugins_url('timify-widget/css/styles.css'); ?>">

	<div class="timify-widget-settings">
		<img src="<?php echo plugins_url('timify-widget/img/logo.svg'); ?>" class="logo">

		<h1>Timify Widget Settings</h1>

		<div class="note">
			This is configuration page for Timify
		</div>

		<?php
			if (isset($_SESSION['timifyWidgetTmp']['success_message'])) {
				echo '<div class="success-message">' . $_SESSION['timifyWidgetTmp']['success_message'] . '</div>';
				unset($_SESSION['timifyWidgetTmp']['success_message']);
			}
		?>

		<form method="post" action="">

			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row">
							<label for="timify_widget_id">Your Timify ID</label>
						</th>
						<td>
							<input type="text" id="timify_widget_id" name="timify_widget_id"
								   value="<?php if (isset($_POST['timify_widget_id'])) { echo $_POST['timify_widget_id']; } ?>"
								   class="regular-text code">
							<?php if (isset($error['timify_widget_id'])) { ?>
								<div class="error-msg"><?php echo $error['timify_widget_id']; ?></div>
							<?php } ?>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="timify_widget_lang">Choose Language</label>
						</th>
						<td>
							<select name="timify_widget_language">
								<?php foreach ($availableLanguages as $lang => $value) { ?>
									<option value="<?php echo $lang; ?>"
										<?php if (isset($_POST['timify_widget_language']) && $_POST['timify_widget_language'] == $lang) { ?> selected="selected"<?php } ?>>
										<?php echo $value; ?>
									</option>
								<?php } ?>
							</select>
							<?php if (isset($error['timify_widget_language'])) { ?>
								<div class="error-msg"><?php echo $error['timify_widget_language']; ?></div>
							<?php } ?>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="timify_widget_position">Choose Position</label>
						</th>
						<td>
							<select name="timify_widget_position" onchange="timifyWidgetChangePosition(this.value);">
								<option value="left"
									<?php if (isset($_POST['timify_widget_position']) && $_POST['timify_widget_position'] == 'left') { ?> selected="selected"<?php } ?>>
									Left side
								</option>
								<option value="right"
									<?php if (isset($_POST['timify_widget_position']) && $_POST['timify_widget_position'] == 'right') { ?> selected="selected"<?php } ?>>
									Right side
								</option>
								<option value="after_post"
									<?php if (isset($_POST['timify_widget_position']) && $_POST['timify_widget_position'] == 'after_post') { ?> selected="selected"<?php } ?>>
									Below every post
								</option>
							</select>
							<?php if (isset($error['timify_widget_position'])) { ?>
								<div class="error-msg"><?php echo $error['timify_widget_position']; ?></div>
							<?php } ?>
						</td>
					</tr>
					<tr id="timify-widget-label"
						<?php if (!isset($_POST['timify_widget_position']) ||
								  trim($_POST['timify_widget_position']) != 'after_post') { ?>
							style="display: none;"
						<?php } ?>>
						<th scope="row">
							<label for="timify_widget_id">Label of the button</label>
						</th>
						<td>
							<input type="text" id="timify_widget_button_label" name="timify_widget_button_label"
								   value="<?php if (isset($_POST['timify_widget_button_label'])) { echo $_POST['timify_widget_button_label']; } ?>"
								   class="regular-text code">
							<?php if (isset($error['timify_widget_button_label'])) { ?>
								<div class="error-msg"><?php echo $error['timify_widget_button_label']; ?></div>
							<?php } ?>
						</td>
					</tr>
				</tbody>
			</table>

			<button type="submit" name="timify_widget_save">Save changes</button>

		</form>

	</div>

<script type="text/javascript">
function timifyWidgetChangePosition(value) {

	if (value == 'after_post') {
		document.getElementById('timify-widget-label').style.display = 'table-row';
	}
	else {
		document.getElementById('timify-widget-label').style.display = 'none';
	}
}
</script>
