<?php

$isloggedin = function ($app) {
	return function () use ($app) {
		if (!isset($_SESSION['user'])) {
			$_SESSION['urlRedirect'] = $app->request()->getPathInfo();
			$app->flash('error', 'Login required');
			$app->redirect('/login');
		} else {
			$app->active_user = ORM::for_table('users')->where('email', $_SESSION['user'])->find_one();
		}
	};
};


$app = new \Slim\Slim(array(
	'templates.path' => '../templates',
));
$app->add(new \Slim\Middleware\SessionCookie(array('secret' => SESSION_SECRET)));


$app->hook('slim.before.dispatch', function () use ($app) {
    $user = null;		
    if (isset($_SESSION['user'])) {
       $user = $_SESSION['user'];
    }
    $app->view()->setData('user', $user);

	// if API request, don't inject header 
	if (!strpos($app->request()->getPathInfo(), "api") !== false) {
		$app->render('header.php');
	}
});

$app->hook('slim.after.dispatch', function () use ($app) {
	// if API request, don't inject header 
	if (!strpos($app->request()->getPathInfo(), "api") !== false) {
		$app->render('footer.php');
	}
});



