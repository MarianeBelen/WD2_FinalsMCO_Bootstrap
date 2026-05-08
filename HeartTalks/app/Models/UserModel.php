<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    // database table for users
    protected $table = 'users';
    // primary key of users table
    protected $primaryKey = 'id';

    // fields allowed for insert/update operations
    protected $allowedFields = [
        'fullname',
        'username',
        'password'
    ];
}