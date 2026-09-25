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
use Intervention\Image\Laravel\Facades\Image;

class ProfileController extends Controller
{

    public function index(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            $user->loadMissing(['emp', 'roles', 'departments.dept', 'branch.branch', 'userBranch.branch']);
        }
        return view('components.profile.index', compact('user'));
    }

    public function changepassword(Request $request)
    {
        return view('components.profile.password');
    }

    public function updatePassword(Request $request)
    {
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
        $rules = [
            'file'          => ['nullable', 'file', 'mimes:png,jpeg,jpg,webp', 'max:10240'],
            'cropped_image' => ['nullable', 'string'],
        ];
        $messages = [
            'file.mimes' => 'Supported image formats are PNG, JPEG, JPG, and WebP.',
            'file.max'   => 'The image size cannot exceed 10MB.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            return response()->json([
                'code' => 400,
                'status' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->getMessageBag()->toArray()
            ], 400);
        }

        if (!$request->hasFile('file') && !$request->filled('cropped_image')) {
            return response()->json([
                'code' => 400,
                'status' => false,
                'message' => 'Please select or crop an image file to upload.'
            ], 400);
        }

        try {
            $user = Auth::user();

            $imagePath = Storage::disk('public')->path('users/profile/');
            if (!File::exists($imagePath)) {
                File::makeDirectory($imagePath, 0755, true, true);
            }

            // Cleanly remove old profile picture from disk so no orphan images accumulate
            if (!empty($user->profile)) {
                $cleanOld = ltrim(str_replace(['storage/', 'public/'], '', $user->profile), '/');
                if (Storage::disk('public')->exists($cleanOld)) {
                    Storage::disk('public')->delete($cleanOld);
                }
                $rawStorage = storage_path('app/public/' . $cleanOld);
                if (File::exists($rawStorage) && is_file($rawStorage)) {
                    @unlink($rawStorage);
                }
                $rawPublic = public_path('storage/' . $cleanOld);
                if (File::exists($rawPublic) && is_file($rawPublic) && !is_link($rawPublic)) {
                    @unlink($rawPublic);
                }
            }

            $extension = 'jpg';
            $imageName = 'user_' . $user->id . '_' . time() . '.' . $extension;
            $fullTarget = $imagePath . $imageName;

            if ($request->filled('cropped_image')) {
                // Base64 Data URL upload from frontend crop
                $dataUrl = $request->input('cropped_image');
                if (preg_match('/^data:image\/(\w+);base64,/', $dataUrl, $typeMatches)) {
                    $base64Data = substr($dataUrl, strpos($dataUrl, ',') + 1);
                    $decodedImage = base64_decode($base64Data);
                    if ($decodedImage === false) {
                        return response()->json(['code' => 400, 'status' => false, 'message' => 'Invalid image data payload.'], 400);
                    }
                    file_put_contents($fullTarget, $decodedImage);

                    // Ensure square 256x256 dimensions
                    try {
                        $img = Image::read($fullTarget);
                        if (method_exists($img, 'cover')) {
                            $img->cover(256, 256);
                        } elseif (method_exists($img, 'resize')) {
                            $img->resize(256, 256);
                        }
                        $img->save($fullTarget);
                    } catch (\Throwable) {}
                }
            } elseif ($request->hasFile('file')) {
                // File upload (either pre-cropped blob or raw file)
                $requestImage = $request->file('file');
                $origExt = strtolower($requestImage->getClientOriginalExtension());
                if (in_array($origExt, ['jpg', 'jpeg', 'png', 'webp'])) {
                    $extension = $origExt;
                    $imageName = 'user_' . $user->id . '_' . time() . '.' . $extension;
                    $fullTarget = $imagePath . $imageName;
                }

                // Process with Intervention Image for crisp square cropping
                try {
                    $img = Image::read($requestImage);
                    if (method_exists($img, 'cover')) {
                        $img->cover(256, 256);
                    } elseif (method_exists($img, 'resize')) {
                        $img->resize(256, 256);
                    }
                    $img->save($fullTarget);
                } catch (\Throwable) {
                    // Fallback to direct move
                    $requestImage->move($imagePath, $imageName);
                }
            }

            $user->profile = 'users/profile/' . $imageName;
            $user->save();

            return response()->json([
                'code' => 200,
                'status' => true,
                'message' => 'Profile picture updated successfully.',
                'image_url' => asset('storage/' . $user->profile) . '?v=' . time(),
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'code' => 500,
                'status' => false,
                'message' => 'Failed to update profile picture: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateBasicInfo(Request $request)
    {
        $maxDobDate = \Carbon\Carbon::today()->subYears(18)->format('Y-m-d');

        $rules = [
            'name'        => 'required|string|max:50|unique:users,name,' . $request->user_id . ',id,deleted_at,NULL',
            'number'      => 'required|digits:10|regex:/^[6-9][0-9]{9}/',
            'mail'        => 'required|email|unique:users,email,' . $request->user_id . ',id,deleted_at,NULL',
            'dob'         => 'required|date|before_or_equal:' . $maxDobDate,
            'gender'      => 'required|string',
            'designation' => 'required|string|max:100',
        ];

        $messages = [
            'dob.before_or_equal' => 'Date of birth must be at least 18 years ago (minimum 18 years of age required).',
            'number.regex'        => 'Please enter a valid 10-digit mobile number starting with 6, 7, 8, or 9.',
            'name.unique'         => 'This full name is already taken by another user.',
            'mail.unique'         => 'This email address is already registered to another account.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            return response()->json([
                'code' => 400,
                'status' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->getMessageBag()->toArray()
            ], 400);
        }

        $user = User::with('emp')->where('id', $request->user_id)->first();
        if (!$user) {
            return response()->json(['code' => 404, 'status' => false, 'message' => 'User not found.'], 404);
        }

        $user->name = $request->name;
        $user->mobile = $request->number;
        $user->save();

        if (!$user->emp) {
            $user->emp = new \App\Models\Employees();
            $user->emp->user = $user->id;
            $user->emp->mem_code = $user->code ?: ('DN' . str_pad($user->id, 4, '0', STR_PAD_LEFT));
            $user->emp->joining_dt = now()->toDateString();
            $user->emp->status = $user->status ?: 'Active';
            $user->emp->created_by = Auth::id() ?: 1;
        }

        $user->emp->name = $request->name;
        $user->emp->gender = $request->gender;
        $user->emp->dob = $request->dob;
        $user->emp->designation = $request->designation;
        $user->emp->updated_by = Auth::id();
        $user->emp->save();

        return response()->json(['code' => 200, 'status' => true, 'message' => 'Profile information updated successfully.'], 200);
    }

    public function updateSocialInfo(Request $request)
    {
        $user = User::with('emp')->where('id', $request->user_id)->first();
        if (!$user) {
            return response()->json(['code' => 404, 'status' => false, 'message' => 'User not found.'], 404);
        }

        if (!$user->emp) {
            $user->emp = new \App\Models\Employees();
            $user->emp->user = $user->id;
            $user->emp->mem_code = $user->code ?: ('DN' . str_pad($user->id, 4, '0', STR_PAD_LEFT));
            $user->emp->joining_dt = now()->toDateString();
            $user->emp->status = $user->status ?: 'Active';
            $user->emp->created_by = Auth::id() ?: 1;
        }

        $user->emp->linkedin = $request->linkedin;
        $user->emp->github   = $request->git ?: $request->github;
        $user->emp->fb       = $request->facebook ?: $request->fb;
        $user->emp->insta    = $request->insta ?: $request->instagram;
        $user->emp->twitter  = $request->twitter;
        $user->emp->youtube  = $request->youtube;
        $user->emp->updated_by = Auth::id();
        $user->emp->save();

        return response()->json(['code' => 200, 'status' => true, 'message' => 'Social media profiles updated successfully.'], 200);
    }
}
