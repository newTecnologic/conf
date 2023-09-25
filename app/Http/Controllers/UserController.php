<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\UserCreateRequest;
use App\Http\Requests\UserEditRequest;
// use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('user_index'), 403);
        $users=User::paginate(5);
        return view('users.index', compact('users'));
    }

    public function create(){
        abort_if(Gate::denies('user_create'), 403);
        $roles = Role::all()->pluck('name', 'id');
        return view('users.create', compact('roles'));
    }

    public function store(UserCreateRequest $request){
        // $request->validate([
        //     'name' => 'required|min:3|max:5',
        //     'email' => 'required|email|unique:users',
        //     'password' => 'required'
        // ]);
        $user = User::create($request->only('name','email')
        +[
            'password' => bcrypt($request->input('password')),
        ]);
        $roles = $request->input('roles', []);
        $user->syncRoles($roles);
        return redirect()->route('users.show', $user->id)->with('success','Usuario agregado correctamente');
    }

    public function show(User $user){
        //$user=User::findOrFail($id);
        //dd($user);
        abort_if(Gate::denies('user_show'), 403);
        $user->load('roles');
        return view('users.show', compact('user'));
    }

    public function edit(User $user)
    {   abort_if(Gate::denies('user_edit'), 403);
        $roles = Role::all()->pluck('name', 'id');
        $user->load('roles');
        return view('users.edit', compact('user','roles'));
    }

    public function update(UserEditRequest $request, User $user)
    {
        // $user=User::findOrFail($id);
        
        $data=$request->only('name','email');
        $password=$request->input('password');
        if($password)
            $data['password'] = bcrypt($password);
        // if(trim($request->password)=='')
        // {
        //     $data=$request->except('password');
        // }
        // else{
        //     $data=$request->all();
        //     $data['password']=bcrypt($request->password);
        // }
        $user->update($data);
        $roles = $request->input('roles',[]);
        $user->syncRoles($roles);
        return redirect()->route('users.show', $user->id)->with('success', 'Usuario actualizado correctamente');
    }

    public function destroy(User $user)
    {
        abort_if(Gate::denies('user_destroy'), 403);
        if (auth()->user()->id == $user->id){
            return redirect()->route('users.index');
        }
        // else{
        //     return back()->with('success', 'Usted no puede ser eliminado');
        // }
        $user->delete();
        return back()->with('success', 'Usuario eliminado correctamente');
    }
}
