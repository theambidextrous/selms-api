<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Validator;
use Storage;
use Config;
use Carbon\Carbon;

/** Charts */
use App\Charts\StudentChart;

class IndexController extends Controller
{
    protected function make_chart($data)
    {
        $chart = new StudentChart;
        $chart->labels($data[0]);
        $chart->dataset('Home page', 'line', $data[1]);
        $chart->height(250);
        $chart->width(400);
        $chart->displayLegend(true);
       return $chart;
    }
    public function index()
    {
        return redirect('api/documentation');
        $chart_data = [['Gate', 'Door', 'Window'], [0,989,1078]];
        return view('index', ['chart' => $this->make_chart($chart_data)]);
    }
}
