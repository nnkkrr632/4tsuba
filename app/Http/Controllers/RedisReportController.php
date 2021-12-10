<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Auth;
//Carbonを使用する
use Carbon\Carbon;
use App\RedisModels\RedisReport;

class RedisReportController extends Controller
{
    /**
     * @param Request $request
     * @return array
     */
    public function returnMonthlyOverview(Request $request)
    {
        $redis_report = new RedisReport();
        $month_overview = $redis_report->returnMonthlyOverview($request->year_month);
        return $month_overview;
    }
}
