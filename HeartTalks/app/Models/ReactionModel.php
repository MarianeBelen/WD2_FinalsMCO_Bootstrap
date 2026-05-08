<?php

namespace App\Models;

use CodeIgniter\Model;

class ReactionModel extends Model
{
    // table that stores post reactions 
    protected $table = 'reactions';
    // primary key of the reactions table
    protected $primaryKey = 'id';

    // fields allowed for insert/update
    protected $allowedFields = [
        'user_id',
        'post_id',
        'type'
    ];
}