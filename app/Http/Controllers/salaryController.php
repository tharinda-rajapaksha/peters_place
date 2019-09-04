<?php

namespace App\Http\Controllers;

use App\Employee;
use App\EmpSalary;
use App\Memployee;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class salaryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function employee()
    {
        $employeeD = Employee::all();
        $salaryD = EmpSalary::all();
        return View('Employee_salary', compact('employeeD', 'salaryD'));
    }

    public function salary(Request $request)
    {

        $pp = $request->get('month');
        $id = $request->get('id');
        $oth = $request->get('othours');


        $employeeD = Employee::all();


//        $attendenceS = Memployee::all()->toArray();
//        foreach($attendenceS as $c) {
//            if ($c['id'] == $id && $c['month'] ==$pp ) {
//                return view('Employee_salary')->with('wrong','this input is wrong!!');
//            }
//            else{
//
//            }
//        }

        $attendenceS = Memployee::all();
        //dd($attendenceS);
        $count = sizeof($attendenceS);


        $user_info = DB::table('memployees')->where('month', $pp)
            ->select('id', DB::raw('count(*) as total'))
            ->groupBy('id')
            ->get();

        foreach ($attendenceS as $value) {
            $value;
            if ($value['month'] == $pp && $value['id'] == $id) {
                foreach ($user_info as $row) {
                    if ($row->id == $id) {
                        $total = $row->total;
                        if ($total == 25) {
                            $basic_salary = DB::table('employees')->where('id', $id)->select('salary')->get();
                            foreach ($basic_salary as $row1) {
                                $basicSalary = $row1->salary;
                                $salary = (($basicSalary / 25) * $total) + (($basicSalary / 25) * $oth);
                                $s = round($salary);
                                $salaryD = new EmpSalary([
                                    'id' => $id,
                                    'type' => $request->get('type'),
                                    'name' => $request->get('name'),
                                    'month' => $pp,
                                    'salary' => $s,
                                ]);
                                $salaryD->save();
                                return redirect()->back()->with(compact('employeeD', 'salaryD'));
                                //return View('Employee_salary', compact('employeeD','salaryD'))->with('success', 'The Form Data is successfully inserted to the Database!');

                            }
                        } else {
                            $user = DB::table('leaves')->find($id);
                            if ($user != null) {
                                $leaveTable = DB::table('leaves')->where('id', $id)->select('leve_type', 'nof_days', 'leaving_date')->get();
                                foreach ($leaveTable as $row3) {
                                    $leaveType = $row3->leve_type;
                                    $noOfLeave = $row3->nof_days;
                                    $Rmonth = $row3->leaving_date;
                                    $timestamp = strtotime($Rmonth);
                                    $month = date('M', $timestamp);
                                    $noOfLeaveT = DB::table('leave_types')->where('leve_type', $leaveType)->select('allow_leaves')->get();
                                    foreach ($noOfLeaveT as $row4) {
                                        $leave = $row4->allow_leaves;

                                        if ($noOfLeave <= $leave && $month == $pp) {
                                            $basic_salary = DB::table('employees')->where('id', $id)->select('salary')->get();
                                            foreach ($basic_salary as $row1) {
                                                $basicSalary = $row1->salary;
                                                $days = $total + $noOfLeave;
                                                //cal ot hours
                                                $salary = (($basicSalary / 25) * $days) + (($basicSalary / 25) * $oth);
                                                $s = round($salary);
                                                $salaryD = new EmpSalary([
                                                    'id' => $id,
                                                    'type' => $request->get('type'),
                                                    'name' => $request->get('name'),
                                                    'month' => $pp,
                                                    'salary' => $s,
                                                ]);
                                                $salaryD->save();
                                                return redirect()->back()->with(compact('employeeD', 'salaryD'));
                                                // return View('Employee_salary', compact('employeeD','salaryD'))->with('success', 'The Form Data is successfully inserted to the Database!');
                                            }
                                        } else {
                                            if ($month == $pp) {
                                                $basic_salary = DB::table('employees')->where('id', $id)->select('salary')->get();
                                                foreach ($basic_salary as $row1) {
                                                    $basicSalary = $row1->salary;
                                                    $days = $total + $leave;
                                                    //cal ot hours
                                                    $salary = (($basicSalary / 25) * $days) + (($basicSalary / 25) * $total);
                                                    $s = round($salary);
                                                    $salaryD = new EmpSalary([
                                                        'id' => $id,
                                                        'type' => $request->get('type'),
                                                        'name' => $request->get('name'),
                                                        'month' => $pp,
                                                        'salary' => $s,
                                                    ]);
                                                    $salaryD->save();
                                                    return redirect()->back()->with(compact('employeeD', 'salaryD'));
                                                    // return View('Employee_salary', compact('employeeD','salaryD'))->with('success', 'The Form Data is successfully inserted to the Database!');
                                                }

                                            } else {
                                                $basic_salary = DB::table('employees')->where('id', $id)->select('salary')->get();
                                                foreach ($basic_salary as $row1) {
                                                    $basicSalary = $row1->salary;
                                                    $salary = (($basicSalary / 25) * $total) + (($basicSalary / 25) * $oth);
                                                    $s = round($salary);
                                                    $salaryD = new EmpSalary([
                                                        'id' => $id,
                                                        'type' => $request->get('type'),
                                                        'name' => $request->get('name'),
                                                        'month' => $pp,
                                                        'salary' => $s,
                                                    ]);
                                                    $salaryD->save();
                                                    return redirect()->back()->with(compact('employeeD', 'salaryD'));
                                                    // return View('Employee_salary', compact('employeeD','salaryD'))->with('success', 'The Form Data is successfully inserted to the Database!');
                                                }
                                            }
                                        }
                                    }
                                }
                            } else {
                                $basic_salary = DB::table('employees')->where('id', $id)->select('salary')->get();
                                foreach ($basic_salary as $row1) {
                                    $basicSalary = $row1->salary;
                                    $salary = (($basicSalary / 25) * $total) + (($basicSalary / 25) * $oth);
                                    $s = round($salary);
                                    $salaryD = new EmpSalary([
                                        'id' => $id,
                                        'type' => $request->get('type'),
                                        'name' => $request->get('name'),
                                        'month' => $pp,
                                        'salary' => $s,
                                    ]);
                                    $salaryD->save();
                                    return redirect()->back()->with(compact('employeeD', 'salaryD'));
                                    // return View('Employee_salary', compact('employeeD','salaryD'))->with('success', 'The Form Data is successfully inserted to the Database!');
                                }
                            }

                        }

                    }

                }
//
            }
        }

        return redirect()->back()->with('wrong', 'please select valide month');


    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public
    function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     */
    public
    function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return Response
     */
    public
    function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return Response
     */
    public
    function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public
    function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        $empdata = EmpSalary::find($id);
        $empdata->delete();

        return redirect()->back();
    }
}