<?php

namespace Shettyanna\DbActivityLogger\Http\Controllers;

use Illuminate\Support\Facades\DB;

class LogController extends Controller
{
    public function index()
    {
        // Fetch logs from the database
        $logs = DB::table('db_activity_log')->orderBy('created_at', 'desc')->get();

        return view('db-activity-logger::logs.index', compact('logs'));
    }
}