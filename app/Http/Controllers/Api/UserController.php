<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller {
    /**
    * Display a listing of the resource.
    *
    * @return \Illuminate\Http\Response
    */

    public function index() {
        
        $users = User::all(); 
        if (count( $users )>0){
            //user exist
            $response = [
                'message' =>count( $users ).'user found', 
                'status' =>1,
                'data'=> $users,
            ];
            return response()->json( $response ,200);
        } else {
            //doesn't exist
            $response = [
                'message' =>count( $users ).'user found', 
                'status' =>0,
                
            ];
        };
        return response()->json( $response ,200);

    }

    /**
    * Show the form for creating a new resource.
    *
    * @return \Illuminate\Http\Response
    */

    // for signup

    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'name' => [ 'required' ],
            'email' => [ 'required', 'email' ],
            'password' => [ 'required', 'min:8' ],
            'user_type' => [ 'required', 'in:customer,admin' ], // Validate user_type
        ]);
    
        // Hash the password before saving
        $validatedData['password'] = bcrypt($validatedData['password']);
    
        $user = User::create($validatedData);
        $token = $user->createToken('auth_token')->accessToken;
    
        return response()->json([
            'token' => $token,
            'user' => $user,
            'message' => 'User created successfully',
            'status' => 1
        ]);
    }
    

    // for login

    public function login(Request $request) {
        $validatedData = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'min:8'],
            

        ]);
    
        $user = User::where('email', $validatedData['email'])->first();
    
        if (!$user || !Hash::check($validatedData['password'], $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }
    
        $token = $user->createToken("auth_token")->accessToken;
        return response()->json([
            'token' => $token,
            'user' => $user,
            'message' => 'Logged in successfully',
            'status' => 1,
            'user_type' => $user->user_type,
            
        ]);
    }
    





    // to get user data 

    public function getUser($id){
        $user = User::find($id);
        if(is_null($user)){
            return response()->json(
                [
                'user' => null,
                'message' => 'User not found',
                'status' => 0
                ]
                );

        }else{
            return response()->json(
                [
                'user' => $user,
                'message' => 'User found',
                'status' => 1
                ]
                );
        }
    }



    /**
    * Store a newly created resource in storage.
    *
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\Response
    */

    public function store( Request $request ) {

        $validator = Validator::make( $request->all(), [
            'name' => [ 'required' ],
            'email' => [ 'required', 'email', 'unique:users,email' ],
            'password' => [ 'required', 'min:8' ]
        ] );
        if ( $validator->fails() ) {
            return response()->json( $validator->messages(), 400 );
        } else {
            $data = [
                'name' => $request->name,
                'email'=> $request->email,
                'password' => bcrypt( $request->password ),
            ];
            DB::beginTransaction();
            try {
                $user = User::create( $data );
                DB::commit();
            } catch ( \Exception $e ) {
                DB::rollBack();
                p( $e->getMessage() );
                $user = null;
            }
            if ($user != null){
                //okay
                return response()->json([
                    'message'=>'User Registered Successfully'
                ],200);
            } else {
                return response()->json([
                    'message'=> ' Internal Server Error'
                ]);
            }
        }

    }

    /**
    * Display the specified resource.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */

    public function show( $id ) {
        //find always find default primary key i.e id
        $user = User::find( $id );
        if(is_null($user)){
            $response=[
                'message'=> 'User not found',
                'status' => '0',
            ];
        }else{
            $response=[
                'message'=> 'User found',
                'status' => '1',
                'data' => $user
            ];
        }
        return response()->json( $response,200 );

    }

    /**
    * Show the form for editing the specified resource.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */

    public function edit( $id ) {
        //
    }

    /**
    * Update the specified resource in storage.
    *
    * @param  \Illuminate\Http\Request  $request
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */

    public function update( Request $request, $id ) {
    
        $user = User::find( $id ); 
        if(is_null($user)){
            return response()->json(
                [
                    'message'=> 'User does not exists'
                ],404
            );
        }else{
            DB::beginTransaction();
            try{
                $user->name=$request['name'];
                $user->email=$request['email'];
                $user->password=$request['password'];
                $user->save();
                DB::commit();
            }
            catch (\Exception $err) {
                DB::rollBack();
                $user = null;
            }
            if(is_null($user)){
                return response()->json(
                    [
                        'message'=> 'Internal Server Error',
                        'status'=> '0',
                        'error_msg' => $err->getMessage()
                    ],500
                );
            }else{
                return response()->json(
                    [
                        'message'=> 'Data Updated Successfully',
                        'status'=> '1',
                    ],200
                );
            }

        }
        
    }

    /**
    * Remove the specified resource from storage.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */

    public function destroy( $id ) {
        $user = User::find( $id );
        if(is_null($user)){
            $response = [
                'message' => "User doesn't exist",
                'status' => 0
            ];
            $respCode = 400;
        }else{
            DB::beginTransaction();
            try{
                $user->delete();
                DB::commit();   
                $response=[
                    'message' => "User Deleted Succesfully",
                    "status"=> 1
                ];
                $respCode = 200;
            }catch ( \Exception $err ) {
                DB::rollBack();
                $response=[
                    'message' => "Internally Server Error",
                    "status"=> 0
                ];
                $respCode = 500;

            }
        }
        return response()->json( $response, $respCode );    
    }
}
