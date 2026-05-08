<?php

namespace App\Models;

use CodeIgniter\Model;

class PostModel extends Model
{
    // database table for posts
    protected $table = 'posts';
    // primary key of posts table
    protected $primaryKey = 'id';

    // fields allowed for insert/update
    protected $allowedFields = [
        'user_id',
        'content'
    ];
}