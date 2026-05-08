<?php

namespace App\Controllers;

use App\Models\UserModel;

class Auth extends BaseController
{
    public function index()
    {
        // default login page view
        return view('login');
    }

    public function login()
    {
        helper(['form']);

        $data = [];

        // check if form is submitted via POST
        if ($this->request->getMethod() == 'POST') {

            // get input values from login form
            $username = $this->request->getPost('username');
            $password = $this->request->getPost('password');

            $userModel = new UserModel();

            // check if username exists in database
            $user = $userModel
                ->where('username', $username)
                ->first();

            if ($user) {

                // verify password using hashed password from DB
                if (password_verify($password, $user['password'])) {

                    // set session data after successful login
                    session()->set([
                        'userid'   => $user['id'],
                        'fullname' => $user['fullname'],
                        'username' => $user['username']
                    ]);

                    // redirect to home page after login
                    return redirect()->to(base_url('home'));

                } else { // wrong password message
                    $data['msg'] = 'Wrong password!';
                }

            } else {  // username not found message
                $data['msg'] = 'Username not found!';
            }
        }

         // load login view with possible error message
        return view('login', $data);
    }

    public function register()
    {
        helper(['form']);

        $data = [];

        // check if register form is submitted
        if ($this->request->getMethod() == 'POST') {

            // get input values from register form
            $fullname = $this->request->getPost('fullname');
            $username = $this->request->getPost('username');
            
            // hash password for security
            $password = password_hash(
                $this->request->getPost('password'),
                PASSWORD_DEFAULT
            );

            $userModel = new UserModel();

            // check if username already exists
            $check = $userModel
                ->where('username', $username)
                ->first();

            if ($check) {

            // show error if username exists
                $data['msg'] = 'Username already exists!';
                $data['success'] = false;

            } else {
                // save new user to database

                 $userModel->save([
                     'fullname' => $fullname,
                     'username' => $username,
                     'password' => $password
                 ]);

                 // redirect to login with success message
                return redirect()->to('/login')->with('msg', 'Registration successful! Please login.');
}
        }

        // load register view
        return view('register', $data);
    }

    public function logout()
    {
        // destroy session to logout user
        session()->destroy();

        // redirect back to login page
        return redirect()->to('/');
    }
}