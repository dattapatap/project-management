<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Image;

class ProfileController extends Controller
{

    public function index(Request $request){
        return view('components.profile.index');
    }

    public function changepassword(Request $request){
        return view('components.profile.password');
    }

    public function updatePassword( Request $request){
        $request->validate([
            'old_password' => 'required',
            'new_password' => 'required|string|min:8|same:confirm_password',
            'confirm_password' => 'required',
        ]);

        if (!Hash::check($request->post('old_password'), auth()->user()->password)) {
            return back()->withInput()->with('error', "Invalid old password, old password dos't match");
        }
        $user = Auth::user();
        $user->password = Hash::make($request->new_password);
        $user->save();
        return back()->with('success', 'Password successfully changed!');
   }



    public function profileimg(Request $request)
    {
             $rules = array(
                'file' => ['required','mimes:png,jpeg,jpg','max:2000'],
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response::json(array('status' => 400,'errors' => $validator->getMessageBag()->toArray()), 400);
                exit();
            } else {
                try {

                    $user=Auth::user();

                    if($request->hasFile('file')) {

                        if(!is_null($user->profile) && Storage::disk('public')->exists($user->profile)){
                            Storage::disk('public')->delete($user->profile);
                        }

                        $request_image = $request->file('file');
                        $image = Image::make($request_image);

                        $image_path = Storage::disk('public')->path('users/profile/');

                        if (!File::exists($image_path)) {
                            File::makeDirectory($image_path, 0777, true, true);
                        }

                        $image_name = time().'.'.$request_image->getClientOriginalExtension();
                        $image->resize(128, 128, function($constraint) {
                            // $constraint->aspectRatio();
                        });

                        $image->save($image_path.$image_name);

                        $user->profile = 'users/profile/'.$image_name;
                        $user->save();
                        return response()->json(['code'=>200, 'status'=>'true',  'message'=> "Profile Updated" ], 200);



                    }
                }catch (\Exception $e) {
                        echo $e->getMessage();
                    }
            }
    }

    public function updateBasicInfo(Request $request){
        $rules = array(
            'name' => 'required|string|max:50|unique:users,name,'.$request->user_id.',id,deleted_at,NULL',
            'number' => 'required|digits:10|regex:/^[6-9][0-9]{9}/',
            'mail' => "required|unique:users,email,".$request->user_id.",id,deleted_at,NULL",
            'dob' => 'required|date',
            'gender' => 'required|string',
            'designation' => 'required|string',

        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response::json(array('status' => 400,'errors' => $validator->getMessageBag()->toArray()), 400);
        }else{
                $user = User::with('emp')->where('id', $request->user_id)->first();
                $user->name = $request->name;
                $user->mobile  = $request->number;
                $user->emp->name = $request->name;
                $user->emp->gender = $request->gender;
                $user->emp->dob =   $request->dob;
                $user->emp->designation =  $request->designation;
                $user->push();
                return response()->json(['code'=>200,'status'=>true, 'message'=> 'Information updated'], 200);
       }
    }


    public function updateSocialInfo(Request $request){
        $user = User::with('emp')->where('id', $request->user_id)->first();
        $user->emp->fb           =   $request->facebook;
        $user->emp->insta	     =   $request->insta;
        $user->emp->linkedin     =   $request->linkedin;
        $user->emp->github	     =   $request->git;
        $user->push();
        return response()->json(['code'=>200,'status'=>true, 'message'=> 'Social information updated'], 200);
    }
}
