<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {



        $fields = $request->validate([
            'name' => 'string|required',
            'password' => 'required|confirmed',
            'email' => 'required|email|unique:users,email',

            'user_type' => '',
            'salary' => 'numeric',
            'age' => 'numeric',
            'gender' => 'string',
            'hired_date' => 'string',
            'job_title' => 'string',
            'manager_id' => 'numeric'
        ]);

        $user = User::create([
            'name' => $fields['name'],
            'password' => bcrypt($fields['password']),
            'email' => $fields['email'],
            'user_type' => $fields['user_type'],
            'salary' => $fields['salary'],
            'age' => $fields['age'],
            'gender' => $fields['gender'],
            'hired_date' => $fields['hired_date'],
            'job_title' => $fields['job_title'],
            'manager_id' => $fields['manager_id']
        ]);


        $token = $user->createToken('myapptoken')->plainTextToken;

        return response([
            'user' => $user,
            'token' => $token
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return  $user = User::find($id)->get(['name', 'age', 'salary', 'hired_date', 'job_title']);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        if (RateLimiter::tooManyAttempts('send-message:' . Auth::user()->id, $perMinute = 5)) {
            return 'Too many attempts!';
        }

        $user = User::find($id);
        $user->update($request->all());

        return $user;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return User::destroy($id);
    }

    public function employeeManagers($id)
    {
        // Log::info(User::getChain($id));  

        return response([
            'result' => User::getChain($id)
        ]);

        // if (User::isNormalEmployee($id)) {
        //     Log::info($id . ' He is normal');
        // }


        // if (User::isFounder($id)) {
        //     Log::info($id . ' is a founder');
        // }
    }

    public function employeeManagersSalary($id)
    {
        Log::info('id in controller: ' . $id);
        return response([
            User::getManagersSalary($id)
        ]);
    }

    public function logout()
    {
        Auth::user()->tokens()->delete();

        return ['message' => 'logged ot'];
    }

    public function searchEmployee($q)
    {
        Log::info(json_encode($q));

        return User::where('name', 'like', '%' . $q . '%')->get();
    }
    
    
    public function employeesExportCsv(Request $request)
    {
        $fileName = 'employees.csv';
        $employees = User::get(['name', 'age', 'salary', 'gender', 'hired_date', 'job_title']);

        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $columns = array('name', 'age', 'salary', 'gender', 'hired_date', 'job_title');

        $callback = function () use ($employees, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($employees as $employee) {
                $row['name']  = $employee->title;
                $row['age']    = $employee->age;
                $row['salary']    = $employee->salary;
                $row['gender']  = $employee->gender;
                $row['hired_date']  = $employee->hired_date;
                $row['job_title']  = $employee->job_title;

                fputcsv($file, array($row['name'], $row['age'], $row['salary'], $row['gender'], $row['hired_date'], $row['job_title']));
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
