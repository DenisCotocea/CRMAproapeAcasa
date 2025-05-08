<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\UsersExport;
use App\Exports\LeadsExport;
use App\Exports\PropertiesExport;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    public function index()
    {
        return view('exporters.index');
    }

    public function exportUsers()
    {
        return Excel::download(new UsersExport, 'users.xlsx');
    }

    public function exportLeads()
    {
        return Excel::download(new LeadsExport, 'leads.xlsx');
    }

    public function exportProperties()
    {
        return Excel::download(new PropertiesExport, 'properties.xlsx');
    }
}
