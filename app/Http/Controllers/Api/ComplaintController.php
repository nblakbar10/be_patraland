<?php

namespace App\Http\Controllers\Api;

use App\Models\Complaint;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

use Kreait\Firebase\Auth as FirebaseAuth;
use Kreait\Firebase\Auth\SignInResult\SignInResult;
use Kreait\Firebase\Exception\FirebaseException;
use Google\Cloud\Firestore\FirestoreClient;

class ComplaintController extends Controller
{
    public function complaint_index()
    {
        // $complaint = Complaint::all();
        $check_role = User::where('id', Auth::user()->id)->first();

        if($check_role->role == 'customer'){
            $complaint = Complaint::where('user_id', Auth::user()->id)->get();
        }else{
            $complaint = Complaint::where('user_handler_id', Auth::user()->id)->where('status', 'done')->get();
        }
        
        if ($complaint->isEmpty()) {
            return response()->json([
                'stat_code' => 400,
                'message' => 'Data complaint tidak ditemukan!',
            ]);
        }

        return response()->json([
            'stat_code' => 200,
            'message' => 'Berhasil mengambil data complaint',
            'data' => $complaint,
        ], 201);
    }

    public function complaint_submit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'home_address' => 'required',
            'description' => 'required',
            'complaint_asset' => 'mimes:png,jpg,jpeg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 400);
        }

        // $handling_asset = $request->handling_asset; 
        // $fileName_progress = time() . '_' . $handling_asset->getClientOriginalName();
        //     $handling_asset->move(public_path('storage/user_document/'), $fileName_progress);
        //     $uploadedfile = fopen(public_path('storage/user_document/').$fileName_progress, 'r');
        //     $bucket_filename_handling = app('firebase.storage')->getBucket()->upload($uploadedfile, ['name' => 'user_document/' . $fileName_progress]);
        //     unlink(public_path('storage/user_document/').$fileName_progress);
        // $file_url_handling_asset = "https://firebasestorage.googleapis.com/v0/b/patraland-4e4af.appspot.com/o/user_document%2F".$fileName_progress."?alt=media";

        $complaint_asset = $request->complaint_asset; 
        $fileName_progress = time() . '_' . $complaint_asset->getClientOriginalName();
            $complaint_asset->move(public_path('storage/user_document/'), $fileName_progress);
            $uploadedfile = fopen(public_path('storage/user_document/').$fileName_progress, 'r');
            $bucket_filename_complaint = app('firebase.storage')->getBucket()->upload($uploadedfile, ['name' => 'user_document/' . $fileName_progress]);
            unlink(public_path('storage/user_document/').$fileName_progress);
        $file_url_complaint_asset = "https://firebasestorage.googleapis.com/v0/b/patraland-4e4af.appspot.com/o/user_document%2F".$fileName_progress."?alt=media";

        $complaint = Complaint::create([
            'user_id' => Auth::user()->id,
            'home_address' => $request->home_address,
            'description' => $request->description,
            // 'handling_asset' => $file_url_handling_asset,
            'complaint_asset' => $file_url_complaint_asset,
            // 'sparepart' => $request->sparepart,
            // 'handling_description' => $request->handling_description,
            'status' => 'receive',
            'user_handler_id' => 0,
        ]);
        
        return response()->json([
            'stat_code' => 200,
            'message' => 'Berhasil submit keluhan',
            'data' => $complaint,
        ]);
    }

    public function get_customer_complaint(Request $request)
    {
        // $complaint = Complaint::where('user_id', Auth::user()->id)->first();
        $complaint = Complaint::where('user_id', Auth::user()->id)->where('status', 'ongoing')->first();
        
        if (!$complaint) {
            return response()->json([
                'stat_code' => 400,
                'message' => 'Tidak ada data complaint!',
            ]);
        }
        return response()->json([
            'stat_code' => 200,
            'message' => 'Berhasil get data complaint',
            'data' => $complaint,
        ]);
    }

    public function complaint_edit(Request $request, $id)
    {
        $complaint = Complaint::find($id);
        if (!$complaint) {
            return response()->json([
                'stat_code' => 404,
                'message' => 'ID Complaint tidak ditemukan!',
            ]);
        }

        $complaint -> update($request->all());
        
        if ($request->hasFile('handling_asset')) {
            $file_progress = $request->handling_asset;
                $fileName_progress = time() . '_' . $file_progress->getClientOriginalName();
                $file_progress->move(public_path('storage/user_document/'), $fileName_progress);
                $uploadedfile = fopen(public_path('storage/user_document/').$fileName_progress, 'r');
                $bucket_filename_handling = app('firebase.storage')->getBucket()->upload($uploadedfile, ['name' => 'user_document/' . $fileName_progress]);
            unlink(public_path('storage/user_document/').$fileName_progress);
            $file_url_handling_asset = "https://firebasestorage.googleapis.com/v0/b/patraland-4e4af.appspot.com/o/user_document%2F".$fileName_progress."?alt=media";
        } else {
            $file_url_handling_asset = null;
        }

        if ($request->hasFile('complaint_asset')) {
            $file_progress = $request->complaint_asset;
                $fileName_progress = time() . '_' . $file_progress->getClientOriginalName();
                $file_progress->move(public_path('storage/user_document/'), $fileName_progress);
                $uploadedfile = fopen(public_path('storage/user_document/').$fileName_progress, 'r');
                $bucket_filename_complaint = app('firebase.storage')->getBucket()->upload($uploadedfile, ['name' => 'user_document/' . $fileName_progress]);
            unlink(public_path('storage/user_document/').$fileName_progress);
            $file_url_complaint_asset = "https://firebasestorage.googleapis.com/v0/b/patraland-4e4af.appspot.com/o/user_document%2F".$fileName_progress."?alt=media";
        } else {
            $file_url_complaint_asset = null;
        }

        $complaint_data = [
            // 'home_address' => $request->home_address,
            // 'description' => $request->description,
            'handling_asset' => $file_url_handling_asset,
            // 'complaint_asset' => $file_url_complaint_asset,
            'sparepart' => $request->sparepart,
            'handling_description' => $request->handling_description,
            'status' => $request->status,
            'user_handler_id' => Auth::user()->id,
        ];

        $complaint->update($complaint_data);

        return response()->json([
            'stat_code' => 200,
            'message' => 'edit complaint success',
            'data' => $complaint
        ]);
    }

    public function get_complaint_by_id(string $id){
        $complaint = Complaint::find($id);
        if (!$complaint) {
            return response()->json([
                'stat_code' => 404,
                'message' => 'ID Complaint tidak ditemukan!',
            ]);
        }

        return response()->json([
            'stat_code' => 200,
            'message' => 'ID complaint ditemukan!',
            'data' => $complaint
        ]);
    }

    public function complaint_officer_index_by_status(string $status)
    {
        // $complaint = Complaint::all();
        $user = User::where('id', Auth::user()->id)->first();
        if($user->role == 'customer'){
            return response()->json([
                'stat_code' => 400,
                'message' => 'Anda bukan petugas!',
            ]);
        }
        if($status == 'receive'){
            $complaint = Complaint::where('user_handler_id', '0')->where('status', $status)->get();
        } else {
            $complaint = Complaint::where('user_handler_id', Auth::user()->id)->where('status', $status)->get();
        }
        
        

        if (!$complaint) {
            return response()->json([
                'stat_code' => 400,
                'message' => 'Data complaint tidak ditemukan!',
            ]);
        }

        return response()->json([
            'stat_code' => 200,
            'message' => 'Berhasil mengambil data complaint',
            'data' => $complaint,
        ], 201);
    }
    
    
    public function complaint_officer_accept_complaint(Request $request, string $id)
    {
        // $complaint = Complaint::all();
        $complaint = Complaint::find($id);

        $complaint_data = [
            'status' => $request->status,
            'user_handler_id' => Auth::user()->id,
        ];

        $complaint->update($complaint_data);

        return response()->json([
            'stat_code' => 200,
            'message' => 'Berhasil menerima complaint',
        ], 201);
    }

}
    // public function sendPushNotification(){

    //     $credentialsFilePath = storage_path("app/firebase/patraland.json");
    //     $client = new \Google_Client();
    //     $client->setAuthConfig($credentialsFilePath);
    //     $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
    //     $apiurl = 'https://fcm.googleapis.com/v1/projects/patraland-4e4af/messages:send';
    //     $client->refreshTokenWithAssertion();
    //     $token = $client->getAccessToken();
    //     $access_token = $token['access_token'];
        
    //     $headers = [
    //         "Authorization: Bearer $access_token",
    //         'Content-Type: application/json'
    //     ];
    //     $test_data = [
    //         "title" => "TITLE_HERE",
    //         "description" => "DESCRIPTION_HERE",
    //     ]; 
        
    //     $data['data'] =  $test_data;
    
    //     $data['token'] = $user['fcm_token']; // Retrive fcm_token from users table
    
    //     $payload['message'] = $data;
    //     $payload = json_encode($payload);
    //     $ch = curl_init();
    //     curl_setopt($ch, CURLOPT_URL, $apiurl);
    //     curl_setopt($ch, CURLOPT_POST, true);
    //     curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //     curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    //     curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    //     curl_exec($ch);
    //     $res = curl_close($ch);
    //     if($res){
    //         return response()->json([
    //                 'message' => 'Notification has been Sent'
    //         ]);
    //     }
    // }
