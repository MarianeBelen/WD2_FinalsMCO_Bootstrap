<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\FriendModel;
use App\Models\FriendRequestModel;

class Profile extends BaseController
{
    public function index()
    {
        // check if user is logged in
        if (!session()->get('userid')) {
            return redirect()->to('/');
        }

        // get logged-in user ID
        $userid = session()->get('userid');

        // load models
        $friendModel = new FriendModel();
        $requestModel = new FriendRequestModel();
        $userModel = new UserModel();

        // ADD FRIEND
        if ($this->request->getGet('add')) {

            $target = $this->request->getGet('add');

             // prevent sending request to self
            if ($target != $userid) {

                // check if request already exists
                $exists = $requestModel
                    ->where('sender_id', $userid)
                    ->where('receiver_id', $target)
                    ->first();

                // insert only if not existing yet
                if (!$exists) {
                    $requestModel->save([
                        'sender_id' => $userid,
                        'receiver_id' => $target
                    ]);
                }
            }

            // reload profile page
            return redirect()->to('/profile');
        }

        // ACCEPT REQUEST
        if ($this->request->getGet('accept')) {

            $rid = $this->request->getGet('accept');

            // get request data
            $req = $requestModel->find($rid);

            if ($req) {
                // move request to friends table
                $friendModel->save([
                    'user1' => $req['sender_id'],
                    'user2' => $req['receiver_id']
                ]);

                // delete friend request after accepting
                $requestModel->delete($rid);
            }

            return redirect()->to('/profile');
        }

        // REJECT REQUEST
        if ($this->request->getGet('reject')) {

            $requestModel->delete($this->request->getGet('reject'));

            return redirect()->to('/profile');
        }

        // REMOVE FRIEND
        if ($this->request->getGet('remove')) {

            $friendModel->delete($this->request->getGet('remove'));

            return redirect()->to('/profile');
        }

        // LOAD DATA FOR VIEW

        // count total friends
        $friendCount = $friendModel
            ->where('user1', $userid)
            ->orWhere('user2', $userid)
            ->countAllResults();

        // list of all users (for "people you may know")
        $users = $userModel
            ->where('id !=', $userid)
            ->findAll();

            // incoming friend requests
        $requests = $requestModel
            ->select('friend_requests.*, users.fullname')
            ->join('users', 'friend_requests.sender_id = users.id')
            ->where('receiver_id', $userid)
            ->findAll();

            // friends list
        $friends = $friendModel
            ->select('friends.id, users.fullname')
            ->join('users', "(
                (users.id = friends.user1 AND friends.user2 = $userid)
                OR
                (users.id = friends.user2 AND friends.user1 = $userid)
            )", 'inner', false)
            ->findAll();

        // pass data to view
        return view('profile', [
            'friendCount' => $friendCount,
            'users' => $users,
            'requests' => $requests,
            'friends' => $friends
        ]);
    }
}