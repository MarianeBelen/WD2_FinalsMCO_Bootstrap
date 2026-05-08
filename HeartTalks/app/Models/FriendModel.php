<?php

namespace App\Models;

use CodeIgniter\Model;

class FriendModel extends Model
{
    // table that stores friendships
    protected $table = 'friends';
    // primary key of the friends table
    protected $primaryKey = 'id';
     // fields allowed for insert/update operations
    protected $allowedFields = ['user1', 'user2'];
}