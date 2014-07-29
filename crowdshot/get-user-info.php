<?php
session_start();

require_once('inc/crowdshot-db-apis.php');
require_once('inc/crowdshot-functions.php');

$current_user = FALSE;
$go_to_URL    = '';

$no_error        = TRUE;
$success_message = '';
$error_message   = '';

unset($_SESSION['current_user']);


/**
 * This function validates incoming parameters of this page
 * 
 * @global string $go_to_URL
 * @global boolean $no_error
 * @global string $error_message
 * @param string $redirect_to
 */
function validate_input_parameters($redirect_to) {
	global $go_to_URL;
	global $no_error, $error_message;

	if ($redirect_to) {
		$redirect_to_parsed = parse_url($redirect_to);
		$redirect_to_pathinfo = pathinfo($redirect_to_parsed['path']);

		$ok = ($redirect_to_parsed['scheme'] == ('http' . (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 's' : ''))) && ($redirect_to_parsed['host'] == $_SERVER["SERVER_NAME"]) && ($redirect_to_pathinfo['dirname'] == dirname($_SERVER['PHP_SELF']));

		if ($ok) {
			switch ($redirect_to_pathinfo['basename']) {
				case 'create-event-vt.php' : // create video timeline for an event
				case 'create-edit-event-vt.php' : // create / edit event video timeline
				case 'create-edit-activity.php' : // create / edit activity
				case 'create-edit-activity-vt.php' : // create / edit activity video timeline
					$go_to_URL = $redirect_to;

					break;
				default :
					$no_error      = FALSE;
					$error_message = 'Next step is not defined. Click <a href="' . dirname($_SERVER['PHP_SELF']) . '">here</a> to exit.';

					break; // default
			} // switch ($next_step)
		} else {
			$no_error      = FALSE;
			$error_message = 'Next step is not valid. Click <a href="' . dirname($_SERVER['PHP_SELF']) . '">here</a> to exit.';
		} // if ($ok) else
	} else {
		$no_error      = FALSE;
		$error_message = 'Next step not specified. Click <a href="' . dirname($_SERVER['PHP_SELF']) . '">here</a> to exit.';
	}
} // function validate_input_parameters


/**
 * This function creates a new user record in the database
 * 
 * @global boolean $no_error
 * @global string $error_message
 * @param string $first_name
 * @param string $last_name
 * @param string $user_email
 */
function create_new_user($first_name, $last_name, $user_email) {
	global $no_error, $error_message;

	$current_user = create_user('', $first_name, $last_name, $user_email);

	if ($current_user) {
		$_SESSION['current_user'] = $current_user;
	} else {
		unset($_SESSION['current_user']);

		$no_error      =  FALSE;
		$error_message .= ($error_message ? '<br />' : '') . 'Error creating user.';
	} // if ($current_user) else

	unset($current_user);
} // function create_new_user {


/**
 * This function checks if the user exists (by e-mail address)
 *		If the e-mail address exists, check to see if the user record has the same first and last names
 *		If he / she does not exists, create a new user record
 * 
 * @global boolean $no_error
 * @global string $error_message
 * @param string $first_name
 * @param string $last_name
 * @param string $user_email
 */
function validate_user($first_name, $last_name, $user_email) {
	global $no_error, $error_message;

	if ($first_name && $last_name && $user_email) {
		$current_user = get_user('', '', '', '', $user_email);

		if ($current_user) {
			if (array_key_exists('first_name', $current_user) && array_key_exists('last_name', $current_user) && ($current_user['first_name'] != $first_name || $current_user['last_name'] != $last_name)) {
				unset($_SESSION['current_user']);

				$no_error      =  FALSE;
				$error_message .= ($error_message ? '<br />' : '') . 'Error: E-mail address already exists but do not match entered first name and or last name.';
			} else {
				$_SESSION['current_user'] = $current_user;
			} // if (array_key_exists('first_name', $current_user) && array_key_exists('last_name', $current_user) && ($current_user['first_name'] != $first_name || $current_user['last_name'] != $last_name)) else
		} else { // user record does not exist, create a new user
			create_new_user($first_name, $last_name, $user_email);
		} // if ($current_user) else

		unset($current_user);
	} else {
		unset($_SESSION['current_user']);

		$no_error      = FALSE;
		$error_message = 'Missing mandatory information. Please complete all mandatory fields before continuing.';
	} // if ($first_name && $last_name && $user_email)  else
} // function validate_user($first_name, $last_name, $user_email)


