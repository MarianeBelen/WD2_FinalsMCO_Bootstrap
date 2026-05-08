<?php

namespace App\Models;

use CodeIgniter\Model;

class CommentModel extends Model
{
    // database table used for comments
    protected $table = 'comments';
    // primary key of the table
    protected $primaryKey = 'id';

    // fields allowed to be inserted/updated using Model::save()
    protected $allowedFields = [
        'user_id', // ID of the user who commented
        'post_id', // ID of the post being commented on
        'comment' // actual comment text
    ];
}