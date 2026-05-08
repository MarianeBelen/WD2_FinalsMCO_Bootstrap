<?php

namespace App\Models;

use CodeIgniter\Model;

class FriendRequestModel extends Model
{
     // table that stores pending friend requests
    protected $table = 'friend_requests';
    // primary key of the table
    protected $primaryKey = 'id';

    // fields allowed for insert/update operations
    protected $allowedFields = [
        'sender_id',  // user who sent the request
        'receiver_id' // user who will receive the request
    ];
}