if (isset($_POST['submit_next_step'])) {
	validate_input_parameters((isset($_POST['redirect_to']) ? urldecode($_POST['redirect_to']) : ''));

	// validate user information
	if ($no_error) {
		validate_user($_POST['first_name'], $_POST['last_name'], $_POST['user_email']);
	} // if ($no_error)

	// if everything is good, go to the next page / step
	if ($no_error) {
		header(sprintf('Location: %1$s', $go_to_URL));

		exit;
	} // if ($no_error)
} else {
	if (isset($_GET['redirect_to'])) {
		$go_to_URL = (isset($_GET['redirect_to']) ? $_GET['redirect_to'] : '');

		if ($go_to_URL) {
		} else {
			$no_error      = FALSE;
			$error_message = 'Next step not specified. Click <a href="' . dirname($_SERVER['PHP_SELF']) . '">here</a> to exit.';
		}
	} else {
		$no_error      = FALSE;
		$error_message = 'Next step not defined. Click <a href="' . dirname($_SERVER['PHP_SELF']) . '">here</a> to exit.';
	}
} // if (isset($_POST['next_step'])) else
?>
		<?php output_header('get-user-info-page', 'User Information | CrowdShot', FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE); ?>

		<!-- Display messages section -->
		<section id="create-edit-messages">
			<div class="container">
				<div class="row">
					<div class="col-md-12" id="messages">
						<?php echo ($error_message ? '<div class="alert alert-danger"><p>' . $error_message . '</p></div>' : ''); ?>
						<?php echo ($success_message ? '<div class="alert alert-success"><p>' . $success_message . '</p></div>' : ''); ?>
					</div>
				</div>
			</div>
		</section>

		<?php if ($go_to_URL) : ?>
		<!-- Main create activity page heading section -->
		<section id="create-edit-introduction">
			<div class="container">
				<div class="row">
					<div class="col-md-12">
						<p>THANK YOU for signing up for <span class="logo-text-crowd">Crowd</span><span class="logo-text-shot">Shot</span>. You can promote your individual or team fundraising activity by creating a <span class="logo-text-crowd">Crowd</span><span class="logo-text-shot">Shot</span> movie. Just complete the steps below and we'll have your movie ready for you to share with your donors.</p>
					</div>
				</div>
			</div><!-- .container -->
		</section><!-- #create-edit-introduction -->

		<!-- Main create / edit activity form -->
		<section id="create-edit-form">
			<div class="container">
				<form class="form-horizontal" role="form" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
					<div class="row form-section-header">
						<div class="col-md-12">
							<h2 class="create-edit-form-section-header">Your Information</h2>
						</div>
					</div>
					<div class="row form-section-content">
						<div class="col-md-12">
							<div class="row form-group">
								<label for="inputFirstName" class="col-md-3 control-label required-field">First Name</label>
								<div class="col-md-9">
									<input type="text" name="first_name" class="form-control" id="inputFirstame" placeholder="e.g. John" maxlength="100" required<?php echo (isset($_POST['first_name'])? ' value="' . $_POST['first_name'] . '"' : ''); ?> />
								</div>
							</div>
							<div class="row form-group">
								<label for="inputLastName" class="col-md-3 control-label required-field">Last Name</label>
								<div class="col-md-9">
									<input type="text" name="last_name" class="form-control" id="inputLastName" placeholder="e.g. Doe" maxlength="100" required<?php echo (isset($_POST['last_name'])? ' value="' . $_POST['last_name'] . '"' : ''); ?> />
								</div>
							</div>
							<div class="row form-group">
								<label for="inputEmail" class="col-md-3 control-label required-field">Email</label>
								<div class="col-md-9">
									<input type="email" name="user_email" class="form-control" id="inputEmail" placeholder="e.g. email@domain.com" maxlength="100" required<?php echo (isset($_POST['user_email'])? ' value="' . $_POST['user_email'] . '"' : ''); ?> />
								</div>
							</div>
						</div>
					</div>

					<nav class="navbar" role="navigation">
						<div class="row row-progress">
							<div class="col-md-2 col-md-offset-10">
								<input type="hidden" name="redirect_to" id="inputRedirectTo" value="<?php echo urlencode($go_to_URL); ?>" />
								<button type="submit" class="lead btn btn-primary pull-right" name="submit_next_step" id="btn-next">Next</button>
							</div>
						</div>
					</nav>
				</form>
			</div><!-- .container -->
		</section><!-- #create-edit-form -->
		<?php endif; // if ($go_to_URL) ?>

		<?php output_footer(); ?>