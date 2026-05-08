<?php

namespace App\Controllers;

use App\Models\PostModel;
use App\Models\ReactionModel;
use App\Models\CommentModel;

class Home extends BaseController
{
    public function index()
    {
        // load models for posts, reactions, and comments
        $postModel = new PostModel();
        $reactionModel = new ReactionModel();
        $commentModel = new CommentModel();

        // get logged-in user ID from session
        $userid = session()->get('userid');

        if ($this->request->getGet('react') && $this->request->getGet('type')) {

    $postid = $this->request->getGet('react');
    $type   = $this->request->getGet('type');

    $allowed = ['like','love','haha','sad','angry'];

    if (in_array($type, $allowed)) {

        $existing = $reactionModel
            ->where('user_id', $userid)
            ->where('post_id', $postid)
            ->first();

        if ($existing) {
            // update reaction
            $reactionModel->update($existing['id'], [
                'type' => $type
            ]);
        } else {
            // insert reaction
            $reactionModel->save([
                'user_id' => $userid,
                'post_id' => $postid,
                'type' => $type
            ]);
        }
    }

    return redirect()->to('/home');
}

        // ===== CREATE POST & COMMENT HANDLING=====
        // runs when form is submitted (POST request)
        if ($this->request->getMethod(true) === 'POST') {

        //CREATE NEW POST
            if ($this->request->getPost('content')) {

                $postModel->save([
                    'user_id' => $userid,
                    'content' => $this->request->getPost('content')
                ]);
            }

            // ADD COMMENT
            if ($this->request->getPost('comment_btn')) {

                $commentModel->save([
                    'user_id' => $userid,
                    'post_id' => $this->request->getPost('post_id'),
                    'comment' => $this->request->getPost('comment')
                ]);
            }

            // reload page after POST to prevent resubmission
            return redirect()->to('/home'); // 
        }

        //LOAD POSTS
        $data['posts'] = $postModel
            ->select('posts.*, users.fullname')
            ->join('users', 'users.id = posts.user_id')
            ->orderBy('posts.id', 'DESC')
            ->findAll();

        // pass user ID to view (for reactions/comments logic)
        $data['userid'] = $userid;
        $data['reactionModel'] = $reactionModel;
        $data['commentModel'] = $commentModel;

         // load home page view
        return view('home', $data);
    }
}