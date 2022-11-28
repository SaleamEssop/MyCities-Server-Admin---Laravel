<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /*public function __construct() {

    }*/

    public function register(Request $request) {

        $postData = $request->post();
        if(empty($postData['action'])) // action = insert, update
            return response()->json(['status' => false, 'code' => 400, 'msg' => 'Oops, action field is required!']);

        $action = $postData['action'];
        $requiredFields = ['full_name', 'email', 'phone_number'];
        if($action == 'insert')
            $requiredFields[] = 'password';
        elseif($action == 'update')
            $requiredFields[] = 'id';
        else
            return response()->json(['status' => false, 'code' => 400, 'msg' => 'Oops, wrong action provided!']);

        $validated = validateData($requiredFields, $postData);
        if(!$validated['status'])
            return response()->json(['status' => false, 'code' => 400, 'msg' => $validated['error']]);

        // Check if user with this email already exists
        if($postData['action'] == 'insert')
            $alreadyExists = User::where('email', $postData['email'])->get();
        elseif($postData['action'] == 'update')
            $alreadyExists = User::where('email', $postData['email'])->where('id','<>', $postData['id'])->get();

        if(count($alreadyExists) !== 0)
            return response()->json(['status' => false, 'code' => 400, 'msg' => 'Oops, user with this email already exists!']);

        $userArr = array(
            'name' => $postData['full_name'],
            'email' => $postData['email'],
            'contact_number' => $postData['phone_number']
        );

        if($action == 'insert') {
            $userArr['password'] = bcrypt($postData['password']);
            $response = User::create($userArr);
        }
        elseif ($action == 'update')
            $response = User::where('id', $postData['id'])->update($userArr);

        if(!$response)
            return response()->json(['status' => false, 'code' => 400, 'msg' => 'Oops, something went wrong!']);

        return response()->json(['status' => true, 'code' => 200, 'msg' => 'Action successfully!']);
    }

    /* *
     * * * User login function
     * */

    public function login(Request $request) {

        $postData = $request->post();
        $requiredFields = ['email', 'password'];
        $validated = validateData($requiredFields, $postData);
        if(!$validated['status'])
            return response()->json(['status' => false, 'code' => 400, 'msg' => $validated['error']]);

        $user = User::where(['email' => $postData['email']])->get();
        if(count($user) !== 1)
            return response()->json(['status' => false, 'code' => 400, 'msg' => 'Oops, username or password is wrong!']);

        // Now check the password hash
        $dbPasswordHash = $user[0]->password;
        $userPassword = $postData['password'];
        if(!Hash::check($userPassword, $dbPasswordHash))
            return response()->json(['status' => false, 'code' => 400, 'msg' => 'Oops, username or password is wrong!']);

        $responseData = array(
            'id' => $user[0]->id,
            'name' => $user[0]->name,
            'email' => $user[0]->email,
            'contact_number' => $user[0]->contact_number
        );
        return response()->json(['status' => true, 'code' => 200, 'msg' => 'User logged in successfully!', 'data' => $responseData]);
    }
}
