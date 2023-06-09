<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PackageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $packages = DB::select("SELECT * FROM `packages` WHERE user_id=" . auth()->user()->id);
        return view('admin.administrator.package')->with('packages', $packages);
    }

    public function share($id)
    {
        $employeesToShare = DB::select('SELECT * FROM users JOIN company_employee ON users.id = company_employee.employee WHERE company_employee.company=' . auth()->user()->id);
        $packageToShare   = DB::select("SELECT * FROM packages WHERE id=".$id);
        return view('admin.administrator.share')->with('packageToShare',$packageToShare)->with('employeesToShare',$employeesToShare);
    }

    public function sharePackage(Request $request, $id)
    {
        DB::statement('INSERT INTO user_package(`sharing_from`, `sharing_to`, `package_id`) VALUES(' . auth()->user()->id . ',' . $request->shareToEmployee . ','.$id.')');
        DB::statement('UPDATE packages SET user_id=' . $request->shareToEmployee . ' WHERE id=' . $id);
        $userShareTo = DB::select('SELECT name FROM users WHERE id=' . $request->shareToEmployee);
        return(redirect(route('package.index')))->with('success', 'The course has been shared to ' . $userShareTo[0]->name);
    }

    public function getAllPackages(){
        $packages =  DB::select("SELECT *, packages.user_id as user_id, (SELECT email FROM users WHERE id = user_id) AS userPackageHolder FROM packages ORDER BY created_at DESC");
        return view('admin.admin.packages.index')->with('packages', $packages);
    }

    public function searchPackage(Request $request){
        $package= DB::select("SELECT *, packages.user_id as user_id, (SELECT email FROM users WHERE id = user_id) AS userPackageHolder FROM packages WHERE packages.id=" . $request->id);
        if ($package === []){
            return redirect()->back()->with('success', 'No record has been found with this id');
        }
        return view('admin.admin.packages.search')->with('package',$package[0]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Package $package)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $package= Package::find($id);
        $employees = DB::select("SELECT *, company_employee.id as relationId FROM users JOIN company_employee ON users.id = company_employee.employee WHERE company_employee.company=" . $package->user_id);
        return view('admin.admin.packages.edit')->with('package', $package)->with('employees', $employees);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'course_name' => 'required|max:50',
            'status'      => 'required',
        ]);
        $order = Package::find($id);
        $order->update([
            'course_name'=>$request->course_name,
            'status'     =>$request->status,
            'user_id'    =>$request->owner,

        ]);
        return redirect(route('packages.index'))->with('success','Package has been updated');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $package = Package::find($id);
        $package->delete();
        return redirect()->back()->with('success', 'Package has been removed');
    }
}
