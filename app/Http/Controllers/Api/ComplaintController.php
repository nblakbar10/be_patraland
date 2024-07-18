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
        }
        $complaint = Complaint::where('user_handler_id', Auth::user()->id)->get();

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
            'handling_asset' => 'mimes:png,jpg,jpeg|max:2048',
            'complaint_asset' => 'mimes:png,jpg,jpeg|max:2048',
            'handling_description' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 400);
        }

        $handling_asset = $request->handling_asset; 
        $fileName_progress = time() . '_' . $handling_asset->getClientOriginalName();
            $handling_asset->move(public_path('storage/user_document/'), $fileName_progress);
            $uploadedfile = fopen(public_path('storage/user_document/').$fileName_progress, 'r');
            $bucket_filename_handling = app('firebase.storage')->getBucket()->upload($uploadedfile, ['name' => 'user_document/' . $fileName_progress]);
            unlink(public_path('storage/user_document/').$fileName_progress);
        $file_url_handling_asset = "https://firebasestorage.googleapis.com/v0/b/patraland-4e4af.appspot.com/o/user_document%2F".$fileName_progress."?alt=media";

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
            'handling_asset' => $file_url_handling_asset,
            'complaint_asset' => $file_url_complaint_asset,
            'sparepart' => $request->sparepart,
            'handling_description' => $request->handling_description,
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
            'home_address' => $request->home_address,
            'description' => $request->description,
            'handling_asset' => $file_url_handling_asset,
            'complaint_asset' => $file_url_complaint_asset,
            'sparepart' => $request->sparepart,
            'handling_description' => $request->handling_description,
            'status' => $request->status,
            'user_handler_id' => $request->user_handler_id,
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

    public function complaint_officer_index_by_received()
    {
        // $complaint = Complaint::all();
        $role = User::where('id', Auth::user()->id)->pluck('role')->first();
        if($role != 'customer'){
            return response()->json([
                'stat_code' => 400,
                'message' => 'Anda bukan petugas!',
            ]);
        }
        $complaint = Complaint::where('user_handler_id', Auth::user()->id)->where('status', 'receive')->get();
        

        if (!$complaint) {
            return response()->json([
                'stat_code' => 400,
                'message' => 'Data complaint tidak ditemukan!',
            ]);
        }

        $data = [
            'complaint' => $complaint,
        ];

        return response()->json([
            'stat_code' => 200,
            'message' => 'Berhasil mengambil data complaint',
            'data' => $data,
        ], 201);
    }
    // complaint/officer/receive

}