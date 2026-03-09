<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Wallet;
use Illuminate\Support\Str;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        // Return students for the logged-in parent/admin
        if ($request->user()->role === 'admin') {
            return Student::with('wallet')->get();
        }
        
        return Student::where('user_id', $request->user()->id)->with('wallet')->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'birth_date' => 'nullable|date',
        ]);

        $student = Student::create([
            'user_id' => $request->user()->id,
            'name' => $request->name,
            'birth_date' => $request->birth_date,
            'qr_code' => Str::upper(Str::random(10)),
            'is_active' => true,
        ]);

        Wallet::create([
            'student_id' => $student->id,
            'balance' => 0.00
        ]);

        return response()->json([
            'message' => 'Student registered successfully',
            'student' => $student->load('wallet')
        ], 201);
    }

    public function show($id)
    {
        $student = Student::with('wallet', 'parentalControl')->findOrFail($id);
        
        return response()->json($student);
    }
}
