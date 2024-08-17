<?php

namespace App\Http\Controllers;
use App\Models\Shipment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\User;

use Illuminate\Http\Request;

class ShipmentController extends Controller {

    public function updateStatus(Request $request, $id) {
        \Log::info('Request Data:', $request->all());
        \Log::info('Updating status for shipment ID: ' . $id);
        \Log::info('New status: ' . $request->status);
    
        // Validate the incoming data
        $shipment = Shipment::find($id);
    
        // Check if the shipment exists
        if (!$shipment) {
            return response()->json(['error' => 'Shipment not found'], 404);
        }
    
        // Update the status
        $shipment->status = $request->status;
        $shipment->save();
    
        return response()->json(['message' => 'Status updated successfully'], 200);
    }
    

    public function getAllShipments() {
        // Ensure only admin users can access this method
        $user = Auth::user();

        if ( !$user || $user->user_type !== 'admin' ) {
            return response()->json( [ 'message' => 'Unauthorized' ], 401 );
        }

        // Fetch all shipments
        $shipments = Shipment::with( 'user' )->get();

        if ( $shipments->count() > 0 ) {
            $response = [
                'message' => $shipments->count() . ' shipments found',
                'status' => 1,
                'data' => $shipments,
            ];
        } else {
            $response = [
                'message' => 'No shipments found',
                'status' => 0,
            ];
        }

        // Log the admin request
        Log::info( 'Admin fetched all shipments', [ 'admin' => $user->name, 'shipments' => $shipments ] );

        return response()->json( $response, 200 );
    }

    public function index() {
        // Get the currently authenticated user
        $user = Auth::user();

        // Check if the user is authenticated
        if ( !$user ) {
            return response()->json( [ 'message' => 'Unauthorized' ], 401 );
        }

        // Fetch shipments for the authenticated user
        $shipments = $user->shipments;

        $data = [
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
            ],
            'shipments' => $shipments,
        ];

        Log::info( 'User Shipments:', [ 'user' => $user->name, 'shipments' => $shipments ] );

        // Return the shipments as JSON
        return response()->json( $data, 200 );
    }

    public function store( Request $request ) {
        // Validate incoming data
        $validatedData = $request->validate( [
            'weight' => 'required',
            'size' => 'required',
            'pickup_location' => 'required',
            'shipment_date' => 'required|date_format:d-m-Y',
            'shipment_time' => 'required|date_format:h:i A',
            'additional_info' => 'required',

        ] );

        // Convert the date to 'Y-m-d' format for MySQL
        $validatedData[ 'shipment_date' ] = Carbon::createFromFormat( 'd-m-Y', $validatedData[ 'shipment_date' ] )->format( 'Y-m-d' );

        // Convert the time to 'H:i:s' format for MySQL
        $validatedData[ 'shipment_time' ] = Carbon::createFromFormat( 'h:i A', $validatedData[ 'shipment_time' ] )->format( 'H:i:s' );

        // Automatically set user_id from the authenticated user
        $validatedData[ 'user_id' ] = auth()->id();

        // Set default status value
        $validatedData[ 'status' ] = 'Pending';

        // Create the shipment record
        $shipment = Shipment::create( $validatedData );

        // Return a success response with the shipment data
        return response()->json( $shipment, 201 );
    }

   
}

