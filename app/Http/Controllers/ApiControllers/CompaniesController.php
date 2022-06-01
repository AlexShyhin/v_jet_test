<?php

namespace App\Http\Controllers\ApiControllers;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CompaniesController extends Controller {

    public function index(Request $request) {
        $user = Auth::user();

        $list = $user->companies;

        return response()->json(['status' => 'success', 'companies' => $list->toArray()]);
    }

    public function store(Request $request) {

        $validator = Validator::make($request->all(), [
            'title'         => 'required|string|min:3|max:50',
            'phone'         => 'required|string|unique:companies,phone',
            'description'   => 'string',
        ], [
            'title.required'        => 'Title name is required.',
            'title.min'             => 'Title name is too short.',
            'title.max'             => 'Title name is too long.',

            'phone.required'        => 'Phone number is required.',
            'phone.unique'          => 'Phone number is registered',
        ]);

        if ($validator->fails()) {
            $messages = $validator->getMessageBag()->getMessages();

            return response()->json(['status' => 'fail', 'error_messages' => $messages]);
        }

        $user = Auth::user();

        $company = Company::create([
            'user_id'       => $user->id,
            'title'         => $request->title,
            'phone'         => $request->phone,
            'description'   => $request->description ?? '',
        ]);

        if ($company) {
            return response()->json(['status' => 'success', 'company' => $company->toArray()]);
        }
        return response()->json(['status' => 'fail']);
    }
}
