<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// default page (opens login page first)
$routes->get('/', 'Auth::login');

// login route
// accepts both GET (open page) and POST (submit form)
$routes->match(['get', 'post'], 'login', 'Auth::login');

// register route
// accepts both GET and POST requests
$routes->match(['get', 'post'], 'register', 'Auth::register');

// logout route
// destroys session and logs user out
$routes->get('logout', 'Auth::logout');

// home/feed page
// handles viewing posts and creating posts/comments
$routes->match(['get', 'post'], 'home', 'Home::index');

// profile page
// handles friend requests, friend list, etc.
$routes->match(['get', 'post'], 'profile', 'Profile::index');
?>
